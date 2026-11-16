#!/usr/bin/env bash
# =============================================================================
# ALKURAS — diegimas į Hostinger per SSH.
#
#   ./deploy-hostinger.sh check     tik patikrina prisijungimą ir serverį
#   ./deploy-hostinger.sh files     įkelia / atnaujina failus (rsync)
#   ./deploy-hostinger.sh db        importuoja duomenų bazę
#   ./deploy-hostinger.sh config    sugeneruoja wp-config.php ir .htaccess
#   ./deploy-hostinger.sh lock      uždeda slaptažodžio apsaugą
#   ./deploy-hostinger.sh unlock    nuima slaptažodžio apsaugą (paleidimui)
#   ./deploy-hostinger.sh all       viskas iš eilės (pirmam diegimui)
#   ./deploy-hostinger.sh verify    patikrina, ar svetainė veikia
# =============================================================================
set -euo pipefail
cd "$(dirname "$0")"

CONF=".hostinger"
KEY=".ssh/alkuras_deploy"

if [ ! -f "$CONF" ]; then
  echo "Nerastas $CONF. Nukopijuokite .hostinger.example į .hostinger ir užpildykite."
  exit 1
fi
set -a; . "./$CONF"; set +a
: "${SSH_HOST:?Trūksta SSH_HOST}" "${SSH_USER:?Trūksta SSH_USER}" "${REMOTE_PATH:?Trūksta REMOTE_PATH}"

SSH_PORT="${SSH_PORT:-65002}"
SITE_URL="${SITE_URL:-https://alkuras.lt}"
SSH_OPTS="-i $KEY -p $SSH_PORT -o StrictHostKeyChecking=accept-new -o BatchMode=yes"
REMOTE="$SSH_USER@$SSH_HOST"

say() { printf '\n\033[1m%s\033[0m\n' "$*"; }
run() { ssh $SSH_OPTS "$REMOTE" "$@"; }

# -----------------------------------------------------------------------------
cmd_check() {
  say "1. SSH prisijungimas"
  run 'echo "  prisijungta: $(whoami)@$(hostname)"; echo "  namų katalogas: $HOME"'

  say "2. Serverio aplinka"
  run 'echo "  php: $(php -v 2>/dev/null | head -1 || echo nera)"
       echo "  mysql klientas: $(command -v mysql >/dev/null && mysql --version || echo nera)"
       echo "  wp-cli: $(command -v wp >/dev/null && wp --version --allow-root 2>/dev/null || echo nera)"
       echo "  rsync: $(command -v rsync >/dev/null && rsync --version | head -1 || echo nera)"'

  say "3. Svetainės katalogas"
  run "if [ -d '$REMOTE_PATH' ]; then
         echo '  katalogas yra'
         echo \"  failų: \$(find '$REMOTE_PATH' -type f 2>/dev/null | wc -l)\"
         ls -A '$REMOTE_PATH' | head -12 | sed 's/^/    /'
       else
         echo '  NĖRA — bus sukurtas'
       fi"

  say "4. Laisva vieta"
  run "df -h '$HOME' 2>/dev/null | tail -1 | awk '{print \"  \" \$4 \" laisva iš \" \$2}'"
}

# -----------------------------------------------------------------------------
cmd_files() {
  if [ ! -d release ]; then
    echo "Nerastas 'release' katalogas. Paleiskite ./build-release.sh"
    exit 1
  fi
  say "Keliami failai į $REMOTE_PATH"
  run "mkdir -p '$REMOTE_PATH'"
  rsync -az --delete --info=stats1,progress2 \
    -e "ssh $SSH_OPTS" \
    --exclude 'wp-config.php' \
    --exclude '.htaccess' \
    --exclude '.htpasswd' \
    --exclude '.well-known' \
    --exclude 'wp-content/uploads/cache' \
    --exclude '.DS_Store' \
    release/ "$REMOTE:$REMOTE_PATH/"
  echo "  failai sukelti"
}

