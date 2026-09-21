#!/usr/bin/env bash
# Upgrade rehearsal: what the owner notices when the live site moves from the previous release to this build.
#
#   scripts/test/upgrade.sh                # previous release = the commit in RAD_OLD_REF (default bc7244d, theme 1.3.0)
#
# It starts a scratch site on the OLD theme, does what the owner had done there (a Customizer that had saved an
# empty Register address, an edited Agenda button, a few posts), installs the CURRENT build over it and checks the
# public pages. A fresh install cannot show these: they only go wrong on a site that already has saved settings.
set -euo pipefail
HERE="$(cd "$(dirname "$0")" && pwd)"
ROOT="$(cd "$HERE/../.." && pwd)"
PORT="${RAD_TEST_PORT:-8092}"
BASE="http://localhost:$PORT"
OLD_REF="${RAD_OLD_REF:-bc7244d}"
CVENT='https://web.cvent.com/event/71e8f910-3826-4a2e-8e49-638654fbd4e6/register'
OWNER_URL='https://owner-events.example.com/register'
failed=0
wp() { bash "$HERE/wp-env.sh" wp "$@"; }
check() { if [ "$2" = "1" ]; then echo "PASS  $1"; else echo "FAIL  $1${3:+ — $3}"; failed=$((failed + 1)); fi; }
count() { grep -o "$1" | wc -l | tr -d ' '; }

bash "$HERE/wp-env.sh" up >/dev/null
NEW_VERSION="$(wp theme get redcliffe-advisory --field=version)"
git -C "$ROOT" show "$OLD_REF:dist-wordpress/redcliffe-advisory.zip" > "$ROOT/dist-wordpress/old-release.zip"
trap 'rm -f "$ROOT/dist-wordpress/old-release.zip"' EXIT

# 1. The site as it was on the old release: fresh database, old theme.
wp db reset --yes >/dev/null
wp core install --url="$BASE" --title="Redcliffe Advisory" --admin_user=admin --admin_password=admin --admin_email=test@example.com --skip-email >/dev/null
wp theme install /package/old-release.zip --activate --force >/dev/null
wp rewrite structure '/%postname%/' --hard >/dev/null 2>&1 || true
OLD_VERSION="$(wp theme get redcliffe-advisory --field=version)"
curl -s -o /dev/null "$BASE/agenda/"   # the old release seeds the Agenda page on first view
echo "old release $OLD_VERSION -> current build $NEW_VERSION"

# 2. What the owner had done there.
wp theme mod set rad_summit_hero_registerUrl "" >/dev/null   # the Customizer saved the empty address
wp theme mod set rad_summit_hero_title "Owner headline" >/dev/null
AGENDA="$(wp post list --post_type=page --name=agenda --field=ID)"
wp eval '
$p = get_post((int) '"$AGENDA"');
$c = str_replace(">Enquire about attending<", ">Register here<", $p->post_content);
$c = preg_replace("~href=\"[^\"]*page_id=\d+\">Register here~", "href=\"'"$OWNER_URL"'\">Register here", $c);
wp_update_post(array("ID" => $p->ID, "post_content" => $c));' >/dev/null
for t in "Karina's column" "A Spanish start-up" "Social skills"; do wp post create --post_title="$t" --post_status=publish --post_content="<p>Body.</p>" >/dev/null; done

# 3. The upgrade, as the owner does it: the new theme installed over the old one.
wp theme install /package/redcliffe-advisory.zip --force --activate >/dev/null
wp cache flush >/dev/null 2>&1 || true

# 4. What the public sees.
bad=""
for p in / /practice/ /who/ /city/ /summit/ /agenda/ /articles/ /ethics/ /contact/; do
  html="$(curl -s -w '\n%{http_code}' "$BASE$p")"
  [ "$(echo "$html" | tail -1)" = "200" ] && echo "$html" | grep -q "style.css?ver=$NEW_VERSION" || bad="$bad $p"
done
check "all nine pages load on the new version" "$([ -z "$bad" ] && echo 1 || echo 0)" "$bad"

summit="$(curl -s "$BASE/summit/")"
check "the Register button is back although an empty address had been saved" "$([ "$(echo "$summit" | count "on-dark-ghost\" href=\"$CVENT\"")" = 1 ] && echo 1 || echo 0)"
check "the owner's own headline change is kept" "$(echo "$summit" | grep -q 'Owner headline' && echo 1 || echo 0)"

agenda="$(curl -s "$BASE/agenda/")"
closing="$(echo "$agenda" | count 'data-rad="agenda\.actions\.\(primary\|secondary\)Label"')"
check "the Agenda has one pair of closing buttons" "$([ "$closing" = 2 ] && echo 1 || echo 0)" "found $closing"
check "the owner's edited Agenda button keeps its words and address" "$(echo "$agenda" | grep -q "href=\"$OWNER_URL\"[^>]*><span data-rad=\"agenda.actions.primaryLabel\">Register here" && echo 1 || echo 0)"

check "the owner's posts are on the Articles page with an author and month" "$([ "$(curl -s "$BASE/articles/" | count 'Karina Robinson · ')" -ge 3 ] && echo 1 || echo 0)"

# Deliberately clearing the address afterwards still hides the button (the migration runs once).
wp theme mod set rad_summit_hero_registerUrl "" >/dev/null
check "clearing the address afterwards hides the button" "$([ "$(curl -s "$BASE/summit/" | count 'on-dark-ghost')" = 0 ] && echo 1 || echo 0)"
wp theme mod remove rad_summit_hero_registerUrl >/dev/null

log="$(docker exec rad-test-wp sh -c 'cat /var/www/html/wp-content/debug.log 2>/dev/null || true' | grep -i 'redcliffe-advisory' | grep -Ei 'warning|notice|fatal|deprecated' || true)"
check "the upgrade wrote no PHP warnings from the theme" "$([ -z "$log" ] && echo 1 || echo 0)" "$log"

echo
[ "$failed" = 0 ] && echo "all passed" || { echo "$failed failed"; exit 1; }
