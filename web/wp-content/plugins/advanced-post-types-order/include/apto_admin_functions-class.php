<?php

    class APTO_admin_functions
        {
            var $interface_helper;
                  
            function __construct()
                {
                    
                }
            
            function __destruct()
                {
                
                }
                
            /**
            * Create menu items
            * 
            *     
            */
            function create_menu_items()
                {
                    global $userdata, $APTO;
                    
                    //load the general styling
                    add_action('admin_print_styles' , array($this, 'admin_print_styles_general'));
                    
                    if(!$APTO->licence->licence_key_verify())
                        return;
                    
                    $settings  = APTO_functions::get_settings(); 
                    
                    //apply a filter to allow capability overwrite; usefull when in multisite environment
                    $admin_capability = apply_filters('apto_reorder_capability', 'manage_options');
                    
                    //put a menu for all custom_type
                    $ignore = array (
                                                'revision',
                                                'nav_menu_item'
                                                );
                    $post_types = get_post_types($ignore);
                                 
                    $location_menus = $this->get_available_menu_locations();
                    
                    //check for removed menu and relocate sorts if belong to those
                    $this->check_removed_menus_and_relocate($location_menus);
                       
                    foreach( $location_menus as $location_menu_slug    =>  $location_menu_data ) 
                        {
                            $hide_reorder_interface =   FALSE;
                            //check settings for hide
                            if(isset($settings['show_reorder_interfaces'][$location_menu_slug]) && $settings['show_reorder_interfaces'][$location_menu_slug] == 'hide')
                                $hide_reorder_interface =   TRUE;                                
                            //filter
                            $hide_reorder_interface =   apply_filters('apto/admin/reorder-interface/hide', $hide_reorder_interface, $location_menu_data);
                                
                            if($hide_reorder_interface  === TRUE)
                                continue;
                                
                            //check for capability
                            if(!current_user_can($admin_capability))
                                {
                                    //get menu items
                                    $menu_sorts =   $this->get_tabs($location_menu_slug);
                                    if($menu_sorts <    1)
                                        continue;
                                    
                                    //check for user capability on at least one sort item
                                    $capability =   '';
                                    foreach($menu_sorts as $menu_sort)
                                        {
                                            $sort_required_capability   =   get_post_meta($menu_sort->ID, '_capability', TRUE);
                                            $sort_required_capability   =   apply_filters('apto/wp-admin/reorder-interface/sort-view-required-capability', $sort_required_capability, $menu_sort->ID);
                                            if(current_user_can($sort_required_capability))
                                                {
                                                    $capability =   $sort_required_capability;
                                                    break;   
                                                }
                                        }
                                    
                                    //continue if no capabioity on any
                                    if($capability == '')
                                        continue;
                                    
                                }
                                else
                                $capability =   $admin_capability;  
                            
                            $menu_title = apply_filters('apto/admin/menu_title', __( 'Re-Order', 'apto'), $location_menu_data['post_type']);
                                
                            $hookID   = add_submenu_page($location_menu_slug, $menu_title, $menu_title, $capability, 'apto_' . $location_menu_data['slug'], array($this, 'load_reorder_interface') );

                            //load the interface helper
                            add_action('load-' . $hookID , array($this, 'load_dependencies'));
                            add_action('all_admin_notices' , array($this, 'admin_notices'));
                            
                            add_action('admin_print_styles-' . $hookID , array($this, 'admin_print_styles'));
                            add_action('admin_print_scripts-' . $hookID , array($this, 'admin_print_scripts'));
                            
                        }
    
                }
                
            static function get_available_menu_locations()
                {
                    global $menu;
                                                            
                    $location_menus = array();
                    
                    $allow_areas =   array(
                                            'edit.php',
                                            'upload.php'
                                            );
                    
                    //filter the menus
                    foreach($menu as $key   =>  $menu_item)
                        {
                            foreach($allow_areas as $allow_area)
                                {
                                    if(strpos($menu_item[2], $allow_area) === 0)   
                                        $location_menus[]   =   $menu_item;   
                                }
                        }
                    
                    $locations  =   array();    
                    foreach($location_menus as $location_menus_item)
                        {
                            $menu_title     =   $location_menus_item[0];
                            $tags           =   array( 'p', 'span');
                            $menu_title     =   preg_replace( '#<(' . implode( '|', $tags) . ')[^>]+>.*?</\1>#s', '', $menu_title);
                            $menu_title     =   trim(strip_tags($menu_title));
                            
                            $post_type      =   '';
                            if(strpos($location_menus_item[2], "?")    === FALSE)
                                $post_type      =   'post';
                                else
                                {
                                    $link_query         =   parse_url($location_menus_item[2]);
                                    parse_str($link_query['query'], $output);
                                    
                                    $post_type  =   $output['post_type'];
                                }
                            $locations[$location_menus_item[2]] =   array(
                                                                                            'slug'      =>  sanitize_title($location_menus_item[2]),  
                                                                                            'name'      =>  $menu_title,
                                                                                            'post_type' =>  $post_type 
                                                                                            );
                        }

                    return $locations;
                }
                
                
            function get_tabs($menu_location)
                {                    
                    global $wpdb;
                    
                    $tabs   =   array();
                    
                    $mysql_query = "SELECT * FROM ". $wpdb->posts ."
                                        INNER JOIN ". $wpdb->postmeta ." AS PM ON (". $wpdb->posts .".ID = PM.post_id)
                                        WHERE ". $wpdb->posts .".post_parent = 0  
                                                AND ". $wpdb->posts .".post_type = 'apto_sort' 
                                                AND ". $wpdb->posts .".post_status = 'publish' 
                                                AND PM.meta_key = '_location' AND PM.meta_value = '".$menu_location."'";
                    $results =   $wpdb->get_results($mysql_query); 
                    
                    foreach($results as $result)
                        {
                            $tabs[]     =   (object)$result;   
                        }
                    
                    if(!is_object($this->interface_helper))
                        $this->load_dependencies();
                    
                    //check for any order
                    $current_menu_slug  =   $this->interface_helper->get_current_menu_location_slug();
                    
                    $menu_order =   get_option('apto-menu-tabs-order-'  .   $current_menu_slug);
                    if(is_array($menu_order)    &&  count($menu_order) > 1)
                        {
                            $sorted_tabs =  array();
                            foreach($menu_order as  $sort_id)
                                {
                                    //try to find the sort in the list 
                                    foreach ($tabs  as  $tab)   
                                        {
                                            if($tab->ID ==  $sort_id)
                                                {
                                                    $sorted_tabs[]  =   $tab;
                                                    break;   
                                                }
                                        }
                                }
                                
                            //add the remaining items
                            foreach ($tabs  as  $tab)   
                                {
                                    if(!in_array($tab->ID, $menu_order))
                                        {
                                            $sorted_tabs[]  =   $tab;
                                        }
                                }
                                
                            $tabs   =   $sorted_tabs;
                            
                        }
                   
                   
                    return $tabs;
                }
                
                
            function load_reorder_interface()
                {
                    $APTO_interface         = new APTO_interface();
                    $APTO_interface->reorder_interface(); 
                }
                
            function load_dependencies()
                {
                    //turn buffering when APTO interface 
                    ob_Start();
                    
                    include_once(APTO_PATH . '/include/apto_interface_helper-class.php');
                    include_once(APTO_PATH . '/include/apto_interface-class.php');
                    
                    if(!is_object($this->interface_helper))
                        $this->interface_helper     =   new APTO_interface_helper();
                }
                
            function admin_notices()
                {
                    $messages = array();
                    if(isset($_GET['settings_saved']) && $_GET['settings_saved'] == 'true')
                        $messages[] =   'Sort settings saved.';    
                        
                    if(isset($_GET['sort_deleted']) && $_GET['sort_deleted'] == 'true')
                        $messages[] =   'Sort deleted.';
                    
                    if(count($messages) > 0)
                        {
                            echo "<div id='notice' class='updated fade'><p>". implode("</p><p>", $messages )  ."</p></div>";
                        }
                }
                  
            function admin_print_styles()
                {
                    wp_register_style('CPTStyleSheets', APTO_URL . '/css/apto.css');
                    wp_enqueue_style( 'CPTStyleSheets');   
                }
                
            function admin_print_styles_general()
                {
                    wp_register_style('APTO_GeneralStyleSheet', APTO_URL . '/css/general.css');
                    wp_enqueue_style( 'APTO_GeneralStyleSheet');   
                }
                
            function admin_print_scripts()
                {
                    wp_enqueue_script('jquery');                         
                    wp_enqueue_script('jquery-ui-core');
                    wp_enqueue_script('jquery-ui-sortable');
                    wp_enqueue_script('jquery-ui-widget');
                    wp_enqueue_script('jquery-ui-mouse');
                    
                    $myJavascriptFile = APTO_URL . '/js/touch-punch.min.js';
                    wp_register_script('touch-punch.min.js', $myJavascriptFile, array(), '', TRUE);
                    wp_enqueue_script( 'touch-punch.min.js');
                       
                    $myJavascriptFile = APTO_URL . '/js/nested-sortable.js';
                    wp_register_script('nested-sortable.js', $myJavascriptFile, array(), '', TRUE);
                    wp_enqueue_script( 'nested-sortable.js');
                     
                    $myJavascriptFile = APTO_URL . '/js/apto-javascript.js';
                    wp_register_script('apto-javascript.js', $myJavascriptFile);
                    wp_enqueue_script( 'apto-javascript.js');  
                }
                
            
            function check_removed_menus_and_relocate($location_menus)
                {
                    $settings  = APTO_functions::get_settings();
                    
                    //this setting has never been set by user
                    if(count($settings['show_reorder_interfaces']) < 1 || count($location_menus) <  1)
                        return;
                    
                    $first_menu =   $this->get_first_available_menu($location_menus);
                    if($first_menu === FALSE)
                        {
                            $apto_system_messages['relocate'][]   =   __( 'All interfaces are set to hide, at least a visible is required. You can change tht from Settings', 'apto' );
                        }
                        else
                        {
                            $apto_system_messages['relocate'] =   array();
                            
                            //get all sorts
                            $args = array(
                                            'post_type'             => 'apto_sort',
                                            'post_parent'           =>  0,
                                            'orderby'               => 'ID',
                                            'order'                 => 'ASC',
                                            'posts_per_page'        =>  -1,
                                            'ignore_custom_sort'    =>  TRUE
                                            );
                            $custom_query = new WP_Query($args);
                            if($custom_query->have_posts())
                                {
                                    global $post;
                                    $_wp_query_post =   $post;
                                    global $apto_system_messages;
                                                                
                                    while($custom_query->have_posts())
                                        {
                                            $custom_query->the_post();
                                            
                                            $sort_location  =   get_post_meta($post->ID, '_location', TRUE);
                                            
                                            //check if the menu still exists
                                            if(!isset($location_menus[$sort_location]) || (isset($settings['show_reorder_interfaces'][$sort_location]) && $settings['show_reorder_interfaces'][$sort_location] == 'hide'))
                                                {
                                                    //relocate the item
                                                    update_post_meta($post->ID, '_location', $first_menu);
                                                    
                                                    //show relocate messages
                                                    $apto_system_messages['relocate'][]   =   'Sort ' . '<b>' . $post->post_title . ' ('. $post->ID .' )</b>'. ' has been relocated to ' .$location_menus[$first_menu]['name'] . ' menu';
                                                }
                                            
                                        }
                                        
                                    //wp_reset_postdata();
                                    //use this instead as using a setup_postdata() without any query will reset to nothing
                                    $post   =   $_wp_query_post;
               
                                }
                        }
                        
                    if( isset($apto_system_messages['relocate'])    &&  is_array ( $apto_system_messages['relocate'] )   &&  count($apto_system_messages['relocate']) > 0)
                        {
                            array_unshift($apto_system_messages['relocate'], __( "Advanced Post Types Order - At least one menu has changed:", 'apto' ));
                            add_action('admin_notices', array($this, 'relocate_nottices'));
                        }
                  
                }
                
            function get_first_available_menu($location_menus)
                {
                    $settings  = APTO_functions::get_settings();
                    
                    //this setting has never been set by user
                    if(count($settings['show_reorder_interfaces']) < 1 || count($location_menus) <  1)
                        {
                            reset($location_menus);
                            return key($location_menus);
                        }
                        
                    foreach($location_menus as $ocation_key =>  $location_data)
                        {
                            if(isset($settings['show_reorder_interfaces'][$ocation_key]) && $settings['show_reorder_interfaces'][$ocation_key] == 'show')
                                return $ocation_key;
                        }
                        
                    return false;
                }
                
            function relocate_nottices()
                {
                    global $apto_system_messages;
            
                    if( ! isset($apto_system_messages['relocate'])    ||  ! is_array ( $apto_system_messages['relocate'] )  || count($apto_system_messages['relocate']) < 1 )
                        return;
                    
                    echo "<div id='notice' class='updated fade'><p>". implode("</p><p>", $apto_system_messages['relocate'] )  ."</p></div>";
                }
                
            static function match_capability_with_availables   ($mathch_capability, $available_roles_capability)
                {
                    $match =  $available_roles_capability['Administrator']['capability'];
                    if($mathch_capability == '')
                        return $match;
                        
                    foreach($available_roles_capability as $role    =>  $role_data)
                        {
                            if($mathch_capability   ==  $role_data['capability'])
                                return $mathch_capability;
                        }
                       
                    return $match;
                }
            
            
            
            /**
            * Return primary sort ID matching a post type
            * 
            * This will also check the other settings like rules
            * 
            * @param mixed $post_type
            */
            function get_primary_from_similar_sorts($sortID)
                {
                    
                    $similar_sorts  =   $this->get_similar_sorts($sortID);
                    
                    if(count($similar_sorts)    <   1)
                        $sortID;
                        
                           
                    $similar_sorts[]    =   $sortID;
                    sort($similar_sorts);
                    
                    reset($similar_sorts);
                    
                    return current($similar_sorts);                    
                    
                }
            
            
            /**
            * Return a list of similar sorts
            * 
            * @param mixed $sortID
            */
            function get_similar_sorts($sortID)
                {
                    $similar_sorts =   array();   
                    
                    $current_sort_settings  =   APTO_functions::get_sort_settings($sortID);
                    
                    //get all sorts
                    $args = array(
                                    'post_type'             => 'apto_sort',
                                    'post_parent'           =>  0,
                                    'orderby'               => 'ID',
                                    'order'                 => 'ASC',
                                    'posts_per_page'        =>  -1,
                                    'post__not_in'          =>  array($sortID),
                                    'ignore_custom_sort'    =>  TRUE
                                    );
                    $custom_query = new WP_Query($args);
                    if($custom_query->have_posts())
                        {
                            global $post;
                            $_wp_query_post =   $post;
                                                        
                            while($custom_query->have_posts())
                                {
                                    $custom_query->the_post();
                                    
                                    $found_similar  =   TRUE;
                                    
                                    $sort_settings  =   APTO_functions::get_sort_settings($post->ID);
                                    
                                    //check if autosort is turned on
                                    if($sort_settings['_autosort']   != 'yes')
                                        continue;
                                    
                                    //check if same post types rules
                                    if((count($current_sort_settings['_rules']['post_type']) != count($sort_settings['_rules']['post_type']))
                                        ||
                                        count(array_diff($current_sort_settings['_rules']['post_type'], $sort_settings['_rules']['post_type'])) > 0 
                                        )
                                        {
                                            
                                            continue;
                                        }
                                        
                                    //check if same taxonomy settings
                                    if(
                                        $current_sort_settings['_rules']['taxonomy_relation']    !=  $sort_settings['_rules']['taxonomy_relation']
                                        ||
                                        (count($current_sort_settings['_rules']['taxonomy']) != count($sort_settings['_rules']['taxonomy']))
                                        ||
                                        $this->taxonomy_settings_diff_exist($current_sort_settings['_rules']['taxonomy'], $sort_settings['_rules']['taxonomy']) === TRUE
                                        )
                                        {
                                            
                                            continue;
                                        }
                                        
                                    
                                    //check if same meta settings
                                    if(
                                        $current_sort_settings['_rules']['meta_relation']    !=  $sort_settings['_rules']['meta_relation']
                                        ||
                                        (count($current_sort_settings['_rules']['meta']) != count($sort_settings['_rules']['meta']))
                                        ||
                                        $this->meta_settings_diff_exist($current_sort_settings['_rules']['meta'], $sort_settings['_rules']['meta']) === TRUE
                                        )
                                        {
                                            
                                            continue;
                                        }    
                                    
                                        
                                    //check if same author rules
                                    if((count($current_sort_settings['_rules']['author']) != count($sort_settings['_rules']['author']))
                                        ||
                                        count(array_diff($current_sort_settings['_rules']['author'], $sort_settings['_rules']['author'])) > 0 
                                        )
                                        {
                                            
                                            continue;
                                        }
                                        
                                    //check for same conditionals
                                    if(
                                        (count($current_sort_settings['_conditionals']) != count($sort_settings['_conditionals']))
                                        ||
                                        $this->conditional_settings_diff_exist($current_sort_settings['_conditionals'], $sort_settings['_conditionals']) === TRUE
                                        )
                                        {
                                            
                                            continue;
                                        }
                                        
                                    if($found_similar   === TRUE)
                                        {
                                            $similar_sorts[]  =     $post->ID;
                                           
                                            $link_argv  =   array(
                                                                                'sort_id' => $post->ID
                                                                                );
                                            $link_argv['page'] =   'apto_' . sanitize_title($sort_settings['_location']);
                                            
                                            $link_argv['base_url'] =   admin_url( $sort_settings['_location'] );;
                                                                                    
                                            $url  = APTO_interface_helper::get_item_link($link_argv) ;

                                            $message                =   __( "Notice: There is", 'apto' ) . ' <b><a href="'. $url .'">'. __( "another", 'apto' ) .'</a></b> '.  __( "similar sort", 'apto' );
                                            if($post->ID < $sortID)
                                                $message    .=  ' which will be used instead.';
                                                else
                                                $message    .=  ', however current (primary) list will be used.';
                                            
                                            $apto_system_nottices[] =   $message;
                                        }
               
                                }
                                
                            //wp_reset_postdata();
                            //use this instead as using a setup_postdata() without any query will reset to nothing
                            $post   =   $_wp_query_post;
       
                        }
                        
                        
                    return $similar_sorts;    
                    
                    
                }
            
            /**
            * Output nottices for similar sorts
            *                 
            * @param mixed $sortID
            * @param mixed $output
            */
            function nottice_similar_sorts($sortID)
                {
                    if($sortID ==    '')
                        return;
                    
                    $current_sort_settings  =   APTO_functions::get_sort_settings($sortID);
                    
                    //check if autsort Yes, oterwise we don't care about similar sorts.
                    if($current_sort_settings['_autosort']   != 'yes')
                        return;
                    
                    $apto_system_nottices   =   array();
                    
                    $similar_sorts  =   $this->get_similar_sorts($sortID);
                    
                    if(count($similar_sorts)    <   1)
                        return;
                        
                    
                    foreach($similar_sorts  as  $similar_sort)
                        {
                            $sort_settings  =   APTO_functions::get_sort_settings($similar_sort);
                            
                            $link_argv  =   array(
                                                                'sort_id' => $similar_sort
                                                                );
                            $link_argv['page'] =   'apto_' . sanitize_title($sort_settings['_location']);
                            
                            $link_argv['base_url'] =   admin_url( $sort_settings['_location'] );;
                                                                    
                            $url  = APTO_interface_helper::get_item_link($link_argv) ;

                            $message                =   __( "Notice: There is", 'apto' ) . ' <b><a href="'. $url .'">'. __( "another", 'apto' ) .'</a></b> '.  __( "similar sort", 'apto' );
                            if($similar_sort < $sortID)
                                $message    .=  ' which will be used instead.';
                                else
                                $message    .=  ', however current (primary) list will be used.';
                            
                            $apto_system_nottices[] =   $message;   
                            
                        }
               
                    if(count($apto_system_nottices) >   0)
                        echo "<div id='notice' class='updated fade'><p>". implode("</p><p>", $apto_system_nottices )  ."</p></div>";   
                }
                
       
       
       
            function taxonomy_settings_diff_exist($taxonomy_1, $taxonomy_2)
                {
                    foreach($taxonomy_1 as $taxonomy_1_item)   
                        {
                            $found = FALSE;
                            foreach($taxonomy_2 as $key =>  $taxonomy_2_item)
                                {
                                    $terms_1    =   $taxonomy_1_item['terms'];
                                    sort($terms_1);
                                    $terms_2    =   $taxonomy_2_item['terms'];
                                    sort($terms_2);
                                    
                                    //compare
                                    if( 
                                        $taxonomy_1_item['taxonomy'] ==  $taxonomy_2_item['taxonomy']
                                        &&
                                        $taxonomy_1_item['operator'] ==  $taxonomy_2_item['operator']
                                        &&
                                        $taxonomy_1_item['include_children'] ===  $taxonomy_2_item['include_children']
                                        && 
                                        count($terms_1)  ==  count($terms_2)
                                        &&
                                        count(array_diff($terms_1, $terms_2)) < 1  
                                        )
                                        {
                                            $found = TRUE;
                                            
                                            //remove the current 
                                            unset($taxonomy_2[$key]);
                                            break;   
                                        }
                                }
                                
                            if($found   === FALSE)
                                return TRUE;
                        }
                       
                    return FALSE;   
                }
            
            
            function meta_settings_diff_exist($meta_1, $meta_2)
                {
                    foreach($meta_1 as $meta_1_item)   
                        {
                            $found = FALSE;
                            foreach($meta_2 as $key =>  $meta_2_item)
                                {
                                    
                                    //compare
                                    if( 
                                        $meta_1_item['meta_key'] ==  $meta_2_item['meta_key']
                                        && $meta_1_item['value_type'] ==  $meta_2_item['value_type']
                                        && $meta_1_item['value'] ==  $meta_2_item['value']
                                        && $meta_1_item['compare'] ==  $meta_2_item['compare']
                                        && $meta_1_item['type'] ==  $meta_2_item['type']
                                        )
                                        {
                                            $found = TRUE;
                                            
                                            //remove the current 
                                            unset($taxonomy_2[$key]);
                                            break;   
                                        }
                                }
                                
                            if($found   === FALSE)
                                return TRUE;
                        }
                       
                    return FALSE;   
                }
              
            
            function conditional_settings_diff_exist($conditional_1, $conditional_2)
                {
                    
                    foreach($conditional_1 as $conditional_1_group)   
                        {
                            $found_group_match  = FALSE;
                            foreach($conditional_2 as $key =>  $conditional_2_group)
                                {
                                    if(count($conditional_1_group) !=  count($conditional_2_group))
                                        continue;
                                    
                                    $found_group_match  =   $this->conditional_arrays_diff_exist($conditional_1_group, $conditional_2_group);
                                    
                                    if($found_group_match  === TRUE)
                                        break;
                         
                                }
                         
                            if($found_group_match  === FALSE)
                                return TRUE;
                        }

                       
                    return FALSE;   
                }
                
            function conditional_arrays_diff_exist($conditional_1_group, $conditional_2_group)
                {
                    $found_group_match  =   TRUE;
                    foreach($conditional_1_group    as  $conditional_1_group_item)
                        {
                            $found_group_item_match  =   FALSE;
                            foreach($conditional_2_group    as  $conditional_2_group_item)
                                {
                                    if(count($conditional_1_group_item) !=  count($conditional_2_group_item))
                                        continue;
                                    
                                    if($conditional_1_group_item === $conditional_2_group_item)
                                        {
                                            $found_group_item_match  = TRUE;
                                            break;   
                                        }
                                }
                                
                            if($found_group_item_match  === FALSE)
                                {
                                    $found_group_match  =   FALSE;
                                    break;
                                }
                        }
                        
                    return $found_group_match;     
                }
              
            
            
        }



?>