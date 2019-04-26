<?php
/*
Plugin Name: Advanced Post Types Order
Plugin URI: http://www.nsp-code.com
Description: Order Post Types Objects using a Drag and Drop Sortable javascript capability
Author: Nsp Code
Author URI: http://www.nsp-code.com 
Version: 4.0.8.2
*/


    define('APTO_PATH',             plugin_dir_path(__FILE__));
    define('APTO_URL_PROTOCOL',     plugins_url('', __FILE__));
    define('APTO_URL',              str_replace(array('https:', 'http:'), "", APTO_URL_PROTOCOL));

    define('APTO_VERSION',          '4.0.8.2');
    define('APTO_DB_VERSION',       '1.1');
    define('APTO_APP_API_URL',      'https://www.nsp-code.com/index.php'); 
    
    define('APTO_PRODUCT_ID',       'APTO');
    define('APTO_SECRET_KEY',       '*#ioK@ud8*&#2');
    define('APTO_INSTANCE',         preg_replace('/:[0-9]+/', '', str_replace(array ("https://" , "http://"), "", get_site_option('siteurl'))));

    define('APTO_AJAX_OBJECTS_PER_PAGE',    3000);
      
    //load language files
    add_action( 'plugins_loaded', 'apto_load_textdomain'); 
    function apto_load_textdomain() 
        {
            load_plugin_textdomain('apto', FALSE, dirname( plugin_basename( __FILE__ ) ) . '/lang');
        }

    
    include_once(APTO_PATH . '/include/apto_functions-class.php');
    include_once(APTO_PATH . '/include/apto_updater-class.php');
    include_once(APTO_PATH . '/include/apto_conditionals-class.php');
    include_once(APTO_PATH . '/include/apto-class.php');
    include_once(APTO_PATH . '/include/utils/apto_query-class.php');
    
        
    include_once(APTO_PATH . '/include/functions.php');
    include_once(APTO_PATH . '/include/apto-licence-class.php'); 
    include_once(APTO_PATH . '/include/apto_plugin_updater.class.php'); 

    include_once(APTO_PATH . '/include/addons.php');

    register_deactivation_hook(__FILE__, 'APTO_deactivated');
    register_activation_hook(__FILE__, 'APTO_activated');

    function APTO_activated($network_wide) 
        {
            global $wpdb;
                                         
            // check if it is a network activation
            if ( $network_wide ) 
                {
                    $current_blog = $wpdb->blogid;
                    
                    // Get all blog ids
                    $blogids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
                    foreach ($blogids as $blog_id) 
                        {
                            switch_to_blog($blog_id);
                            APTO_updater::check_version_update();
                        }
                    
                    switch_to_blog($current_blog);
                    
                    return;
                }
                else
                APTO_updater::check_version_update(); 
        }

    function APTO_deactivated() 
        {
            
        }
    
    //check on settings when new blog created    
    add_action( 'wpmu_new_blog', 'APTO_new_blog', 10, 6);       
    function APTO_new_blog($blog_id, $user_id, $domain, $path, $site_id, $meta ) 
        {
            global $wpdb;
         
            if (is_plugin_active_for_network('advanced-post-types-order/advanced-post-types-order.php')) 
                {
                    $current_blog = $wpdb->blogid;
                    
                    switch_to_blog($blog_id);
                    
                    APTO_updater::check_version_update();
                    
                    switch_to_blog($current_blog);
                }
        }
        
    
    //early trigger
    add_action('plugins_loaded', 'APTO_plugins_loaded', 11);
    function APTO_plugins_loaded()
        {
            global $APTO;
            
            $APTO = new APTO();
            $APTO->init();
            
            if(!is_network_admin())
                {
                    
                    //update run only on dashboard
                    if ( is_admin() && !defined('DOING_AJAX' ) )
                        APTO_updater::check_version_update(); 
                }
                else
                {
                    //run the shceduled actions for all blogs
                    //this is the superadmin interface
                    
                    //may be too larget to run for superadmin, better trigger individual (each)  
                }
            
            //load the APTO WPML class if WPML plugin is active
            if(defined('ICL_LANGUAGE_CODE') && defined('ICL_SITEPRESS_VERSION'))
                include_once(APTO_PATH . 'include/utils/apto_wpml-class.php');
            
            //Polylang    
            if(defined('POLYLANG_VERSION'))
                include_once(APTO_PATH . 'include/utils/class.apto.polylang.php');
                
            //WooCommerce
            if(in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ))
                include_once(APTO_PATH . 'include/utils/apto_woocommerce-class.php');
            
            if(is_admin())
                {
                    include_once(APTO_PATH . '/include/apto_admin-class.php');
                    include_once(APTO_PATH . '/include/apto_interface_helper-class.php');
                    include_once(APTO_PATH . '/include/apto_admin_functions-class.php');
                    
                    new APTO_admin();
                }
                        
            if(is_admin() && is_user_logged_in() && !defined('DOING_AJAX'))
                {
                    include_once(APTO_PATH . '/include/apto_admin_functions-class.php');
                    include_once(APTO_PATH . '/include/apto_options_class.php');
                    
                    $APTO_options_interface   =   new APTO_options_interface();
                    if(is_multisite())
                        {
                            if($APTO->licence->licence_key_verify())
                                add_action( 'admin_menu', array($APTO_options_interface, 'create_plugin_options'), 100 );
                        }   
                        else
                        add_action( 'admin_menu', array($APTO_options_interface, 'create_plugin_options'), 100 );

                    
                    $APTO->functions->disable_post_types_order();
                }
        }
            
    add_action('init', 'APTO_init' );
    function APTO_init()
        {
            global $APTO;

            //add AJAX actions 
            if(is_admin() && defined('DOING_AJAX'))
                {
                    include_once(APTO_PATH . '/include/apto_interface_helper-class.php');
                    include_once(APTO_PATH . '/include/apto_admin_functions-class.php');
                    
                    include_once(APTO_PATH . '/include/walkers/post-types-order-walker-class.php');
                    include_once(APTO_PATH . '/include/walkers/taxonomies-terms-dropdown-class.php');
                    include_once(APTO_PATH . '/include/walkers/terms-dropdown-categories-class.php');
                    
                    $APTO_interface_helper = new APTO_interface_helper();
            
                    add_action( 'wp_ajax_apto_get_rule_box', array($APTO_interface_helper, 'get_rule_box') );
                    add_action( 'wp_ajax_apto_get_conditional_group', array($APTO_interface_helper, 'get_conditional_group') );
                    add_action( 'wp_ajax_apto_get_conditional_rule', array($APTO_interface_helper, 'get_conditional_rule') );
                    add_action( 'wp_ajax_apto_change_taxonomy_item', array($APTO_interface_helper, 'change_taxonomy_item') );
                    add_action( 'wp_ajax_apto_metabox_toggle', array($APTO_interface_helper, 'metabox_toggle') );
                    add_action( 'wp_ajax_update-custom-type-order', array($APTO_interface_helper, 'saveAjaxOrder') );
                    add_action( 'wp_ajax_update-sorting-menu-tabs', array($APTO_interface_helper, 'saveAjaxTabsOrder') );
                    
                    add_action( 'wp_ajax_apto_automatic_add_falback_order', array($APTO_interface_helper, 'automatic_add_falback_order') );
                }
                
            else if (is_admin() && is_user_logged_in()) 
                {
                    include_once(APTO_PATH . '/include/apto_admin_functions-class.php');
                    
                    $APTO_admin_functions = new APTO_admin_functions();
                    add_action( 'admin_menu', array($APTO_admin_functions, 'create_menu_items'), 99 );

                }
            else
                {
                    //this is front side load shortcode
                    include_once(APTO_PATH . '/shortcodes/apto_shortcodes.php');
                }
            
            //delete a sort view list when term deleted    
            add_action( 'delete_term', array('APTO_functions', 'wp_delete_term'), 99, 4); 

        }

    add_action('wp_loaded', 'init_APTO', 99 );
    function init_APTO() 
        {
	        global $APTO;
            
            if(!$APTO->licence->licence_key_verify())
                return;
                            
            add_filter('pre_get_posts',         array($APTO, 'pre_get_posts'));
            add_filter('posts_orderby',         array($APTO, 'posts_orderby'), 99, 2);
                
            add_filter('posts_orderby_request', array($APTO->functions, 'wp_ecommerce_orderby'), 99, 2);
            add_filter('posts_groupby',         array($APTO, 'APTO_posts_groupby'), 99, 2);
            add_filter('posts_distinct',        array($APTO, 'APTO_posts_distinct'), 99, 2);
                           
            //make sure the vars are set as default
            $options = $APTO->functions->get_settings();
      
            //bbpress reverse option check
            if (isset($options['bbpress_replies_reverse_order']) && $options['bbpress_replies_reverse_order'] == "1")
                add_filter('bbp_before_has_replies_parse_args', array($APTO->functions, 'bbp_before_has_replies_parse_args' ));

        }
        
    add_action('wp', 'APTO_wp');
    function APTO_wp()
        {
            global $APTO;   
            
            //make sure the vars are set as default
            $options = $APTO->functions->get_settings();
     
            if(!is_admin())
                {
                    $navigation_sort_apply   =  ($options['navigation_sort_apply'] ==  "1")    ?   TRUE    :   FALSE;
                    $navigation_sort_apply   =  apply_filters('apto/navigation_sort_apply', $navigation_sort_apply);
                    
                    if($navigation_sort_apply)
                        {
                            //next and prevous post links 
                            add_filter('get_next_post_where', array($APTO->functions, 'get_next_post_where'), 99, 3);
                            add_filter('get_next_post_sort', array($APTO->functions, 'get_next_post_sort'));

                            add_filter('get_previous_post_where', array($APTO->functions, 'get_previous_post_where'), 99, 3); 
                            add_filter('get_previous_post_sort', array($APTO->functions, 'get_previous_post_sort'));
                        }
                }
        }
        
?>