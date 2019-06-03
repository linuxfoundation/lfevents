=== Clone Page Tree ===
Contributors: cjyabraham
Tags: clone, clone, page, tree
Requires at least: 4
Tested up to: 5.2.1
Stable tag: trunk
Requires PHP: 5.6.20
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Gutenberg block to serve China-based video to Chinese audience and YouTube video to everyone else.

== Description ==

This Gutenberg block allows for embedding a video in a WordPress post or page.  If the user who views the page is in China, a different video will show from users in the rest of the world.  This is to appropriately deal with China's Internet wall which blocks YouTube and other video platforms.

For more information on how to develop this block, please see the [github repo](https://github.com/cncf/china-video-block).

The plugin has been developed for [CNCF](https://www.cncf.io/) and [The Linux Foundation](https://www.linuxfoundation.org/).

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/china-video-block` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Use the Settings->China Video Block screen to set the IPInfo.io token
1. Insert the block into a post or page.

== Screenshots ==

1. China Video block inserted into a post.

== Changelog ==

= 0.1.0 =
* First release
