=== Conditional Blocks - Control visibility of any Gutenberg block ===
Contributors: morganhvidt
Donate link: https://www.paypal.me/morganhvidt/
Tags: conditional blocks, Gutenberg, conditions, woocommerce, restrict blocks, hide content, restrict content, mobile blocks, restrict, block controls, block visibility, toggle blocks, hide blocks, block editor
Requires at least: 5.4
Tested up to: 5.8
Stable tag: trunk
Requires PHP: 5.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Author URI: https://conditionalblocks.com/

Conditionally show or hide ANY Gutenberg blocks! Change the visibility of content to create dynamic blocks. Great for restricting content for membership sites.

== Description ==

Conditional Blocks allows you to create a unique experience for your visitors and customers. Dynamic content using WordPress blocks is now easy.

Conditionally hide Gutenberg blocks, even third-party blocks! You can use Conditional Blocks to restrict content on a memebership site, or you can created unique design on your landing pages.

Be creative, build templates with dynamic content - you can change the visibility of any block, grouped blocks and each nested block even reusable blocks! We plan on having Conditional Blocks ready for Full Site Editing (FSE) themes, if you have any ideas - let us know.

## What can conditional blocks do?

Conditional Blocks makes it easy to change visibility of any block.

Included condition types in the free version:

* Hide blocks from everyone using the "Lockdown" condition. Great for when you are preparing new content.
* Show or Hide block based user state (logged in users or logged out users). Great for creating membership content for signed in users.
* Show or hide block on mobile screens.
* Show or hide block on tablet screens.
* Show or hide block on desktop screens.
* Modify the screensizes to fit the CSS breakpoints of your theme.

