#!/usr/bin/env bash
# The four quotations on the homepage: each one editable on its own, and an edit that reaches visitors.
#
#   scripts/test/testimonials.sh      (after `wp-env.sh up`)
#
# Before theme 1.4.7 the quotations lived in a list inside the page's JavaScript, which rewrote
# the words the moment the page loaded: only the first could be edited, and editing it changed
# nothing a visitor saw. These checks fail on that theme and pass on this one.
set -euo pipefail
HERE="$(cd "$(dirname "$0")" && pwd)"
PORT="${RAD_TEST_PORT:-8092}"
BASE="http://localhost:$PORT"
failed=0
wp() { bash "$HERE/wp-env.sh" wp "$@"; }
check() { if [ "$2" = "1" ]; then echo "PASS  $1"; else echo "FAIL  $1${3:+ — $3}"; failed=$((failed + 1)); fi; }
count() { grep -o "$1" | wc -l | tr -d ' '; }
has() { grep -q -- "$1" && echo 1 || echo 0; }
home() { curl -s "$BASE/"; }

cleanup() {
  for n in 1 2 3 4; do
    wp theme mod remove "rad_home_testimonial_${n}_quote" >/dev/null 2>&1 || true
    wp theme mod remove "rad_home_testimonial_${n}_attribution" >/dev/null 2>&1 || true
  done
  wp theme mod set rad_section_home_testimonials 1 >/dev/null 2>&1 || true
  return 0
}
trap cleanup EXIT
cleanup

h="$(home)"

# 1. Four quotations, each with a field of its own.
check "the homepage shows four quotations" "$([ "$(echo "$h" | count '<figure class="test-item"')" = 4 ] && echo 1 || echo 0)" "$(echo "$h" | count '<figure class="test-item"')"
bad=""
for n in 1 2 3 4; do
  echo "$h" | grep -q "data-rad=\"home.testimonial.$n.quote\"" && echo "$h" | grep -q "data-rad=\"home.testimonial.$n.attribution\"" || bad="$bad $n"
done
check "each quotation has its own words and its own attribution to edit" "$([ -z "$bad" ] && echo 1 || echo 0)" "$bad"
check "the words of all four are in the page, not in its JavaScript" "$([ "$(echo "$h" | has '__TESTIMONIALS')" = 0 ] && echo "$h" | grep -q 'without an interpreter' && echo "$h" | grep -q 'having decided things' && echo 1 || echo 0)"
check "only the first is showing; the rest wait their turn" "$([ "$(echo "$h" | count '<figure class="test-item" hidden')" = 3 ] && echo 1 || echo 0)"
check "the 01 / 04 counter is worked out by the page, so it is not a field to fill in" "$([ "$(echo "$h" | has 'id="t-count" data-rad')" = 0 ] && echo "$h" | has 'id="t-count"')"

# 2. What the owner types is what the visitor reads — the bug this replaces.
wp theme mod set rad_home_testimonial_1_quote 'A room where the hard questions get asked politely.' >/dev/null
wp theme mod set rad_home_testimonial_1_attribution 'A pension fund trustee · Chatham House rule' >/dev/null
wp theme mod set rad_home_testimonial_3_quote 'The only diary date my chair keeps himself.' >/dev/null
h="$(home)"
check "a published first quotation reaches the visitor" "$(echo "$h" | has 'A room where the hard questions get asked politely.')"
check "and the designed wording it replaced is gone" "$([ "$(echo "$h" | has 'in the best sense, a benign disruptor')" = 0 ] && echo 1 || echo 0)"
check "the third quotation can be published too, on its own" "$(echo "$h" | grep -q 'The only diary date my chair keeps himself.' && echo "$h" | grep -q 'without an interpreter' && echo 1 || echo 0)"
check "a quotation typed plainly still gets one pair of gold quote marks" "$([ "$(echo "$h" | count '<span class="open-q">')" = 4 ] && [ "$(echo "$h" | count '<span class="close-q">')" = 4 ] && echo 1 || echo 0)" "$(echo "$h" | count '<span class="open-q">')"
check "an attribution typed plainly still shows the name in ink" "$(echo "$h" | has '<span class="name">A pension fund trustee</span> · Chatham House rule')"

# 3. A quotation saved under an older theme, which carried the quote marks inside the field.
wp theme mod set rad_home_testimonial_2_quote '<span class="open-q">“</span>Saved the old way, with its own quote marks.<span class="close-q">”</span>' >/dev/null
h="$(home)"
check "an older saved quotation does not print two pairs of quote marks" "$([ "$(echo "$h" | count '<span class="open-q">')" = 4 ] && echo 1 || echo 0)" "$(echo "$h" | count '<span class="open-q">')"
check "and its words are still there" "$(echo "$h" | has 'Saved the old way, with its own quote marks.')"
wp theme mod set rad_home_testimonial_2_quote '<span class="open-q">“Words left with half a tag deleted.<span class="close-q">”</span>' >/dev/null
check "a quotation with a half-deleted tag keeps its words" "$(home | has 'Words left with half a tag deleted.')"

# 4. Clearing a box puts the designed wording back, as everywhere else on the site.
cleanup >/dev/null
h="$(home)"
check "clearing the boxes puts the designed quotations back" "$(echo "$h" | grep -q 'in the best sense, a benign disruptor' && echo "$h" | grep -q 'A City Chair' && echo 1 || echo 0)"

# 5. The switch still hides the whole section.
wp theme mod set rad_section_home_testimonials '' >/dev/null
check "the Testimonials switch still hides all of them" "$([ "$(home | has 'class="test-item"')" = 0 ] && echo 1 || echo 0)"
wp theme mod set rad_section_home_testimonials 1 >/dev/null
check "and shows them again" "$(home | has 'class="test-item"')"

log="$(docker exec rad-test-wp sh -c 'cat /var/www/html/wp-content/debug.log 2>/dev/null || true' | grep -Ei 'warning|notice|fatal|deprecated' | grep -v mysqli_real_connect || true)"
check "no PHP warnings from the theme" "$([ -z "$log" ] && echo 1 || echo 0)" "$log"

echo
[ "$failed" = 0 ] && echo "all passed" || { echo "$failed failed"; exit 1; }
