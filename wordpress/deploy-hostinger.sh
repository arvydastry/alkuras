#!/usr/bin/env bash
# =============================================================================
# ALKURAS — diegimas į Hostinger (SSH + WP-CLI).
#
# Hostinger WordPress branduolys yra bendrinamas per symlink'us
# (public_html/wp-admin -> ../__wp__/wp-admin) ir jį atnaujina patys.
# Todėl keliame TIK savo turinį: temą, įskiepius, mediją.
#
#   ./deploy-hostinger.sh check    aplinkos patikra
#   ./deploy-hostinger.sh files    rsync: tema, įskiepiai, medija
#   ./deploy-hostinger.sh db       duomenų bazės importas (wp db import)
#   ./deploy-hostinger.sh setup    temos/įskiepių aktyvavimas, nuorodos
#   ./deploy-hostinger.sh lock     uždaro svetainę (įskiepis alkuras-maintenance)
#   ./deploy-hostinger.sh unlock   atidaro viešai
#
# PASTABA: Hostinger serveris (hws) nepalaiko .htaccess AuthType Basic —
# grąžina 200 net su neteisingu slaptažodžiu. Todėl apsauga daroma per
# WordPress įskiepį, o ne per .htpasswd.
#   ./deploy-hostinger.sh verify   patikra per HTTP (apeinant DNS)
#   ./deploy-hostinger.sh all      viskas iš eilės
# =============================================================================
set -euo pipefail
cd "$(dirname "$0")"

CONF=".hostinger"
KEY=".ssh/alkuras_deploy"
[ -f "$CONF" ] || { echo "Nerastas $CONF"; exit 1; }
set -a; . "./$CONF"; set +a
: "${SSH_HOST:?}" "${SSH_USER:?}" "${REMOTE_PATH:?}"

SSH_PORT="${SSH_PORT:-65002}"
SITE_URL="${SITE_URL:-https://alkuras.lt}"
HOST_IP="$SSH_HOST"
SSH_OPTS="-i $KEY -p $SSH_PORT -o StrictHostKeyChecking=accept-new -o BatchMode=yes"
SCP_OPTS="-i $KEY -P $SSH_PORT -o StrictHostKeyChecking=accept-new -o BatchMode=yes"
REMOTE="$SSH_USER@$SSH_HOST"
WP="cd '$REMOTE_PATH' && wp"

say()  { printf '\n\033[1m%s\033[0m\n' "$*"; }
run()  { ssh $SSH_OPTS "$REMOTE" "$@" 2>&1 | grep -v "post-quantum\|store now\|openssh.com/pq\|^\*\*" || true; }
rs()   { rsync -az --stats -e "ssh $SSH_OPTS" "$@"; }

# -----------------------------------------------------------------------------
cmd_check() {
  say "Aplinka"
  run "echo '  serveris: '\$(hostname); echo '  php: '\$(php -r 'echo PHP_VERSION;'); $WP core version --quiet 2>/dev/null | sed 's/^/  wordpress: /'"
  say "Katalogas"
  run "ls -A '$REMOTE_PATH' | tr '\n' ' '; echo"
}

# -----------------------------------------------------------------------------
cmd_files() {
  [ -d release/wp-content ] || { echo "Nerastas release/ — paleiskite ./build-release.sh"; exit 1; }

  say "Tema alkuras"
  rs --delete release/wp-content/themes/alkuras/ "$REMOTE:$REMOTE_PATH/wp-content/themes/alkuras/"

  say "Įskiepis alkuras-core"
  rs --delete release/wp-content/plugins/alkuras-core/ "$REMOTE:$REMOTE_PATH/wp-content/plugins/alkuras-core/"

  say "Įskiepis alkuras-maintenance (laikina apsauga)"
  rs --delete release/wp-content/plugins/alkuras-maintenance/ "$REMOTE:$REMOTE_PATH/wp-content/plugins/alkuras-maintenance/"

  say "WooCommerce"
  rs --delete release/wp-content/plugins/woocommerce/ "$REMOTE:$REMOTE_PATH/wp-content/plugins/woocommerce/"

  say "Medija (uploads)"
  rs release/wp-content/uploads/ "$REMOTE:$REMOTE_PATH/wp-content/uploads/"

  say "Kalbos"
  rs release/wp-content/languages/ "$REMOTE:$REMOTE_PATH/wp-content/languages/"
}

