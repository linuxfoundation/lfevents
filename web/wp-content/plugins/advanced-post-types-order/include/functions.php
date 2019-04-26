<?php
        
        /**
        * Enhanced post adjancent links
        * 
        * @param mixed $format
        * @param mixed $link
        * @param mixed $args
        */
        function previous_post_type_link($format='&laquo; %link', $link='%title', $args = array())
            {
                global $APTO;
                
                $APTO->functions->adjacent_post_type_link($format, $link, $args,  TRUE);   
            }
            
        /**
        * Enhanced post adjancent links
        * 
        * @param mixed $format
        * @param mixed $link
        * @param mixed $args
        */
        function next_post_type_link($format='&laquo; %link', $link='%title', $args = array())
            {
                global $APTO;
                
                $APTO->functions->adjacent_post_type_link($format, $link, $args, FALSE);   
            }
            
        
        function apto_get_adjacent_post($args, $previous)
            {
                global $APTO;
                
                if ( $previous && is_attachment() )
                    $post = & get_post($GLOBALS['post']->post_parent);
                    else
                    $post = $APTO->functions->apto_get_adjacent_post($args, $previous);
                
                if ( !$post )
                    return FALSE;
                    
                return $post;
            }    
        
    
        function apto_is_plugin_active( $plugin ) 
            {
                return in_array( $plugin, (array) get_option( 'active_plugins', array() ) ) || apto_is_plugin_active_for_network( $plugin );
            }

        function apto_is_plugin_inactive( $plugin ) 
            {
                return ! is_plugin_active( $plugin );
            }

        function apto_is_plugin_active_for_network( $plugin ) 
            {
                if ( !is_multisite() )
                    return false;

                $plugins = get_site_option( 'active_sitewide_plugins');
                if ( isset($plugins[$plugin]) )
                    return true;

                return false;
            }

        //turn on output buffering for certain actions to allow redirects
        add_action('plugins_loaded', 'APTO_muplugins_loaded', 1);    
        function APTO_muplugins_loaded()
            {
                if(!is_admin())
                    return;
                    
                if(isset($_GET['delete_sort']) || isset($_GET['new-item']) || isset($_POST['apto_sort_settings_form_submit']))
                    ob_start();   
            }
          
            
        function APTO_register_post_types() 
            {
                $args = array(
                                'public'                =>  FALSE,
                                'exclude_from_search'   =>  TRUE,
                                'publicly_queryable'    =>  FALSE,
                                'show_ui'               =>  FALSE,
                                'show_in_menu'          =>  FALSE,  
                                'show_in_admin_bar'     =>  FALSE,
                                'show_in_nav_menus'     =>  FALSE,
                                'has_archive'           =>  FALSE,
                                'rewrite'               =>  FALSE
                              );

                if(!post_type_exists( 'apto_sort' ))
                    register_post_type( 'apto_sort', $args );
            }
        add_action( 'init', 'APTO_register_post_types', 1 );    

  
?>