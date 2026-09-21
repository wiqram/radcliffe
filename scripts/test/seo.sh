#!/usr/bin/env bash
# What search engines and shared links see, and what happens when the owner changes a page's address.
#
#   scripts/test/seo.sh      (after `wp-env.sh up`)
#
# Titles, descriptions, share tags, the firm's name when the Site Title is only a web address, the Customizer
# overrides, and the nine pages when one of them is given a new address or thrown away.
set -euo pipefail
HERE="$(cd "$(dirname "$0")" && pwd)"
PORT="${RAD_TEST_PORT:-8092}"
BASE="http://localhost:$PORT"
failed=0
wp() { bash "$HERE/wp-env.sh" wp "$@"; }
check() { if [ "$2" = "1" ]; then echo "PASS  $1"; else echo "FAIL  $1${3:+ — $3}"; failed=$((failed + 1)); fi; }
count() { grep -o "$1" | wc -l | tr -d ' '; }
has() { grep -q -- "$1" && echo 1 || echo 0; }
head_of() { curl -s "$BASE$1" | sed -n '/<head>/,/<\/head>/p'; }
title_of() { head_of "$1" | grep -o '<title>[^<]*</title>' | sed 's/<[^>]*>//g' | python3 -c 'import html,sys; print(html.unescape(sys.stdin.read().strip()))'; }

ORIGINAL_NAME="$(wp option get blogname)"
ORIGINAL_SLUG="$(wp post list --post_type=page --name=summit --field=ID 2>/dev/null || true)"
ORIGINAL_SLUG="${ORIGINAL_SLUG:-$(wp post list --post_type=page --meta_key=_wp_page_template --meta_value=template-summit.php --field=ID | head -1)}"
ETHICS="$(wp post list --post_type=page --name=ethics --field=ID)"
cleanup() {
  wp option update blogname "$ORIGINAL_NAME" >/dev/null 2>&1 || true
  wp post update "$ORIGINAL_SLUG" --post_name=summit >/dev/null 2>&1 || true
  wp post update "$ETHICS" --post_status=publish >/dev/null 2>&1 || true
  for k in title description; do for s in home practice who city summit agenda articles ethics contact; do wp theme mod remove "rad_seo_${k}_$s" >/dev/null 2>&1 || true; done; done
  wp theme mod remove rad_seo_share_image >/dev/null 2>&1 || true
  [ -n "${POST:-}" ] && wp post delete "$POST" --force >/dev/null 2>&1 || true
  return 0
}
trap cleanup EXIT

wp option update blogname "test-site-885406.hostingersite.com" >/dev/null   # what a new Hostinger site is called

# 1. Every page carries the tags, once.
bad=""
for p in / /practice/ /who/ /city/ /summit/ /agenda/ /articles/ /ethics/ /contact/; do
  h="$(head_of "$p")"
  [ "$(echo "$h" | count '<title>')" = 1 ] && [ "$(echo "$h" | count '<meta name="description"')" = 1 ] && [ "$(echo "$h" | count 'property="og:title"')" = 1 ] \
    && [ "$(echo "$h" | count 'property="og:image"')" = 1 ] && [ "$(echo "$h" | count 'property="og:url"')" = 1 ] && [ "$(echo "$h" | count 'name="twitter:card"')" = 1 ] \
    && [ "$(echo "$h" | count 'rel="canonical"')" -le 1 ] || bad="$bad $p"
