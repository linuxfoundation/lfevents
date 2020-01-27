<?php

class ShortPixelTools {
/*    public static function parseJSON($data) {
        if ( function_exists('json_decode') ) {
            $data = json_decode( $data );
        } else {
            require_once( 'JSON/JSON.php' );
            $json = new Services_JSON( );
            $data = $json->decode( $data );
        }
        return $data;
    }*/

    /** Find if a certain plugin is active
    * @param String $plugin The name of plugin being searched for
    * @return Boolean Active or not
    */
    public static function shortPixelIsPluginActive($plugin) {
        $activePlugins = apply_filters( 'active_plugins', get_option( 'active_plugins', array()));
        if ( is_multisite() ) {
            $activePlugins = array_merge($activePlugins, get_site_option( 'active_sitewide_plugins'));
        }
        return in_array( $plugin, $activePlugins);
    }

    public static function snakeToCamel($snake_case) {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $snake_case)));
    }

    public static function getPluginPath()
    {
       return plugin_dir_path(SHORTPIXEL_PLUGIN_FILE);
    }

    public static function namespaceit($name)
    {
      return '\ShortPixel\\'  . $name;
    }

    public static function requestIsFrontendAjax()
    {
        $script_filename = isset($_SERVER['SCRIPT_FILENAME']) ? $_SERVER['SCRIPT_FILENAME'] : '';

        //Try to figure out if frontend AJAX request... If we are DOING_AJAX; let's look closer
        if((defined('DOING_AJAX') && DOING_AJAX))
        {
            //From wp-includes/functions.php, wp_get_referer() function.
            //Required to fix: https://core.trac.wordpress.org/ticket/25294
            $ref = '';
            if ( ! empty( $_REQUEST['_wp_http_referer'] ) ) {
                $ref = wp_unslash( $_REQUEST['_wp_http_referer'] );
            } elseif ( ! empty( $_SERVER['HTTP_REFERER'] ) ) {
                $ref = wp_unslash( $_SERVER['HTTP_REFERER'] );
            }
          //If referer does not contain admin URL and we are using the admin-ajax.php endpoint, this is likely a frontend AJAX request
          if(((strpos($ref, admin_url()) === false) && (basename($script_filename) === 'admin-ajax.php')))
            return true;
        }

        //If no checks triggered, we end up here - not an AJAX request.
        return false;
    }

    /** Function to convert dateTime object to a date output
    *
    * Function checks if the date is recent and then uploads are friendlier message. Taken from media library list table date function
    * @param DateTime $date DateTime object
    **/
    public static function format_nice_date( $date ) {

    if ( '0000-00-00 00:00:00' === $date->format('Y-m-d ') ) {
        $h_time = '';
    } else {
        $time   = $date->format('U'); //get_post_time( 'G', true, $post, false );
        if ( ( abs( $t_diff = time() - $time ) ) < DAY_IN_SECONDS ) {
            if ( $t_diff < 0 ) {
                $h_time = sprintf( __( '%s from now' ), human_time_diff( $time ) );
            } else {
                $h_time = sprintf( __( '%s ago' ), human_time_diff( $time ) );
            }
        } else {
            $h_time = $date->format( 'Y/m/d' );
        }
    }

    return $h_time;
}

