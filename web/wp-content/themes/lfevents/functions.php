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

/** Register all navigation menus */
require_once 'library/navigation.php';

/** Enqueue scripts */
require_once 'library/enqueue-scripts.php';

/** Add theme support */
require_once 'library/theme-support.php';

/** Change WP's sticky post class */
require_once 'library/sticky-posts.php';

/** Configure responsive image sizes */
require_once 'library/responsive-images.php';

/** Gutenberg color palette */
require_once 'library/gutenberg.php';

/** Add LFEvents functions */
require_once 'library/lfe-functions.php';
require_once 'library/lfe-options.php';

require_once 'library/shortcode-travel-fund-request-form.php';
require_once 'library/shortcode-visa-request-form.php';
require_once 'library/shortcode-staff.php';
