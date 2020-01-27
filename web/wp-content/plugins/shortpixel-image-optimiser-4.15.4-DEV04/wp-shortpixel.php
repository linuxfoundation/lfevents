<?php
/**
 * Plugin Name: ShortPixel Image Optimizer
 * Plugin URI: https://shortpixel.com/
 * Description: ShortPixel optimizes images automatically, while guarding the quality of your images. Check your <a href="options-general.php?page=wp-shortpixel-settings" target="_blank">Settings &gt; ShortPixel</a> page on how to start optimizing your image library and make your website load faster.
 * Version: 4.15.4-DEV04
 * Author: ShortPixel
 * Author URI: https://shortpixel.com
 * Text Domain: shortpixel-image-optimiser
 * Domain Path: /lang
 */
if (! defined('SHORTPIXEL_RESET_ON_ACTIVATE'))
  define('SHORTPIXEL_RESET_ON_ACTIVATE', false); //if true TODO set false
//define('SHORTPIXEL_DEBUG', true);
//define('SHORTPIXEL_DEBUG_TARGET', true);

define('SHORTPIXEL_PLUGIN_FILE', __FILE__);
define('SHORTPIXEL_PLUGIN_DIR', __DIR__);

//define('SHORTPIXEL_AFFILIATE_CODE', '');

define('SHORTPIXEL_IMAGE_OPTIMISER_VERSION', "4.15.4-DEV04");
define('SHORTPIXEL_MAX_TIMEOUT', 10);
define('SHORTPIXEL_VALIDATE_MAX_TIMEOUT', 15);
define('SHORTPIXEL_BACKUP', 'ShortpixelBackups');
define('SHORTPIXEL_MAX_API_RETRIES', 50);
define('SHORTPIXEL_MAX_ERR_RETRIES', 5);
define('SHORTPIXEL_MAX_FAIL_RETRIES', 3);
if(!defined('SHORTPIXEL_MAX_THUMBS')) { //can be defined in wp-config.php
    define('SHORTPIXEL_MAX_THUMBS', 149);
}

if(!defined('SHORTPIXEL_USE_DOUBLE_WEBP_EXTENSION')) { //can be defined in wp-config.php
    define('SHORTPIXEL_USE_DOUBLE_WEBP_EXTENSION', false);
}

define('SHORTPIXEL_PRESEND_ITEMS', 3);
define('SHORTPIXEL_API', 'api.shortpixel.com');

$max_exec = intval(ini_get('max_execution_time'));
if ($max_exec === 0) // max execution time of zero means infinite. Quantify.
  $max_exec = 60;
elseif($max_exec < 0) // some hosts like to set negative figures on this. Ignore that.
  $max_exec = 30;
define('SHORTPIXEL_MAX_EXECUTION_TIME', $max_exec);

// ** @todo For what is this needed? */
//require_once(ABSPATH . 'wp-admin/includes/file.php');
require_once(SHORTPIXEL_PLUGIN_DIR . '/build/shortpixel/autoload.php');

$sp__uploads = wp_upload_dir();
define('SHORTPIXEL_UPLOADS_BASE', (file_exists($sp__uploads['basedir']) ? '' : ABSPATH) . $sp__uploads['basedir'] );
//define('SHORTPIXEL_UPLOADS_URL', is_main_site() ? $sp__uploads['baseurl'] : dirname(dirname($sp__uploads['baseurl'])));
define('SHORTPIXEL_UPLOADS_NAME', basename(is_main_site() ? SHORTPIXEL_UPLOADS_BASE : dirname(dirname(SHORTPIXEL_UPLOADS_BASE))));
$sp__backupBase = is_main_site() ? SHORTPIXEL_UPLOADS_BASE : dirname(dirname(SHORTPIXEL_UPLOADS_BASE));
define('SHORTPIXEL_BACKUP_FOLDER', $sp__backupBase . '/' . SHORTPIXEL_BACKUP);
define('SHORTPIXEL_BACKUP_URL',
    ((is_main_site() || (defined( 'SUBDOMAIN_INSTALL' ) && SUBDOMAIN_INSTALL))
        ? $sp__uploads['baseurl']
        : dirname(dirname($sp__uploads['baseurl'])))
    . '/' . SHORTPIXEL_BACKUP);

/*
 if ( is_numeric(SHORTPIXEL_MAX_EXECUTION_TIME)  && SHORTPIXEL_MAX_EXECUTION_TIME > 10 )
    define('SHORTPIXEL_MAX_EXECUTION_TIME', SHORTPIXEL_MAX_EXECUTION_TIME - 5 );   //in seconds
else
    define('SHORTPIXEL_MAX_EXECUTION_TIME', 25 );
*/

define('SHORTPIXEL_MAX_EXECUTION_TIME2', 2 );
define("SHORTPIXEL_MAX_RESULTS_QUERY", 30);
//define("SHORTPIXEL_NOFLOCK", true); // don't use flock queue, can cause instability.
//define("SHORTPIXEL_EXPERIMENTAL_SECURICACHE", true);  // tries to add timestamps to URLS, to prevent hitting the cache.

/* Function to reach core function of ShortPixel
* Use to get plugin url, plugin path, or certain core controllers
*/
function wpSPIO()
{
   return \ShortPixel\ShortPixelPlugin::getInstance();
}

// [BS] Start runtime here
require_once(SHORTPIXEL_PLUGIN_DIR . '/wp-shortpixel-req.php'); // @todo should be incorporated here.
require_once(SHORTPIXEL_PLUGIN_DIR . '/class/controller/controller.php');
require_once(SHORTPIXEL_PLUGIN_DIR . '/class/shortpixel-model.php');
require_once(SHORTPIXEL_PLUGIN_DIR . '/shortpixel-plugin.php'); // loads runtime and needed classes.


if (! defined('SHORTPIXEL_DEBUG'))
{
    define('SHORTPIXEL_DEBUG', false);
}
$log = ShortPixel\ShortPixelLogger\ShortPixelLogger::getInstance();
if (ShortPixel\ShortPixelLogger\ShortPixelLogger::debugIsActive())
  $log->setLogPath(SHORTPIXEL_BACKUP_FOLDER . "/shortpixel_log");

// Pre-Runtime Checks
// @todo Better solution for pre-runtime inclusions of externals.
// Should not be required here. wpspio initruntime loads externals

wpSPIO(); // let's go!

register_activation_hook( __FILE__, array('\ShortPixel\ShortPixelPlugin','activatePlugin') );
register_deactivation_hook( __FILE__,  array('\ShortPixel\ShortPixelPlugin','deactivatePlugin') );
register_uninstall_hook(__FILE__,  array('\ShortPixel\ShortPixelPlugin','uninstallPlugin') );
