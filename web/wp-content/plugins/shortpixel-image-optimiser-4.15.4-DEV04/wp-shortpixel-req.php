<?php
/*
if(defined('SHORTPIXEL_DEBUG') && SHORTPIXEL_DEBUG === true) {
    require_once('shortpixel-debug.php');
} else {
    define('SHORTPIXEL_DEBUG', false);
} */

// Debug. Hook as early as possible.
//require_once('class/controller/controller.php');
//require_once('class/controller/debug.php');
//require_once('class/model/shortpixel-debug.php');

// @todo wp-shortpixel-settings which depends on this model should be called when needed; in the model/ directory. That will be some work, so for now here.
//require_once('class/shortpixel-model.php');

//use ShortPixel\DebugItem as DebugItem;
//use ShortPixel\ShortPixelLogger\ShortPixelLogger as Log;

/*if (! defined('SHORTPIXEL_DEBUG'))
{
    define('SHORTPIXEL_DEBUG', false);
} */

// [BS] New plugin runtime.
//require_once('shortpixel-plugin.php'); // loads runtime and needed classes.
//new Shortpixel\ShortPixelPlugin();

// @todo Temporary until main plugin file will receive it's unclutter.  Require the things loaded by new plugin main
/*if (! class_exists('ShortPixel\ShortPixelPlugin'))
{
  require_once('class/shortpixel_queue.php');
  require_once('class/shortpixel-png2jpg.php');
  require_once('class/wp-short-pixel.php');
  require_once('class/wp-shortpixel-settings.php');
  require_once('class/wp-shortpixel-cloudflare-api.php');
  require_once('class/shortpixel-tools.php');
  require_once('class/controller/bulk-restore-all.php');
} */

//require_once('class/wp-short-pixel.php');
//require_once('class/wp-shortpixel-settings.php');
//require_once('class/wp-shortpixel-cloudflare-api.php');
require_once('shortpixel_api.php');
//require_once('class/shortpixel_queue.php');
//require_once('class/shortpixel-png2jpg.php');
//entities
require_once('class/model/shortpixel-entity.php');
require_once('class/model/shortpixel-meta.php');
require_once('class/model/shortpixel-folder.php');
//exceptions
require_once('class/model/sp-file-rights-exception.php');
//database access
require_once('class/db/shortpixel-db.php');
require_once('class/db/wp-shortpixel-db.php');
require_once('class/db/shortpixel-custom-meta-dao.php');
require_once('class/db/shortpixel-nextgen-adapter.php');
require_once('class/db/wp-shortpixel-media-library-adapter.php');
require_once('class/db/shortpixel-meta-facade.php');
//view
require_once('class/view/shortpixel_view.php');

//require_once('class/shortpixel-tools.php');

//require_once('class/controller/bulk-restore-all.php');

require_once( ABSPATH . 'wp-admin/includes/image.php' );
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

// for retro compatibility with WP < 3.5
// @todo Move this to compatibility file.
if( !function_exists('wp_normalize_path') ){
    function wp_normalize_path( $path ) {
        $path = str_replace( '\\', '/', $path );
        $path = preg_replace( '|(?<=.)/+|', '/', $path );
        if ( ':' === substr( $path, 1, 1 ) ) {
            $path = ucfirst( $path );
        }
        return $path;
    }
}

/*
if ( !is_plugin_active( 'wpmandrill/wpmandrill.php' ) //avoid conflicts with some plugins
  && !is_plugin_active( 'wp-ses/wp-ses.php' )
  && !is_plugin_active( 'wordfence/wordfence.php') ) {
    require_once( ABSPATH . 'wp-includes/pluggable.php' );
}
*/