static public function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');

    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    $bytes /= pow(1024, $pow);

    return round($bytes, $precision) . ' ' . $units[$pow];
}

    public static function commonPrefix($str1, $str2) {
        $limit = min(strlen($str1), strlen($str2));
        for ($i = 0; $i < $limit && $str1[$i] === $str2[$i]; $i++);
        return substr($str1, 0, $i);
    }

    /**
     * This is a simplified wp_send_json made compatible with WP 3.2.x to 3.4.x
     * @param type $response
     */
    public static function sendJSON($response) {
        @header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
        die(json_encode($response));
        //wp_send_json($response); // send json proper, dies.
    }



    /**
     * finds if an array contains an item, comparing the property given as key
     * @param $item
     * @param $arr
     * @param $key
     * @return the position that was removed, false if not found
     */
    public static function findItem($item, $arr, $key) {
        foreach($arr as $elm) {
            if($elm[$key] == $item) {
                return $elm;
            }
        }
        return false;
    }


    public static function getConflictingPlugins() {
        $settings = \wpSPIO()->settings();

        $conflictPlugins = array(
            'WP Smush - Image Optimization'
                => array(
                        'action'=>'Deactivate',
                        'data'=>'wp-smushit/wp-smush.php',
                        'page'=>'wp-smush-bulk'
                ),
            'Imagify Image Optimizer'
                => array(
                        'action'=>'Deactivate',
                        'data'=>'imagify/imagify.php',
                        'page'=>'imagify'
                ),
            'Compress JPEG & PNG images (TinyPNG)'
                => array(
                        'action'=>'Deactivate',
                        'data'=>'tiny-compress-images/tiny-compress-images.php',
                        'page'=>'tinify'
                ),
            'Kraken.io Image Optimizer'
                => array(
                        'action'=>'Deactivate',
                        'data'=>'kraken-image-optimizer/kraken.php',
                        'page'=>'wp-krakenio'
                ),
            'Optimus - WordPress Image Optimizer'
                => array(
                        'action'=>'Deactivate',
                        'data'=>'optimus/optimus.php',
                        'page'=>'optimus'
                ),
            'EWWW Image Optimizer'
                => array(
                        'action'=>'Deactivate',
                        'data'=>'ewww-image-optimizer/ewww-image-optimizer.php',
                        'page'=>'ewww-image-optimizer%2F'
                ),
            'EWWW Image Optimizer Cloud'
                => array(
                        'action'=>'Deactivate',
                        'data'=>'ewww-image-optimizer-cloud/ewww-image-optimizer-cloud.php',
                        'page'=>'ewww-image-optimizer-cloud%2F'
                ),
            'ImageRecycle pdf & image compression'
                => array(
                        'action'=>'Deactivate',
                        'data'=>'imagerecycle-pdf-image-compression/wp-image-recycle.php',
                        'page'=>'option-image-recycle'
                ),
            'CheetahO Image Optimizer'
                => array(
                        'action'=>'Deactivate',
                        'data'=>'cheetaho-image-optimizer/cheetaho.php',
                        'page'=>'cheetaho'
                ),
            'Zara 4 Image Compression'
                => array(
                        'action'=>'Deactivate',
                        'data'=>'zara-4/zara-4.php',
                        'page'=>'zara-4'
                ),
            'CW Image Optimizer'
                => array(
                        'action'=>'Deactivate',
                        'data'=>'cw-image-optimizer/cw-image-optimizer.php',
                        'page'=>'cw-image-optimizer'
                ),
            'Simple Image Sizes'
                => array(
                        'action'=>'Deactivate',
                        'data'=>'simple-image-sizes/simple_image_sizes.php'
                ),
            'Regenerate Thumbnails and Delete Unused'
              => array(
                      'action' => 'Deactivate',
                      'data' => 'regenerate-thumbnails-and-delete-unused/regenerate_wpregenerate.php',
              ),
               //DEACTIVATED TEMPORARILY - it seems that the customers get scared.
            /* 'Jetpack by WordPress.com - The Speed up image load times Option'
                => array(
                        'action'=>'Change Setting',
                        'data'=>'jetpack/jetpack.php',
                        'href'=>'admin.php?page=jetpack#/settings'
                )
            */
        );
        if($settings->processThumbnails) {
            $details = __('Details: recreating image files may require re-optimization of the resulting thumbnails, even if they were previously optimized. Please use <a href="https://wordpress.org/plugins/regenerate-thumbnails-advanced/" target="_blank">reGenerate Thumbnails Advanced</a> instead.','shortpixel-image-optimiser');

            $conflictPlugins = array_merge($conflictPlugins, array(
                'Regenerate Thumbnails'
                    => array(
                            'action'=>'Deactivate',
                            'data'=>'regenerate-thumbnails/regenerate-thumbnails.php',
                            'page'=>'regenerate-thumbnails',
                            'details' => $details
                    ),
                'Force Regenerate Thumbnails'
                    => array(
                            'action'=>'Deactivate',
                            'data'=>'force-regenerate-thumbnails/force-regenerate-thumbnails.php',
                            'page'=>'force-regenerate-thumbnails',
                            'details' => $details
                    )
            ));
        }
        if(!$settings->frontBootstrap){
            $conflictPlugins['Bulk Images to Posts Frontend'] = array (
                'action'=>'Change Setting',
                'data'=>'bulk-images-to-posts-front/bulk-images-to-posts.php',
                'href'=>'options-general.php?page=wp-shortpixel-settings&part=adv-settings#siteAuthUser',
                'details' => __('This plugin is uploading images in front-end so please activate the "Process in front-end" advanced option in ShortPixel in order to have your images optimized.','shortpixel-image-optimiser')
            );
        }

        $found = array();
        foreach($conflictPlugins as $name => $path) {
            $action = ( isset($path['action']) ) ? $path['action'] : null;
            $data = ( isset($path['data']) ) ? $path['data'] : null;
            $href = ( isset($path['href']) ) ? $path['href'] : null;
            $page = ( isset($path['page']) ) ? $path['page'] : null;
            $details = ( isset($path['details']) ) ? $path['details'] : null;
            if(is_plugin_active($data)) {
                if( $data == 'jetpack/jetpack.php' ){
                    $jetPackPhoton = get_option('jetpack_active_modules') ? in_array('photon', get_option('jetpack_active_modules')) : false;
                    if( !$jetPackPhoton ){ continue; }
                }
                $found[] = array( 'name' => $name, 'action'=> $action, 'path' => $data, 'href' => $href , 'page' => $page, 'details' => $details);
            }
        }
        return $found;
    }


} // class

function ShortPixelVDD($v){
    return highlight_string("<?php\n\$data =\n" . var_export($v, true) . ";\n?>");
}