[Learn more about the features](https://conditionalblocks.com/?cb=wporg)

## Do more with Conditional Blocks Pro

* Show block depending on user roles (WooCommerce Customer, editor, subscriber, custom roles, etc).
* Show block content for between date ranges.
* Show block for any device (HTTP UserAgent) (iPhone, Android, macOS, linux and Windows).
* Show block based on URL Referer (e.g  if user came from Google.com or twitter.com).
* Show block based on post meta fields, including custom meta fields and data.
* Show block based on URL query strings (URL variables), You display custom confirmation content for form plugins like Gravitiy Forms.
* Show block based on post ID or hide block on post IDs.
* Create and manage presets of conditions to apply to multiple blocks acorss your whole site.
* Show block based on PHP Logic and custom functions. The possibilities are unlimited.
* WooCommerce - change the visibility of blocks based on total cart value of the current customer. Perfect for upselling.
* WooCommerce - We have even more WooCommerce conditions for blocks coming, each condition will help you create dynamic upsells for smart marketing. 
* Support development of new features.

[See all features & benefits of Conditional Blocks Pro](https://conditionalblocks.com/?cb=wporg)

### Create content while your page is live.

Safely work on your already published WordPress content over multiple days by hiding the blocks that aren't yet finished. Just make them visble on when you are ready. 

### Restrict block content

Restrict any WordPress Block by simply clicking on it inside the WordPress Block Editor. You can open the Condition Builder and add visibility rules to the selected block. You'll be able to set it to appear for logged in or out users only.

Great for creating content that's visible to members only on your site.

### Control Mobile Blocks

You can select specific screen sizes to display your blocks on, and exclude them from others! Pick and choose if the block should be shown on mobile, tablet or desktop. The block content will automatically be displayed or hidden for each different screensizes.

### Compatibility

Conditional Blocks works with any theme that uses the WordPress Block Editor (Gutenberg Block Editor).

We've tested Conditional Blocks with the follow plugins:

* Stackable Blocks
* Atomic Blocks
* CoBlocks
* Editorskit
* WooCommerce Blocks (Product Blocks)
* Easy Digital Downloads Blocks
* Ultimate Addons for Gutenberg
* Otter Blocks & Templates
* GenerateBlocks
* Kadence Blocks
* Genesis Blocks

Please reach out on support if you are experiencing issues with another plugin.

## LEARN MORE

* [Documentation](https://conditionalblocks.com/docs?cb=wporg): Learn how to set up and use our features.
* [Blog](https://conditionalblocks.com/blog?cb=wporg): Read our guides and tutorials.
* [Website](https://conditionalblocks.com/?cb=wporg): Find out more about us and the pro version.
* [Twitter](https://twitter.com/ConditionalBloc): Follow us on Twitter.

== Installation ==

Conditional Blocks for Gutenberg Installation Instructions:

1. Upload the Conditional Blocks plugin to your /wp-content/plugin/ directory or through the plugin admin section under "add new".,
2. Activate the plugin through the ‘Plugins’ menu in WordPress.,
3. That's it!

== Frequently Asked Questions ==

= Can I conditionally hide blocks? =

Yes, you can conditionally hide any Gutenberg block using any of the conditions types.

== Screenshots ==

1. How to customize WordPress visibility with Conditonal Blocks.
2. Conditonal Blocks 2.0+ - logged out/in user content block.
3. Frontend showing the logged out user block.
4. Editor options for conditionally showing block on screen sizes.
5. Frontend showing different blocks for different screensizes using responsive blocks.

== Changelog ==

= 2.4.2 =

Tested and ready for WordPress 5.8

= 2.4.0 =

New: Lockdown Condition (Hide block for everyone).
New: Most conditions now have a selectable "Block Action" where you can choose if the block should be visible or hidden when the condition is met.
Improved: UI - easily to select your options, the modal is now slimmer, match the current WordPress Admin UI colors.
Improved: Refactored condition checking.
Improved: Fully translatable.

= 2.3.0 =

Release notes: https://conditionalblocks.com/v2-3-woocommerce-condition/

* Improved and categorised the condition selector, now with "type to search" support.

Conditional Blocks Pro
* New - WooCommerce support available in Conditional Blocks Pro. Control the visibility of blocks using the customers total cart value.

= 2.0.1 =

* Improved performance by only including the CSS for responsive blocks on the pages they are used.

= 2.0.0 =

Complete rebuild of Conditional Blocks. Please read the 2.0 introduction & release notes [https://conditionalblocks.com/introducing-conditional-blocks-2-0/](https://conditionalblocks.com/introducing-conditional-blocks-2-0/)

* Conditional Blocks 2.0 is a complete rebuild.
* Existing users can safely update, old conditions will continue to work in the background. Editing blocks with old conditions will require clicking "Convert to 2.0 conditions", when opening the new Condition Builder.
* Ready for WordPress 5.7 and Gutenberg 10.1

= 1.0.9 =
* Fixed: Inner blocks conditions work as expected again.

= 1.0.8 =
* Ready and tested with WordPress 5.6
* Improved Compatibility with themes that could be override css. https://wordpress.org/support/topic/maybe-adds-important-css-rules/

= 1.0.7 =
* Tested: with GenerateBlocks.
* Fixed: Added a fix to the issue where server side rendered blocks would cause errors with Conditional Blocks. https://github.com/WordPress/gutenberg/issues/16850

= 1.0.6 =
* Ready for WordPress 5.5

= 1.0.5 =
* Added feature to modify the CSS breakpoints.
* Updated date range conditions in Conditional Blocks Pro
* Fixed issue where MomentJS could not be found in Conditional Blocks Pro

= 1.0.4 =
 * Ready and tested with WordPress 5.4
 * Pro: Fixed date conditions sometimes applying to new blocks when new dates are added.
 * Minor code improvements.
 * Tested with Gutenberg 7.7.1

= 1.0.3 =
 * Improved how device size conditions are handled across all blocks.
 * Fixed hiding on device sizes across could cause full-width blocks to be standard size.
 * Fixed Conditional Blocks highlighting for Gutenberg 7.4+
 * Fixed postmeta condition being skipped.
 * Fixed postmeta condition saving incorrectly on blocks.

= 1.0.2 =
 * Removed Freeemius and tracking.

= 1.0.1 =
* New Date Range Conditions for Blocks in Pro! [Learn about Date Conditions](https://conditionalblocks.com/)
* Improved JS browers caching with file versions.
* Improved code structure.
* Fixed conflict with Yoast and other plugings which triggers Gutenberg by using React.

= 1.0.0 =
* initial Release of Conditional Blocks for Gutenberg.

== Upgrade Notice ==
