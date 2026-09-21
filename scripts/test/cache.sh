#!/usr/bin/env bash
# What the theme asks the page cache to do when the owner changes something.
#
#   scripts/test/cache.sh      (after `wp-env.sh up`)
#
# The hosting caches finished pages; LiteSpeed clears a page when that page is edited but not when a sponsor, a
# tier, a menu, a Customizer setting or an article changes what ANOTHER page shows. This checks the theme asks for
# those pages to be cleared, and asks for short-lived copies, using a stand-in for the plugin (rad-test-litespeed.php).
set -euo pipefail
HERE="$(cd "$(dirname "$0")" && pwd)"
PORT="${RAD_TEST_PORT:-8092}"
BASE="http://localhost:$PORT"
failed=0
wp() { bash "$HERE/wp-env.sh" wp "$@"; }
log() { docker exec rad-test-wp sh -c 'cat /var/www/html/wp-content/rad-cache.log 2>/dev/null || true'; }
mark() { MARK="$(log | wc -l | tr -d ' ')"; }
since() { log | tail -n +"$((MARK + 1))" | sort -u | tr '\n' ' '; }
expect() { # name, pattern that must appear, [pattern that must not]
  local got; got="$(since)"
  if echo "$got" | grep -q -- "$2" && { [ -z "${3:-}" ] || ! echo "$got" | grep -q -- "$3"; }; then echo "PASS  $1"; else echo "FAIL  $1 — asked for: ${got:-nothing}"; failed=$((failed + 1)); fi
}
expect_nothing() { local got; got="$(since)"; if [ -z "$got" ]; then echo "PASS  $1"; else echo "FAIL  $1 — asked for: $got"; failed=$((failed + 1)); fi; }

docker exec rad-test-wp sh -c 'rm -f /var/www/html/wp-content/rad-cache.log'

mark; curl -s -o /dev/null "$BASE/summit/"
expect "a visitor's page is cached for ten minutes only" "ttl:600"
mark; curl -s -o /dev/null "$BASE/summit/?rad_test_login=1"
expect_nothing "a logged-in owner's page is left alone"

mark; ID="$(wp post create --post_type=rad_sponsor --post_title='Cache Test Co' --post_status=publish --porcelain)"
expect "adding a sponsor clears the Summit page" "purge_post:summit" "purge_all"
mark; wp post update "$ID" --post_title='Cache Test Co Ltd' >/dev/null
expect "changing a sponsor clears the Summit page" "purge_post:summit"
mark; wp eval "wp_trash_post($ID);" >/dev/null   # wp-cli's own 'post delete' refuses to trash this post type; the admin's Trash link uses this
expect "trashing a sponsor clears the Summit page" "purge_post:summit"
mark; wp eval "wp_untrash_post($ID);" >/dev/null
expect "restoring a sponsor clears the Summit page" "purge_post:summit"
mark; wp post delete "$ID" --force >/dev/null
expect "deleting a sponsor clears the Summit page" "purge_post:summit"

mark; TIER="$(wp term create rad_tier 'Media partner' --porcelain)"
expect "adding a tier clears the Summit page" "purge_post:summit"
mark; wp term update rad_tier "$TIER" --name='Media partners' >/dev/null
expect "renaming a tier clears the Summit page" "purge_post:summit"
mark; wp term delete rad_tier "$TIER" >/dev/null
expect "removing a tier clears the Summit page" "purge_post:summit"

mark; wp theme mod set rad_home_hero_title "A new headline $(date +%s)" >/dev/null
expect "publishing a Customizer change clears every page" "purge_all"
mark; MENU="$(wp menu create "Cache test $(date +%s)" --porcelain)"
expect "changing a menu clears every page" "purge_all"
mark; wp menu item add-custom "$MENU" "Extra" "https://example.com/" >/dev/null
expect "adding something to a menu clears every page" "purge_all"
wp menu delete "$MENU" >/dev/null

PAGE="$(wp post create --post_type=page --post_title='Cache test page' --post_status=publish --porcelain)"
mark; wp post update "$PAGE" --post_title='Cache test page, renamed' >/dev/null
expect "renaming a page clears every page (menus and links)" "purge_all"
mark; wp post update "$PAGE" --post_content='<p>only the words changed</p>' >/dev/null
expect_nothing "editing only a page's words leaves the other pages alone"
mark; wp eval "wp_trash_post($PAGE);" >/dev/null
expect "trashing a page clears every page" "purge_all"
wp post delete "$PAGE" --force >/dev/null

mark; POST="$(wp post create --post_title='Cache test article' --post_status=publish --post_content='<p>x</p>' --porcelain)"
expect "publishing an article clears the Articles page" "purge_post:articles"
mark; wp post update "$POST" --post_title='Cache test article, edited' >/dev/null
expect "editing an article clears the Articles page" "purge_post:articles"
mark; wp post delete "$POST" --force >/dev/null
expect "deleting an article clears the Articles page" "purge_post:articles"
mark; CAT="$(wp term create category 'Cache category' --porcelain)"
expect "adding a category clears the Articles page" "purge_post:articles"
wp term delete category "$CAT" >/dev/null

mark; wp option update rad_cache_test_unrelated "$(date +%s)" >/dev/null
expect_nothing "an unrelated setting clears nothing"
wp option delete rad_cache_test_unrelated >/dev/null

echo
[ "$failed" = 0 ] && echo "all passed" || { echo "$failed failed"; exit 1; }