# -----------------------------------------------------------------------------
cmd_db() {
  [ -f dist/alkuras-db.sql ] || { echo "Nerasta dist/alkuras-db.sql"; exit 1; }

  local TMP="$REMOTE_PATH/.alk-tmp"
  run "mkdir -p '$TMP'"

  say "Atsarginė esamos bazės kopija"
  run "$WP db export '$TMP/pries-importa.sql' --quiet && echo '  padaryta'"
  mkdir -p deploy/backup
  scp $SCP_OPTS -q "$REMOTE:$TMP/pries-importa.sql" deploy/backup/db-pries-importa.sql 2>/dev/null \
    && echo "  parsisiųsta: deploy/backup/db-pries-importa.sql"

  say "Importuojama duomenų bazė ($(du -h dist/alkuras-db.sql | cut -f1))"
  gzip -c dist/alkuras-db.sql | ssh $SSH_OPTS "$REMOTE" "cat > '$TMP/import.sql.gz' && gunzip -f '$TMP/import.sql.gz'"
  run "$WP db import '$TMP/import.sql' --quiet && echo '  importuota'"
  run "rm -rf '$TMP'"

  say "Patikra"
  run "$WP db query 'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE()' --skip-column-names | sed 's/^/  lentelių: /'
       $WP option get siteurl | sed 's/^/  siteurl: /'
       $WP post list --post_type=product --post_status=publish --format=count | sed 's/^/  prekių: /'"
}

# -----------------------------------------------------------------------------
cmd_setup() {
  say "LiteSpeed laikinai išjungiamas (kad netrukdytų tikrinant)"
  run "$WP plugin deactivate litespeed-cache --quiet 2>/dev/null; rm -f '$REMOTE_PATH/wp-content/object-cache.php'; echo '  išjungta, object-cache.php pašalintas'"

  say "Aktyvuojami įskiepiai ir tema"
  run "$WP plugin activate alkuras-core woocommerce --quiet; $WP theme activate alkuras --quiet
       echo '  aktyvūs įskiepiai: '\$($WP plugin list --status=active --field=name | tr '\n' ' ')
       echo '  tema: '\$($WP theme list --status=active --field=name)"

  say "Nuorodų struktūra ir talpyklos"
  run "$WP rewrite structure '/%postname%/' --hard --quiet; $WP rewrite flush --hard --quiet; $WP cache flush --quiet 2>/dev/null; $WP transient delete --all --quiet 2>/dev/null; echo '  atnaujinta'"
}

# -----------------------------------------------------------------------------
cmd_lock() {
  say "Uždaroma svetainė (įskiepis alkuras-maintenance)"
  run "cd '$REMOTE_PATH' && wp plugin activate alkuras-maintenance --quiet && echo '  uždaryta — svečiai mato „svetainė ruošiama“'"
  cmd_purge
}

cmd_unlock() {
  say "Svetainė daroma vieša"
  run "cd '$REMOTE_PATH' && wp plugin deactivate alkuras-maintenance --quiet && wp option update blog_public 1 --quiet && echo '  vieša, indeksavimas įjungtas'"
  cmd_purge
}

cmd_purge() {
  say "Valoma talpykla"
  run "cd '$REMOTE_PATH' && wp cache flush --quiet 2>/dev/null; wp transient delete --all --quiet 2>/dev/null; rm -rf wp-content/litespeed/* wp-content/cache/* 2>/dev/null; echo '  išvalyta'"
}

# -----------------------------------------------------------------------------
cmd_verify() {
  say "Patikra per serverio IP (apeinant DNS, nes domenas dar rodo į seną hostingą)"
  local host="${SITE_URL#https://}"; host="${host#http://}"
  local auth=""
  for p in "/" "/karjerai/" "/produkcija/" "/skaiciuokles/" "/preke/skalda-4-16/" "/krepselis/" "/wp-login.php"; do
    printf "  %-24s " "$p"
    curl -sk -o /dev/null -w "%{http_code}\n" --max-time 30 \
      --resolve "$host:443:$HOST_IP" --resolve "$host:80:$HOST_IP" $auth "$SITE_URL$p"
  done
  printf "  %-24s " "pavadinimas"
  curl -sk --max-time 30 --resolve "$host:443:$HOST_IP" $auth "$SITE_URL/" \
    | grep -o '<title>[^<]*' | head -1 | sed 's/<title>//'
}

# -----------------------------------------------------------------------------
case "${1:-check}" in
  check)  cmd_check ;;
  files)  cmd_files ;;
  db)     cmd_db ;;
  setup)  cmd_setup ;;
  lock)   cmd_lock ;;
  unlock) cmd_unlock ;;
  purge)  cmd_purge ;;
  verify) cmd_verify ;;
  all)    cmd_files; cmd_db; cmd_setup; cmd_lock; cmd_verify ;;
  *)      echo "check | files | db | setup | lock | unlock | purge | verify | all"; exit 1 ;;
esac
