=== Stats Block ===
Contributors: The Linux Foundation
Requires at least: 6.5
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 0.1.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A responsive row of stat cards (big number + uppercase label) whose numbers count up from zero when the block scrolls into view.

== Description ==

Adds the "Stats" block. Each stat is a bordered card containing a large number and a small uppercase label. Cards lay out four across on desktop, two across on tablet and stack on mobile.

Numbers are animated from zero on load using `IntersectionObserver`. Any surrounding text is preserved, so values like `10,000+`, `$1.5M` or `3 Days` animate only their numeric portion. The final value is always present in the markup, so the block degrades gracefully without JavaScript and honours `prefers-reduced-motion`.

== Development ==

	npm install
	npm run build

== Changelog ==

= 0.1.0 =
* Initial release.
