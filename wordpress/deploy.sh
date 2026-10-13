#!/usr/bin/env bash
# ALKURAS — temos ir įskiepio įkėlimas į serverį per FTP.
# Prisijungimus įrašykite į wordpress/.ftp (į Git nekeliamas).
set -euo pipefail
cd "$(dirname "$0")"

if [ ! -f .ftp ]; then
  cat <<'MSG'
Nerastas .ftp failas. Sukurkite jį kataloge wordpress/ su tokiu turiniu:

FTP_HOST=ftp.serveriai.lt
FTP_USER=jusu_vartotojas
FTP_PASS=jusu_slaptazodis
FTP_PATH=/domains/alkuras.lt/public_html

Tada paleiskite dar kartą: ./deploy.sh
MSG
  exit 1
fi

set -a; . ./.ftp; set +a
: "${FTP_HOST:?Trūksta FTP_HOST}" "${FTP_USER:?Trūksta FTP_USER}" "${FTP_PASS:?Trūksta FTP_PASS}" "${FTP_PATH:?Trūksta FTP_PATH}"

if ! command -v lftp >/dev/null 2>&1; then
  echo "Reikia lftp. Įdiekite: brew install lftp"
  exit 1
fi

echo "Keliama į $FTP_HOST:$FTP_PATH"

lftp -u "$FTP_USER","$FTP_PASS" "$FTP_HOST" <<LFTP
set ssl:verify-certificate no
set ftp:ssl-allow yes
mirror -R --delete --verbose \
  --exclude-glob .DS_Store \
  --exclude-glob *.map \
  wp-content/themes/alkuras "$FTP_PATH/wp-content/themes/alkuras"
mirror -R --delete --verbose \
  --exclude-glob .DS_Store \
  wp-content/plugins/alkuras-core "$FTP_PATH/wp-content/plugins/alkuras-core"
bye
LFTP

echo "Baigta. Serveryje aktyvuokite įskiepį „Alkuras Core" ir temą „Alkuras"."
