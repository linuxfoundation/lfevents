<?php

    class APTO_functions
        {
            var $conditional_rules  = '';
               
            function __construct()
                {
                    global $APTO;
                    $this->conditional_rules    = &$APTO->conditional_rules;

                }

            function __destruct()
                {
                
                }
            
            /**
            * put your comment there...
            * 
            */
            static function  get_post_types($ignore = array())
                {
                    $all_post_types =   get_post_types();
                    
                    if (apto_is_plugin_active('bbpress/bbpress.php'))
                        {
                            $ignore = array_merge($ignore, array( 'reply'));
                        }                    
                    
                    foreach ($all_post_types as $key => $post_type)
                        {
                             if (in_array($post_type, $ignore))
                                unset($all_post_types[$key]);
                        }
                       
                     $all_post_types    =   apply_filters('apto_get_post_types', $all_post_types);
                     
                    return $all_post_types;    
                    
                }
            
            static function get_sort_settings($item_ID)
                {
                    
                    $settings = array();
                    
                    if($item_ID != '')
                        {                        
                            $data = get_post_meta($item_ID);
                            
                            //process the data and return as simple array
                            foreach($data as $key => $key_data)
                                {
                                    reset($key_data);
                                    $value =    maybe_unserialize(current($key_data));
                                    
                                    $settings[$key] =   $value;
                                }
                        }
                    
                    $defaults   = array (
                                            '_rules'                    =>  array(),
                                            '_conditionals'             =>  array(),
                                            '_last_sort_view_ID'        =>  '',
                                            '_view_type'                =>  '',
                                            '_title'                    =>  '',
                                            '_description'              =>  '',
                                            '_location'                 =>  '',
                                            '_autosort'                 =>  'yes',
                                            '_adminsort'                =>  'yes',
                                            '_pto_interface_sort'       =>  'no',
                                            '_new_items_to_bottom'      =>  'no',
                                            '_show_thumbnails'          =>  'no',
                                            '_pagination'               =>  'no',
                                            '_pagination_posts_per_page'=>  100,
                                            '_pagination_offset_posts'  =>  5,
                                            
                                            '_wpml_synchronize'         =>  'no',
                                            
                                            '_capability'               =>  'manage_options'
                                        );
                    $settings          = wp_parse_args( $settings, $defaults );
                    
                    
                    //prepare the new Status settings introduced since 3.8.7
                    $settings['_status']    =   self::get_sort_statuses_setting($item_ID);
                    
                    $defaults   = array (
                                            'post_type'                     =>  array(),
                                            'taxonomy'                      =>  array(),
                                            'taxonomy_relation'             =>  'AND',
                                            'meta'                          =>  array(),
                                            'meta_relation'                 =>  'AND',
                                            'author'                        =>  array(),
                                        );
                    $settings['_rules']          = wp_parse_args( $settings['_rules'], $defaults );
                    
                    $settings   =   apply_filters('apto/get_sort_settings', $settings, $item_ID);
                    
                    return $settings;
                }
            
            
            /**
            * Prepare the sort Status settings
            * 
            * @param mixed $sortID
            */
            static function get_sort_statuses_setting($sortID)
                {
                    
                    $statuses   =   self::get_wp_post_statuses();
                    
                    $current_sort_statuses_setting  =   array();
                    
                    if(!empty($sortID))
                        $current_sort_statuses_setting   =  get_post_meta($sortID, '_status', TRUE);
                        
                    if(empty($current_sort_statuses_setting)    ||  !is_array($current_sort_statuses_setting)   ||  count($current_sort_statuses_setting) < 1 )
                        $current_sort_statuses_setting  =   $statuses;
                        else
                        {
                            //filter if they still exists
                            foreach($current_sort_statuses_setting  as  $status   => $data)
                                {
                                    if(!isset($statuses[$status]))
                                        unset($current_sort_statuses_setting[$status]);
                                }
                                
                            //add the new statuses
                            foreach($statuses   as  $status   => $data)
                                {
                                    if(!isset($current_sort_statuses_setting[$status]))
                                        $current_sort_statuses_setting[$status] =   $data;   
                                }
                        }   
                    
                    $current_sort_statuses_setting   =   apply_filters('apto/get_sort_statuses_setting', $current_sort_statuses_setting,  $sortID);
                    
                    return $current_sort_statuses_setting;                        
                }
                
            
            /**
            * Get site post statuses in requited format
            * 
            */
            static function get_wp_post_statuses()
                {
                    global $wp_post_statuses;
                    $statuses   =   array();
                    
                    //add all
                    $statuses['all']    =   array(
                                                    'label'     =>  '<b>All</b>',
                                                    'status'    =>  'hide'
                                                    );
                    
                    foreach($wp_post_statuses   as  $status   => $data)
                        {
                            $statuses[$status]    =   array(
                                                                'label'     =>  $data->label,
                                                                'status'    =>  'show'
                                                                );
                        }
                        
                    //keep the trash hidden as default
                    if(isset($statuses['trash']))
                        {
                            $statuses['trash']['status']    =   'hide';
                        }
                    //keep the auto-draft hidden as default
                    if(isset($statuses['auto-draft']))
                        {
                            $statuses['auto-draft']['status']    =   'hide';
                        }
                        
                    
                    return $statuses;   
                }
                
                
            static function get_sort_view_settings($item_ID)
                {
                    if($item_ID == '')
                        return array();
                    
                    $data = get_post_meta($item_ID);
                    
                    $settings = array();
                    
                    //process the data and return as simple array
                    foreach($data as $key => $key_data)
                        {
                            reset($key_data);
                            $value =    maybe_unserialize(current($key_data));
                            
                            $settings[$key] =   $value;
                        }
                        
                    $defaults   = array (
                                            '_order_type'                   =>  'manual',
                                            '_view_selection'               =>  'archive',
                                            '_taxonomy'                     =>  '',
                                            '_term_id'                      =>  '',
                                            '_view_language'                =>  '',
                                            '_auto_order_by'                =>  '_default_',
                                            '_auto_custom_field_name'       =>  '',
                                            '_auto_custom_field_type'       =>  '',
                                            '_auto_custom_function_name'    =>  '',
                                            '_auto_order'                   =>  'DESC'
                                        );
                    $settings          = wp_parse_args( $settings, $defaults );
                    
                    return $settings;
                }
                
            static function get_settings()
                {
                    $settings = get_option('apto_settings');    
                    
                    $defaults   = array (
                                            'plugin_version'                =>  1,
                                            'database_version'              =>  1,
                                            'show_reorder_interfaces'       =>  array(),
                                            
                                            'ignore_supress_filters'        =>  '',
                                            'ignore_sticky_posts'           =>  '',
                                            'navigation_sort_apply'         =>  '',
                                            'create_logs'                   =>  '',
                                            'bbpress_replies_reverse_order' =>  '',
                                            'woocommerce_upsells_sort'      =>  ''
                                        );
                    $settings          = wp_parse_args( $settings, $defaults );
                    
                    return $settings;
                }
                
            static function update_settings($settings)
                {
                    update_option('apto_settings', $settings);
                }
                
            
            /**
            * Check against the settings rule if it's a single woocommerce sort type
            * 
            */
            static function is_woocommerce($sortID)
                {
                    $is_woocommerce = FALSE;
                    if (!in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) )
                        return FALSE;
                    
                    $sort_settings  =   self::get_sort_settings($sortID);    
                    $sort_rules     =   $sort_settings['_rules'];
                    if(count($sort_rules['post_type']) > 1)
                        return FALSE;
                        
                    reset($sort_rules['post_type']);
                    $post_type          =   current($sort_rules['post_type']);
                    if($post_type != "product")
                        return FALSE;
                        
                    return TRUE;                    
                }
                
            
            function query_get_orderby($orderBy, $query, $sorts_match_filter = array())
                {
                    //filter the query for unnecesarelly data;  i.e. empty taxonomy rules
                    $query          =   $this->query_filter_valid_data($query);

                    //identify the appropiate sort id and sort_view_id which match this query
                    $sort_view_id   =   $this->query_match_sort_id($query, $sorts_match_filter);
                    $sort_view_id   =   apply_filters('apto_query_match_sort_id', $sort_view_id, $orderBy, $query, $sorts_match_filter);
                    
                    //return default $orderBy if nothing found
                    if ($sort_view_id == '')
                        return  $orderBy;
                        
                    if(!is_admin())
                        $this->save_log('query_match', array('sort_view_id'  =>  $sort_view_id, 'query'  =>  $query));
                    
                    global $wpdb;
                    
                    $new_orderBy = $orderBy;
                        
                    $sort_view_settings =   $this->get_sort_view_settings($sort_view_id);
                    
                    $sort_view_data     =   get_post($sort_view_id);
                    if($sort_view_data->post_parent > 0)
                        $sortID             =   $sort_view_data->post_parent;
                        else
                        $sortID             =   $sort_view_id;
                    $sort_settings      =   $this->get_sort_settings($sortID); 
                    
                    if($sort_view_settings['_order_type'] == 'auto')
                        {
                            //Add falback for multiple 
                            $data_set = array(
                                                'order_by'              =>  (array)$sort_view_settings['_auto_order_by'],
                                                'custom_field_name'     =>  (array)$sort_view_settings['_auto_custom_field_name'],
                                                'custom_field_type'     =>  (array)$sort_view_settings['_auto_custom_field_type'],
                                                'custom_function_name'  =>  (array)$sort_view_settings['_auto_custom_function_name'],
                                                'order'                 =>  (array)$sort_view_settings['_auto_order']
                                                );
                            
                            $new_orderBy = '';
                            
                            $counter = 0;
                            foreach($data_set['order_by']   as $key =>  $data)
                                {
                                    if($new_orderBy != '')
                                        $new_orderBy .= ', ';
                                    
                                    switch ($data_set['order_by'][$key])
                                        {
                                            case '_default_'        :
                                                                        $new_orderBy    =   $orderBy;   
                                                                        break;
                                            
                                            case '_random_'         :
                                                                        $new_orderBy .= "RAND()";
                                                                        
                                                                        break;
                                            
                                            case '_custom_field_'   :
                                                                        
                                                                        $new_orderBy .=  $this->query_get_orderby_custom_field($key, $sort_view_id, $orderBy, $query);
                                   
                                                                        break;
                                            
                                            case '_custom_function_'   :
                                                                        
                                                                        $new_orderBy .=  $this->query_get_orderby_custom_function($key, $sort_view_id, $orderBy, $query);
                                   
                                                                        break;
                                                                    
                                            default: 
                                                                        $new_orderBy .= $wpdb->posts .".". $data_set['order_by'][$key] . " " . $data_set['order'][$key];
                                                                        
                                                                        break;
                                            
                                        }
                                        
                                   $counter++; 
                                }
                                
                            if($counter <   2)
                                {
                                    if(!empty($new_orderBy))
                                        $new_orderBy    .=  ", ";
                                    $new_orderBy .= $wpdb->posts .".post_date ". $data_set['order'][0];
                                }
                              
                            $new_orderBy    =   apply_filters('apto_get_orderby', $new_orderBy, $orderBy, $query);
                            $new_orderBy    =   apply_filters('apto/get_orderby', $new_orderBy, $orderBy, $sort_view_id, $query);
                            
                            return $new_orderBy;
                        }
                    
                    
                    //check for sticky posts then use another filter instead.
                    if(isset($sort_view_settings['_sticky_data']) && is_array($sort_view_settings['_sticky_data']) && count($sort_view_settings['_sticky_data']) > 0)
                        {
                            //hold the $sorts_match_filter piece of information for posts_clauses_request filter
                            
                            /**
                            *   
                            *   ToDo
                            * 
                            */
                            //to find another way to replace superglobal
                            global $sorts_match_filter__posts_clauses_request;
                            $sorts_match_filter__posts_clauses_request['filters']           =   $sorts_match_filter;
                            $sorts_match_filter__posts_clauses_request['query_vars_hash']   =   $query->query_vars_hash;
                            add_filter('posts_clauses_request', array($this, 'sticky_posts_clauses_request'), 999, 2);   
                            
                            return $orderBy;
                        }
                        

                    //custom order apply
                    $order_list  = $this->get_order_list($sort_view_id);
                    
                    //check for bbPress 
                    if( $this->is_BBPress_topic_simple($sortID) === TRUE)
                        {
                            $orderBy    =   'menu_order';   
                            return $orderBy;
                        }
                    
                    $new_orderBy    =   $this->query_get_new_orderBy($orderBy, $query, $sort_view_id, $order_list);
                    
                    //deprecated filter      
                    $new_orderBy    =   apply_filters('apto_get_orderby', $new_orderBy, $orderBy, $query);
                    
                    $new_orderBy    =   apply_filters('apto/get_orderby', $new_orderBy, $orderBy, $sort_view_id, $query);
                    
                    return $new_orderBy; 
                    
                }
            
            
            function query_get_new_orderBy($orderBy, $query, $sort_view_id, $order_list)
                {
                    
                    global $wpdb;
                    
                    $new_orderBy = $orderBy;
                        
                    $sort_view_settings =   $this->get_sort_view_settings($sort_view_id);
                    
                    $sort_view_data     =   get_post($sort_view_id);
                    if($sort_view_data->post_parent > 0)
                        $sortID             =   $sort_view_data->post_parent;
                        else
                        $sortID             =   $sort_view_id;
                    $sort_settings      =   $this->get_sort_settings($sortID);
                    
                    
                    if (count($order_list) > 0 )
                        {
                            $query_order = isset($query->query['order']) ? strtoupper($query->query['order']) : 'ASC';
                            
                            //check if the orderby is not menu_order and autosort is turned on to make the order as ASC;  This will fix when use the get_posts() as it send DESC by default
                            if((!isset($query->query['orderby']) || (isset($query->query['orderby']) && $query->query['orderby'] != 'menu_order'))
                                && $sort_settings['_autosort'] == "yes")
                                {
                                    $query_order   =   'ASC';   
                                }

                            //check for bottom append new posts
                            $new_items_to_bottom    =   $sort_settings['_new_items_to_bottom'];
                            $new_items_to_bottom    =   apply_filters('new_items_to_bottom', $new_items_to_bottom, $sort_view_id, $query);

                            if($new_items_to_bottom == "yes")
                                {
                                    $_order_list = array_reverse($order_list);
                                    if($query_order == 'DESC')   
                                        $_order_list = array_reverse($_order_list);
                                    
                                    $new_orderBy = "FIELD(".$wpdb->posts.".ID, ". implode(",", $_order_list) .") DESC, ".$wpdb->posts.".post_date ASC";
                                }
                                else
                                {
                                    $_order_list = $order_list;
                                    if($query_order == 'DESC')   
                                        $_order_list = array_reverse($_order_list);
                                        
                                    $new_orderBy = "FIELD(".$wpdb->posts.".ID, ". implode(",", $_order_list) ."), ".$wpdb->posts.".post_date DESC";
                                }
                        }
                        else if($new_orderBy != '')
                            {
                                //if use just menu_order, append post_date in case a menu_order haven't been set
                                $temp_orderBy = $new_orderBy;
                                $temp_orderBy = str_ireplace("asc", "", $temp_orderBy);
                                $temp_orderBy = str_ireplace("desc", "", $temp_orderBy);
                                $temp_orderBy = trim($temp_orderBy);
                                if($temp_orderBy != $wpdb->posts . '.menu_order')
                                    {
                                        unset($temp_orderBy);
                                        return  apply_filters('apto_get_orderby', $new_orderBy, 'DESC', $query);
                                    }
                                    else
                                    {
                                       
                                        //apply order only when in _archive_
                                        if ($sort_settings['_view_type'] == 'multiple' && $sort_view_settings['_view_selection'] == 'archive')
                                            {
                                                $new_orderBy = $wpdb->posts.".menu_order, " . $wpdb->posts.".post_date ";
                                                //$new_orderBy .= $query->query_vars['order'];
                                                $new_orderBy .= "DESC";
                                            }
                                            else
                                            {
                                                $new_orderBy = $wpdb->posts. ".post_date DESC";   
                                            }
                                        
                                          
                                        return  apply_filters('apto_get_orderby', $new_orderBy, $orderBy, $query);
                                    }
                                                        
                            }
                        else
                        {
                            $new_orderBy = $wpdb->posts.".menu_order, " . $wpdb->posts.".post_date " . $query->query_vars['order'];
                        }
                       
                    return $new_orderBy;   
                }
            
                
            /**
            * Identify the appropiate sort id and sort_view_id which match this query    
            * 
            * @param mixed $query
            * @param mixed $sorts_match_filter  Contain set of filters (cutsto fileld with values) to allos sorts filtering
            */
            function query_match_sort_id($query, $sorts_match_filter)
                {
                    //check if there is already a query paramether as sort_view_id
                    if(isset($query->query['sort_view_id']))
                        return $query->query['sort_view_id'];
                    
                    $sort_items =   array();
                        
                    //check if there is already a query paramether as sort_id
                    if(isset($query->query['sort_id']) && $query->query['sort_id'] > 0)
                        {
                            $object         = new stdClass();
                            $object->ID     =   $query->query['sort_id'];
                            $sort_items[]   =   $object;
                            unset($object);     
                        }
                        
                    if(count($sort_items) < 1)
                        {
                            $sort_items =   $this->get_sorts_by_filters($sorts_match_filter);
                        }
                    
                    //v3.0 try a partial match, for general queries like category term without a post type specification (presuming the category is assigned to multiple post types)
                    $partial_match_options  =   array(
                                                FALSE, 
                                                TRUE
                                                );
                    foreach($partial_match_options      as  $partial_match)
                    foreach($sort_items as $sort_item)
                        {
                            
                            /*
                            if($this->sort_id_exists($sort_item->ID) === FALSE)
                                continue;
                            */
                            
                            $sort_view_settings =   $this->get_sort_view_settings($sort_item->ID);
                            
                            switch($sort_view_settings['_view_type'])
                                {
                                    case 'simple'   :
                                                        /*
                                                        //no need to check again
                                                        if($partial_match   !==  FALSE)
                                                            continue;
                                                        */
                                                        
                                                        $match  =   $this->sort_simple_match_check_on_query($sort_item->ID, $query, $partial_match);
                                                        if($match   !== FALSE)
                                                            return $match;
                                                            
                                                        break;      
                                    
                                    case 'multiple'   :
                                                        $match  =   $this->sort_multiple_match_check_on_query($sort_item->ID, $query, $partial_match);
                                                        if($match   !== FALSE)
                                                            return $match;
                                                            
                                                        break;
                                }
                        }
                    
                    return '';
                }
                
                
            static function get_sorts_by_filters($sort_filters, $post_column_filters = array())
                {
                    $defaults   = array (
                                            'post_parent'               =>  '0',
                                            'post_type'                 =>  'apto_sort',
                                            'post_status'               =>  'publish'
                                        );
                    $post_column_filters          = wp_parse_args( $post_column_filters, $defaults );
                            
                    //try the cache
                    global $APTO;
                    $arguments_hash =   md5( serialize($sort_filters) . serialize($post_column_filters) );
                    if ( isset($APTO->cache['get_sorts_by_filters']) && isset($APTO->cache['get_sorts_by_filters'][$arguments_hash]) )
                        return $APTO->cache['get_sorts_by_filters'][$arguments_hash];
                    
                    //try to identify other sorts which match this
                            
                    //get all sort items
                    //First try the specific / simple sorts then use the multiple / general
                    global $wpdb;
                    $mysql_query = "SELECT ". $wpdb->posts .".ID FROM ". $wpdb->posts ;
                    
                    if(count($sort_filters) > 0)
                        {
                            $q_inner_count  = 1;
                            foreach($sort_filters as $cf_name =>  $cf_values)
                                {
                                    $mysql_query .= " INNER JOIN ". $wpdb->postmeta ." AS PMF". $q_inner_count ." ON (". $wpdb->posts .".ID = PMF" . $q_inner_count ." .post_id) ";
                                    $q_inner_count++;
                                }
                        }
                        
                    $mysql_query .= " INNER JOIN ". $wpdb->postmeta ." AS PM2 ON (". $wpdb->posts .".ID = PM2.post_id) 
                                        
                                        WHERE 1 = 1 ";
                    
                    foreach($post_column_filters as $post_column    =>  $volumn_value)
                        {
                            $mysql_query .= " AND " . $wpdb->posts . "." . $post_column ." = '". $volumn_value  ."'" ;
                        }
                     
                    if(count($sort_filters) > 0)
                        {
                            $q_inner_count  = 1;
                            foreach($sort_filters as $cf_name =>  $cf_values)
                                {
                                    $mysql_query .= " AND (PMF" . $q_inner_count ." .meta_key = '" . $cf_name . "' AND CAST(PMF". $q_inner_count ." .meta_value AS CHAR) IN ('". implode("', '", $cf_values)  ."'))";
                                    $q_inner_count++;
                                }
                        }
                                                
                    $mysql_query .= " AND PM2.meta_key = '_view_type'
                                                
                                        GROUP BY ". $wpdb->posts .".ID 
                                        
                                        ORDER BY FIELD(PM2.meta_value, 'simple', 'multiple'),  ". $wpdb->posts .".ID ASC  ";
                    $sort_items =   $wpdb->get_results($mysql_query);   
                    
                    //set a cahce for later usage
                    $APTO->cache['get_sorts_by_filters'][$arguments_hash]   =   $sort_items;
                    
                    return $sort_items;
                    
                }
            
            
            /**
            * Simple view match check
            *     
            * @param mixed $sortID
            * @param mixed $query
            */
            function sort_simple_match_check_on_query($sortID, $query, $partial_match)
                {
                    $sort_settings  =   $this->get_sort_settings($sortID);
                    $sort_rules     =   $this->get_sort_current_language_rules($sort_settings, FALSE);
                    if($sort_rules  === FALSE)
                        return FALSE;
                    
                                        
                    //check for query rules match
                    
                    /**
                    * 
                    * Check for post type
                    * 
                    */
                    $query_post_type = $this->query_get_post_types($query, $partial_match);
                    
                    //check for 'any' sort post types
                    if(array_search('any', $query_post_type) === FALSE  && array_search('any', $sort_rules['post_type']) !== FALSE)
                        {
                            $sort_rules['post_type']    =   $this->get_post_types();    
                        }
                    $differences = array_diff($query_post_type, $sort_rules['post_type']);
                    if(count($query_post_type) != count($sort_rules['post_type']) || count($differences) > 0)
                        return FALSE;
                        
                    
                    
                                 
                    /**
                    * 
                    * Check for taxonomies match
                    * 
                    */
                    //check for exact taxonomy match
                    if(count($sort_rules['taxonomy']) > 0)
                        {
                            if(APTO_query_utils::tax_queries_count($query->tax_query->queries) > 0)
                                {
                                    if(APTO_query_utils::tax_queries_count($query->tax_query->queries) != count($sort_rules['taxonomy']))
                                        return FALSE;
                                    
                                    //check for relation
                                    if($query->tax_query->relation != $sort_rules['taxonomy_relation'])
                                        return FALSE;
                                    
                                    foreach(APTO_query_utils::get_tax_queries($query->tax_query->queries) as $query_tax)
                                        {
                                            $found_match = FALSE;
                                            
                                            switch ($query_tax['field'])
                                                {
                                                    case 'term_id':
                                                    case 'ID':
                                                    case 'id':
                                                                $query_tax_terms    = $query_tax['terms'];
                                                                if(!is_array($query_tax_terms))
                                                                    $query_tax_terms    =   array($query_tax_terms);
                                                                break;
                                                                
                                                    case 'slug':
                                                                
                                                                $query_tax_terms    = $query_tax['terms'];
                                                                if(!is_array($query_tax_terms))
                                                                    $query_tax_terms    =   array($query_tax_terms);
                                                                
                                                                //switch terms to id 
                                                                foreach($query_tax_terms as $key => $query_tax_term_slug)
                                                                    {
                                                                          $term_data                =   get_term_by('slug', $query_tax_term_slug, $query_tax['taxonomy']);
                                                                          $query_tax_terms[$key]    =   $term_data->term_id;
                                                                    }

                                                                break;
                                                    case 'name':
                                                            
                                                            $query_tax_terms    = $query_tax['terms'];
                                                            if(!is_array($query_tax_terms))
                                                                $query_tax_terms    =   array($query_tax_terms);
                                                            
                                                            //switch terms to id 
                                                            foreach($query_tax_terms as $key => $query_tax_term_slug)
                                                                {
                                                                      $term_data                =   get_term_by('name', $query_tax_term_slug, $query_tax['taxonomy']);
                                                                      $query_tax_terms[$key]    =   $term_data->term_id;
                                                                }

                                                            break;
                                                            
                                                    case 'term_taxonomy_id':
                                                        
                                                            $query_tax_terms    = $query_tax['terms'];
                                                            if(!is_array($query_tax_terms))
                                                                $query_tax_terms    =   array($query_tax_terms);
                                                            
                                                            //switch terms to id 
                                                            foreach($query_tax_terms as $key => $query_tax_term_slug)
                                                                {
                                                                      $term_data                =   get_term_by('term_taxonomy_id', $query_tax_term_slug, $query_tax['taxonomy']);
                                                                      $query_tax_terms[$key]    =   $term_data->term_id;
                                                                }

                                                        break;
                                                }
                                            
                                            foreach($sort_rules['taxonomy'] as $tax_rule)
                                                {
                                                    //check for taxonomy name match
                                                    if($tax_rule['taxonomy'] != $query_tax['taxonomy'])
                                                        continue;
                                                    
                                                    //check for operator match
                                                    if($tax_rule['operator'] != $query_tax['operator'])
                                                        continue;
                                                        
                                                    //check for operator match
                                                    if(isset($query_tax['include_children']) &&  $this->str_to_bool($tax_rule['include_children']) !== $query_tax['include_children'])
                                                        continue;
                                                    
                                                    //check for terms
                                                    $differences = array_diff($query_tax_terms, $tax_rule['terms']);
                                                    if(count($query_tax_terms) != count($tax_rule['terms']) || count($differences) > 0)
                                                        continue;
                                                        
                                                    $found_match    =   TRUE;
                                                }
                                            
                                            if($found_match === FALSE)
                                                return FALSE;
                                        }
                                    
                                }
                                else
                                {
                                    //if sort settings contain taxonomy rules, return false
                                    if(count($sort_rules['taxonomy'])   >   0)
                                        return FALSE;                            
                                }
                        
                        }    
  
                    
                            
                    /**
                    * 
                    * Check for meta match
                    * 
                    */
                    //check for exact meta match
                    
                    if($partial_match   === FALSE   &&  APTO_query_utils::meta_queries_count($query->meta_query->queries)   !=  count($sort_rules['meta']))
                        return FALSE;
                    
                    if(
                        ($partial_match   === FALSE   &&  APTO_query_utils::meta_queries_count($query->meta_query->queries) > 0)
                        ||
                        //try to ignore meta within queries if sort meta is empty; This trigger only on partial match
                        ($partial_match   === TRUE   &&  APTO_query_utils::meta_queries_count($query->meta_query->queries) > 0  &&  count($sort_rules['meta'])  >   0)
                        )
                        {
                            if(APTO_query_utils::meta_queries_count($query->meta_query->queries) != count($sort_rules['meta']))
                                return FALSE;
                            
                            //check for relation
                            if($query->meta_query->relation != $sort_rules['meta_relation'])
                                return FALSE;
                            
                            foreach(APTO_query_utils::get_meta_queries($query->meta_query->queries) as $query_meta)
                                {
                                    $found_match = FALSE;
                                                                   
                                    foreach($sort_rules['meta'] as $meta_rule)
                                        {
                                            $meta_rule  =   $this->meta_data_prepare_value($meta_rule);
                                            
                                            //check for taxonomy name match
                                            if($meta_rule['key'] != $query_meta['key'])
                                                continue;
                                            
                                            //check if value is a string or array
                                            if((is_array($meta_rule['value'])    &&  !is_array($query_meta['value']))
                                                ||  (!is_array($meta_rule['value'])    &&  is_array($query_meta['value']))
                                                )
                                                continue;
                                            
                                            //check for value
                                            if(is_array($meta_rule['value']))
                                                {
                                                    $differences = array_diff($meta_rule['value'], $query_meta['value']);
                                                    if(count($meta_rule['value']) != count($query_meta['value']) || count($differences) > 0)
                                                        continue;       
                                                }
                                                else
                                                {
                                                    
                                                    //Allow dynamic values for this meta, if use !* 
                                                    if((string)$meta_rule['value']  !=  '!*')
                                                        {
                                                            if((string)$meta_rule['value'] !== (string)$query_meta['value'])
                                                                continue;    
                                                        }
                                                }
                                                
                                            //check compare if exists
                                            if(isset($query_meta['compare'])    &&  strtolower($query_meta['compare'])  !=  strtolower($meta_rule['compare']))
                                                continue;
                                                
                                            //check type if exists
                                            if(isset($query_meta['type'])    &&  strtolower($query_meta['type'])  !=  strtolower($meta_rule['type']))
                                                continue;
                                                
                                            $found_match    =   TRUE;
                                        }
                                    
                                    if($found_match === FALSE)
                                        return FALSE;
                                }
                            
                        }                    
     
                    
                    
                    
                    /**
                    * 
                    * Check for conditionals match
                    * 
                    */
                    if(count($sort_settings['_conditionals']) > 0)
                        {
                            foreach($sort_settings['_conditionals'] as $conditional_group)
                                {
                                    $group_match    =   TRUE;
                                    foreach($conditional_group as $conditional)
                                        {
                                            $value      =   isset($conditional['conditional_value']) ?  $conditional['conditional_value'] :   '';
                                            $comparison =   isset($conditional['conditional_comparison']) ?  $conditional['conditional_comparison'] :   '';
                                            $match  =   call_user_func_array($this->conditional_rules->rules[$conditional['conditional_id']]['query_check_callback'], array($comparison, $value, $query));
                                            if($match   ===  FALSE)
                                                {
                                                    $group_match    =   FALSE;
                                                    break;
                                                }
                                        }
                                        
                                    if($group_match === TRUE)
                                        break;
                                }
                                
                            if($group_match === FALSE)
                                return FALSE;

                        }
                    
                    //identify the sort view
                    $attr = array(
                                    '_view_selection'   =>  'simple',
                                    '_view_language'    =>  $this->get_blog_language()
                                    );
                    $sort_view_id   =   $this->get_sort_view_id_by_attributes($sortID, $attr);
                    
                    if($sort_view_id > 0)
                        return $sort_view_id;     
                        else
                        return FALSE;   
                }
                
            
            /**
            * Multiple view match check
            * 
            * @param mixed $sortID
            * @param mixed $query
            */
            function sort_multiple_match_check_on_query($sortID, $query, $partial_match = FALSE)
                {
                    $sort_settings =   $this->get_sort_settings($sortID);
                    //check for query rules match
                    
                    /**
                    * 
                    * Check for post type
                    * 
                    */
                    $query_post_type = $this->query_get_post_types($query, $partial_match);
                    
                    //v3.0 try a partial match, for general queries like category term without a post type specification (presuming the category is assigned to multiple post types)
                    if(count($query_post_type) === 1 && strtolower($query_post_type[0]) == 'any')
                        $query_post_type[0] =   'post';
                    
                    if($partial_match === FALSE)
                        {
                            $differences = array_diff($query_post_type, $sort_settings['_rules']['post_type']);
                            if(count($query_post_type) != count($sort_settings['_rules']['post_type']) || count($differences) > 0)
                                return FALSE;
                        }
                    else
                        {
                            if(count(array_intersect($query_post_type, $sort_settings['_rules']['post_type'])) < 1)
                                return FALSE;        
                        }
                    
                             
                    //check the taxonomy
                    $_view_selection    =   '';
                    //need a single taxonomy to match otherwise a simple sort need to be manually created
                    //fallback on archive;  This maybe changed later and return FALSE !! 
                    if(APTO_query_utils::tax_queries_count($query->tax_query->queries) < 1 || APTO_query_utils::tax_queries_count($query->tax_query->queries) > 1)
                        $_view_selection    =   'archive';
                        else
                            {
                                $tax_queries    =   APTO_query_utils::get_tax_queries($query->tax_query->queries);
                                reset($tax_queries);
                                $query_tax      =   current($tax_queries);
                                $taxonomy       =   $query_tax['taxonomy'];
                                
                                //identify the term
                                switch ($query_tax['field'])
                                    {
                                        case 'term_id':
                                        case 'ID':
                                        case 'id':
                                                    $query_tax_terms    = $query_tax['terms'];
                                                    if(!is_array($query_tax_terms))
                                                        $query_tax_terms    =   array($query_tax_terms);
                                                    break;
                                                    
                                        case 'slug':
                                                    
                                                    $query_tax_terms    = $query_tax['terms'];
                                                    if(!is_array($query_tax_terms))
                                                        $query_tax_terms    =   array($query_tax_terms);
                                                    
                                                    //switch terms to id 
                                                    foreach($query_tax_terms as $key => $query_tax_term_slug)
                                                        {
                                                              $term_data                =   get_term_by('slug', $query_tax_term_slug, $query_tax['taxonomy']);
                                                              if(isset($term_data->term_id))
                                                                $query_tax_terms[$key]    =   $term_data->term_id;
                                                        }

                                                    break;
                                        case 'name':
                                                    
                                                    $query_tax_terms    = $query_tax['terms'];
                                                    if(!is_array($query_tax_terms))
                                                        $query_tax_terms    =   array($query_tax_terms);
                                                    
                                                    //switch terms to id 
                                                    foreach($query_tax_terms as $key => $query_tax_term_slug)
                                                        {
                                                              $term_data                =   get_term_by('name', $query_tax_term_slug, $query_tax['taxonomy']);
                                                              $query_tax_terms[$key]    =   $term_data->term_id;
                                                        }

                                                    break;
                                                    
                                        case 'term_taxonomy_id':
                                                
                                                $query_tax_terms    = $query_tax['terms'];
                                                    if(!is_array($query_tax_terms))
                                                        $query_tax_terms    =   array($query_tax_terms);
                                                    
                                                    //switch terms to id 
                                                    foreach($query_tax_terms as $key => $query_tax_term_slug)
                                                        {
                                                              $term_data                =   get_term_by('term_taxonomy_id', $query_tax_term_slug, $query_tax['taxonomy']);
                                                              $query_tax_terms[$key]    =   $term_data->term_id;
                                                        }

                                                break;
                                    }
                                     
                                //fallback on archive;  
                                //This maybe changed later and return FALSE !!    
                                if(count($query_tax_terms) < 1 || count($query_tax_terms) > 1)
                                    {
                                        //check agains the include_children paramether 
                                        if(count($query_tax_terms) > 1 && $query_tax['include_children'] == FALSE)
                                            {
                                                $_view_selection    =   'taxonomy'; 
                                                 
                                                reset($query_tax_terms);
                                                $term_id    =      current($query_tax_terms);
                                            }
                                            else
                                            $_view_selection    =   'archive';
                                    }
                                    else
                                    {
                                        //check the operator
                                        //fallback on archive;  This maybe changed later and return FALSE !! 
                                        if(!in_array($query_tax['operator'], array('IN', 'AND', 'NOT IN')))
                                            $_view_selection    =   'archive';
                                            else
                                            {
                                                $_view_selection    =   'taxonomy';
                                                
                                                reset($query_tax_terms);
                                                $term_id    =      current($query_tax_terms);
                                            }
                                    }
                            }
                    
                    /**
                    * 
                    * Check for conditionals match
                    * 
                    */
                    if(count($sort_settings['_conditionals']) > 0)
                        {
                            foreach($sort_settings['_conditionals'] as $conditional_group)
                                {
                                    $group_match    =   TRUE;
                                    foreach($conditional_group as $conditional)
                                        {
                                            $value      =   isset($conditional['conditional_value']) ?  $conditional['conditional_value'] :   '';
                                            $comparison =   isset($conditional['conditional_comparison']) ?  $conditional['conditional_comparison'] :   '';
                                            $match  =   call_user_func_array($this->conditional_rules->rules[$conditional['conditional_id']]['query_check_callback'], array($comparison, $value, $query));
                                            if($match   ===  FALSE)
                                                {
                                                    $group_match    =   FALSE;
                                                    break;
                                                }
                                        }
                                        
                                    if($group_match === TRUE)
                                        break;
                                }
                                
                            if($group_match === FALSE)
                                return FALSE;

                        }
                    
                            
                    //identify the sort view
                    $attr = array(
                                    '_view_selection'    =>  $_view_selection
                                    );
                    if($_view_selection == 'taxonomy')
                        {
                            $attr['_taxonomy']      =   $taxonomy;
                            $attr['_term_id']       =   $term_id;
                            $attr['_view_language'] =   $this->get_blog_language();
                        }
                    if($_view_selection  ==  'archive')
                                $attr['_view_language']   =   $this->get_blog_language();                    
                    $sort_view_id   =   $this->get_sort_view_id_by_attributes($sortID, $attr);
                    
                    if($sort_view_id > 0)
                        return $sort_view_id;     
                        else
                        return FALSE;
                }
                
            
            function meta_data_prepare_value($meta_data)
                {
                    if($meta_data['value_type']    ==  'array')
                        {
                            $value  =   explode(",", $meta_data['value']);
                            $value  =   array_map('trim',   $value);
                            $value  =   array_filter($value);
                            $meta_data['value']   =   $value;
                        }
                        
                    unset($meta_data['value_type']);
                    
                    return $meta_data;                    
                }
                
            function query_get_post_types($query, $_if_empty_set_post_types = FALSE)
                {
                    $query_post_types = isset($query->query_vars['post_type']) ? $query->query_vars['post_type'] :   array();
                    if(!empty($query_post_types) && !is_array($query_post_types))
                        $query_post_types    =   (array)$query_post_types;
                    if(empty($query_post_types) && !is_array($query_post_types))
                        $query_post_types    =   array();
                        
                    /**
                    * 
                    *   If empty post type query field AND use default category taxonomy, use post
                    *   To check further
                    * 
                    */
                    if ( empty($query_post_types) && $_if_empty_set_post_types  === FALSE) 
                        {
                            $taxonomies =   array();
                            if(isset($query->tax_query) && isset($query->tax_query->queries))
                                {
                                    $taxonomies =   APTO_query_utils::get_query_taxonomies($query->tax_query->queries);
                                }
                            
                            if(count($taxonomies) > 0   && count($taxonomies)   < 2)
                                {
                                    reset($taxonomies);
                                    $query_taxonomy =  current($taxonomies);
                                    
                                    if($query_taxonomy  ==  'category')
                                        $query_post_types[]  =   'post';   
                                }
                        }
                   
                        
                    if ( empty($query_post_types) && $_if_empty_set_post_types  === TRUE) 
                        {
                            $taxonomies =   array();
                            if(isset($query->tax_query) && isset($query->tax_query->queries))
                                {
                                    $taxonomies =   APTO_query_utils::get_query_taxonomies($query->tax_query->queries);
                                }
                            
                            $ignore = array (
                                                'revision',
                                                'nav_menu_item'
                                                );
                            foreach ( $this->get_post_types($ignore) as $pt ) 
                                {
                                    $object_taxonomies = $pt === 'attachment' ? get_taxonomies_for_attachments() : get_object_taxonomies( $pt );
                                    if ( array_intersect( $taxonomies, $object_taxonomies ) )
                                        $query_post_types[] = $pt;
                                }
                               
                            //v3.0  ??????chose the first
                            /*
                            if(count($query_post_types) > 1)
                                $query_post_types  =   array_slice($query_post_types, 0, 1);
                            */
                            
                            if(count($query_post_types) < 1)
                                $query_post_types[]  =   'post';
                        }
                        
                    
                    $query_post_types   =   apply_filters('APTO/query_get_post_types', $query_post_types, $query, $_if_empty_set_post_types );
                                            
                    return  $query_post_types;    
                }
            
            
            function sticky_posts_clauses_request($query_pieces, $query)
                {
                    global $sorts_match_filter__posts_clauses_request;
                    
                    //make sure this is applied to correct query; possible a new query has called durring the execution
                    if ($sorts_match_filter__posts_clauses_request['query_vars_hash'] != $query->query_vars_hash)
                        return $query_pieces;
                    
                    //remove this filter for being triggered again
                    remove_filter('posts_clauses_request', array($this, 'sticky_posts_clauses_request'), 999);
                    
                    //filter the query for unnecesarelly data;  i.e. empty taxonomy rules
                    $query          =   $this->query_filter_valid_data($query);

                    
                    //identify the appropiate sort id and sort_view_id which match this query
                    $sort_view_id   =   $this->query_match_sort_id($query, $sorts_match_filter__posts_clauses_request['filters']);
                    
                    global $wpdb;
                    
                    $sort_view_settings =   $this->get_sort_view_settings($sort_view_id);
                    
                    $sort_view_data     =   get_post($sort_view_id);
                    if($sort_view_data->post_parent > 0)
                        $sortID             =   $sort_view_data->post_parent;
                        else
                        $sortID             =   $sort_view_id;
                    $sort_settings      =   $this->get_sort_settings($sortID);
                    
                    
                    $new_orderBy    =   $orderBy    =   $query_pieces['orderby'];
                    
                    $order_list     =   $this->get_order_list($sort_view_id);
                    
                    if (count($order_list) > 0 )
                        {
                            $query_order = isset($query->query['order']) ? strtoupper($query->query['order']) : 'ASC';
                            
                            //check if the orderby is not menu_order and autosort is turned on to make the order as ASC;  This will fix when use the get_posts() as it send DESC by default
                            if((!isset($query->query['orderby']) || (isset($query->query['orderby']) && $query->query['orderby'] != 'menu_order'))
                                && $sort_settings['_autosort'] == "yes")
                                {
                                    $query_order   =   'ASC';   
                                }

                            //check for bottom append new posts
                            $new_items_to_bottom    =   $sort_settings['_new_items_to_bottom'];
                            $new_items_to_bottom    =   apply_filters('new_items_to_bottom', $new_items_to_bottom, $sort_view_id, $query);

                            if($new_items_to_bottom == "yes")
                                {
                                    $_order_list = array_reverse($order_list);
                                    if($query_order == 'DESC')   
                                        $_order_list = array_reverse($_order_list);
                                    
                                    $new_orderBy = "FIELD(".$wpdb->posts.".ID, ". implode(",", $_order_list) .") DESC, ".$wpdb->posts.".post_date DESC";
                                }
                                else
                                {
                                    $_order_list = $order_list;
                                    if($query_order == 'DESC')   
                                        $_order_list = array_reverse($_order_list);
                                        
                                    $new_orderBy = "FIELD(".$wpdb->posts.".ID, ". implode(",", $_order_list) ."), ".$wpdb->posts.".post_date DESC";
                                }
                        }
                        else if($new_orderBy != '')
                            {
                                //if use just menu_order, append post_date in case a menu_order haven't been set
                                $temp_orderBy = $new_orderBy;
                                $temp_orderBy = str_ireplace("asc", "", $temp_orderBy);
                                $temp_orderBy = str_ireplace("desc", "", $temp_orderBy);
                                $temp_orderBy = trim($temp_orderBy);
                                if($temp_orderBy != $wpdb->posts . '.menu_order')
                                    {
                                        unset($temp_orderBy);
                                    }
                                    else
                                    {
                                        //apply order only when in _archive_
                                        if ($sort_settings['_view_type'] == 'multiple' && $sort_view_settings['_view_selection'] == 'archive')
                                            {
                                                $new_orderBy = $wpdb->posts.".menu_order, " . $wpdb->posts.".post_date ";
                                                //$new_orderBy .= $query->query_vars['order'];
                                                $new_orderBy .= "DESC";
                                            }
                                            else
                                            {
                                                $new_orderBy = $wpdb->posts. ".post_date DESC";   
                                            }
                                    }
                                                        
                            }
                        else
                        {
                            $new_orderBy = $wpdb->posts.".menu_order, " . $wpdb->posts.".post_date " . $query->query_vars['order'];
                        }
                    
                    
                    $query_groupby    =   "";
                    if($query_pieces['groupby'] !=  '')
                        $query_groupby    =   'GROUP BY ' . $query_pieces['groupby'];
                        
                    $query_orderby    =   "";
                    if($new_orderBy !=  '')
                        $query_orderby    =   'ORDER BY ' . $new_orderBy;
                    
                    //create the sort list
                    $query_request  = "SELECT ". $query_pieces['distinct'] ." " . $wpdb->posts .".ID FROM " . $wpdb->posts ." " . $query_pieces['join'] ." WHERE 1=1 " . $query_pieces['where'] ." " . $query_groupby . "  " . $query_orderby;
                    $results = $wpdb->get_results($query_request);
                    
                    $order_list =   array();
                    foreach ($results as $result)
                        $order_list[] = $result->ID;
                    
                    //apply sicky
                    if(isset($sort_view_settings['_sticky_data']) && is_array($sort_view_settings['_sticky_data']) && count($sort_view_settings['_sticky_data']) > 0)
                        $order_list     =   $this->order_list_apply_sticky_data($order_list, $sort_view_settings['_sticky_data']);

                    $new_orderBy    =   $this->query_get_new_orderBy($orderBy, $query, $sort_view_id, $order_list);
                    
                    //update the orderby piece
                    $query_pieces['orderby']    =   $new_orderBy;
                       
                    return  $query_pieces;   
                }
                
            function get_order_list($sort_view_id)
                {
                    global $wpdb;
                    
                    $order_list = array();
                    
                    $query = "SELECT object_id FROM `". $wpdb->prefix ."apto_sort_list`
                                    WHERE `sort_view_id`    =   ". $sort_view_id;
                    $query .= " ORDER BY id ASC";
                    
                    $results = $wpdb->get_results($query);
                    
                    foreach ($results as $result)
                        $order_list[] = $result->object_id;
                        
                    //$sort_view_settings =   $this->get_sort_view_settings($sort_view_id);
                    
                    $order_list = apply_filters('apto_get_order_list', $order_list, $sort_view_id);
                    
                    return $order_list;    
                }
                
            function order_list_apply_sticky_data($order_list, $sticky_data)
                {
                    $updated_order_list     =   array();
                    
                    foreach($sticky_data as $key =>  $object_id)
                        {
                             if(array_search($object_id, $order_list)   !== FALSE)
                                $updated_order_list[$key - 1]  =   $object_id;   
                        }
                    
                    
                    $current_index = 0;
                    foreach($order_list as $key =>  $object_id)
                        {
                            if(array_search($object_id, $updated_order_list)   !== FALSE)
                                continue;
                            
                            while(isset($updated_order_list[$current_index]))
                                {
                                    $current_index++;
                                }
                                
                             $updated_order_list[$current_index]  =   $object_id;   
                        }
                        
                    ksort($updated_order_list);
                    
                    return $updated_order_list;
                }   
            
            /**
            * Return the orderby argv for query on a custom field sort
            * 
            * @param mixed $sort_view_id
            * @param mixed $query
            */
            function query_get_orderby_custom_field($data_set_key, $sort_view_id, $orderBy, $query)
                {
                    global $wpdb;
                        
                    $sort_view_settings =   $this->get_sort_view_settings($sort_view_id);
                    
                    $sort_view_data     =   get_post($sort_view_id);
                    $sortID             =   $sort_view_data->post_parent;
                    
                    $sort_settings      =   $this->get_sort_settings($sortID);
                    
                    $data_set = array(
                                                'order_by'              =>  (array)$sort_view_settings['_auto_order_by'],
                                                'custom_field_name'     =>  (array)$sort_view_settings['_auto_custom_field_name'],
                                                'custom_field_type'     =>  (array)$sort_view_settings['_auto_custom_field_type'],
                                                'custom_function_name'  =>  (array)$sort_view_settings['_auto_custom_function_name'],
                                                'order'                 =>  (array)$sort_view_settings['_auto_order']
                                                );
                    
                    $custom_field_name    = $data_set['custom_field_name'][$data_set_key];
                    //if empty no need to continue
                    if(empty($custom_field_name))
                        return $orderBy;
                        
                    $custom_field_type    = $data_set['custom_field_type'][$data_set_key];
                    
                    //fallback compatibility
                    if($custom_field_type   ==  '')
                        $custom_field_type  =   'none';
                    
                    $order_list = array();
                    
                    //retrieve the list of posts which contain the custom field
                    if(isset($sort_settings['_view_type']) && $sort_settings['_view_type']    == 'simple')
                        {
                            //this is the simple view
                        
                            $mysql_query = "SELECT DISTINCT ". $wpdb->posts .".ID, pm1.meta_value FROM ". $wpdb->posts ."  
                                            JOIN ". $wpdb->postmeta ." as pm1 ON (". $wpdb->posts .".ID = pm1.post_id)";
                            
                            //taxonomy
                            if(isset($sort_settings['_rules']['taxonomy']) && count($sort_settings['_rules']['taxonomy']) > 0)
                                {
                                    $q_inner_count  =   1;
                                    foreach($sort_settings['_rules']['taxonomy'] as $rule_tax)
                                        {
                                            $mysql_query .= " INNER JOIN ". $wpdb->term_relationships ." AS tr" . $q_inner_count ." ON (". $wpdb->posts .".ID = tr" . $q_inner_count .".object_id)";        
                                            
                                            $q_inner_count++;
                                        }
                                }

                            $mysql_query .= " WHERE 1=1";
                            
                            //taxonomy
                            if(isset($sort_settings['_rules']['taxonomy']) && count($sort_settings['_rules']['taxonomy']) > 0)
                                {
                                    $mysql_query .= " AND ( ";
                                    
                                    $first_tax      =   TRUE;
                                    $q_inner_count  =   1;
                                    foreach($sort_settings['_rules']['taxonomy'] as $rule_tax)
                                        {
                                            if($first_tax   === TRUE)
                                                {
                                                    $first_tax  =   FALSE;
                                                    $mysql_query .= " ( ";
                                                }
                                                else
                                                $mysql_query .= " " . $sort_settings['_rules']['taxonomy_relation'] . " ( ";
                                            
                                            $query_terms = array();
                                            foreach($rule_tax['terms'] as $term_id)
                                                {
                                                    $term_data      =    get_term($term_id, $rule_tax['taxonomy']);
                                                    $query_terms[]  =   $term_data->term_taxonomy_id;
                                                }
                                            
                                            if($rule_tax['operator'] == 'IN')
                                                {
                                                    $mysql_query .=   "tr" . $q_inner_count .".term_taxonomy_id IN (". implode(",", $query_terms) .")";
                                                }
                                                else if($rule_tax['operator'] == 'NOT IN')
                                                    {
                                                        $mysql_query .=   $wpdb->posts . ".ID NOT IN (
                                                                                    SELECT object_id
                                                                                    FROM " . $wpdb->term_relationships ."
                                                                                    WHERE term_taxonomy_id IN (". implode(",", $query_terms) ."))";
                                                    }
                                                else if($rule_tax['operator'] == 'AND')
                                                    {
                                                        $mysql_query .=   " (
                                                                            SELECT COUNT(1)
                                                                            FROM ". $wpdb->term_relationships ."
                                                                            WHERE term_taxonomy_id IN (". implode(",", $query_terms) .")
                                                                            AND object_id = wp_posts.ID
                                                                        ) = ". count($query_terms) ." ";
                                                    }
                                            
                                            $mysql_query .= " ) ";
                                            
                                            $q_inner_count++;
                                        }
                                        
                                    $mysql_query .= " ) ";
                                }
                                  
                            //add author if set
                            if(isset($sort_settings['_rules']['author']) && count($sort_settings['_rules']['author']) > 0)
                                {
                                    $mysql_query .= " AND ". $wpdb->posts .".post_author IN ('"  .   implode("', '", $sort_settings['_rules']['author']) .   "')";        
                                }
                                
                            $mysql_query .= " AND pm1.meta_key = '". esc_sql($custom_field_name) ."'
                                        AND ". $wpdb->posts .".post_type IN ('"  .   implode("', '", $sort_settings['_rules']['post_type']) .   "') ";
                            
                            
                            switch($custom_field_type)
                                {
                                    case "SIGNED"     :
                                                        $mysql_query .= " ORDER BY CAST(pm1.meta_value AS SIGNED) ". $data_set['order'][$data_set_key];
                                                        break;
                                    
                                    case "UNSIGNED"     :
                                                        $mysql_query .= " ORDER BY CAST(pm1.meta_value AS UNSIGNED) ". $data_set['order'][$data_set_key];
                                                        break;
                                                        
                                    case "float"     :
                                                        $mysql_query .= " ORDER BY CAST(pm1.meta_value AS DECIMAL(20,6)) ". $data_set['order'][$data_set_key];
                                                        break;
                                                        
                                    case "DATE"     :
                                                        $mysql_query .= " ORDER BY CAST(pm1.meta_value AS DATE) ". $data_set['order'][$data_set_key];
                                                        break;
                                                        
                                    case "DATETIME"     :
                                                        $mysql_query .= " ORDER BY CAST(pm1.meta_value AS DATETIME) ". $data_set['order'][$data_set_key];
                                                        break;
                                                        
                                    case "TIME"     :
                                                        $mysql_query .= " ORDER BY CAST(pm1.meta_value AS TIME) ". $data_set['order'][$data_set_key]; 
                                                        break;
                                                        
                                    default:
                                                        $mysql_query .= " ORDER BY pm1.meta_value ". $data_set['order'][$data_set_key];                            
                                                        break;
                                }
                                        
                            
                            $results = $wpdb->get_results($mysql_query);    

                        }
                        else
                        {
                            /**
                            * To deep Check !!
                            * Possible just to run query?
                            */
                            
                            //this is the multiple view  
                            $mysql_query = "SELECT DISTINCT ". $wpdb->posts .".ID, pm1.meta_value FROM ". $wpdb->posts ."  
                                            JOIN ". $wpdb->postmeta ." as pm1 ON (". $wpdb->posts .".ID = pm1.post_id)";
                            
                            //taxonomy
                            if(isset($query->tax_query->queries) && APTO_query_utils::tax_queries_count($query->tax_query->queries) > 0)
                                {
                                    $q_inner_count  =   1;
                                    foreach(APTO_query_utils::get_tax_queries($query->tax_query->queries) as $rule_tax)
                                        {
                                            $mysql_query .= " INNER JOIN ". $wpdb->term_relationships ." AS tr" . $q_inner_count ." ON (". $wpdb->posts .".ID = tr" . $q_inner_count .".object_id)";        
                                            
                                            $q_inner_count++;
                                        }
                                }

                            $mysql_query .= " WHERE 1=1";
                            
                            //taxonomy
                            if(isset($query->tax_query->queries) && APTO_query_utils::tax_queries_count($query->tax_query->queries) > 0)
                                {
                                    $mysql_query .= " AND ( ";
                                    
                                    $first_tax      =   TRUE;
                                    $q_inner_count  =   1;
                                    foreach(APTO_query_utils::get_tax_queries($query->tax_query->queries) as $rule_tax)
                                        {
                                            if($first_tax   === TRUE)
                                                {
                                                    $first_tax  =   FALSE;
                                                    $mysql_query .= " ( ";
                                                }
                                                else
                                                $mysql_query .= " " . $query->tax_query->relation . " ( ";
                                            
                                            $query_terms = array();
                                            foreach($rule_tax['terms'] as $term_id)
                                                {
                                                    
                                                    $term_data      =    get_term_by($rule_tax['field'], $term_id, $rule_tax['taxonomy']);
                                                    $query_terms[]  =   $term_data->term_taxonomy_id;
                                                    
                                                    if(isset($rule_tax['include_children']) &&  $rule_tax['include_children']   === TRUE)
                                                        {
                                                            $_child_terms   =   get_terms($rule_tax['taxonomy'], array( 'child_of'          => $term_data->term_id ));
                                                            if(count($_child_terms) >   0)
                                                                {
                                                                    foreach($_child_terms   as  $_child_term)
                                                                        {
                                                                            $query_terms[]  =   $_child_term->term_taxonomy_id;   
                                                                        }
                                                                }
                                                        }
                                                    
                                                }
                                            
                                            if($rule_tax['operator'] == 'IN')
                                                {
                                                    $mysql_query .=   "tr" . $q_inner_count .".term_taxonomy_id IN (". implode(",", $query_terms) .")";
                                                }
                                                else if($rule_tax['operator'] == 'NOT IN')
                                                    {
                                                        $mysql_query .=   $wpdb->posts . ".ID NOT IN (
                                                                                    SELECT object_id
                                                                                    FROM tr" . $q_inner_count ."
                                                                                    WHERE term_taxonomy_id IN (". implode(",", $query_terms) ."))";
                                                    }
                                                else if($rule_tax['operator'] == 'AND')
                                                    {
                                                        $mysql_query .=   " (
                                                                            SELECT COUNT(1)
                                                                            FROM ". $wpdb->term_relationships ."
                                                                            WHERE term_taxonomy_id IN (". implode(",", $query_terms) .")
                                                                            AND object_id = wp_posts.ID
                                                                        ) = ". count($query_terms) ." ";
                                                    }
                                            
                                            $mysql_query .= " ) ";
                                            
                                            $q_inner_count++;
                                        }
                                        
                                    $mysql_query .= " ) ";
                                }
                                  
                            //add author if set
                            if(isset($query->query['author']) && $query->query['author'] != '')
                                {
                                    $authors    =   (array)$query->query['author'];
                                    $mysql_query .= " AND ". $wpdb->posts .".post_author IN ('"  .   implode("', '", $query->query['author']) .   "')";        
                                }
                                
                            $post_types =   $this->query_get_post_types($query, TRUE);
                            $mysql_query .= " AND pm1.meta_key = '". esc_sql($custom_field_name) ."'
                                        AND ". $wpdb->posts .".post_type IN ('"  .   implode("', '", $post_types) .   "')" ;
                            
                            switch($custom_field_type)
                                {
                                    case "SIGNED"     :
                                                        $mysql_query .= " ORDER BY CAST(pm1.meta_value AS SIGNED) ". $data_set['order'][$data_set_key];
                                                        break;
                                    
                                    case "UNSIGNED"     :
                                                        $mysql_query .= " ORDER BY CAST(pm1.meta_value AS UNSIGNED) ". $data_set['order'][$data_set_key];
                                                        break;
                                                        
                                    case "float"     :
                                                        $mysql_query .= " ORDER BY CAST(pm1.meta_value AS DECIMAL(20,6)) ". $data_set['order'][$data_set_key];
                                                        break;
                                                        
                                    case "DATE"     :
                                                        $mysql_query .= " ORDER BY CAST(pm1.meta_value AS DATE) ". $data_set['order'][$data_set_key];
                                                        break;
                                                        
                                    case "DATETIME"     :
                                                        $mysql_query .= " ORDER BY CAST(pm1.meta_value AS DATETIME) ". $data_set['order'][$data_set_key];
                                                        break;
                                                        
                                    case "TIME"     :
                                                        $mysql_query .= " ORDER BY CAST(pm1.meta_value AS TIME) ". $data_set['order'][$data_set_key]; 
                                                        break;
                                                        
                                    default:
                                                        $mysql_query .= " ORDER BY pm1.meta_value ". $data_set['order'][$data_set_key];                            
                                                        break;
                                }            
                            
                            $results = $wpdb->get_results($mysql_query);    
                        }
                        
                    $orderBy    =   '';    
                    if (count($results) > 0 )
                        {
                                
                            $counter = 1;
                            $previous_meta_value    =   NULL;  
                            
                            $orderBy = "CASE ";
                            foreach ($results as $result)
                                {
                                    if($previous_meta_value !== NULL && $previous_meta_value != $result->meta_value)
                                        $counter++;
                                    
                                    $previous_meta_value    =   $result->meta_value;
                                    
                                    $orderBy .= " WHEN ". $wpdb->posts .".ID = ".$result->ID."  THEN  ". $counter;   
                                }
                            
                            $counter++;
                            $orderBy .= " ELSE ". $counter ." END";
                        }
                    
                    
                    return $orderBy;
                }
            
            
            /**
            * Return the orderby argv for query on a custom field sort
            * 
            * @param mixed $sort_view_id
            * @param mixed $query
            */
            function query_get_orderby_custom_function($data_set_key, $sort_view_id, $orderBy, $query)
                {
                    global $wpdb, $post;
                        
                    $sort_view_settings =   $this->get_sort_view_settings($sort_view_id);
                    
                    $sort_view_data     =   get_post($sort_view_id);
                    $sortID             =   $sort_view_data->post_parent;
                    
                    $sort_settings      =   $this->get_sort_settings($sortID);
                    
                    $data_set = array(
                                                'order_by'              =>  (array)$sort_view_settings['_auto_order_by'],
                                                'custom_field_name'     =>  (array)$sort_view_settings['_auto_custom_field_name'],
                                                'custom_field_type'     =>  (array)$sort_view_settings['_auto_custom_field_type'],
                                                'custom_function_name'  =>  (array)$sort_view_settings['_auto_custom_function_name'],
                                                'order'                 =>  (array)$sort_view_settings['_auto_order']
                                                );
                    
                    $custom_function_name    = $data_set['custom_function_name'][$data_set_key];
                    //if empty no need to continue
                    if(empty($custom_function_name))
                        return $orderBy;
                                        
                    $post_list = array();
                    
                    //retrieve the list of posts
                    $args                       =   $query->query;
                    $args['fields']             =   'ids';
                    $args['posts_per_page']     =   -1;
                    $args['ignore_custom_sort'] =   TRUE;
                    
                    $custom_query = new WP_Query($args);
                    if($custom_query->have_posts())
                        {
                            $post_list  =   $custom_query->posts;
                        }
                     
                    wp_reset_postdata();
                    
                    //call the user function 
                    if(count($post_list) > 0    &&  function_exists($custom_function_name))
                        {
                            $post_list  =   call_user_func($custom_function_name, $post_list, $sort_view_id, $orderBy, $query);    
                        }
                        
                    if (count($post_list) > 0 )
                        {
                            $orderBy    =   '';
                                        
                            $counter = 1;
                            $previous_meta_value    =   NULL;  
                            
                            $orderBy = "CASE ";
                            foreach ($post_list as $post_id)
                                {
                
                                    $orderBy .= " WHEN ". $wpdb->posts .".ID = ".   $post_id    ."  THEN  ". $counter;   
                                    
                                    $counter++;
                                }
                            
                            $counter++;
                            $orderBy .= " ELSE ". $counter ." END";
                        }
                    
                    
                    return $orderBy;
                }
            
            
            /**
            * Return arguments for a query, per sort settings
            * 
            * @param mixed $sort_view_id
            */
            function query_arguments_from_sort_settings( $sort_view_id )
                {
                    
                    $args   =   array();
                    
                    $sort_view_settings =   $this->get_sort_view_settings($sort_view_id);
                    
                    $sort_view_data     =   get_post($sort_view_id);
                    $sortID             =   $sort_view_data->post_parent;
                    
                    $sort_settings      =   $this->get_sort_settings($sortID);
                    
                    switch ( $sort_view_settings['_view_selection'] )
                        {
                            
                            case 'archive'   :
                                                
                                                if (! empty($sort_settings['_rules']['post_type']))
                                                    $args['post_type']  =   $sort_settings['_rules']['post_type'];
                                                                 
                                                break;
                                                
                            case 'taxonomy'   :
                                                
                                                if (! empty($sort_settings['_rules']['post_type']))
                                                    $args['post_type']  =   $sort_settings['_rules']['post_type'];
                                                    
                                                $args['tax_query']  =   array(
                                                                                array(
                                                                                        'taxonomy' => $sort_view_settings['_taxonomy'],
                                                                                        'field'    => 'term_id',
                                                                                        'terms'    => array( $sort_view_settings['_term_id'] )
                                                                                        )
                                                                                
                                                                                );
                                                                 
                                                break;
                            
                            case 'simple'   :
                                                
                                                if (! empty($sort_settings['_rules']['post_type']))
                                                    $args['post_type']  =   $sort_settings['_rules']['post_type'];
                                                    
                                                if (! empty($sort_settings['_rules']['taxonomy']))
                                                    {
                                                        $args['tax_query']  =   $sort_settings['_rules']['taxonomy'];
                                                        $args['relation']   =   $sort_settings['_rules']['taxonomy_relation'];
                                                    }
                                                
                                                //++++ MetaData to be implemented
                                                
                                                break;
                            
                        }
                    
                    $args['posts_per_page']     =   -1;
                    $args['posts_status']       =   'any';
                    
                    return $args;
                       
                }
            
            
            /**
            * Retrieve the sort view ID
            *     
            * @param mixed $sortID      This is the main sort ID holder
            * @param mixed $attr
            */
            static function get_sort_view_id_by_attributes($sortID, $attr)
                {
                    $defaults   = array (
                                            '_view_selection'          =>  'archive'
                                        );
                    
                    // Parse incoming $args into an array and merge it with $defaults
                    $attr = wp_parse_args( $attr, $defaults );
                    
                    //try the cache
                    global $APTO;
                    $arguments_hash =   md5( $sortID . serialize($attr) );
                    if ( isset($APTO->cache['get_sort_view_id_by_attributes']) && isset($APTO->cache['get_sort_view_id_by_attributes'][$arguments_hash]) )
                        return $APTO->cache['get_sort_view_id_by_attributes'][$arguments_hash];
                    
                    
                    $sort_view_ID = '';
                    
                    global $wpdb;
                    
                    $mysql_query    =   "SELECT ID FROM ". $wpdb->posts;
                    
                    $inner_no   =   1;
                    foreach($attr   as $key =>  $value)
                        {
                            $mysql_query    .=  "   INNER JOIN ". $wpdb->postmeta ." AS pm". $inner_no ."  ON (". $wpdb->posts .".ID = pm". $inner_no .".post_id) ";
                            
                            $inner_no++;
                        }
                    
                    $mysql_query    .=  " WHERE 1=1 AND ". $wpdb->posts .".post_parent = ". $sortID ."  AND ". $wpdb->posts .".post_type = 'apto_sort' AND (". $wpdb->posts .".post_status = 'publish')";
                    
                    $inner_no   =   1;
                    foreach($attr   as $key =>  $value)
                        {
                            $mysql_query    .=  "   AND  (pm". $inner_no .".meta_key = '".  $key ."' AND CAST(pm". $inner_no .".meta_value AS CHAR) = '".   $value  ."') ";
                            
                            $inner_no++;
                        }
                        
                    $mysql_query    .=  "  LIMIT 1 ";
                    
                    $sort_view_ID = $wpdb->get_var($mysql_query);
                    
                    //set a cahce for later usage
                    $APTO->cache['get_sort_view_id_by_attributes'][$arguments_hash]   =   $sort_view_ID;
                     
                             
                    return $sort_view_ID;   
                    
                }
                
            
            /**
            * Check if a given sort id exists
            * 
            * @param mixed $sortID
            */
            function sort_id_exists($sortID)
                {
                    if($sortID == '')
                        return FALSE;
                    
                    global $wpdb;
                    
                    $query              =    "SELECT count(ID) AS founds FROM " .  $wpdb->posts ."
                                                    WHERE ID = '". $sortID ."'";
                    $founds             =   $wpdb->get_var($query);
                    if($founds > 0)
                        return TRUE;
                    
                    return FALSE;   
                }
            
            function exists_sorts_with_autosort_on()
                {
                    global $wpdb, $APTO;
                    
                    if ( isset($APTO->cache['exists_sorts_with_autosort_on']))
                        return $APTO->cache['exists_sorts_with_autosort_on'];
                    
                    $APTO->cache['exists_sorts_with_autosort_on']   =   FALSE;
                    
                    $mysql_query = "SELECT ". $wpdb->posts .".ID FROM ". $wpdb->posts ."
                                        INNER JOIN ". $wpdb->postmeta ." AS PM ON (". $wpdb->posts .".ID = PM.post_id)
                                        WHERE ". $wpdb->posts .".post_parent = 0  
                                                AND ". $wpdb->posts .".post_type = 'apto_sort' 
                                                AND ". $wpdb->posts .".post_status = 'publish' 
                                                AND PM.meta_key = '_autosort' AND PM.meta_value = 'yes'";
                    $sort_items =   $wpdb->get_results($mysql_query); 
                    if(count($sort_items) > 0)   
                        {
                            $APTO->cache['exists_sorts_with_autosort_on']   =   TRUE;
                            return TRUE;
                        }
                        else
                        return FALSE;
                }
            
            
            static function roles_capabilities()
                {
                    $roles_capability = array(
                                                'Subscriber'                =>    array(
                                                                                            'title'         =>  __('Subscriber', 'apto'),
                                                                                            'capability'    =>  'read'
                                                                                            ),
                                                'Contributor'               =>    array(
                                                                                            'title'         =>  __('Contributor', 'apto'),
                                                                                            'capability'    =>  'edit_posts'
                                                                                            ),
                                                'Author'                    =>    array(
                                                                                            'title'         =>  __('Author', 'apto'),
                                                                                            'capability'    =>  'publish_posts'
                                                                                            ),
                                                'Editor'                    =>    array(
                                                                                            'title'         =>  __('Editor', 'apto'),
                                                                                            'capability'    =>  'publish_pages'
                                                                                            ),
                                                'Administrator'             =>    array(
                                                                                            'title'         =>  __('Administrator', 'apto'),
                                                                                            'capability'    =>  'manage_options'
                                                                                            )                                                                                                                                                             
                                                );
                   $roles_capability = apply_filters('apto_get_roles_capability', $roles_capability);
                   
                   return $roles_capability;    
                    
                }
            
            
            /**
            * Filter the query and return a filtered data which will be used further in the code
            *     
            * @param mixed $query
            */
            function query_filter_valid_data($query)
                {
                    
                    $query->tax_query->queries  =   APTO_query_utils::filter_valid_data($query->tax_query->queries);
                    
                    $query  =   apply_filters('APTO/query_filter_valid_data', $query);
                    
                    return $query;
                }
                
                            
            static function get_blog_language()
                {
                    $language   =   '';
                    
                    //check if WPML is active
                    if (defined('ICL_LANGUAGE_CODE') && defined('ICL_SITEPRESS_VERSION'))
                        {
                            //do not rely on ICL_LANGUAGE_CODE as main language can switch durring the code execution
                            //$language = ICL_LANGUAGE_CODE;
                            global $sitepress;
                            if(is_object($sitepress))
                                $language   =   $sitepress->get_current_language();
                            
                            //polylang
                            /*
                            global $polylang;
                            if(is_object($polylang))
                                $language   =   $polylang->curlang->slug;
                            */
                        }
                        
                    //check Polylang
                    if(function_exists('pll_current_language'))
                        $language   =   pll_current_language();
                    
                    $wp_locale  =   get_locale();
                    if($language == '' && $wp_locale   != '' && !defined('TRANSPOSH_PLUGIN_VER'))
                        {
                            $locale_data    =   explode("_", $wp_locale);
                            $language       =   $locale_data[0];
                        }
                    
                    if ($language == '')
                        $language = 'en';
                    
                    return $language;   
                }
                
            public function get_blog_default_language()
                {
                    $language   =   '';
                    
                    //check if WPML is active
                    if (defined('ICL_LANGUAGE_CODE') && defined('ICL_SITEPRESS_VERSION'))
                        {
                            global $sitepress;
                            if(is_object($sitepress))
                                $language   =   $sitepress->get_default_language();

                        }
                        
                    //polylang
                    global $polylang;
                    if(is_object($polylang) && isset($polylang->curlang->slug))
                        $language   =   $polylang->options['default_lang'];
                    
                    $wp_locale  =   get_locale();
                    if($language == '' && $wp_locale   != '' && !defined('TRANSPOSH_PLUGIN_VER'))
                        {
                            $locale_data    =   explode("_", $wp_locale);
                            $language       =   $locale_data[0];
                        }
                    
                    if ($language == '')
                        $language = 'en';
                    
                    return $language;   
                }
                
            function get_sort_current_language_rules($sort_settings, $ReturnDefaultIfEmpty = TRUE)
                {

                    if (!defined('ICL_LANGUAGE_CODE')   ||  $sort_settings['_view_type']    ==  "multiple")
                        return $sort_settings['_rules'];
                                        
                    $default_language   =   $this->get_blog_default_language(); 
                    $current_language   =   $this->get_blog_language();
                    if(isset($sort_settings['_rules_' . $current_language]))
                        return $sort_settings['_rules_' . $current_language];
                    
                    if($ReturnDefaultIfEmpty    === TRUE)
                        return $sort_settings['_rules'];   
                        else
                        return false;
                }
                
            function get_sort_language_rules($sort_settings, $language, $ReturnDefaultIfEmpty = TRUE)
                {
                                        
                    $default_language   =   $this->get_blog_default_language(); 
                    $current_language   =   $this->get_blog_language();
                    if(isset($sort_settings['_rules_' . $language]))
                        return $sort_settings['_rules_' . $language];
                    
                    if($ReturnDefaultIfEmpty    === TRUE    ||  empty($language))
                        return $sort_settings['_rules'];   
                        else
                        return false;
                }
                
            function get_sort_view_language($sort_view_ID)
                {
                    $language   =   '';
                    
                    $sort_view_selection    =   get_post_meta($sort_view_ID, '_view_selection', TRUE);
                    
                    switch($sort_view_selection)
                        {
                            case 'archive'  :
                                                $language    =   get_post_meta($sort_view_ID, '_view_language', TRUE);
                                                                    
                                                break;
                                                
                            case 'taxonomy'  :
                                                //only specific for WPML
                                                if (defined('ICL_LANGUAGE_CODE'))
                                                    {
                                                        $_taxonomy      =   get_post_meta($sort_view_ID, '_taxonomy', TRUE);
                                                        $_term_id       =   get_post_meta($sort_view_ID, '_term_id', TRUE);
                                                        
                                                        $language       =   get_post_meta($sort_view_ID, '_view_language', TRUE);
                                                        
                                                        //WPML specific
                                                        if(defined('ICL_SITEPRESS_VERSION'))
                                                            {
                                                                //check if the taxonomy is translatable
                                                                $icl_sitepress_settings =   get_option('icl_sitepress_settings');
                                                                if(empty($language) && isset($icl_sitepress_settings['taxonomies_sync_option']) && isset($icl_sitepress_settings['taxonomies_sync_option'][$_taxonomy])  &&  $icl_sitepress_settings['taxonomies_sync_option'][$_taxonomy] == "0")
                                                                    {
                                                                        //use current language
                                                                        $language   =   $this->get_blog_language();   
                                                                    }
                                                                    else
                                                                    {
                                                                        $language_term_is =     icl_object_id($_term_id, $_taxonomy, FALSE, $this->get_blog_language());
                                                                        if($language_term_is == $_term_id)
                                                                            $language   =   $this->get_blog_language();
                                                                    }
                                                            }
                                                    }
                                                    else
                                                    $language    =   get_post_meta($sort_view_ID, '_view_language', TRUE);
                                                                    
                                                break;
                                                
                            case 'simple'     :
                                                $language    =   get_post_meta($sort_view_ID, '_view_language', TRUE); 
                                                break;
                        }
                    
                    if($language    ==   '')
                        $language   =   $this->get_blog_default_language();
                           
                    return $language;   
                }
            
            function delete_sort_list_from_table($sort_view_id)
                {
                    global  $wpdb;
                    
                    $mysql_query = "DELETE FROM `". $wpdb->prefix ."apto_sort_list`
                                        WHERE `sort_view_id`    =   '".     $sort_view_id   ."'";
                    $results =   $wpdb->get_var($mysql_query);   
                }
            
            
            function save_log($event, $argv)
                {
                    //check for disabled logs
                    $settings   =   $this->get_settings();
                    if (!isset($settings['create_logs']) || $settings['create_logs'] != "1")
                        return FALSE;
                    
                    $apto_logs  =   get_option('apto_logs');
                    if(!is_array($apto_logs))
                        $apto_logs  =   array();
                        
                    $apto_logs  =   array_slice($apto_logs, 0, 19);
                    
                    switch($event)
                        {
                            case 'query_match':
                                                    $sort_view_data     =   get_post($argv['sort_view_id']);
                                                    if($sort_view_data->post_parent > 0)
                                                        $sortID             =   $sort_view_data->post_parent;
                                                        else
                                                        $sortID             =   $argv['sort_view_id'];
                                                        
                                                    $sort_data          =   get_post($sortID);
                                                    
                                                    array_unshift($apto_logs, date("Y-m-d H:i:s", time()) . ' Found Sort ID '. $sortID .' (<b>'. $sort_data->post_title .'</b>), Sort View ID '. $argv['sort_view_id'] .',  for query hash ' . $argv['query']->query_vars_hash);
                                                    break;
                                                    
                            case 'raw':             
                                                    array_unshift($apto_logs, $argv['raw']);
                                                    break;
                                                    
                            case 'log_start':
                                                    if(count($apto_logs) > 0)
                                                        {
                                                            reset($apto_logs);
                                                            if(current($apto_logs) != '-----')
                                                                array_unshift($apto_logs, '-----');
                                                        }
                                                    break;
                            
                        }
                        
                    update_option('apto_logs', $apto_logs);
                    
                }
            

            function next_previous_get_posts_list($post_type)
                {
                    global $wpdb;
                    
                    //check if WPML is active
                    if (defined('ICL_LANGUAGE_CODE') && defined('ICL_SITEPRESS_VERSION'))
                        {
                            //do not rely on ICL_LANGUAGE_CODE as main language can switch durring the code execution
                            //$language = ICL_LANGUAGE_CODE;
                            global $sitepress;
                            if(is_object($sitepress))
                                $language   =   $sitepress->get_current_language();
                            
                            $query  =   "SELECT ID FROM ". $wpdb->posts ."
                                            JOIN ". $wpdb->prefix ."icl_translations as wpml_it  ON wpml_it.element_id = ". $wpdb->posts .".ID    
                                            WHERE post_type =   '". $post_type ."' AND post_status = 'publish'
                                                    AND wpml_it.language_code = '". $language ."'
                                            GROUP BY  ". $wpdb->posts .".ID
                                            ORDER BY menu_order ASC, post_date DESC";
                            
                        }
                        else
                        {
                            $query  =   "SELECT ID FROM ". $wpdb->posts ."
                                            WHERE post_type =   '". $post_type ."' AND post_status = 'publish'
                                            ORDER BY menu_order ASC, post_date DESC";   
                            
                        }

                    $results         =   $wpdb->get_results($query);
                    
                    $order_list =   array();
                    foreach($results as $item)
                        {
                            $order_list[]   =   $item->ID;
                        }
                        
                    return $order_list;
                }
            
            
            
            
            
            
            /**
            * put your comment there...
            * 
            * @param mixed $where
            * @param mixed $in_same_term
            * @param mixed $excluded_terms
            */
            function get_previous_post_where($where, $in_same_term, $excluded_terms)
                {
                    global $post, $wpdb;

                    if ( empty( $post ) )
                        return $where;
                    
                    //?? WordPress does not pass through this varialbe, so we presume it's category..
                    $taxonomy = 'category';
                    if(preg_match('/ tt.taxonomy = \'([^\']+)\'/i',$where, $match)) 
                        $taxonomy   =   $match[1]; 
                    
                    $_join = '';
                    $_where = '';

                                        
                    if ( $in_same_term || ! empty( $excluded_terms ) ) 
                        {
                            $_join = " INNER JOIN $wpdb->term_relationships AS tr ON p.ID = tr.object_id INNER JOIN $wpdb->term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id";
                            $_where = $wpdb->prepare( "AND tt.taxonomy = %s", $taxonomy );

                            if ( ! empty( $excluded_terms ) && ! is_array( $excluded_terms ) ) 
                                {
                                    // back-compat, $excluded_terms used to be $excluded_terms with IDs separated by " and "
                                    if ( false !== strpos( $excluded_terms, ' and ' ) ) 
                                        {
                                            _deprecated_argument( __FUNCTION__, '3.3', sprintf( __( 'Use commas instead of %s to separate excluded terms.' ), "'and'" ) );
                                            $excluded_terms = explode( ' and ', $excluded_terms );
                                        } 
                                    else 
                                        {
                                            $excluded_terms = explode( ',', $excluded_terms );
                                        }

                                    $excluded_terms = array_map( 'intval', $excluded_terms );
                                }

                            if ( $in_same_term ) 
                                {
                                    $term_array = wp_get_object_terms( $post->ID, $taxonomy, array( 'fields' => 'ids' ) );

                                    // Remove any exclusions from the term array to include.
                                    $term_array = array_diff( $term_array, (array) $excluded_terms );
                                    $term_array = array_map( 'intval', $term_array );
                            
                                    $_where .= " AND tt.term_id IN (" . implode( ',', $term_array ) . ")";
                                }

                            if ( ! empty( $excluded_terms ) ) {
                                $_where .= " AND p.ID NOT IN ( SELECT tr.object_id FROM $wpdb->term_relationships tr LEFT JOIN $wpdb->term_taxonomy tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id) WHERE tt.term_id IN (" . implode( $excluded_terms, ',' ) . ') )';
                            }
                        }
                        
                    $current_menu_order = $post->menu_order;
                    
                    $query = "SELECT p.* FROM $wpdb->posts AS p
                                $_join
                                WHERE p.post_date < '". $post->post_date ."'  AND p.menu_order = '".$current_menu_order."' AND p.post_type = '". $post->post_type ."' AND p.post_status = 'publish' $_where";
                    $results = $wpdb->get_results($query);
                            
                    if (count($results) > 0)
                            {
                                $where .= " AND p.menu_order = '".$current_menu_order."'";
                            }
                        else
                            {
                                $where = str_replace("p.post_date < '". $post->post_date  ."'", "p.menu_order > '$current_menu_order'", $where);  
                            }
                    
                    return $where;
                }
            
            
            /**
            * put your comment there...
            *     
            * @param mixed $where
            * @param mixed $in_same_term
            * @param mixed $excluded_terms
            */
            function get_previous_post_sort($sort)
                {
                    global $post, $wpdb;
                    
                    $sort = 'ORDER BY p.menu_order ASC, p.post_date DESC LIMIT 1';

                    return $sort;
                }

            /**
            * put your comment there...
            * 
            * @param mixed $where
            * @param mixed $in_same_term
            * @param mixed $excluded_terms
            */
            function get_next_post_where($where, $in_same_term, $excluded_terms)
                {
                    global $post, $wpdb;

                    if ( empty( $post ) )
                        return $where;
                    
                    //?? WordPress does not pass through this varialbe, so we presume it's category..
                    $taxonomy = 'category';
                    if(preg_match('/ tt.taxonomy = \'([^\']+)\'/i',$where, $match)) 
                        $taxonomy   =   $match[1]; 
                    
                    $_join = '';
                    $_where = '';
                                        
                    if ( $in_same_term || ! empty( $excluded_terms ) ) 
                        {
                            $_join = " INNER JOIN $wpdb->term_relationships AS tr ON p.ID = tr.object_id INNER JOIN $wpdb->term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id";
                            $_where = $wpdb->prepare( "AND tt.taxonomy = %s", $taxonomy );

                            if ( ! empty( $excluded_terms ) && ! is_array( $excluded_terms ) ) 
                                {
                                    // back-compat, $excluded_terms used to be $excluded_terms with IDs separated by " and "
                                    if ( false !== strpos( $excluded_terms, ' and ' ) ) 
                                        {
                                            _deprecated_argument( __FUNCTION__, '3.3', sprintf( __( 'Use commas instead of %s to separate excluded terms.' ), "'and'" ) );
                                            $excluded_terms = explode( ' and ', $excluded_terms );
                                        } 
                                    else 
                                        {
                                            $excluded_terms = explode( ',', $excluded_terms );
                                        }

                                    $excluded_terms = array_map( 'intval', $excluded_terms );
                                }

                            if ( $in_same_term ) 
                                {
                                    $term_array = wp_get_object_terms( $post->ID, $taxonomy, array( 'fields' => 'ids' ) );

                                    // Remove any exclusions from the term array to include.
                                    $term_array = array_diff( $term_array, (array) $excluded_terms );
                                    $term_array = array_map( 'intval', $term_array );
                            
                                    $_where .= " AND tt.term_id IN (" . implode( ',', $term_array ) . ")";
                                }

                            if ( ! empty( $excluded_terms ) ) {
                                $_where .= " AND p.ID NOT IN ( SELECT tr.object_id FROM $wpdb->term_relationships tr LEFT JOIN $wpdb->term_taxonomy tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id) WHERE tt.term_id IN (" . implode( $excluded_terms, ',' ) . ') )';
                            }
                        }
                        
                    $current_menu_order = $post->menu_order;
                    
                    //check if there are more posts with lower menu_order
                    $query = "SELECT p.* FROM $wpdb->posts AS p
                                $_join
                                WHERE p.post_date > '". $post->post_date ."' AND p.menu_order = '".$current_menu_order."' AND p.post_type = '". $post->post_type ."' AND p.post_status = 'publish' $_where";
                    $results = $wpdb->get_results($query);
                            
                    if (count($results) > 0)
                            {
                                $where .= " AND p.menu_order = '".$current_menu_order."'";
                            }
                        else
                            {
                                $where = str_replace("p.post_date > '". $post->post_date  ."'", "p.menu_order < '$current_menu_order'", $where);  
                            }
                    
                    return $where;
                }

                
            /**
            * put your comment there...
            * 
            * @param mixed $where
            * @param mixed $in_same_cat
            * @param mixed $excluded_categories
            * @return mixed
            */
            function get_next_post_sort($sort)
                {
                    global $post, $wpdb; 
                    
                    $sort = 'ORDER BY p.menu_order DESC, p.post_date ASC LIMIT 1';
                    
                    return $sort;    
                }
            
            
            
            
            
            /**
            * 
            * 
            * @param mixed $where
            * @param mixed $in_same_cat
            * @param mixed $excluded_categories
            */
            function get_next_previous_post_where($where, $in_same_cat, $excluded_categories)
                {
                    global $post;
                      
                    //check if there are any items saved for this sort view
                    $order_list  = $this->next_previous_get_posts_list($post->post_type);
                    
                    if(count($order_list)   <    1)
                        return $where;
                        
                    
                    return '';
                }
  

            
            /**
            * put your comment there...
            *     
            * @param mixed $previous
            * @param mixed $sort
            */
            function get_adjacent_post_sort($previous = TRUE, $sort)
                {
                    global $post, $wpdb;
                    
                    $order_list  = $this->next_previous_get_posts_list($post->post_type);
                    
                    if(count($order_list)   <    1)
                        return $sort;
                        
                     //get the current element key
                    $current_position_key = array_search($post->ID, $order_list);
                    
                    if ($previous === TRUE)
                        $required_index = $current_position_key + 1;
                        else
                        $required_index = $current_position_key - 1;
                    
                    //check if there is another position after the current in the list
                    if (isset($order_list[ ($required_index) ]))
                        {
                            //found
                            $sort = 'ORDER BY FIELD(p.ID, "'. $order_list[ ($required_index) ] .'") DESC LIMIT 1 ';   
                        }
                        else
                        {
                            //not found 
                            $sort = 'ORDER BY p.post_date DESC LIMIT 0';  
                        }
           
                    return $sort;   
                
                }
            

                
                
           /**
           * put your comment there...
           *      
           * @param mixed $sorts_list
           * @param mixed $post_type
           * @return array
           */
           function filter_sorts_list_by_post_type($sorts_list, $post_type)
                {
                    foreach($sorts_list as $key =>  $sort_item)   
                        {
                            $sort_data  =   $this->get_sort_settings($sort_item->ID);
                            
                            if(!isset($sort_data['_rules']['post_type']) || count($sort_data['_rules']['post_type']) !== 1)
                                {
                                    unset($sorts_list[$key]);
                                    continue;
                                }
                                
                            $sort_post_type =   $sort_data['_rules']['post_type'][0];
                            if($sort_post_type  !=  $post_type)
                                unset($sorts_list[$key]);
                        }
                        
                    return array_values($sorts_list);
                } 
                
           /**
            * put your comment there...
            * 
            * @param mixed $format
            * @param mixed $link
            * @param mixed $args
            * @param mixed $previous
            */
           function adjacent_post_type_link($format, $link, $args,  $previous = TRUE) 
                {
                                        
                    if ( $previous && is_attachment() )
                        $post = & get_post($GLOBALS['post']->post_parent);
                        else
                        $post = $this->apto_get_adjacent_post($args, $previous);

                    if ( !$post )
                        return;

                    $title = $post->post_title;

                    if ( empty($post->post_title) )
                        $title = $previous ? __('Previous Post') : __('Next Post');

                    $title = apply_filters('the_title', $title, $post->ID);
                    $date = mysql2date(get_option('date_format'), $post->post_date);
                    $rel = $previous ? 'prev' : 'next';

                    $string = '<a href="'.get_permalink($post).'" rel="'.$rel.'">';
                    $link = str_replace('%title', $title, $link);
                    $link = str_replace('%date', $date, $link);
                    $link = $string . $link . '</a>';

                    $format = str_replace('%link', $link, $format);

                    $adjacent = $previous ? 'previous' : 'next';
                    echo apply_filters( "{$adjacent}_post_link", $format, $link );
                }
                
           
           /**
           * Return first sort ID matching a post type
           * This will check only against post type and no other settings
           *  
           * @param mixed $post_type
           * @return mixed
           */
           function get_first_match_sort_id_for_post_type($post_type)
                {
                    global $post, $wpdb;
                    
                    $args   =   array(
                                        '_autosort' =>  array('yes'),
                                        '_view_type' =>  array('multiple')
                                        );
                    $available_sorts    =   $this->get_sorts_by_filters($args);
                    $available_sorts    =   $this->filter_sorts_list_by_post_type($available_sorts, $post_type);
                    
                    if(count($available_sorts)  <   1)
                        return '';
                        
                    //use the first
                    reset($available_sorts);
                    $use_sort   =   current($available_sorts);
                    $sortID     =   $use_sort->ID;
                    
                    return $sortID;   
                }
                
                
           function apto_get_adjacent_post( $args, $previous = TRUE ) 
                {
                    global $post, $wpdb;
                    
                    if ( empty( $post ) )
                        return null;

                    $defaults   = array (
                                            'sort_id'                   =>  '',
                                            'sort_view_id'              =>  '',
                                            'taxonomy'                  =>  '',
                                            'term_id'                   =>  '',
                                            'use_default_order'         =>  FALSE
                                        );
                    $function_args    = wp_parse_args( $args, $defaults );
                    
                    //try to get a sort id to match this
                    if($function_args['sort_id'] == '')
                        {
                            $function_args['sort_id']   =   $this->get_first_match_sort_id_for_post_type($post->post_type);
                        }
                        
                    if($function_args['sort_id']    ==  ''  ||  $function_args['sort_id'] < 1   ||  $function_args['use_default_order']  ===  TRUE)
                        return $this->get_default_adjacent_post($post, $previous);

                    if($function_args['sort_id'] != '')
                        {
                            //get the sort _view_type simple or multiple
                            $view_type = get_post_meta($function_args['sort_id'], '_view_type', TRUE);   
                            if($view_type   ==  'simple')
                                {
                                    $attr = array(
                                                '_view_selection'   =>  'simple',
                                                '_view_language'    =>  $this->get_blog_language()
                                            );
                                }
                                else
                                {
                                    if($function_args['taxonomy']  ==  ''  ||  $function_args['term_id']  ==  '')
                                        {
                                            $attr = array(
                                                            '_view_selection'   =>  'archive',
                                                            '_view_language'    =>  $this->get_blog_language()
                                                        );   
                                        }
                                        else
                                        {
                                            //get taxonomy sort view id
                                            $attr = array(
                                                            '_view_selection'   =>  'taxonomy',
                                                            '_taxonomy'         =>  $function_args['taxonomy'],
                                                            '_term_id'          =>  $function_args['term_id'],
                                                            '_view_language'    =>  $this->get_blog_language()
                                                            );   
                                        }
                                    
                                }
                            
                            $function_args['sort_view_id']   =   $this->get_sort_view_id_by_attributes($function_args['sort_id'], $attr);
                            
                        }
                                      
                    if($function_args['sort_view_id']    ==  ''  ||  $function_args['sort_view_id'] < 1)
                        return $this->get_default_adjacent_post($post, $previous);
                    
                    
                    $sort_settings          =   $this->get_sort_settings($function_args['sort_id']);
                    $sort_view_settings     =   $this->get_sort_view_settings($function_args['sort_view_id']);
                    
                    //prepare the query to get the full list for this
                    $args = array(
                                        'depth'         =>  0,
                                        'post_status'   =>  'publish',
                                        'sort_id'       =>  $function_args['sort_id'],
                                        'sort_view_id'  =>  $function_args['sort_view_id'],
                                        'fields'        =>  'ids'
                                    );
   
                    if ($sort_settings['_view_type'] == 'multiple')
                        {
                            $args['post_type']         =  $sort_settings['_rules']['post_type'];
                            $args['posts_per_page']    = -1;
                            $args['orderby']           = 'menu_order';
                            $args['order']             = 'ASC';

                            //set author if need
                            if(isset($sort_settings['_rules']['author']) && is_array($sort_settings['_rules']['author']) && count($sort_settings['_rules']['author']) > 0)
                                $args['author'] =   implode(",",    $sort_settings['_rules']['author']);
                            
                            //set taxonomy if need (deppends on current view_selection
                            if($sort_view_settings['_view_selection'] == 'taxonomy')
                                {
                                    $args['tax_query']  =   array(
                                                                        array(
                                                                                'taxonomy'  => $sort_view_settings['_taxonomy'],
                                                                                'field'     => 'id',
                                                                                'terms'     => array(intval($sort_view_settings['_term_id']))
                                                                                )
                                                                        );   
                                }
                                 
                        }
                        
                    if ($sort_settings['_view_type'] == 'simple')
                        {
                            $args['post_type']         =  $sort_settings['_rules']['post_type'];
                            $args['posts_per_page']    = -1;
                            $args['orderby']           = 'menu_order';
                            $args['order']             = 'ASC';      

                            $sort_rules = $this->get_sort_current_language_rules($sort_settings, FALSE);
                            
                            //set author if need
                            if(isset($sort_rules['author']) && is_array($sort_rules['author']) && count($sort_rules['author']) > 0)
                                $args['author'] =   implode(",",    $sort_rules['author']);
                            
                            //set taxonomy if need (deppends on current view_selection
                            $taxonomy_data              =   $sort_rules['taxonomy'];
                            $taxonomy_data['relation']  =   $sort_rules['taxonomy_relation'];                          
                            $args['tax_query']          =   $taxonomy_data;
                        } 
                    
                    
                    $custom_query = new WP_Query($args);
                    $order_list = $custom_query->posts;
                               
                    //get the current element key
                    $current_position_key = array_search($post->ID, $order_list);
                    
                    if ($previous === TRUE)
                        $required_index = $current_position_key + 1;
                        else
                        $required_index = $current_position_key - 1;
                    
                    //check if there is another position after the current in the list
                     if (isset($order_list[ ($required_index) ]))
                            {
                                //found
                                $post_data  =   get_post($order_list[ ($required_index) ]);   
                            }
                        else
                            {
                                //not found 
                                $post_data  =   null;  
                            }
                            
                     return $post_data;

                }
                
            
           function get_default_adjacent_post($post, $previous)
                {
                    $order_list  = $this->next_previous_get_posts_list($post->post_type);
                    
                    if(count($order_list)   <    1)
                        return null;
                        
                     //get the current element key
                    $current_position_key = array_search($post->ID, $order_list);
                    
                    if ($previous === TRUE)
                        $required_index = $current_position_key + 1;
                        else
                        $required_index = $current_position_key - 1;
                    
                    //check if there is another position after the current in the list
                    if (isset($order_list[ ($required_index) ]))
                        {
                            //found
                            $post_data  =   get_post($order_list[ ($required_index) ]);   
                        }
                        else
                        {
                            //not found 
                            $post_data  =   null;  
                        }
                        
                      
                    return $post_data;
                }
                
                
            
           /**
            * 
            * bbPress filter function 
            * 
            */
           function bbp_before_has_replies_parse_args($args)
                {
                    $args['order'] = 'DESC';  
            
                    return $args;
                }
                
                
           /**
            * 
            * 
            */
           function wp_ecommerce_is_draganddrop()
                {
                    $wpec_orderby = get_option( 'wpsc_sort_by' );
                    if ($wpec_orderby != "dragndrop")
                        return FALSE;
                        
                    return TRUE;
                }
                
                
           /**
            * WP E-Commerce Order Update 
            * 
            * @param mixed $orderBy
            * @param mixed $query
            */
           function wp_ecommerce_orderby($orderBy, $query)
                {
                    //only for non-admin
                    if (is_admin())
                        return $orderBy;
                    
                    if (!apto_is_plugin_active('wp-e-commerce/wp-shopping-cart.php') || ($query->is_archive('wpsc-product') === FALSE && $query->is_tax('wpsc_product_category') === FALSE))
                        return $orderBy;
                      
                    if($this->wp_ecommerce_is_draganddrop() === FALSE)
                        return $orderBy;

                    //always use ascending
                    $query->query['order']  =   'ASC';
                    $orderBy = $this->query_get_orderby('menu_order', $query);

                    return $orderBy;
                }
                
                
                
           function is_BBPress_topic_simple($sortID)
                {
                    if (apto_is_plugin_active('bbpress/bbpress.php') ===    FALSE)    
                        return FALSE;
                        
                        
                    $sort_settings  =   $this->get_sort_settings($sortID);
                    $sort_rules     =   $this->get_sort_current_language_rules($sort_settings, FALSE);
                    if($sort_rules  === FALSE)
                        return FALSE;
                    
                    //check for query rules match
                    
                    /**
                    * 
                    * Check for post type
                    * 
                    */
                    if(count($sort_rules['post_type']) < 1 || count($sort_rules['post_type']) > 1 || $sort_rules['post_type'][0] != 'topic')
                        return FALSE;
                        
                    
                    /**
                    * 
                    * Check for taxonomies match
                    * 
                    */
                    if(isset($sort_rules['taxonomy']) && is_array($sort_rules['taxonomy']) && count($sort_rules['taxonomy']) > 0)
                        return FALSE;
                                         
                    return TRUE;
                }
                
           static function wp_delete_term($term, $tt_id, $taxonomy, $deleted_term)
                {
                    global $wpdb;
                    
                    $sort_items =   self::get_sorts_by_filters(array());
                    
                    foreach($sort_items as $sort_item)
                        {
                                 
                            $sort_view_settings =   self::get_sort_view_settings($sort_item->ID);
                            
                            switch($sort_view_settings['_view_type'])
                                {
                                    case 'simple'   :
                                                                                         
                                                        break;      
                                    
                                    case 'multiple'   :
                                                        //seek for sort view
                                                        $mysql_query = "SELECT PM1.post_id FROM ". $wpdb->posts ."
                                                                            INNER JOIN ". $wpdb->postmeta ." AS PM1 ON (". $wpdb->posts .".ID = PM1.post_id)
                                                                            INNER JOIN ". $wpdb->postmeta ." AS PM2 ON (". $wpdb->posts .".ID = PM2.post_id)
                                                                            INNER JOIN ". $wpdb->postmeta ." AS PM3 ON (". $wpdb->posts .".ID = PM3.post_id)
                                                                            WHERE ". $wpdb->posts .".post_parent = ".   $sort_item->ID  ."  
                                                                                    AND ". $wpdb->posts .".post_type = 'apto_sort' 
                                                                                    AND PM1.meta_key =   '_view_selection'   AND PM1.meta_value   =   'taxonomy'
                                                                                    AND PM2.meta_key =   '_taxonomy'   AND PM2.meta_value   =   '"  .   $taxonomy   ."'
                                                                                    AND PM3.meta_key =   '_term_id'   AND PM3.meta_value   =   '"   .   $term   ."'
                                                                                    ";
                                                        $results =   $wpdb->get_results($mysql_query);  
                                                        
                                                        if(count($results)    >   0)
                                                            {
                                                                foreach($results    as  $result)
                                                                    {
                                                                        $sort_view_id   =   $result->post_id;
                                                                        
                                                                        //delete from posts
                                                                        wp_delete_post( $sort_view_id, TRUE );
                                                                        
                                                                        self::delete_sort_list_from_table($sort_view_id);    
                                                                    }
                                                            }
                                                           
                                                        break;
                                }
                        }
                        
                    return ;   
                }

                
           /**
           * Disable the free plugin if active
           * 
           */
           function disable_post_types_order()
                {
                    if ( is_network_admin() ) 
                        {
                            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                            if ( is_plugin_active_for_network( 'post-types-order/post-types-order.php' ) ) 
                                {
                                    deactivate_plugins( 'post-types-order/post-types-order.php' );
                                    
                                    $url_scheme =   is_ssl() ?  'https://'  :   'http://';
                                    
                                    //reload the page
                                    $current_url = set_url_scheme( $url_scheme . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ); 
                                    wp_redirect($current_url);
                                    die();
                                }     
                            
                        }
                        else
                        {
                            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                            if ( is_plugin_active( 'post-types-order/post-types-order.php' ) ) 
                                {
                                    deactivate_plugins( 'post-types-order/post-types-order.php' );
                                    
                                    $url_scheme =   is_ssl() ?  'https://'  :   'http://';
                                    
                                    //reload the page
                                    $current_url = set_url_scheme( $url_scheme . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ); 
                                    wp_redirect($current_url);
                                    die();
                                } 
                        }   
                }
                
           
           
           /**
           * Convert a string TRUE/FALSE to boolean
           * 
           * @param mixed $string
           */
           function str_to_bool( $string )
                {
                    $string =   strtolower($string);
                    switch ($string)
                        {
                            case 'true' :
                                            return TRUE;
                                            break;
                            default :
                                            return FALSE;
                                            break;
                        }
                }
            
        }
        

?>