# -----------------------------------------------------------------------------
cmd_config() {
  : "${DB_NAME:?Trūksta DB_NAME}" "${DB_USER:?Trūksta DB_USER}" "${DB_PASS:?Trūksta DB_PASS}"
  say "Generuojamas wp-config.php"

  local salts tmp
  salts=$(curl -s --max-time 20 https://api.wordpress.org/secret-key/1.1/salt/ || true)
  if [ -z "$salts" ]; then
    salts=$(for k in AUTH_KEY SECURE_AUTH_KEY LOGGED_IN_KEY NONCE_KEY AUTH_SALT SECURE_AUTH_SALT LOGGED_IN_SALT NONCE_SALT; do
      printf "define( '%s', '%s' );\n" "$k" "$(openssl rand -base64 48 | tr -d "\n'\\\\")"
    done)
  fi

  tmp=$(mktemp)
  cat > "$tmp" <<PHPEOF
<?php
/** WordPress konfigūracija — UAB „Alkuras". Sugeneruota diegimo metu. */

define( 'DB_NAME',     '$DB_NAME' );
define( 'DB_USER',     '$DB_USER' );
define( 'DB_PASSWORD', '$DB_PASS' );
define( 'DB_HOST',     '${DB_HOST:-localhost}' );
define( 'DB_CHARSET',  'utf8mb4' );
define( 'DB_COLLATE',  '' );

\$table_prefix = 'wp_';

$salts

define( 'WP_HOME',    '$SITE_URL' );
define( 'WP_SITEURL', '$SITE_URL' );

define( 'DISALLOW_FILE_EDIT', true );
define( 'WP_MEMORY_LIMIT', '256M' );
define( 'WP_MAX_MEMORY_LIMIT', '512M' );
define( 'WP_AUTO_UPDATE_CORE', 'minor' );
define( 'WP_POST_REVISIONS', 10 );
define( 'EMPTY_TRASH_DAYS', 30 );

define( 'WP_DEBUG', false );
define( 'WP_DEBUG_LOG', false );
define( 'WP_DEBUG_DISPLAY', false );
@ini_set( 'display_errors', 0 );

if ( ! empty( \$_SERVER['HTTP_X_FORWARDED_PROTO'] ) && 'https' === \$_SERVER['HTTP_X_FORWARDED_PROTO'] ) {
	\$_SERVER['HTTPS'] = 'on';
}

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}
require_once ABSPATH . 'wp-settings.php';
PHPEOF

  scp $SSH_OPTS -q "$tmp" "$REMOTE:$REMOTE_PATH/wp-config.php"
  rm -f "$tmp"
  run "chmod 600 '$REMOTE_PATH/wp-config.php'"
  echo "  wp-config.php įkeltas (teisės 600)"

  say "Generuojamas .htaccess"
  scp $SSH_OPTS -q deploy/htaccess-public "$REMOTE:$REMOTE_PATH/.htaccess"
  echo "  .htaccess įkeltas"
}

# -----------------------------------------------------------------------------
cmd_db() {
  : "${DB_NAME:?Trūksta DB_NAME}" "${DB_USER:?Trūksta DB_USER}" "${DB_PASS:?Trūksta DB_PASS}"
  if [ ! -f dist/alkuras-db.sql ]; then
    echo "Nerasta dist/alkuras-db.sql"
    exit 1
  fi
  say "Importuojama duomenų bazė ($(du -h dist/alkuras-db.sql | cut -f1))"
  gzip -c dist/alkuras-db.sql | \
    ssh $SSH_OPTS "$REMOTE" \
      "gunzip | MYSQL_PWD='$DB_PASS' mysql --default-character-set=utf8mb4 -h '${DB_HOST:-localhost}' -u '$DB_USER' '$DB_NAME'"
  echo "  importuota"

  say "Patikra"
  run "MYSQL_PWD='$DB_PASS' mysql -N -B -h '${DB_HOST:-localhost}' -u '$DB_USER' '$DB_NAME' \
       -e \"SELECT CONCAT('  lentelių: ', COUNT(*)) FROM information_schema.tables WHERE table_schema='$DB_NAME';
            SELECT CONCAT('  siteurl: ', option_value) FROM wp_options WHERE option_name='siteurl';
            SELECT CONCAT('  prekių: ', COUNT(*)) FROM wp_posts WHERE post_type='product' AND post_status='publish';\""
}

# -----------------------------------------------------------------------------
cmd_lock() {
  say "Uždedama slaptažodžio apsauga"
  scp $SSH_OPTS -q deploy/.htpasswd "$REMOTE:$REMOTE_PATH/.htpasswd"
  run "sed -e 's#AUTH_PATH_PLACEHOLDER#$REMOTE_PATH/.htpasswd#' > '$REMOTE_PATH/.htaccess'" < deploy/htaccess-locked
  run "mkdir -p '$REMOTE_PATH/.well-known' && printf 'Satisfy any\nRequire all granted\n' > '$REMOTE_PATH/.well-known/.htaccess'"
  echo "  svetainė uždaryta (vartotojas: alkuras)"
}

cmd_unlock() {
  say "Nuimama slaptažodžio apsauga"
  scp $SSH_OPTS -q deploy/htaccess-public "$REMOTE:$REMOTE_PATH/.htaccess"
  run "rm -f '$REMOTE_PATH/.htpasswd'"
  echo "  svetainė vieša"
}

# -----------------------------------------------------------------------------
cmd_verify() {
  say "Patikra per HTTP"
  local auth=""
  if [ -f deploy/.htpasswd-plain ]; then
    auth="-u alkuras:$(cat deploy/.htpasswd-plain)"
  fi
  for p in "/" "/karjerai/" "/produkcija/" "/skaiciuokles/" "/krepselis/" "/wp-login.php"; do
    printf "  %-18s " "$p"
    curl -sk -o /dev/null -w "%{http_code}\n" --max-time 25 $auth "$SITE_URL$p"
  done
  printf "  %-18s " "pavadinimas"
  curl -sk --max-time 25 $auth "$SITE_URL/" | grep -o '<title>[^<]*' | head -1 | sed 's/<title>//'
}

# -----------------------------------------------------------------------------
case "${1:-check}" in
  check)  cmd_check ;;
  files)  cmd_files ;;
  config) cmd_config ;;
  db)     cmd_db ;;
  lock)   cmd_lock ;;
  unlock) cmd_unlock ;;
  verify) cmd_verify ;;
  all)    cmd_check; cmd_files; cmd_config; cmd_db; cmd_lock; cmd_verify ;;
  *)      echo "Komandos: check | files | config | db | lock | unlock | verify | all"; exit 1 ;;
esac