done
check "every page has one title, one description and the share tags" "$([ -z "$bad" ] && echo 1 || echo 0)" "$bad"
check "the Summit page keeps its designed title" "$([ "$(title_of /summit/)" = 'The City Quantum & AI Summit · 7 October 2026 — Redcliffe Advisory' ] && echo 1 || echo 0)" "$(title_of /summit/)"
check "the Summit description is about the Summit" "$(head_of /summit/ | has 'name="description" content="The City Quantum &amp; AI Summit, sixth anniversary, 7 October 2026')"
check "the share tags name the page without the firm's name on the end" "$(head_of /summit/ | has 'property="og:title" content="The City Quantum &amp; AI Summit · 7 October 2026" ')"
check "the share picture is the Summit hall photograph until another is chosen" "$(head_of /summit/ | has 'property="og:image" content="[^"]*/images/share-default.jpg"')"
check "the shared picture file is there" "$([ "$(curl -s -o /dev/null -w '%{http_code}' "$BASE/wp-content/themes/redcliffe-advisory/images/share-default.jpg")" = 200 ] && echo 1 || echo 0)"

# 2. An article, on a site still called by its temporary address.
POST="$(wp post create --post_title='Quantum "supremacy" & the City' --post_status=publish --post_excerpt='Why the City cares, in a sentence.' --post_content='<p>Body of the article.</p>' --porcelain)"
SLUG="$(wp post get "$POST" --field=post_name)"
check "an article's title ends with the firm's name, not the temporary address" "$([ "$(title_of "/$SLUG/")" = 'Quantum “supremacy” & the City — Redcliffe Advisory' ] && echo 1 || echo 0)" "$(title_of "/$SLUG/")"
h="$(head_of "/$SLUG/")"
check "an article says it is an article, with its excerpt as the description" "$(echo "$h" | grep -q 'property="og:type" content="article"' && echo "$h" | grep -q 'name="description" content="Why the City cares, in a sentence."' && echo 1 || echo 0)"
check "quotes and ampersands in a title cannot break the tags" "$([ "$(echo "$h" | count 'property="og:title" content="Quantum')" = 1 ] && ! echo "$h" | grep -q 'content="Quantum "' && echo 1 || echo 0)"
check "the enquiry email is sent from the firm's name, not the temporary address" "$([ "$(wp eval 'echo rad_mail_from_name();')" = 'Redcliffe Advisory' ] && echo 1 || echo 0)"

wp option update blogname "Acme Partners Ltd" >/dev/null
check "a real Site Title is used when there is one" "$([ "$(title_of "/$SLUG/")" = 'Quantum “supremacy” & the City — Acme Partners Ltd' ] && echo 1 || echo 0)" "$(title_of "/$SLUG/")"
check "and in the enquiry email" "$([ "$(wp eval 'echo rad_mail_from_name();')" = 'Acme Partners Ltd' ] && echo 1 || echo 0)"
wp option update blogname "redcliffeadvisory.com" >/dev/null
check "a Site Title that is only a web address is replaced by the firm's name" "$([ "$(wp eval 'echo rad_brand_name();')" = 'Redcliffe Advisory' ] && echo 1 || echo 0)"
wp option update blogname "Redcliffe Advisory" >/dev/null

# 3. What the owner can change.
wp theme mod set rad_seo_title_summit 'Summit 2026 & "Quantum" — Redcliffe' >/dev/null
wp theme mod set rad_seo_description_summit 'Tom'"'"'s "own" words & more.' >/dev/null
check "a title typed in the Customizer replaces the designed one" "$([ "$(title_of /summit/)" = 'Summit 2026 & "Quantum" — Redcliffe' ] && echo 1 || echo 0)" "$(title_of /summit/)"
check "a description typed in the Customizer replaces the designed one, safely escaped" "$(head_of /summit/ | has 'name="description" content="Tom&#039;s &quot;own&quot; words &amp; more."')"
wp theme mod remove rad_seo_title_summit >/dev/null; wp theme mod remove rad_seo_description_summit >/dev/null
check "clearing them puts the designed ones back" "$([ "$(title_of /summit/)" = 'The City Quantum & AI Summit · 7 October 2026 — Redcliffe Advisory' ] && echo 1 || echo 0)"

FEATURED="$(wp media import "$(docker exec rad-test-wp sh -c 'ls /var/www/html/wp-content/themes/redcliffe-advisory/images/city-skyline.webp')" --porcelain 2>/dev/null || true)"
if [ -n "$FEATURED" ]; then
  wp theme mod set rad_seo_share_image "$FEATURED" >/dev/null
  check "a share picture chosen in the Customizer is used" "$(head_of /summit/ | has 'property="og:image" content="[^"]*city-skyline')"
  wp post meta update "$POST" _thumbnail_id "$FEATURED" >/dev/null
  check "an article's featured image wins over it" "$(head_of "/$SLUG/" | has 'property="og:image" content="[^"]*city-skyline')"
  wp theme mod remove rad_seo_share_image >/dev/null
  wp post meta delete "$POST" _thumbnail_id >/dev/null
  wp post delete "$FEATURED" --force >/dev/null
fi

check "an SEO plugin being active switches these tags off" "$([ "$(wp eval 'define( "WPSEO_VERSION", "1" ); echo rad_seo_plugin_active() ? "yes" : "no";')" = yes ] && echo 1 || echo 0)"

# 4. The owner gives the Summit page a new address.
wp post update "$ORIGINAL_SLUG" --post_name=summit-2026 >/dev/null
check "the Summit page answers on its new address" "$([ "$(curl -s -o /dev/null -w '%{http_code}' "$BASE/summit-2026/")" = 200 ] && echo 1 || echo 0)"
check "it is still the Summit page (page hook, title)" "$(curl -s "$BASE/summit-2026/" | grep -q 'data-page="summit"' && [ "$(title_of /summit-2026/)" = 'The City Quantum & AI Summit · 7 October 2026 — Redcliffe Advisory' ] && echo 1 || echo 0)"
home="$(curl -s "$BASE/")"
check "links to it from the home page follow the new address" "$([ "$(echo "$home" | count 'href="[^"]*/summit-2026/"')" -ge 2 ] && ! echo "$home" | grep -q 'href="[^"]*/summit/"' && echo 1 || echo 0)" "$(echo "$home" | grep -o 'href="[^"]*/summit[^"]*"' | sort | uniq -c | tr '\n' ' ')"
r="$(curl -s -o /dev/null -w '%{http_code} %{redirect_url}' "$BASE/the-city-quantum-and-ai-summit-2026/")"
check "the old Squarespace address goes to the new one" "$(echo "$r" | has "^301 .*/summit-2026/")" "$r"
check "the theme still finds the page (sponsors, buttons and cache clearing rely on this)" "$([ "$(wp eval 'echo rad_design_page( "summit" )->post_name;')" = summit-2026 ] && echo 1 || echo 0)"
wp post update "$ORIGINAL_SLUG" --post_name=summit >/dev/null

# 5. The owner throws a page away.
wp post update "$ETHICS" --post_status=trash >/dev/null
check "with the Ethics page in the Trash, links to it go to the homepage, not to an error" "$([ "$(curl -s -o /dev/null -w '%{http_code}' "$BASE/")" = 200 ] && ! curl -s "$BASE/" | grep -q 'href="[^"]*/ethics/"' && echo 1 || echo 0)"
wp post update "$ETHICS" --post_status=publish >/dev/null
check "restoring it puts the links back" "$(curl -s "$BASE/" | has 'href="[^"]*/ethics/"')"

log="$(docker exec rad-test-wp sh -c 'cat /var/www/html/wp-content/debug.log 2>/dev/null || true' | grep -i 'redcliffe-advisory' | grep -Ei 'warning|notice|fatal|deprecated' || true)"
check "no PHP warnings from the theme" "$([ -z "$log" ] && echo 1 || echo 0)" "$log"

echo
[ "$failed" = 0 ] && echo "all passed" || { echo "$failed failed"; exit 1; }
