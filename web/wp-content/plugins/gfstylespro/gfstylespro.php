<?php
/*
Plugin Name: Gravity Forms Styles Pro
Plugin URI: http://www.gravitystylespro.com/
Description: Marry your Gravity Forms functionality with visuals to match. Easily improve your UI/UX with icons, images, easy to use layouts and customizable responsive/fluid themes.
Version: 2.3.4
Author: Warp Lord
Author URI: http://www.gravitystylespro.com/author/
*/

define( 'GF_STYLES_PRO_ADDON_VERSION', '2.3.4' );

add_action( 'gform_loaded', array( 'GFStyles_Pro_Bootstrap', 'load' ), 5 );

class GFStyles_Pro_Bootstrap {

    public static function load() {

        if ( ! method_exists( 'GFForms', 'include_addon_framework' ) ) {
            return;
        }

        require_once( 'class-gfstylespro.php' );

        GFAddOn::register( 'StylesPro' );
    }

}

function gf_styles_pro() {
    return StylesPro::get_instance();
}