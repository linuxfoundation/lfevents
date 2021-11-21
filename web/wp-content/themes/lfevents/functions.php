<?php
/**
 * Author: Ole Fredrik Lie
 * URL: http://olefredrik.com
 *
 * FoundationPress functions and definitions
 *
 * Set up the theme and provides some helper functions, which are used in the
 * theme as custom template tags. Others are attached to action and filter
 * hooks in WordPress to change core functionality.
 *
 * @link https://codex.wordpress.org/Theme_Development
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

/** Various clean up functions */
require_once 'library/cleanup.php';

/** Required for Foundation to work properly */
require_once 'library/foundation.php';

/** Format comments */
// require_once 'library/class-foundationpress-comments.php'; // comments not in use.

/** Register all navigation menus */
require_once 'library/navigation.php';

/** Add menu walkers for top-bar and off-canvas */
// require_once 'library/class-foundationpress-top-bar-walker.php'; // Not needed.
// require_once 'library/class-foundationpress-mobile-walker.php'; // Not needed.

/** Create widget areas in sidebar and footer */
require_once 'library/widget-areas.php';

/** Return entry meta information for posts */
// require_once 'library/entry-meta.php'; // removed as only used once, moved code in to template.

/** Enqueue scripts */
require_once 'library/enqueue-scripts.php';

/** Add theme support */
require_once 'library/theme-support.php';

/** Add Nav Options to Customer */
// require_once 'library/custom-nav.php'; // removed as we don't need or use it.

/** Change WP's sticky post class */
require_once 'library/sticky-posts.php';

/** Configure responsive image sizes */
require_once 'library/responsive-images.php';

/** Gutenberg editor support */
require_once 'library/gutenberg.php';

/** If your site requires protocol relative url's for theme assets, uncomment the line below */
// require_once( 'library/class-foundationpress-protocol-relative-theme-assets.php' ); //.

/** Add LFEvents functions */
require_once 'library/lfe-functions.php';
require_once 'library/lfe-options.php';

require_once 'library/shortcode-travel-fund-request-form.php';
require_once 'library/shortcode-visa-request-form.php';
require_once 'library/shortcode-staff.php';
