#!/usr/bin/env bash
# Disposable WordPress 7.1 test site for the Redcliffe theme.
#   scripts/test/wp-env.sh up      build the theme zip, start the site, install + activate the theme
#   scripts/test/wp-env.sh update  rebuild the zip and install it over the running site (the upgrade path)
#   scripts/test/wp-env.sh seed    add sample sponsors (awkward logos of every kind) and enough articles for two pages
#   scripts/test/wp-env.sh wp ...  run WP-CLI against the site
#   scripts/test/wp-env.sh down    remove everything
# The site answers on http://localhost:${RAD_TEST_PORT:-8092} with pretty permalinks.
set -euo pipefail
HERE="$(cd "$(dirname "$0")" && pwd)"
ROOT="$(cd "$HERE/../.." && pwd)"
PORT="${RAD_TEST_PORT:-8092}"
IMAGE="${RAD_TEST_IMAGE:-wordpress:7-php8.3-apache}"
NET=rad-test-net; DB=rad-test-db; WP=rad-test-wp; VOL=rad-test-html

wpcli() {
  docker run --rm --network "$NET" -v "$VOL":/var/www/html -v "$ROOT/dist-wordpress":/package:ro --user 33 \
    -e WORDPRESS_DB_HOST="$DB" -e WORDPRESS_DB_USER=wp -e WORDPRESS_DB_PASSWORD=wp -e WORDPRESS_DB_NAME=wp \
    wordpress:cli wp "$@"
}

down() { docker rm -f "$WP" "$DB" >/dev/null 2>&1 || true; docker volume rm "$VOL" >/dev/null 2>&1 || true; }

up() {
  down
  (cd "$ROOT" && npm run --silent package:wp >/dev/null)
  docker network create "$NET" >/dev/null 2>&1 || true
  docker run -d --name "$DB" --network "$NET" -e MARIADB_ROOT_PASSWORD=root -e MARIADB_DATABASE=wp -e MARIADB_USER=wp -e MARIADB_PASSWORD=wp mariadb:11 >/dev/null
  docker run -d --name "$WP" --network "$NET" -p "$PORT":80 \
    -e WORDPRESS_DB_HOST="$DB" -e WORDPRESS_DB_USER=wp -e WORDPRESS_DB_PASSWORD=wp -e WORDPRESS_DB_NAME=wp \
    -e WORDPRESS_DEBUG=1 \
    -e WORDPRESS_CONFIG_EXTRA="define('WP_DEBUG_LOG', true); define('WP_DEBUG_DISPLAY', false); define('WP_ENVIRONMENT_TYPE','local');" \
    -v "$VOL":/var/www/html -v "$HERE/rad-test-login.php":/var/www/html/wp-content/mu-plugins/rad-test-login.php:ro \
    "$IMAGE" >/dev/null
  for i in $(seq 1 90); do curl -s -o /dev/null "http://localhost:$PORT/" && break; sleep 2; done
  sleep 6
  wpcli core install --url="http://localhost:$PORT" --title="Redcliffe Advisory" --admin_user=admin --admin_password=admin --admin_email=test@example.com --skip-email >/dev/null
  wpcli theme install /package/redcliffe-advisory.zip --activate >/dev/null
  wpcli rewrite structure '/%postname%/' --hard >/dev/null 2>&1 || true
  wpcli rewrite flush --hard >/dev/null 2>&1 || true
  echo "ready: http://localhost:$PORT  (wp $(wpcli core version))"
}

update() {
  (cd "$ROOT" && npm run --silent package:wp >/dev/null)
  wpcli theme install /package/redcliffe-advisory.zip --force --activate >/dev/null
  wpcli cache flush >/dev/null 2>&1 || true
  echo "updated to $(wpcli theme get redcliffe-advisory --field=version)"
}

seed() {
  docker run --rm --network "$NET" -v "$VOL":/var/www/html -v "$HERE/seed.php":/seed.php:ro --user 33 \
    -e WORDPRESS_DB_HOST="$DB" -e WORDPRESS_DB_USER=wp -e WORDPRESS_DB_PASSWORD=wp -e WORDPRESS_DB_NAME=wp \
    wordpress:cli wp eval-file /seed.php
}

case "${1:-}" in
  up) up ;;
  seed) seed ;;
  update) update ;;
  down) down ;;
  wp) shift; wpcli "$@" ;;
  *) echo "usage: $0 up|down|wp ..."; exit 1 ;;
esac
