<?php

    class APTO_updater
        {
               
            function __construct()
                {
                    
                }
            
            function __destruct()
                {
                
                }
                
                
            static function check_version_update()
                {
                    //check if the apto_sort post type exists, oterwise call the function to register it
                    if(!post_type_exists( 'apto_sort' ))
                        APTO_register_post_types();
                    
                    //check on the database version and if need update
                    $settings  = APTO_functions::get_settings();
                    
                    //check the table                    
                    if(!isset($settings['database_version']) || version_compare( $settings['database_version'], APTO_DB_VERSION , '<' )     ||  ! self::table_exists())
                        self::update_tables();
                    
                    //check for settings
                    if(!isset($settings['plugin_version']) || version_compare( $settings['plugin_version'], 1 , '<=' )) 
                        self::create_settings();
                        else if (version_compare( $settings['plugin_version'], APTO_VERSION , '<' ))
                        {
                            self::update_from_old_version_settings();   
                        }
                    
                    //refetch the latest settings dataset
                    $settings  = APTO_functions::get_settings();
                    
                    //use a later filter to import from an older format
                    if(isset($settings['schedule_data_import']) && $settings['schedule_data_import'] === TRUE)
                        {
                            unset($settings['schedule_data_import']);
                            add_action('admin_menu', array(__CLASS__, 'old_version_data_import'), 98);
                            
                            APTO_functions::update_settings($settings);   
                        }
                        
                    //run a later filter to create sorts for every post type
                    if(isset($settings['schedule_create_default_sorts']) && $settings['schedule_create_default_sorts'] === TRUE)
                        {
                            unset($settings['schedule_create_default_sorts']);
                            add_action('admin_menu', array(__CLASS__, 'create_default_sorts'), 98);
                            
                            APTO_functions::update_settings($settings);   
                        }
                       
                }
            
            /**
            * Create the required plugin settings
            * 
            */
            static function create_settings()
                {
                    $settings  = APTO_functions::get_settings();      
                    
                    //update and further processing area
                    $settings['plugin_version']   =   APTO_VERSION;
                    
                    //check if it's a blank install or update from V2
                    $old_version_options = get_option('cpto_options');
                    if (isset($old_version_options['code_version']) && version_compare( strval( '2.5' ), $old_version_options['code_version'] , '<' ) === TRUE)
                        {
                            $settings['schedule_data_import']   =   true;     
                        }
                        else
                        {
                            $settings['schedule_create_default_sorts']   =   true;
                        }
            
                    
                    APTO_functions::update_settings($settings);
                }
            
            
            static function create_default_sorts()
                {
                    $settings  = APTO_functions::get_settings(); 
                    
                    include_once(APTO_PATH . '/include/apto_interface_helper-class.php');
                    
                    $roles_capability = APTO_functions::roles_capabilities();
                            
                    //create default sorts instances for post tyes
                    $ignore = array (
                                        'revision',
                                        'nav_menu_item'
                                        );
                    $blog_post_types    =   APTO_functions::get_post_types($ignore);   
                    
                    $ignore = array (
                                        'acf'
                                        );
                                        
                    foreach ($blog_post_types as $key => $post_type)
                        {
                            if (in_array($post_type, $ignore))
                                continue;
                                
                            $post_type_data    =   get_post_type_object($post_type);
                             
                            //ignore if not show in menus
                            if($post_type_data  ==  'any'   ||  $post_type_data->show_in_menu === FALSE)
                               continue;
                                
                            $post_type_menu_item   =   '';
                            switch($post_type)
                                {
                                    case 'post';
                                                    $post_type_menu_item    =   'edit.php';
                                                    break;
                                    
                                    case 'attachment';
                                                    $post_type_menu_item    =   'upload.php';
                                                    break;
                                                    
                                    default:
                                                    $post_type_menu_item    =   'edit.php?post_type=' . $post_type;
                                }
                                
                            if(!is_bool($post_type_data->show_in_menu) && $post_type_data->show_in_menu != '')
                                $post_type_menu_item    =   $post_type_data->show_in_menu;
                             
                             $options    =   array(
                                                        '_title'                =>  'Sort #' . $post_type_data->label,
                                                        '_description'          =>  '',
                                                        '_location'             =>  $post_type_menu_item,
                                                        '_autosort'             =>  'yes',
                                                        '_adminsort'            =>  'yes',
                                                        '_pto_interface_sort'   =>  'no',
                                                        '_new_items_to_bottom'  =>  'no',
                                                        '_show_thumbnails'      =>  'no',
                                                        '_pagination'               =>  'no',
                                                        '_pagination_posts_per_page'=>  100,
                                                        '_pagination_offset_posts'  =>  5,
                                                        
                                                        '_wpml_synchronize'         =>  'no',
                                                        
                                                        '_capability'           =>  $roles_capability['Administrator']['capability']
                                                        );
                             
                             $sort_view_meta     =   array(
                                                            '_order_type'               =>  'manual',
                                                            '_view_selection'           =>  'archive',
                                                            '_view_language'            =>  APTO_functions::get_blog_language()
                                                            );  
                             $sort_id   =   self::create_post_type_sort($post_type, $options, $sort_view_meta);
                             
                        }
                        
                        
                    //hide by default certain re-order menus
                    $hide   =   array(
                                        'edit-comments.php',
                                        'edit-tags.php?taxonomy=link_category'
                                        );
                    foreach($hide as $hide_item)
                        {
                            $settings['show_reorder_interfaces'][$hide_item]    =   'hide';
                        }   
                    
                    APTO_functions::update_settings($settings);
                    
                }
            
            
            
            static function update_from_old_version_settings()
                {
                    $settings  = APTO_functions::get_settings();      
                    
                    // Since version 3.5.4 all taxonomies view selection require also a _view_language custom field
                    if( version_compare( strval( '3' ), $settings['plugin_version'] , '<=' ) === TRUE    &&  version_compare( strval( '3.5.4' ), $settings['plugin_version'] , '>' ) === TRUE)
                        {
                            self::update_V3_5_4();
                        }
                    
                    if( version_compare( strval( '3.9.8' ), $settings['plugin_version'] , '<=' ) === TRUE)
                        {
                            self::update_V3_9_8_1();
                        }
   
                    if( version_compare( strval( '4.0.8' ), $settings['plugin_version'] , '>' ))
                        {
                            self::update_V4_0_8();
                            $settings['plugin_version'] =   '4.0.8';
                        }
   
                    
                    //update and further processing area
                    $settings['plugin_version']   =   APTO_VERSION; 
                                
                    APTO_functions::update_settings($settings);  
                }
            
            static function old_version_data_import()
                {
                    $settings  = APTO_functions::get_settings();
                       
                    $roles_capability = APTO_functions::roles_capabilities();
                    
                    $old_version_options = get_option('cpto_options');
                    
                    include_once(APTO_PATH . '/include/apto_interface_helper-class.php');
                    include_once(APTO_PATH . '/include/apto_admin_functions-class.php'); 
                            
                    $roles_capability = APTO_functions::roles_capabilities();
                    
                    //create default sorts instances for post tyes
                    $ignore = array (
                                                'revision',
                                                'nav_menu_item'
                                                );
                    $blog_post_types    =   APTO_functions::get_post_types($ignore);
                    
                    $ignore = array (
                                        'acf'
                                        ); 
                    
                    foreach ($blog_post_types as $key => $post_type)
                        {
                            if (in_array($post_type, $ignore))
                                unset($blog_post_types[$key]);   
                            
                            if(isset($old_version_options['allow_post_types']) && is_array($old_version_options['allow_post_types']) && count($old_version_options['allow_post_types']) > 0)
                                {
                                    if (!in_array($post_type, $old_version_options['allow_post_types']))
                                        unset($blog_post_types[$key]);                                                 
                                }
                        }
                        
                    if(count($blog_post_types) > 0)
                        {
                            $available_menus    =   APTO_admin_functions::get_available_menu_locations();
                            
                            $autosort   =   isset($old_version_options['autosort']) ?   $old_version_options['autosort']    :   '1';
                            if($autosort    === "1")
                                $autosort   =   'yes';
                                else
                                $autosort   =   'no';
                            $adminsort   =   isset($old_version_options['adminsort']) ?   $old_version_options['adminsort']    :   '1';
                            if($adminsort    === "1")
                                $adminsort   =   'yes';
                                else
                                $adminsort   =   'no';
                            $show_thumbnails   =   isset($old_version_options['always_show_thumbnails']) ?   $old_version_options['always_show_thumbnails']    :   '';
                            if($show_thumbnails    === "1")
                                $show_thumbnails   =   'yes';
                                else
                                $show_thumbnails   =   'no';
                            

                            $capability =   APTO_admin_functions::match_capability_with_availables($old_version_options['capability'], $roles_capability);
                            
                            global $wpdb, $blog_id;
                            
                            //find out the available languages in the sort
                            $mysql_query            =   "SELECT lang FROM `" . $wpdb->base_prefix ."apto` 
                                                            GROUP BY lang";
                            $sort_languages_raw         =   $wpdb->get_results($mysql_query);
                            foreach($sort_languages_raw as $data)
                                {
                                    $sort_languages[]   =   $data->lang;
                                }
                            unset($sort_languages_raw);
                                
                            foreach ($blog_post_types as $key => $post_type)
                                {
                                    $post_type_data    =   get_post_type_object($post_type);
                                     
                                    //ignore if not show in menus
                                    if($post_type_data  ==  'any'   ||  $post_type_data->show_in_menu === FALSE)
                                        continue;
                                     
                                    $post_type_menu_item   =   '';
                                    switch($post_type)
                                        {
                                            case 'post';
                                                            $post_type_menu_item    =   'edit.php';
                                                            break;
                                            
                                            case 'attachment';
                                                            $post_type_menu_item    =   'upload.php';
                                                            break;
                                                            
                                            default:
                                                            $post_type_menu_item    =   'edit.php?post_type=' . $post_type;
                                        }
                                        
                                    if(!is_bool($post_type_data->show_in_menu) && $post_type_data->show_in_menu != '')
                                        $post_type_menu_item    =   $post_type_data->show_in_menu;
                                     
                                     $options    =   array(
                                                                '_title'                =>  'Sort #' . $post_type_data->label,
                                                                '_description'          =>  '',
                                                                '_location'             =>  $post_type_menu_item,
                                                                '_autosort'             =>  $autosort,
                                                                '_adminsort'            =>  $adminsort,
                                                                '_pto_interface_sort'   =>  'no',
                                                                '_new_items_to_bottom'  =>  'no',
                                                                '_show_thumbnails'      =>  $show_thumbnails,
                                                                '_pagination'               =>  'no',
                                                                '_pagination_posts_per_page'=>  100,
                                                                '_pagination_offset_posts'  =>  5,
                                                                
                                                                '_wpml_synchronize'         =>  'no',
                                                                
                                                                '_capability'           =>  $capability
                                                                );
                                                                
                                     //create the sort and a default sort view as archive
                                     $sort_view_meta     =   array(
                                                            '_order_type'               =>  'manual',
                                                            '_view_selection'           =>  'archive',
                                                            '_view_language'            =>  APTO_functions::get_blog_language()
                                                            
                                                            );    
                                     $sort_id   =   self::create_post_type_sort($post_type, $options, $sort_view_meta);
                                     
                                     $old_options_post_type_terms   =   array();
                                     
                                     //check sort type auto or manual
                                     if(isset($old_version_options['taxonomy_settings']) && is_array($old_version_options['taxonomy_settings']) && isset($old_version_options['taxonomy_settings'][$post_type])
                                                && is_array($old_version_options['taxonomy_settings'][$post_type]) && count($old_version_options['taxonomy_settings'][$post_type]) > 0)
                                                {
                                                    foreach($old_version_options['taxonomy_settings'][$post_type] as $option_post_type_selection  =>    $data_block)
                                                        {     
                                                            //check if the taxonomy still exists and is assigned to current post type
                                                            if($option_post_type_selection != '_archive_' && (!taxonomy_exists($option_post_type_selection) || !in_array($option_post_type_selection , get_object_taxonomies($post_type))))
                                                                continue;
                                                            
                                                            foreach($data_block as $term_id =>  $data)
                                                                {
                                                                    //check if it's auto
                                                                    if($data['order_type'] != 'auto')
                                                                        continue;
                                                                        
                                                                    if(!isset($data['order_by']) || $data['order_by'] == '')
                                                                        $data['order_by'] = '_default_';
                                                                    if(!isset($data['custom_field_name']) || $data['custom_field_name'] == '')
                                                                        $data['custom_field_name'] = '';
                                                                    if(!isset($data['order'])   ||  $data['order']  ==  '')
                                                                        $data['order'] = 'DESC';
                                                                    
                                                                    $old_options_post_type_terms[]  =   $term_id;
                                                                    
                                                                    if($option_post_type_selection == '_archive_')
                                                                        {
                                                                            foreach($sort_languages as $language)
                                                                                {
                                                                                    //check if already created this sort view
                                                                                    $attr           =   array(
                                                                                                                '_view_selection'           =>  'archive',
                                                                                                                '_view_language'            =>  $language
                                                                                                                );
                                                                                    $sort_view_id   =   APTO_functions::get_sort_view_id_by_attributes($sort_id, $attr);
                                                                                    
                                                                                    //create the view if does not exists
                                                                                    if($sort_view_id    ==  '')
                                                                                        {
                                                                                            $sort_view_meta     =   array(
                                                                                                                                '_view_selection'           =>  'archive',
                                                                                                                                '_view_language'            =>  $language
                                                                                                                            );    
                                                                                            $sort_view_id       =   APTO_interface_helper::create_view($sort_id, $sort_view_meta);   
                                                                                        }
                                                                                    
                                                                                    update_post_meta($sort_view_id, '_order_type', 'auto');
                                                                                    update_post_meta($sort_view_id, '_auto_order_by', $data['order_by']); 
                                                                                    update_post_meta($sort_view_id, '_auto_custom_field_name', $data['custom_field_name']);
                                                                                    update_post_meta($sort_view_id, '_auto_custom_field_type', $data['custom_field_type']); 
                                                                                    update_post_meta($sort_view_id, '_auto_order', $data['order']);
                                                                                }
                                                                            continue;
                                                                        }
                                                                        
                                                                    //check if the term still exists
                                                                    if(!term_exists(intval($term_id), $option_post_type_selection))
                                                                        continue;
                                                                    
                                                                    $sort_view_meta     =   array(
                                                                                                        '_order_type'               =>  'auto',
                                                                                                        '_view_selection'           =>  'taxonomy',
                                                                                                        '_taxonomy'                 =>  $option_post_type_selection,
                                                                                                        '_term_id'                  =>  $term_id,
                                                                                                        '_view_language'            =>  APTO_functions::get_blog_language()
                                                                                                    );    
                                                                    $sort_view_id       =   APTO_interface_helper::create_view($sort_id, $sort_view_meta);
                                                                        
                                                                    update_post_meta($sort_view_id, '_order_type', 'auto');
                                                                    update_post_meta($sort_view_id, '_auto_order_by', $data['order_by']); 
                                                                    update_post_meta($sort_view_id, '_auto_custom_field_name', $data['custom_field_name']);
                                                                    update_post_meta($sort_view_id, '_auto_custom_field_type', $data['custom_field_type']);
                                                                    update_post_meta($sort_view_id, '_auto_order', $data['order']);
                                                                }
                                                        }
                                                    
                                                    
                                                }
                                     
                                     //process data in the table
                                     $mysql_query   =   "SELECT term_id, taxonomy FROM " . $wpdb->base_prefix ."apto
                                                            WHERE blog_id = '". $blog_id ."' AND post_type =   '" . $post_type  . "'
                                                            GROUP BY term_id";
                                     $post_type_terms       =   $wpdb->get_results($mysql_query);
                                     
                                     
                                     foreach ($post_type_terms as $data)
                                        {
                                            //check if is set as autosort
                                            //allow in case user change his mind and switch back to manual sort
                                            /*
                                            if(in_array($data->term_id, $old_options_post_type_terms))
                                                continue;
                                            */
                                                
                                            //check if the term still exists
                                            if($data->term_id > 0 && !term_exists(intval($data->term_id), $data->taxonomy))
                                                continue;
                                                
                                            if($data->term_id < 1)
                                                {
                                                    //process each language as there can be sort for each
                                                    foreach($sort_languages as $language)
                                                        { 
                                                            $mysql_query   =   "SELECT post_id FROM " . $wpdb->base_prefix ."apto
                                                                                    WHERE blog_id = '". $blog_id ."' AND post_type =   '" . $post_type  . "' AND term_id ='".$data->term_id ."' AND taxonomy = '".$data->taxonomy."' AND lang = '".$language."'
                                                                                    ORDER BY id ASC";
                                                            $post_type_term_sort_data      =   $wpdb->get_results($mysql_query);
                                                            
                                                            if(count($post_type_term_sort_data) < 1)
                                                                continue;
                                                            
                                                            $attr           =   array(
                                                                                        '_view_selection'           =>  'archive',
                                                                                        '_view_language'            =>  $language
                                                                                        );
                                                            $sort_view_id   =   APTO_functions::get_sort_view_id_by_attributes($sort_id, $attr);
                                                            
                                                            //create the view if does not exists
                                                            if($sort_view_id    ==  '')
                                                                {
                                                                    $sort_view_meta     =   array(
                                                                                                        '_view_selection'           =>  'archive',
                                                                                                        '_view_language'            =>  $language,
                                                                                                        '_order_type'               =>  'manual'
                                                                                                    );    
                                                                    $sort_view_id       =   APTO_interface_helper::create_view($sort_id, $sort_view_meta);   
                                                                }
                                                            
                                                            //create the entries within the apto_sort_list table
                                                            $mysql_query    =   "INSERT INTO `". $wpdb->prefix ."apto_sort_list`
                                                                                      (id, sort_view_id, object_id)
                                                                                    VALUES ";
                                                            $first = TRUE;
                                                            foreach($post_type_term_sort_data as $sort_post_data)
                                                                {
                                                                    if($first === TRUE)   
                                                                        $first  = FALSE;
                                                                        else
                                                                        $mysql_query    .=  ", \n";
                                                                        
                                                                    $mysql_query  .= "(null, ". $sort_view_id .", ". $sort_post_data->post_id .")";
                                                                }
                                                            $results = $wpdb->get_results($mysql_query);
                                                        }
                                                }
                                                else
                                                {
                                                    //create the sort entries for this
                                                    $mysql_query   =   "SELECT post_id FROM " . $wpdb->base_prefix ."apto
                                                                            WHERE blog_id = '". $blog_id ."' AND post_type =   '" . $post_type  . "' AND term_id ='".$data->term_id ."' AND taxonomy = '".$data->taxonomy."'
                                                                            ORDER BY id ASC";
                                                    $post_type_term_sort_data      =   $wpdb->get_results($mysql_query);
                                                    
                                                    
                                                    $sort_view_meta     =   array(
                                                                            '_order_type'               =>  'manual',
                                                                            '_view_selection'           =>   'taxonomy',
                                                                            '_taxonomy'                 =>   $data->taxonomy,
                                                                            '_term_id'                  =>   $data->term_id,
                                                                            '_view_language'            =>  APTO_functions::get_blog_language()
                                                                            );
                                                    
                                                    $sort_view_id       =   APTO_interface_helper::create_view($sort_id, $sort_view_meta);
                                                    
                                                    //create the entries within the apto_sort_list table
                                                    $mysql_query    =   "INSERT INTO `". $wpdb->prefix ."apto_sort_list`
                                                                              (id, sort_view_id, object_id)
                                                                            VALUES ";
                                                    $first = TRUE;
                                                    foreach($post_type_term_sort_data as $sort_post_data)
                                                        {
                                                            if($first === TRUE)   
                                                                $first  = FALSE;
                                                                else
                                                                $mysql_query    .=  ", \n";
                                                                
                                                            $mysql_query  .= "(null, ". $sort_view_id .", ". $sort_post_data->post_id .")";
                                                        }
                                                    $results = $wpdb->get_results($mysql_query);
                                                }

                                            
                                        }
                                        
                                     //mark as show this menu where post type reside
                                     $settings['show_reorder_interfaces'][$post_type_menu_item] =   'show';
                                }   
                            
                        }
                        
                    //migrating the remaining settings
                    $ignore_supress_filters   =   isset($old_version_options['ignore_supress_filters']) ?   $old_version_options['ignore_supress_filters']    :   '';
                    $settings['ignore_supress_filters'] =   $ignore_supress_filters;
                    
                    //mark all remaining menus as hide
                    foreach($available_menus as $available_menu =>  $available_menu_data)
                        {
                            if(!isset($settings['show_reorder_interfaces'][$available_menu]))
                                $settings['show_reorder_interfaces'][$available_menu]   =   'hide';
                        }
                        
                    
                    APTO_functions::update_settings($settings);
                
                }    
            
            
            /**
            * Check if the required tables exists
            * 
            */
            static function table_exists()
                {
                    
                    global $wpdb;
                    
                    $query = "SHOW TABLES LIKE '". $wpdb->prefix ."apto_sort_list';";
                    $results    =   $wpdb->get_var($query);
                    
                    if(!empty($results))
                        return TRUE;
                    
                    return FALSE;
                    
                }
            
            
            /**
            * @desc 
            * 
            * Create plugin required tables
            * 
            */
            static function update_tables()
                {
                    
                    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                    global $wpdb;
                    
                    $query = "CREATE TABLE IF NOT EXISTS `". $wpdb->prefix ."apto_sort_list` (
                                  `id` int(11) NOT NULL auto_increment,
                                  `sort_view_id` int(11) NOT NULL,
                                  `object_id` int(11) NOT NULL,
                                  PRIMARY KEY  (`id`),
                                  KEY `sort_view_id` (`sort_view_id`)
                                ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
                    dbDelta($query);
                    
                    $settings  = APTO_functions::get_settings();
                    
                    //set the database settings
                    $settings['database_version']   =   APTO_DB_VERSION;
                    
                    
                    APTO_functions::update_settings($settings);
                }
            
            
            static function create_post_type_sort($post_type, $options, $sort_view_meta)
                {
                    $post_type_data    =   get_post_type_object($post_type);
                        
                     //create the sort
                    $post_data  =   array(
                                            'post_type'     =>  'apto_sort',
                                            'post_status'   =>  'publish',
                                            'post_title'    =>  'Sort #' . $post_type_data->label
                                            );
                    $sort_id = wp_insert_post( $post_data );
                                        
                    $rules  =   array();
                    $rules['post_type']   =   array($post_type);
                    update_post_meta($sort_id, '_rules', $rules);
                                        
                    //process the conditionals
                    $conditionals = array();
                    update_post_meta($sort_id, '_conditionals', $conditionals);
                    
                    foreach($options as $option_key =>  $value)
                        {
                            //add as meta value
                            update_post_meta($sort_id, $option_key, $value);
                        }
                        
                    update_post_meta($sort_id, '_view_type', 'multiple'); 
                    
                    //create the default view for this sortID
                    $sort_view_id       =   APTO_interface_helper::create_view($sort_id, $sort_view_meta);
                    
                    //set this sort view as default for the main sort
                    update_post_meta($sort_id, '_last_sort_view_ID', $sort_view_id);
                    
                    return $sort_id;
                    
                }
            
            /**
            * Add _view_language to taxonomy view selection to allow WPML untranslated taxonomies to be sorted for different languages
            *     
            */
            static function update_V3_5_4()
                {
                    global $wpdb; 
                    
                    if (defined('ICL_LANGUAGE_CODE') && defined('ICL_SITEPRESS_VERSION'))
                        {
                            global $sitepress;    
                            $default_language = $sitepress->get_default_language();
                        }
                        else
                        $default_language = APTO_functions::get_blog_language();
                    
                    $mysql_query = "SELECT ID FROM " . $wpdb->posts . " AS pt
                                        JOIN " . $wpdb->postmeta . " AS pm ON pm.post_id = pt.ID
                                        WHERE pt.post_type = 'apto_sort' AND pm.meta_key = '_view_selection' AND pm.meta_Value = 'taxonomy'";
                    $results =  $wpdb->get_results($mysql_query); 

                    foreach($results    as  $result)
                        {
                            $sort_view_id = $result->ID;
                            $_view_language =   get_post_meta($sort_view_id, '_view_language', TRUE);
                            
                            if(!empty($_view_language))
                                continue;
                                
                            $_taxonomy  =   get_post_meta($sort_view_id, '_taxonomy', TRUE);
                            $_term_id   =   get_post_meta($sort_view_id, '_term_id', TRUE);
                            
                            //check if WPML is active
                            if (defined('ICL_LANGUAGE_CODE') && defined('ICL_SITEPRESS_VERSION'))
                                {
                                    
                                    
                                    $_language_details   =   $sitepress->get_element_language_details( $_term_id, 'tax_' . $_taxonomy );           
                                    
                                    if(!is_object($_language_details)   || empty($_language_details->language_code))
                                        $language_term_is   =   $default_language;
                                        else
                                        $language_term_is   =   $_language_details->language_code;
                                }
                                else
                                {
                                    $language_term_is   =   $default_language;   
                                }
                                
                            update_post_meta($sort_view_id, '_view_language', $language_term_is);
                                           
                        }

                }
                
                
            /**
            * Change default sort switch_theme capability to manage_options
            *     
            */
            static function update_V3_9_8_1()
                {
                    global $wpdb, $APTO; 
                    
                    $mysql_query = "SELECT ID FROM " . $wpdb->posts . " AS pt
                                        JOIN " . $wpdb->postmeta . " AS pm ON pm.post_id = pt.ID
                                        WHERE pt.post_type = 'apto_sort' AND pm.meta_key = '_capability' AND pm.meta_value = 'switch_themes'";
                    $results =  $wpdb->get_results($mysql_query); 

                    foreach($results    as  $result)
                        {
                                                         
                            update_post_meta($result->ID, '_capability', 'manage_options');
                                           
                        }

                }
                
            /**
            * Update all sorts taxonomy meta, to include a include_children paramether
            *     
            */
            static function update_V4_0_8()
                {
                    
                    global $wpdb, $APTO; 
                    
                    $mysql_query = "SELECT * FROM " . $wpdb->posts . " AS pt
                                        JOIN " . $wpdb->postmeta . " AS pm ON pm.post_id = pt.ID
                                        WHERE pt.post_type = 'apto_sort' AND pt.post_parent < 1 AND pm.meta_key LIKE '_rules%'";
                    $results =  $wpdb->get_results($mysql_query); 

                    foreach($results    as  $result)
                        {
                            $meta_value =   unserialize( $result->meta_value );
                            
                            if (isset ($meta_value['taxonomy']) &&  count($meta_value['taxonomy']) > 0)
                                {
                                    $require_update =   FALSE;
                                    foreach($meta_value['taxonomy'] as  $key    =>  $group_data)
                                        {
                                            //as default should be TRUE
                                            if ( !isset($group_data['include_children']) )
                                                {
                                                    $meta_value['taxonomy'][$key]['include_children']   =   'TRUE';
                                                    $require_update =   TRUE;
                                                }
                                        }
                                    
                                    if ( $require_update    === TRUE )
                                        update_post_meta($result->ID, $result->meta_key , $meta_value);
                                }
                        }   
                    
                }
                
        }
        
?>