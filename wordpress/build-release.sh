#!/usr/bin/env bash
# Surenka 'release' katalogą iš veikiančio lokalaus WordPress (Docker).
set -euo pipefail
cd "$(dirname "$0")"

if ! docker ps --format '{{.Names}}' | grep -q '^alkuras-wordpress-1$'; then
  echo "Konteineris alkuras-wordpress-1 nepaleistas. Paleiskite: docker compose up -d"
  exit 1
fi

echo "Renkamas release katalogas…"
rm -rf release && mkdir -p release
docker exec alkuras-wordpress-1 tar -cf - -C /var/www/html \
  --exclude='./wp-content/cache' \
  --exclude='./wp-content/upgrade' \
  --exclude='./wp-content/themes/twentytwentythree' \
  --exclude='./wp-content/themes/twentytwentyfour' \
  --exclude='./wp-content/plugins/akismet' \
  --exclude='./wp-content/plugins/hello.php' \
  --exclude='./wp-content/debug.log' \
  --exclude='./wp-config.php' \
  --exclude='./wp-config-docker.php' \
  --exclude='./.htaccess' \
  --exclude='./.maintenance' \
  . | tar -xf - -C release
find release -name '.DS_Store' -delete 2>/dev/null || true

echo "Eksportuojama duomenų bazė…"
mkdir -p dist
docker compose run --rm cli search-replace 'http://localhost:8099' 'https://alkuras.lt' --all-tables --precise --quiet >/dev/null
docker compose run --rm cli db export /var/www/html/alk.sql --default-character-set=utf8mb4 --add-drop-table --quiet >/dev/null
docker cp alkuras-wordpress-1:/var/www/html/alk.sql /tmp/alk-raw.sql
docker exec alkuras-wordpress-1 rm -f /var/www/html/alk.sql
docker compose run --rm cli search-replace 'https://alkuras.lt' 'http://localhost:8099' --all-tables --precise --quiet >/dev/null
sed -e '/enable the sandbox mode/d' -e 's/utf8mb4_unicode_520_ci/utf8mb4_unicode_ci/g' /tmp/alk-raw.sql > dist/alkuras-db.sql
rm -f /tmp/alk-raw.sql

echo
echo "Paruošta:"
echo "  release/            $(du -sh release | cut -f1), $(find release -type f | wc -l | tr -d ' ') failų"
echo "  dist/alkuras-db.sql $(du -h dist/alkuras-db.sql | cut -f1), $(grep -c '^CREATE TABLE' dist/alkuras-db.sql) lentelės"
