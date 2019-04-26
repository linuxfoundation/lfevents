<?php
 
    class APTO_interface_helper
        {
            
            var $response           =   array();
            var $functions;
            var $admin_functions;
            var $conditional_rules;
                        
            function __construct()
                {
                    $this->functions            =   new APTO_functions();
                    $this->admin_functions      =   new APTO_admin_functions(); 
                    
                    global $APTO;
                    $this->conditional_rules    = &$APTO->conditional_rules;
                                                        
                }

            
            function get_current_menu_location()
                {
                    $location_menus = $this->admin_functions->get_available_menu_locations();
                    
                    $current_menu_page  = $_GET['page'];
                    $current_menu_page  =   str_replace('apto_' ,   '', $current_menu_page);
                    
                    foreach($location_menus as $menu_id =>  $location_menu_data)
                        {
                            if($location_menu_data['slug']  ==  $current_menu_page)
                                return $menu_id;
                        }
                    
                    return FALSE;   
                }
                
            function get_current_menu_location_slug()
                {
                    $location_menus = $this->admin_functions->get_available_menu_locations();
                    
                    $current_menu_page  = isset($_GET['page']) ?    $_GET['page']   :   '';
                    $current_menu_page  =   str_replace('apto_' ,   '', $current_menu_page);
                    
                    foreach($location_menus as $menu_id =>  $location_menu_data)
                        {
                            if($location_menu_data['slug']  ==  $current_menu_page)
                                return $location_menu_data['slug'];
                        }
                    
                    return FALSE;   
                }
                
            function get_menu_id_from_menu_slug($menu_slug)
                {
                    $location_menus = $this->admin_functions->get_available_menu_locations();
                     
                    foreach($location_menus as $menu_id =>  $location_menu_data)
                        {
                            if($location_menu_data['slug']  ==  $menu_slug)
                                return $menu_id;
                        }
                    
                    return FALSE;   
                }
                
            function get_menu_slug_from_menu_id($menu_id)
                {
                    $location_menus = $this->admin_functions->get_available_menu_locations();
                     
                    foreach($location_menus as $location_menu_id =>  $location_menu_data)
                        {
                            if($location_menu_id  ==  $menu_id)
                                return $location_menu_data['slug'];
                        }
                    
                    return FALSE;   
                }
                
            
            
                
            
            function get_sort_meta($sort_id, $meta_name)
                {
                    if($sort_id == '')   
                        return '';
                        
                    return get_post_meta($sort_id, $meta_name, TRUE);
                }
                
            
            /**
            * Check for sort list deletion
            * 
            */
            function sort_list_delete()
                {
                    if(!isset($_GET['delete_sort']))
                        return FALSE;
                        
                    //check nonce
                    if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'APTO/sort-delete' ) ) 
                        return FALSE;
                    
                    global $post;
                    $_wp_query_post =   $post;
                    
                    $sort_id    =   intval($_GET['sort_id']);
                    
                    //delete all views sorts (childs)
                    $argv   =   array(
                                        'post_type'             =>  'apto_sort',
                                        'posts_per_page'        =>  '-1',
                                        'post_parent'           =>  $sort_id,
                                        'ignore_custom_sort'    =>  TRUE  
                                        );
                    $custom_query       =   new WP_Query($argv);
                    while($custom_query->have_posts())
                        {
                            $custom_query->the_post();
                            wp_delete_post( $post->ID, TRUE );
                            
                            //delete the sort indexes
                            $this->functions->delete_sort_list_from_table($post->ID);    
                        }
                    
                    //wp_reset_postdata();
                    //use this instead as using a setup_postdata() without any query will reset to nothing
                    $post   =   $_wp_query_post;
                    
                    //delete sort holder
                    wp_delete_post( $sort_id, TRUE );
                    
                    //redirect to the list                        
                    $redirect_argv                          =   array();
                    $redirect_argv['page']                  =   'apto_' . $this->get_menu_slug_from_menu_id($this->get_current_menu_location());
                    $redirect_argv['base_url']              =   $this->get_current_menu_location();
                    $redirect_argv['sort_deleted']     =   'true';
                    
                    wp_redirect($this->get_tab_link($redirect_argv));
                    //echo 'REDIRECT TO '  . $this->get_tab_link($redirect_argv);
                    die();
                }
            
            
            function general_interface_update($sortID)
                {
                    $sort_view_ID   =   isset($_GET['sort_view_id']) ? intval($_GET['sort_view_id']) : '';
                    $taxonomy       =   isset($_GET['taxonomy']) ? preg_replace( '/[^a-zA-Z0-9_\-]/', '', $_GET['taxonomy']) : '';
                    $term_id        =   isset($_GET['term_id']) ? intval($_GET['term_id']) : '';
                    
                    $doRedirect     =   FALSE;
                    $redirect_argv  =   array();
                    
                    //check for order_type update auto /manual
                    $order_type = isset($_GET['order_type']) ? preg_replace( '/[^a-zA-Z0-9_\-]/', '', $_GET['order_type']) : '';
                    if($order_type != '')
                        {
                            update_post_meta($sort_view_ID, '_order_type', $order_type); 
                        }
                        
                    //check for archive
                    $view_selection =   isset($_GET['view_selection']) ? preg_replace( '/[^a-zA-Z0-9_\-]/', '', $_GET['view_selection']) : '';
                    if($view_selection != '')
                        {
                            //get a $term_id if empty and $taxonomny is set
                            if($taxonomy != '' && $term_id == '')
                                {
                                    $argv = array(
                                                    'hide_empty'    =>   0,
                                                    'fields'        =>  'ids'
                                                    );
                                    $terms = get_terms($taxonomy, $argv);
                                    if(count($terms) > 0)
                                        {
                                            reset ($terms);
                                            $term_id    =   current($terms);
                                        }
                                }
                            
                            //get archive view id 
                            $attr   =   array(
                                                '_view_selection'   =>  $view_selection
                                                );
                            
                            if($taxonomy != '' && $term_id != '')
                                {
                                    $attr['_taxonomy']      =   $taxonomy;
                                    
                                    //check to translate term
                                    if (defined('ICL_LANGUAGE_CODE') && defined('ICL_SITEPRESS_VERSION'))
                                        $attr['_term_id']       =   icl_object_id($term_id, $taxonomy, FALSE, $this->functions->get_blog_language());
                                        else
                                        $attr['_term_id']       =   $term_id;
                                        
                                    $attr['_view_language'] =   $this->functions->get_blog_language();     
                                }

                            if($view_selection  ==  'archive')
                                $attr['_view_language']   =   $this->functions->get_blog_language();     
                                                        
                            $view_ID   =   $this->functions->get_sort_view_id_by_attributes($sortID, $attr);
                            
                            //create view if not exists
                            if($view_ID == '')
                                {
                                    //create the sort view. 
                                    $sort_view_meta     =   array(
                                                                    '_order_type'               =>  'manual',
                                                                    '_view_selection'           =>  $view_selection
                                                                    );
                                    if($taxonomy != '' && $term_id != '')
                                        {
                                            $sort_view_meta['_taxonomy']  =   $taxonomy;
                                            //check to translate term
                                            if (defined('ICL_LANGUAGE_CODE') && defined('ICL_SITEPRESS_VERSION'))
                                                {
                                                    $attr['_term_id']       =   icl_object_id($term_id, $taxonomy, FALSE, $this->functions->get_blog_language());
                                                    if($attr['_term_id']    !=  $term_id)
                                                        {
                                                            $doRedirect =   TRUE;
                                                            $redirect_argv ['term_id']      =   $attr['_term_id'];
                                                            $redirect_argv ['taxonomy']     =   $taxonomy;
                                                        }
                                                }
                                                else
                                                $attr['_term_id']       =   $term_id;
                                        }
                                        
                     
                                    $sort_view_meta['_view_language']   =   $this->functions->get_blog_language();
                                        
                                    $view_ID       =   $this->create_view($sortID, $sort_view_meta);   
                                    
                                }
                            
                            update_post_meta($sortID, '_last_sort_view_ID', $view_ID);
                                
                        }
                        
                    //check for taxonomy / term change
                    if($taxonomy != '' && $term_id != '')
                        {
                            $attr   =   array(
                                                '_view_selection'   =>  'taxonomy',
                                                '_taxonomy'         =>  $taxonomy,
                                                '_term_id'          =>  $term_id,
                                                '_view_language'    =>  $this->functions->get_blog_language()
                                                );
                            
                            //check to translate term
                            if (defined('ICL_LANGUAGE_CODE') && defined('ICL_SITEPRESS_VERSION'))
                                {
                                    $attr['_term_id']       =   icl_object_id($term_id, $taxonomy, FALSE, $this->functions->get_blog_language());
                                }
                            
                            $view_ID   =   $this->functions->get_sort_view_id_by_attributes($sortID, $attr); 
                            if($view_ID == '')
                                {
                                    //create the sort view. 
                                    $sort_view_meta     =   array(
                                                                    '_order_type'               =>  'manual',
                                                                    '_view_selection'           =>  'taxonomy'
                                                                    );
                                    if($taxonomy != '' && $term_id != '')
                                        {
                                            $sort_view_meta['_taxonomy']        =   $taxonomy;
                                            //check to translate term
                                            if (defined('ICL_LANGUAGE_CODE') && defined('ICL_SITEPRESS_VERSION'))
                                                $sort_view_meta['_term_id']       =   icl_object_id($term_id, $taxonomy, FALSE, $this->functions->get_blog_language());                    
                                                else
                                                $sort_view_meta['_term_id']       =   $term_id;
                                            
                                            $sort_view_meta['_view_language']   =  $this->functions->get_blog_language();
                                        }
                                    $view_ID       =   $this->create_view($sortID, $sort_view_meta);
                                    
                                    //$doRedirect = TRUE;
                                }
                                
                            update_post_meta($sortID, '_last_sort_view_ID', $view_ID);
                            
                            //chek for translated term, make sure it's updated for url
                            if($attr['_term_id']    !=  $term_id)
                                {
                                    $doRedirect =   TRUE;
                                    $redirect_argv ['term_id']      =   $attr['_term_id'];
                                    $redirect_argv ['taxonomy']     =   $taxonomy;
                                }
                        }
                    
                    //check for order reset
                    if (isset($_POST['order_reset']) && $_POST['order_reset'] == 'true')
                        {
                            if(wp_verify_nonce($_POST['nonce'],  'reorder-interface-reset-' . get_current_user_id()))
                                { 
                                    $reset_sort_view_ID =   intval($_POST['sort_view_ID']);
                                    
                                     global $wpdb;
                                                        
                                    $query = "DELETE FROM `". $wpdb->prefix ."apto_sort_list`
                                                    WHERE `sort_view_id`    =   ". $reset_sort_view_ID;
                                    $results = $wpdb->get_results($query);
                                    
                                    //check if archive, then reset the posts table too
                                    $sort_view__view_selection  =   get_post_meta( $reset_sort_view_ID,  '_view_selection', TRUE);
                                    if($sort_view__view_selection   ==  'archive')
                                        {
                                            $sort_view_post =   get_post($reset_sort_view_ID);
                                            $sort_settings  =   $this->functions->get_sort_settings($sort_view_post->post_parent);
                                            
                                            if(isset($sort_settings['_rules']['post_type']) && count($sort_settings['_rules']['post_type']) == 1)
                                                {
                                                    $sort_post_type =   $sort_settings['_rules']['post_type'][0];
                                                    
                                                    //reset the menu_order
                                                    $query = "UPDATE `". $wpdb->posts ."`
                                                                    SET menu_order = 0
                                                                    WHERE `post_type`    =   '". $sort_post_type ."'";
                                                    $results = $wpdb->get_results($query);
                                                }
                                                
                                        }
                                    
                                    echo '<div id="message" class="updated"><p>' . __('Sort order reset successfully', 'apto') . '</p></div>';
                                }
                                else
                                {
                                    echo '<div id="message" class="updated"><p>' . __( 'Invalid Nonce', 'apto' )  . '</p></div>';
                                } 
                        } 
                    
                    if($doRedirect === TRUE)
                        {
                            $sort_view_data     =   get_post($view_ID);
                            if($sort_view_data->post_parent > 0)
                                $sortID             =   $sort_view_data->post_parent;
                                else
                                $sortID             =   $argv['sort_view_id'];
                            
                            //redirect to new sort view
                            $redirect_argv ['sort_id']      =   $sortID;
                            $redirect_argv['page']          =   'apto_' . $this->get_menu_slug_from_menu_id($this->get_current_menu_location());
                                                 
                                                 
                            wp_redirect($this->get_tab_link($redirect_argv));
                            
                            die();
                        }
                    
                }
                           
            
            /**
            * Check for settings form update
            * 
            */
            function settings_update()
                {
                    if(!isset($_POST['apto_sort_settings_form_submit']))
                        return FALSE;
                        
                    //check the nonce
                    if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'APTO/sort-settings' ) )
                        {
                            $redirect_argv['settings_saved']    =   'false';
                    
                            wp_redirect($this->get_tab_link($redirect_argv));   
                            
                            die();
                        }
                        
                    $sort_id =  sanitize_key($_POST['sort_id']);
                    
                    $new_sort   =   FALSE;
                    
                    //check for new sort tab
                    if($sort_id == '')
                        {
                            //create new sort
                            $post_data  =   array(
                                                    'post_type'     =>  'apto_sort',
                                                    'post_status'   =>  'publish'
                                                    );
                            $sort_id = wp_insert_post( $post_data );
                            
                            $new_sort   =   TRUE;
                        }
                    
                    $current_language   =   $this->functions->get_blog_language();
                    $default_language   =   $this->functions->get_blog_default_language();
                    
                    //process the query rules
                    $rules  =   array();
                    if(isset($_POST['rules']))
                        $rules = $this->query_rules_filter( $_POST['rules'] );     
                                                  
                    //mark the current language sort update
                    $sort_settings_update_languages =   get_post_meta($sort_id, '_settings_update_languages', TRUE);
                    $sort_settings_update_languages[$current_language]  =   true;
                    update_post_meta($sort_id, '_settings_update_languages', $sort_settings_update_languages);
                    
                                        
                    //process the conditionals
                    $conditionals = array();
                    if(isset($_POST['conditional_rules']))
                        $conditionals = $this->conditional_rules_filter( $_POST['conditional_rules'] );
                    update_post_meta($sort_id, '_conditionals', $conditionals);
                    
                    
                    //process the interface
                    $options    =   array(
                                            '_title',
                                            '_description',
                                            '_location',
                                            '_autosort',
                                            '_adminsort',
                                            '_pto_interface_sort',
                                            '_new_items_to_bottom',
                                            '_show_thumbnails',
                                            '_pagination',
                                            '_pagination_posts_per_page',
                                            '_pagination_offset_posts',
                                            '_wpml_synchronize',
                                            '_capability'
                                            );
                                            
                    $default_options_values         =   array(
                                                                '_pto_interface_sort'           =>  'no',
                                                                '_pagination_posts_per_page'    =>  100,
                                                                '_pagination_offset_posts'      =>  5  ,
                                                                
                                                                '_wpml_synchronize'             =>  'no',
                                                                );
                    
                    $post_main_fields               =   array(
                                                                '_title'         =>  'post_title',
                                                                '_description'   =>  'post_content'
                                                                );
                                                    
                    $overwrite_options              =   array();                        
                    
                    foreach($options as $option)
                        {
                            $value = isset($_POST['interface'][$option]) ? trim(stripslashes($_POST['interface'][$option])) :   '';
                                          
                            //check for empty titles
                            if($option == '_title' && $value == '')
                                $value = 'Sort #' . $sort_id;
                            
                            
                            //never allow pagination for hierarhical post types
                            if($option == '_pagination' && $this->get_is_hierarhical_by_settings($sort_id)   === TRUE)
                                {
                                    $value  =   'no';
                                    
                                    $overwrite_options['_pagination_posts_per_page']    =   '';
                                    $overwrite_options['_pagination_offset_posts']      =   '';
                                }
                            
                            if(empty($value)    && isset($default_options_values[$option]))
                                {
                                    $value =    $default_options_values[$option];   
                                }
                            
                            
                            //add as meta value
                            update_post_meta($sort_id, $option, $value);
                            
                            //check if it's title or description to update the main apto_sort data
                            if(isset($post_main_fields[$option]))
                                {
                                    $post_data['ID']    =   $sort_id;
                                    $post_data[$post_main_fields[$option]]    =   $value;

                                    wp_update_post( $post_data );
                                }
                        }
                        
                    //if no pagination option, reset other related settings
                    if ( array_search ( '_pagination', $options ) ===   FALSE )
                        {
                            $overwrite_options['_pagination']                   =   'no';
                            $overwrite_options['_pagination_posts_per_page']    =   '';
                            $overwrite_options['_pagination_offset_posts']      =   '';   
                        }
                        
                    if ( count ( $overwrite_options ) > 0 )
                        {
                            foreach ($overwrite_options as  $option    =>  $value )
                                {
                                    update_post_meta($sort_id, $option, $value);
                                }   
                            
                        }
                        
                               
                    //process the status
                    global $wp_post_statuses;
                    $statuses = $_POST['interface']['_status'];
                    
                    $all_statuses   =   $this->functions->get_wp_post_statuses();
                    
                    //turn off all
                    foreach($all_statuses   as  $status =>  $data)
                        {
                            $all_statuses[$status]['status']    =   'hide';
                        }
                        
                    foreach($statuses   as  $status)
                        {
                            $all_statuses[$status]['status']    =   'show';
                        }
                    //add as meta value
                    update_post_meta($sort_id, '_status', $all_statuses);    
                        
                        
                    
                    $redirect_argv  =   array(
                                                'sort_id'    =>  $sort_id
                                                );
                                                
                    $redirect_argv['page']      = 'apto_' . $this->get_menu_slug_from_menu_id($_POST['interface']['_location']);
                    $redirect_argv['base_url']  = $_POST['interface']['_location'];
                                        
                    //set the infromation regarding view type (if use single list or multiple;
                    $reference_rules =  $rules;
                    $old_sort_settings  =   $this->functions->get_sort_settings($sort_id);
                    
                    /*
                    if($new_sort   === FALSE    &&  defined('ICL_LANGUAGE_CODE'))
                        $reference_rules    =   $old_sort_settings['_rules'];
                    */
                    
                    $sort_view_type     =   $this->get_sort_view_type_by_settings($reference_rules);
                    update_post_meta($sort_id, '_view_type', $sort_view_type); 
                    
                    
                    //WPML sort rules save
                    if(defined('ICL_LANGUAGE_CODE') && $sort_view_type ==  'simple')
                        {
                            if($default_language    ==  $current_language)
                                update_post_meta($sort_id, '_rules', $rules);
                            
                            
                            //WPML
                            if(defined('ICL_SITEPRESS_VERSION'))
                                {
                                
                                    //try to translate the sort settings for other languages
                                    $wpml_languages     =   APTO_WPML_utils::get_wpml_languages();
                                    foreach($wpml_languages as  $wpml_language)
                                        {
                                                                                
                                            //skipp if the same language
                                            if($wpml_language['code']   ==  $current_language)
                                                continue;
                                                
                                            $translated_rules   =   APTO_WPML_utils::translate_sort_rules($rules, $wpml_language['code']);
                                            if($translated_rules    === FALSE)
                                                continue;

                                            update_post_meta($sort_id, '_rules_' . $wpml_language['code'], $translated_rules);
                                            
                                            //set the language as being translated
                                            $sort_settings_update_languages =   get_post_meta($sort_id, '_settings_update_languages', TRUE);
                                            $sort_settings_update_languages[$wpml_language['code']]  =   TRUE;
                                            update_post_meta($sort_id, '_settings_update_languages', $sort_settings_update_languages);
                                            
                                            //set also default rules if default langauge
                                            if($wpml_language['code']   ==  $default_language)
                                                update_post_meta($sort_id, '_rules', $translated_rules);
                                                
                                        }
                                }
                                              
                            update_post_meta($sort_id, '_rules_' . $current_language, $rules);
                            
                        }
                        else
                        update_post_meta($sort_id, '_rules', $rules);
                    
                    
                    if($sort_view_type == 'simple')
                        {
                            //check if default sort view already exists
                            $attr = array(
                                            '_view_selection'       =>  'simple',
                                            '_view_language'        =>  $this->functions->get_blog_language()
                                            );
                            $sort_view_id   =   $this->functions->get_sort_view_id_by_attributes($sort_id, $attr);
                            
                            if($sort_view_id == '')
                                {
                                    //create the default view for this sortID
                                    $sort_view_meta     =   array(
                                                                    '_view_selection'       =>  'simple',
                                                                    '_order_type'           =>  'manual',
                                                                    '_view_language'        =>  $this->functions->get_blog_language()
                                                                    );
                                    $sort_view_id       =   $this->create_view($sort_id, $sort_view_meta);
                                }
                                
                            //set this sort view as default for the main sort
                            update_post_meta($sort_id, '_last_sort_view_ID', $sort_view_id);
                        }
                    
                    else if($sort_view_type == 'multiple')
                        {
                            //check if default sort view already exists
                            $attr = array(
                                            '_view_selection'       =>  'archive',
                                            '_view_language'        =>  $this->functions->get_blog_language()
                                            );
                            
                            $sort_view_id   =   $this->functions->get_sort_view_id_by_attributes($sort_id, $attr);
                            
                            if($sort_view_id == '')
                                {
                                    //create the default view for this sortID
                                    $sort_view_meta     =   array(
                                                                    '_order_type'               =>  'manual',
                                                                    '_view_selection'           =>  'archive',
                                                                    '_view_language'            =>  $this->functions->get_blog_language()
                                                                    );
                                    $sort_view_id       =   $this->create_view($sort_id, $sort_view_meta);
                                    
                                    //set this sort view as default for the main sort
                                    update_post_meta($sort_id, '_last_sort_view_ID', $sort_view_id);
                                }
                        }

                    $redirect_argv['settings_saved']    =   'true';
                    
                    wp_redirect($this->get_tab_link($redirect_argv));
                    //echo 'REDIRECT TO '  . $this->get_tab_link($redirect_argv);
                    die();
                                        
                }
                
            
            /**
            * Check for sort list update (automatic order, manual is sent through ajax) 
            * 
            */
            function automatic_sort_order_update()
                {
                    if(!isset($_POST['apto_sort_form_order_update']))
                        return FALSE;        
                    
                    //check for order migrate to manual
                    if( isset($_POST['automatic_order_send_to_manual']))
                        {
                            $this->automatic_order_send_to_manual();
                            return FALSE;
                        }
                                            
                    $sort_id        =   $_POST['sort_id'];
                    $sort_view_id   =   $_POST['sort_view_ID'];
                    
                    $order_by               =   array_values($_POST['auto_order_by']);
                    $custom_field_name      =   array_values($_POST['auto_custom_field_name']);
                    $custom_field_type      =   array_values($_POST['auto_custom_field_type']);
                    $custom_function_name   =   array_values($_POST['auto_custom_function_name']);
                    $order                  =   array_values($_POST['auto_order']);
                    
                    update_post_meta($sort_view_id, '_auto_order_by', $order_by); 
                    update_post_meta($sort_view_id, '_auto_custom_field_name', $custom_field_name);
                    update_post_meta($sort_view_id, '_auto_custom_field_type', $custom_field_type); 
                    update_post_meta($sort_view_id, '_auto_custom_function_name', $custom_function_name);
                    update_post_meta($sort_view_id, '_auto_order', $order);
                    
                    $is_batch_update    = FALSE;
                    if(isset($_POST['batch_order_update']) && $_POST['batch_order_update'] == 'yes')
                        $is_batch_update = TRUE;
                    
                    if($is_batch_update === TRUE)
                        {
                            $sort_view_settings =   $this->functions->get_sort_view_settings($sort_view_id);
                            
                            //get all terms of current taxonomy
                            $args = array(
                                            'hide_empty'    => false,
                                            'fields'        =>  'ids'
                                            );
                            
                            if($sort_view_settings['_term_id']  >   0)
                                {
                                    $args['child_of']   =   $sort_view_settings['_term_id'];
                                }
                            
                            $batch_work_terms = get_terms( $sort_view_settings['_taxonomy'], $args );
                            
                            //update the order type for all terms
                            foreach($batch_work_terms as $batch_term_id)   
                                {
                                    //check if the sort view already exists
                                    $attr = array(
                                                    '_view_selection'   =>  'taxonomy',
                                                    '_taxonomy'         =>  $sort_view_settings['_taxonomy'],
                                                    '_term_id'          =>  $batch_term_id,
                                                    '_view_language'    =>  $this->functions->get_blog_language()
                                                    );
                                    $batch_sort_view_id   =   $this->functions->get_sort_view_id_by_attributes($sort_id, $attr);
                                    
                                    if($batch_sort_view_id > 0)
                                        {
                                            update_post_meta($batch_sort_view_id, '_auto_order_by',             $order_by); 
                                            update_post_meta($batch_sort_view_id, '_auto_custom_field_name',    $custom_field_name);
                                            update_post_meta($batch_sort_view_id, '_auto_custom_field_type',    $custom_field_type); 
                                            update_post_meta($batch_sort_view_id, '_auto_custom_function_name', $custom_function_name);
                                            update_post_meta($batch_sort_view_id, '_auto_order',                $order);    
                                            
                                            continue;
                                        }
                                        
                                    //the sort view does not exists, create that
                                    $sort_view_meta     =   array(
                                                                    '_order_type'                   =>  'auto',
                                                                    '_view_selection'               =>  'taxonomy',
                                                                    '_taxonomy'                     =>  $sort_view_settings['_taxonomy'],
                                                                    '_term_id'                      =>  $batch_term_id,
                                                                    '_view_language'                =>  $this->functions->get_blog_language(),
                                                                    '_auto_order_by'                =>  $order_by,
                                                                    '_auto_custom_field_name'       =>  $custom_field_name,
                                                                    '_auto_custom_field_type'       =>  $custom_field_type,
                                                                    '_auto_custom_function_name'    =>  $custom_function_name,
                                                                    '_auto_order'                   =>  $order
                                                                    );
                                    $batch_sort_view_id =   $this->create_view($sort_id, $sort_view_meta);                                    
                                    
                                }
                        } 

                }
            
            
            
            /**
            * Check for automatic order send to manual
            * 
            */
            function automatic_order_send_to_manual()
                {
                    
                    $sort_view_id   =   $_POST['sort_view_ID'];    
                    
                    $args   =   $this->functions->query_arguments_from_sort_settings( $sort_view_id );
                    
                    //we need only the id's
                    $args['fields']         =   'ids';
                    
                    //add the sort_view_id to arguments
                    $args['sort_view_id']   =   $sort_view_id;
                    
                    $custom_query   =   new WP_Query( $args );
                    
                    $objects_list   =   $custom_query->posts;
                                    
                    if (count( $objects_list )  <   1)
                        return;                    
                    
                    global $wpdb;
                    
                    //remove the old order
                    $this->functions->delete_sort_list_from_table($sort_view_id);
                    
                    //add to sort list table
                    foreach( $objects_list   as  $object_id )
                        {
                            $mysql_query = "INSERT INTO `". $wpdb->prefix ."apto_sort_list`
                                                (`id`, `sort_view_id`, `object_id`)
                                                VALUES (NULL, '" . $sort_view_id . "', '" . $object_id . "')";
                            $results =   $wpdb->get_var($mysql_query);    
                            
                        }
                         
                    do_action('apto_order_update_complete', $sort_view_id);        
                    
                }
                
            
            static function create_view($sortID, $sort_view_meta = array())
                {
                    
                    $post_data  =   array(
                                            'post_type'     =>  'apto_sort',
                                            'post_status'   =>  'publish',
                                            'post_parent'   =>  $sortID
                                            );
                    $sort_view_id = wp_insert_post( $post_data );
                          
                    //add the meta
                    foreach($sort_view_meta as $key =>  $value)
                        {
                            update_post_meta($sort_view_id, $key, $value); 
                        }
                        
                    return $sort_view_id;
                    
                }
                
            
            /**
            *   Filter the conditionals before save
            *   remove empty data
            * 
            *   @param array $conditionals
            */
            function conditional_rules_filter($conditionals)
                {
                    if(count($conditionals) > 0)
                        {
                            foreach($conditionals as $key   =>  $conditional_block)   
                                {
                                    $conditionals[$key]   =   array_filter($conditional_block, array($this, 'conditional_rules_empty_callback'));
                                }
                        }
                    
                    //filter again for empty blocks
                    $conditionals   =   array_filter($conditionals);
                                        
                    return $conditionals;   
                }
                
            function conditional_rules_empty_callback($element)
                {
                    return !empty($element['conditional_id']);   
                }
                
                
            /**
            *   Filter the query rules before save
            *   remove empty data
            * 
            *   @param array $conditionals
            */
            function query_rules_filter($rules)
                {
                    if(isset($rules['post_type']))
                        {
                            $rules['post_type'] = array_unique($rules['post_type']);
                        }
                        
                    if(isset($rules['author']))
                        {
                            $rules['author'] = array_unique($rules['author']);
                        }
                        
                    if(isset($rules['taxonomy']))
                        {
                            $rules['taxonomy']   =   array_filter($rules['taxonomy'], array($this, 'rules_taxonomy_filter_callback'));
                        }
                                        
                    return $rules;   
                }
                
            function rules_taxonomy_filter_callback($element)
                {
                    //check for terms, this need to include at least one element
                    if(!isset($element['terms']) || count($element['terms']) < 1)
                        return FALSE;
                    
                    return TRUE;    
                }
                
                
                
            function get_tab_link($attr)
                {
                    $defaults   = array (
                                            'page'      =>   $this->get_menu_slug_from_menu_id($this->get_current_menu_location()),
                                            'post'      =>   isset($_GET['post'])    ?   $_GET['post']   :   '',
                                            'base_url'  =>   $this->get_current_menu_location()
                                        );
                                        
                    // Parse incoming $args into an array and merge it with $defaults
                    $attr   =   wp_parse_args( $attr, $defaults );
                    $attr   =   array_filter($attr);
                    
                    $base_url =     'edit.php';
                    if(isset($attr['base_url']))
                        {
                            $base_url   =   $attr['base_url'];
                            unset($attr['base_url']);
                        }
                    
                    $link = admin_url($base_url);
                    if(strpos($base_url, "?") === FALSE)
                        $link .= '?';
                    
                    $link .=    '&' . http_build_query($attr);
                    
                    return $link;                        
                }
             
            /**
            * Return link for items within front side
            *     
            * @param array $attr
            */
            static function get_item_link($attr)
                {
                    $defaults   = array (

                                        );
                                        
                    // Parse incoming $args into an array and merge it with $defaults
                    $attr   =   wp_parse_args( $attr, $defaults );
                    $attr   =   array_filter($attr);
                    
                    global $wp_rewrite;
                    
                    $link   =   $attr['base_url'];
                    unset($attr['base_url']);

                    if(strpos($link, "?") === FALSE)
                        $link .= '?';
                    
                    $link .=    '&' . http_build_query($attr);
                    
                    return $link;                        
                }
               
            
            function get_rule_box()
                {
                    $rule_type   =   preg_replace( '/[^a-zA-Z0-9_\-]/', '', $_POST['type']);
                    
                    switch($rule_type)
                        {
                            case 'post_type'    :
                                                    $html_data  =   $this->get_rule_post_type_html_box();
                                                    
                                                    $this->response['html']             =   $html_data;
                                                    $this->response['message']          =   '';
                                                    $this->response['response_code']    =   '0';
                                                    
                                                    break;   
                            
                            case 'taxonomy'     :
                                                    $options    = array(
                                                                            'group_id'  =>  intval($_POST['group_id'])
                                                                        );
                                                    $html_data  =   $this->get_rule_taxonomy_html_box($options);
                                                    
                                                    $this->response['html']             =   $html_data;
                                                    $this->response['message']          =   '';
                                                    $this->response['response_code']    =   '0';
                                                    
                                                    break;
                                                    
                            case 'meta'        :
                                                    $options    = array(
                                                                            'group_id'  =>  intval($_POST['group_id'])
                                                                        );
                                                    $html_data  =   $this->get_rule_meta_html_box($options);
                                                    
                                                    $this->response['html']             =   $html_data;
                                                    $this->response['message']          =   '';
                                                    $this->response['response_code']    =   '0';
                                                    
                                                    break;
                                                    
                            case 'author'     :
                                                    $html_data  =   $this->get_rule_author_html_box();
                                                    
                                                    $this->response['html']             =   $html_data;
                                                    $this->response['message']          =   '';
                                                    $this->response['response_code']    =   '0';
                                                    
                                                    break;  
                        }
                    
                                        
                    $this->output_response();
                    die();                    
                }
                
            
            function get_rule_post_type_html_box($options = array())
                {
                    $defaults = array (
                                             'default'          =>  FALSE,
                                             'selected_value'   =>  ''
                                        );
                    
                    // Parse incoming $args into an array and merge it with $defaults
                    $options = wp_parse_args( $options, $defaults );
                    
                    
                    ob_start();
                    
                    ?>
                        <tr>
                                                                                                      
                            <td class="param">
                                <select class="select" name="rules[post_type][]">
                                    <?php
                                    
                                        $ignore =   array();
                                    
                                        /**
                                        * Since 3.9.7
                                        * We don't remove any post types anymore
                                        * 
                                        * @var mixed
                                        */
                                        /*
                                        $ignore = array (
                                                            'revision',
                                                            'nav_menu_item'
                                                            );
                                        */
                                        
                                        $post_types =   $this->functions->get_post_types($ignore);
                                        //add Any support
                                        $post_types['any'] =   'any';
                                        
                                        foreach($post_types as $post_type)
                                            {
                                                if($post_type  ==   'any')
                                                    {
                                                        $post_type_data = new stdClass();
                                                        $post_type_data->label  =   'Any';
                                                    }
                                                    else
                                                    $post_type_data = get_post_type_object ( $post_type );
                                                
                                                ?><option <?php
                                                
                                                    if($options['selected_value'] == $post_type)
                                                        echo 'selected="selected" ';
                                                
                                                ?>value="<?php echo $post_type ?>"><?php echo $post_type_data->label ?> <small>(<?php echo $post_type; ?>)</small><?php
                                                    if($post_type  ==   'any')
                                                        {
                                                            ?><small> <?php _e('**WARNING!! This will load all post types objects. Recommended to be used with Advanced Query Rules', 'apto');  ?></small><?php   
                                                        }
                                                ?></option><?php            
                                            }
                                    
                                    ?>
                                </select>
                            </td>
                            <td class="buttons">
                                <?php if($options['default'] !== TRUE) { ?><a href="javascript: void(0);" onClick="APTO.remove_rule_item(this, 'tr')" class="remove item"></a><?php } ?>
                            </td>
                        </tr>
                    <?php
                    
                    $html_data  =   ob_get_contents();
                    ob_end_clean();
                    
                    return $html_data;   
                }
                
                
            function get_rule_taxonomy_html_box($options = array())
                {
                    $defaults   = array (
                                            'group_id'          =>  1,
                                            'taxonomy'          =>  '',
                                            'operator'          =>  'IN',
                                            'include_children'  =>  'TRUE',
                                            'selected'          =>  array(),
                                            'html_alternate'    =>  FALSE
                                        );
                    
                    // Parse incoming $args into an array and merge it with $defaults
                    $options = wp_parse_args( $options, $defaults );
                    
                    
                    ob_start();
                    
                    ?>
                        <table class="apto_input widefat apto_rules apto_table" data-id="<?php echo $options['group_id'] ?>">
                            <tbody>
                                <tr<?php
                        
                                    if($options['html_alternate'] === TRUE)
                                        echo ' class="alternate"';
                                    
                                ?>>
                                    <td class="param">
                                        <select onChange="APTO.change_taxonomy_item(<?php echo $options['group_id'] ?>)" name="rules[taxonomy][<?php echo $options['group_id'] ?>][taxonomy]" class="select taxonomy_item">
                                            <?php
                                            
                                                $taxonomies =   get_taxonomies(array(), 'objects'); 
                                                foreach ($taxonomies as $taxonomy ) 
                                                    {
                                                        ?><option <?php if($taxonomy->name == $options['taxonomy']) { echo 'selected="selected"'; }?> value="<?php echo $taxonomy->name; ?>"><?php echo $taxonomy->label; ?> <small>(<?php echo $taxonomy->name; ?>)</small></option><?php
                                                    }
                                            
                                            ?>
                                        </select>
                                        <h5>Taxonomy Operator</h5>
                                        <select name="rules[taxonomy][<?php echo $options['group_id'] ?>][operator]" class="select">
                                            <?php
                                            
                                                $operator_values = array(
                                                                            'IN',
                                                                            'NOT IN',
                                                                            'AND'
                                                                            );
                                                foreach($operator_values as $operator_value)
                                                    {
                                                        ?><option <?php if($operator_value == $options['operator']) { echo 'selected="selected"'; }?>    value="<?php echo $operator_value ?>"><?php echo $operator_value ?></option><?php
                                                    }
                                            ?>
                                        </select>
                                        <h5>Include Children</h5>
                                        <select name="rules[taxonomy][<?php echo $options['group_id'] ?>][include_children]" class="select">
                                            <?php
                                            
                                                $include_children_values = array(
                                                                            'TRUE',
                                                                            'FALSE'
                                                                            );
                                                foreach($include_children_values as $include_children_value)
                                                    {
                                                        ?><option <?php if($include_children_value == $options['include_children']) { echo 'selected="selected"'; }?>    value="<?php echo $include_children_value ?>"><?php echo $include_children_value ?></option><?php
                                                    }
                                            ?>
                                        </select>
                                    </td>
                                                            
                                    <td class="value">
                                        <?php
                                        
                                            $taxonomy_name  =   '';
                                            if($options['taxonomy'] == '')
                                                {
                                                    //get first taxonomy
                                                    reset($taxonomies);
                                                    $first_taxonomy = current($taxonomies);
                                                    
                                                    $taxonomy_name  =   $first_taxonomy->name;
                                                }
                                                else
                                                {
                                                    $taxonomy_name  =   $options['taxonomy'];  
                                                }
                                        
                                            $ti_options = array(
                                                                    'group_id'      =>  $options['group_id'],
                                                                    'taxonomy'      =>  $taxonomy_name,
                                                                    'selected'      =>  $options['selected']
                                                                    );
                                            echo $this->change_taxonomy_item_html($ti_options);                                            
                                        
                                        ?>
                                        
                                    </td>
                                    <td class="buttons">
                                        <a href="javascript: void(0);" onClick="APTO.remove_taxonomy_item(this, 'table')" class="remove item"></a>                                                                 
                                    </td>
                                </tr>
                                </tbody>
                         </table>
                    <?php
                    
                    $html_data  =   ob_get_contents();
                    ob_end_clean();
                    
                    return $html_data;   
                }
            
                
            function change_taxonomy_item()
                {
                    $options    = array(
                                            'group_id'  =>  intval($_POST['group_id']),
                                            'taxonomy'  =>  preg_replace( '/[^a-zA-Z0-9_\-]/', '', $_POST['taxonomy'])
                                        );
                       
                    $html_data  =   $this->change_taxonomy_item_html($options);
                                                    
                    $this->response['html']             =   $html_data;
                    $this->response['message']          =   '';
                    $this->response['group_id']         =   $options['group_id'];
                    $this->response['response_code']    =   '0';
  
                    $this->output_response();
                    die(); 
                } 
                
            function change_taxonomy_item_html($options)
                {
                    $defaults = array (
                                            'group_id'      =>  1,
                                            'taxonomy'      =>  '',
                                            'selected'      =>  array()
                                        );
                    
                    // Parse incoming $args into an array and merge it with $defaults
                    $options = wp_parse_args( $options, $defaults );
                    
                    ob_start();
                    
                    $args   =   array(
                                        'orderby'               =>  'name', 
                                        'hide_empty'            =>  FALSE,
                                        'hierarchical'          =>  TRUE,
                                        'walker'                =>  new APTO_Walker_TermsDropdownCategories(),
                                        'taxonomy'              =>  $options['taxonomy'],
                                        'echo'                  =>  FALSE,
                                        'name'                  =>  'rules[taxonomy]['. $options['group_id'] .'][terms][]',
                                        'class'                 =>  'select multiple',
                                        'selected'              =>  $options['selected']
                                        );
                    $select_html        =   wp_dropdown_categories($args);
                    $select_html        = str_replace("<select ", '<select multiple="multiple"', $select_html);
                    echo ($select_html);
                      
                    $html_data  =   ob_get_contents();
                    ob_end_clean();
                    
                    return $html_data; 
                }
            
            
            
            
            function get_rule_meta_html_box($options = array())
                {
                    $defaults   = array (
                                            'group_id'      =>  1,
                                            'key'      =>  '',
                                            'value_type'    =>  'string',
                                            'value'    =>  '',
                                            'compare'       =>  '=',
                                            'type'          =>  'CHAR',

                                            'html_alternate'   =>  FALSE
                                        );
                    
                    // Parse incoming $args into an array and merge it with $defaults
                    $options = wp_parse_args( $options, $defaults );
                    
                    
                    ob_start();
                    
                    ?>
                        <table class="apto_input widefat apto_rules apto_table" data-id="<?php echo $options['group_id'] ?>">
                            <tbody>
                                <tr<?php
                        
                                    if($options['html_alternate'] === TRUE)
                                        echo ' class="alternate"';
                                    
                                ?>>
                                    <td class="param">
                                        <h5>Meta Key</h5>
                                        <input type="text" name="rules[meta][<?php echo $options['group_id'] ?>][key]" class="text" value="<?php  echo $options['key'] ?>">
                                        
                                        <h5>Meta Value Type</h5>
                                        <select onChange="APTO.change_meta_value_type_item(jQuery(this), <?php echo $options['group_id'] ?>)" name="rules[meta][<?php echo $options['group_id'] ?>][value_type]" class="select">
                                            <?php
                                            
                                                $type_values = array(
                                                                            'string', 'array'
                                                                            );
                                                foreach($type_values as $type_value)
                                                    {
                                                        ?><option <?php if($type_value == $options['value_type']) { echo 'selected="selected"'; }?>    value="<?php echo $type_value ?>"><?php echo ucfirst($type_value) ?></option><?php
                                                    }
                                            ?>
                                        </select>
                                        
                                        <div class="meta_value" data-type="string"<?php if($options['value_type']   ==  'array') {echo ' style="display: none"';}  ?>>
                                            <h5>Meta Value</h5>    
                                        </div>
                                        
                                        <div class="meta_value" data-type="array"<?php if($options['value_type']   ==  'string') {echo ' style="display: none"';}  ?>>
                                            <h5>Meta Values (comma separated)</h5>
                                        </div>
                                        
                                        <input type="text" name="rules[meta][<?php echo $options['group_id'] ?>][value]" class="text" value="<?php  echo htmlspecialchars($options['value']) ?>">
                                        
                                    </td>
                                    <td class="value">
                                        <h5>Compare</h5>
                                        <select name="rules[meta][<?php echo $options['group_id'] ?>][compare]" class="select">
                                            <?php
                                            
                                                $compare_values = array(
                                                                            '=', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN', 'EXISTS','NOT EXISTS'
                                                                            );
                                                foreach($compare_values as $compare_value)
                                                    {
                                                        ?><option <?php if($compare_value == $options['compare']) { echo 'selected="selected"'; }?>    value="<?php echo $compare_value ?>"><?php echo $compare_value ?></option><?php
                                                    }
                                            ?>
                                        </select>
                                        
                                        <h5>Compare Type</h5>
                                        <select name="rules[meta][<?php echo $options['group_id'] ?>][type]" class="select">
                                            <?php
                                            
                                                $type_values = array(
                                                                            'NUMERIC', 'BINARY', 'CHAR', 'DATE', 'DATETIME', 'DECIMAL', 'SIGNED', 'TIME', 'UNSIGNED'
                                                                            );
                                                foreach($type_values as $type_value)
                                                    {
                                                        ?><option <?php if($type_value == $options['type']) { echo 'selected="selected"'; }?>    value="<?php echo $type_value ?>"><?php echo $type_value ?></option><?php
                                                    }
                                            ?>
                                        </select>                                        
                                    </td>
                                    <td class="buttons">
                                        <a href="javascript: void(0);" onClick="APTO.remove_taxonomy_item(this, 'table')" class="remove item"></a>                                                                 
                                    </td>
                                </tr>
                                </tbody>
                         </table>
                    <?php
                    
                    $html_data  =   ob_get_contents();
                    ob_end_clean();
                    
                    return $html_data;   
                }
            
            
            
            function get_rule_author_html_box($options = array())
                {
                    $defaults = array (
                                            'selected'    =>  ''
                                        );
                    
                    // Parse incoming $args into an array and merge it with $defaults
                    $options = wp_parse_args( $options, $defaults );
                    
                    
                    ob_start();
                    
                    ?>
                        <tr>
                            <td class="param">
                                <select class="select" name="rules[author][]">
                                    <?php
                                        
                                        $blogusers = get_users();
                                        foreach ($blogusers as $user) 
                                            {
                                                ?><option <?php if($options['selected'] == $user->ID) { echo 'selected="selected"'; }?> value="<?php echo $user->ID ?>"><?php echo $user->data->display_name ?></option><?php
                                            }
                                    ?>
                                </select>
                            </td>
                            <td class="buttons">
                                <a href="javascript: void(0);" onClick="APTO.remove_rule_item(this, 'tr')" class="remove item"></a>
                            </td>
                        </tr>
                    <?php
                    
                    $html_data  =   ob_get_contents();
                    ob_end_clean();
                    
                    return $html_data;   
                }
            
            
            function get_conditional_group()
                {
                    $options    = array(
                                            'group_id'  =>  intval($_POST['group_id'])
                                        );
                       
                    $html_data  =   $this->get_html_conditional_group($options);
                                                    
                    $this->response['html']             =   $html_data;
                    $this->response['message']          =   '';
                    $this->response['response_code']    =   '0';
  
                    $this->output_response();
                    die(); 
                    
                }
            
            function get_html_conditional_group($options = array())
                {
                    $defaults   = array (
                                            'group_id'  =>  1,
                                            'data'      =>  array()
                                        );
                    
                    // Parse incoming $args into an array and merge it with $defaults
                    $options = wp_parse_args( $options, $defaults );
                    
                    
                    ob_start();
                    
                    ?>
                        <div data-id="<?php echo $options['group_id'] ?>" class="conditional_rules" id="conditional_rules_<?php echo $options['group_id'] ?>">
                            <h4>Or</h4>
                            <table class="apto_input widefat apto_rules apto_table">
                                <tbody>
                                    
                                    <?php 
                                    
                                        if(count($options['data']) > 0)
                                            {
                                                $row_id = 1;
                                                foreach($options['data'] as $key => $data)
                                                    {
                                                        $rule_options    = array(
                                                                                'group_id'          =>  $options['group_id'],
                                                                                'row_id'            =>  $row_id,
                                                                                'selected'          =>  $data['conditional_id'],
                                                                                'comparison'        =>  $this->conditional_rules->get_rule_comparison($data['conditional_id']),
                                                                                'comparison_value'  =>  $data['conditional_comparison'],
                                                                                'selected_value'    =>  isset($data['conditional_value']) ? $data['conditional_value'] : ''
                                                                            );
                                                        echo $this->get_html_conditional_rule($rule_options);   
                                                        
                                                        $row_id++;
                                                    }
                                            }
                                            else
                                            echo $this->get_html_conditional_rule($options);
                                    
                                    ?>
                                                                      
                                </tbody>
                             </table>
                             
                             
                             <table class="apto_input widefat apto_more">
                                <tbody>
                                    <tr><td><a class="button-secondary" href="javascript: void(0)" onClick="APTO.add_conditional_rule(this)">Add </a> &nbsp;&nbsp;<img class="ajax_loading" src="<?php echo APTO_URL ?>/images/ajax-loader.gif" alt="Loading" /></td></tr>
                                </tbody>
                            </table>
                        </div>
                    <?php
                    
                    $html_data  =   ob_get_contents();
                    ob_end_clean();
                    
                    return $html_data;   
                }
                
                
            function get_conditional_rule()
                {
                    $options    = array(
                                            'group_id'          =>  intval($_POST['group_id']),
                                            'selected'          =>  isset($_POST['selected']) ? $_POST['selected'] : '',
                                            'row_id'            =>  isset($_POST['row_id']) ? intval($_POST['row_id']) : 1,
                                            'comparison_value'  =>  isset($_POST['comparison_value']) ? $_POST['comparison_value'] : '', 
                                        );
                                        
                    if($options['selected'] !=  '')
                        {
                            $options['comparison']  =   $this->conditional_rules->get_rule_comparison($options['selected']);
                        }
                       
                    $html_data  =   $this->get_html_conditional_rule($options);
                                                    
                    $this->response['html']             =   $html_data;
                    $this->response['group_id']         =   $options['group_id'];
                    $this->response['row_id']           =   $options['row_id'];
                    
                    $this->response['message']          =   '';
                    $this->response['response_code']    =   '0';
  
                    $this->output_response();
                    die(); 
                    
                }
            
            function get_html_conditional_rule($options = array())
                {
                    $defaults   = array (
                                            'selected'          =>  '',
                                            'comparison'        =>  array(),
                                            'comparison_value'  =>  '', 
                                            'selected_value'    =>  '',
                                            'group_id'          =>  1,
                                            'row_id'            =>  1 
                                        );
                    
                    // Parse incoming $args into an array and merge it with $defaults
                    $options = wp_parse_args( $options, $defaults );
                    
                    
                    ob_start();
                    
                    ?>
                        <tr data-id="<?php echo $options['row_id'] ?>">
                            <td class="param">
                                <select name="conditional_rules[<?php echo $options['group_id'] ?>][<?php echo $options['row_id'] ?>][conditional_id]" class="select" onchange="APTO.conditional_item_change(this)">
                                    <option <?php if($options['selected'] == '') { echo 'selected="selected"'; }?> value="">&nbsp;</option>
                                    <?php
                                    
                                        foreach($this->conditional_rules->rules as $rule_id => $rule_data)
                                            {
                                                ?><option <?php if($options['selected'] == $rule_id) { echo 'selected="selected"'; }?> value="<?php echo $rule_id ?>"><?php echo $rule_data['title'] ?></option><?php                  
                                            }
                                    
                                    ?>

                                </select>
                            </td>
                            
                            <?php if(count($options['comparison']) > 0) { ?>
                            <td class="comparison">
                                <select name="conditional_rules[<?php echo $options['group_id'] ?>][<?php echo $options['row_id'] ?>][conditional_comparison]" class="select" onchange="APTO.conditional_item_comparison_change(this)">
                                    <?php
                                    
                                        foreach($options['comparison'] as $key  =>  $comparison_data)
                                            {
                                                $comparison =   is_array($comparison_data)  ?   $key    :   $comparison_data;
                                                
                                                ?>
                                                    <option <?php if($options['comparison_value'] == $comparison || ($options['comparison_value'] == '' && $key === 0)) { echo 'selected="selected"'; }?> value="<?php echo $comparison ?>"><?php echo $comparison ?></option>            
                                                <?php   
                                            }
                                        
                                    
                                    ?>
                                </select>
                            </td>
                            <?php } ?>
                            
                            <?php
                            
                                if ($options['selected'] != '')
                                    {
                                        $rule_html_output = call_user_func_array($this->conditional_rules->rules[$options['selected']]['admin_html'], array($options)) ;
                                    }
                                    
                                if($options['selected'] == '' || $rule_html_output == '') 
                                    {
                                        ?>
                                            <td colspan="2" class="buttons">
                                                <a href="javascript: void(0);" onClick="APTO.remove_conditional_item(this, 'tr')" class="remove item"></a>
                                            </td>
                                        <?php   
                                    }
                                    else
                                    {
                                        ?>
                                            <td class="value">
                                                <?php echo $rule_html_output ?>
                                            </td>
                                            <td class="buttons">
                                                <a href="javascript: void(0);" onClick="APTO.remove_conditional_item(this, 'tr')" class="remove item"></a>
                                            </td>
                                        <?php
                                    }
                            
                            ?>
                        </tr>
                    <?php
                    
                    $html_data  =   ob_get_contents();
                    ob_end_clean();
                    
                    return $html_data;   
                }

                
            function output_response()
                {
                    echo json_encode($this->response);
                }
                
                
            function automatic_add_falback_order($options = array())
                {
                    $defaults = array (
                                             'default'              =>  FALSE,
                                             'group_id'             =>  isset($_POST['group_id']) ? intval($_POST['group_id']) : 1,
                                             'data_set'             =>  array()
                                        );
                    
                    // Parse incoming $args into an array and merge it with $defaults
                    $options = wp_parse_args( $options, $defaults );   
                    
                    $html_data  =    $this->html_automatic_add_falback_order($options);
                    
                    $this->response['html']             =   $html_data;
                    $this->response['group_id']         =   $options['group_id'];
     
                    $this->output_response();
                    die();   
                }
                
                
            function html_automatic_add_falback_order($options = array())
                {
                    $defaults = array (
                                             'default'              =>  FALSE,
                                             'group_id'             =>  1,
                                             'data_set'             =>  array()
                                        );
                    
                    // Parse incoming $args into an array and merge it with $defaults
                    $options = wp_parse_args( $options, $defaults );     
                    
                    if(count($options['data_set']) < 1)
                        {
                            $options['data_set']    =   array(
                                                                    'order_by'              =>  '_default_',
                                                                    'custom_field_name'     =>  '',
                                                                    'custom_field_type'     =>  '',
                                                                    'custom_function_name'  =>  '',
                                                                    'order'                 =>  'DESC'
                                                                );
                        }
                    
                    ob_start();  
                    
                    ?>
                        <tr class="automatic_order_by" data-id="<?php echo $options['group_id'] ?>">
                            <td class="label">
                                <label for=""><?php _e( "Order By", 'apto' ) ?></label>
                                <p class="description"><?php _e( "More details about Order By paramether can be found at", 'apto' ) ?> <a target="_blank" href="http://codex.wordpress.org/Class_Reference/WP_Query#Order_.26_Orderby_Parameters"><?php _e( "Order & Orderby Parameters", 'apto' ) ?></a></p>
                                <p class="description"><br /><?php _e( "Additional details and example of usage for Custom Function can be found at", 'apto' ) ?> <a target="_blank" href="http://www.nsp-code.com/applying-automatic-order-for-a-sort-through-a-user-function-callback/"><?php _e( "Custom Function", 'apto' ) ?></a></p>
                            </td>
                            <td>
                                <input type="radio" id="auto_order_by_default_<?php echo $options['group_id'] ?>" <?php if ($options['data_set']['order_by'] == '_default_') {echo 'checked="checked"'; } ?> onchange="APTO.apto_autosort_orderby_field_change(this)" value="_default_" name="auto_order_by[<?php echo $options['group_id'] ?>]" />
                                <label for="auto_order_by_default_<?php echo $options['group_id'] ?>"><?php _e( "Default / None", 'apto' ) ?></label><br>
                                
                                <input type="radio" id="auto_order_by_id_<?php echo $options['group_id'] ?>" <?php if ($options['data_set']['order_by'] == 'ID') {echo 'checked="checked"'; } ?> onchange="APTO.apto_autosort_orderby_field_change(this)" value="ID" name="auto_order_by[<?php echo $options['group_id'] ?>]" />
                                <label for="auto_order_by_id_<?php echo $options['group_id'] ?>"><?php _e( "Post ID", 'apto' ) ?></label><br>
                                
                                <input type="radio" id="auto_order_by_post_author_<?php echo $options['group_id'] ?>" <?php if ($options['data_set']['order_by'] == 'post_author') {echo 'checked="checked"'; } ?> onchange="APTO.apto_autosort_orderby_field_change(this)" value="post_author" name="auto_order_by[<?php echo $options['group_id'] ?>]" />
                                <label for="auto_order_by_post_author_<?php echo $options['group_id'] ?>"><?php _e( "Author", 'apto' ) ?></label><br>
                                
                                <input type="radio" id="auto_order_by_post_title_<?php echo $options['group_id'] ?>" <?php if ($options['data_set']['order_by'] == 'post_title') {echo 'checked="checked"'; } ?> onchange="APTO.apto_autosort_orderby_field_change(this)" value="post_title" name="auto_order_by[<?php echo $options['group_id'] ?>]" />
                                <label for="auto_order_by_post_title_<?php echo $options['group_id'] ?>"><?php _e( "Title", 'apto' ) ?></label><br>
                                  
                                <input type="radio" id="auto_order_by_post_name_<?php echo $options['group_id'] ?>" <?php if ($options['data_set']['order_by'] == 'post_name') {echo 'checked="checked"'; } ?> onchange="APTO.apto_autosort_orderby_field_change(this)" value="post_name" name="auto_order_by[<?php echo $options['group_id'] ?>]" />
                                <label for="auto_order_by_post_name_<?php echo $options['group_id'] ?>"><?php _e( "Slug", 'apto' ) ?></label><br>
                                
                                <input type="radio" id="auto_order_by_post_date_<?php echo $options['group_id'] ?>" <?php if ($options['data_set']['order_by'] == 'post_date') {echo 'checked="checked"'; } ?> onchange="APTO.apto_autosort_orderby_field_change(this)" value="post_date" name="auto_order_by[<?php echo $options['group_id'] ?>]" />
                                <label for="auto_order_by_post_date_<?php echo $options['group_id'] ?>"><?php _e( "Date", 'apto' ) ?></label><br>
                                
                                <input type="radio" id="auto_order_by_post_modified_<?php echo $options['group_id'] ?>" <?php if ($options['data_set']['order_by'] == 'post_modified') {echo 'checked="checked"'; } ?> onchange="APTO.apto_autosort_orderby_field_change(this)" value="post_modified" name="auto_order_by[<?php echo $options['group_id'] ?>]" />
                                <label for="auto_order_by_post_modified_<?php echo $options['group_id'] ?>"><?php _e( "Modified", 'apto' ) ?></label><br>
                                
                                <input type="radio" id="auto_order_by_comment_count_<?php echo $options['group_id'] ?>" <?php if ($options['data_set']['order_by'] == 'comment_count') {echo 'checked="checked"'; } ?> onchange="APTO.apto_autosort_orderby_field_change(this)" value="comment_count" name="auto_order_by[<?php echo $options['group_id'] ?>]" />
                                <label for="auto_order_by_comment_count_<?php echo $options['group_id'] ?>"><?php _e( "Comments Count", 'apto' ) ?></label><br>
                                
                                <input type="radio" id="auto_order_by_random_<?php echo $options['group_id'] ?>" <?php if ($options['data_set']['order_by'] == '_random_') {echo 'checked="checked"'; } ?> onchange="APTO.apto_autosort_orderby_field_change(this)" value="_random_" name="auto_order_by[<?php echo $options['group_id'] ?>]" />
                                <label for="auto_order_by_random_<?php echo $options['group_id'] ?>"><?php _e( "Random", 'apto' ) ?></label><br><br>
                                
                                <input type="radio" id="auto_order_by_custom_field_<?php echo $options['group_id'] ?>" <?php if ($options['data_set']['order_by'] == '_custom_field_') {echo 'checked="checked"'; } ?> onchange="APTO.apto_autosort_orderby_field_change(this)" value="_custom_field_" name="auto_order_by[<?php echo $options['group_id'] ?>]" />
                                <label for="auto_order_by_custom_field_<?php echo $options['group_id'] ?>"><?php _e( "Custom Field", 'apto' ) ?></label><br>
                                <div id="apto_custom_field_area_<?php echo $options['group_id'] ?>" <?php
                                    if ($options['data_set']['order_by'] != '_custom_field_')
                                        echo 'style="display: none"';
                                ?> class="toggle_area">
                                    <table class="apto_input inner_table widefat">
                                        <tbody>
                                            <tr class="alt"><td>
                                                <h4><?php _e( "Field Name", 'apto' ) ?></h4>
                                                <p class="description"><?php _e( "The name of custom field", 'apto' ) ?></p>
                                                <input id="auto_custom_field_name_<?php echo $options['group_id'] ?>" type="text" class="regular-text custom-field-text" value="<?php echo $options['data_set']['custom_field_name'] ?>" name="auto_custom_field_name[<?php echo $options['group_id'] ?>]">
                                            </td></tr>
                                            
                                            <tr class="alt"><td>
                                                <h4><?php _e( "Field Type", 'apto' ) ?></h4>
                                                <p class="description"><?php _e( "MySql Type of field, more details at", 'apto' ) ?> <a href="http://dev.mysql.com/doc/refman/5.0/en/cast-functions.html" target="_blank"><?php _e( "Cast Functions and Operators", 'apto' ) ?></a></p>
                                                
                                                <input type="radio" id="custom_field_type_none_<?php echo $options['group_id'] ?>" <?php if ($options['data_set']['custom_field_type'] == 'none' || $options['data_set']['custom_field_type'] == '') {echo 'checked="checked"'; } ?> value="none" name="auto_custom_field_type[<?php echo $options['group_id'] ?>]" />
                                                <label for="custom_field_type_none_<?php echo $options['group_id'] ?>"><?php _e( "None / Default", 'apto' ) ?></label><br>
                                                
                                                <input type="radio" id="custom_field_type_signed_<?php echo $options['group_id'] ?>" <?php if ($options['data_set']['custom_field_type'] == 'SIGNED') {echo 'checked="checked"'; } ?> value="SIGNED" name="auto_custom_field_type[<?php echo $options['group_id'] ?>]" />
                                                <label for="custom_field_type_signed_<?php echo $options['group_id'] ?>"><?php _e( "Signed (Integer)", 'apto' ) ?></label><br>
                                                
                                                <input type="radio" id="custom_field_type_signed_<?php echo $options['group_id'] ?>" <?php if ($options['data_set']['custom_field_type'] == 'UNSIGNED') {echo 'checked="checked"'; } ?> value="UNSIGNED" name="auto_custom_field_type[<?php echo $options['group_id'] ?>]" />
                                                <label for="custom_field_type_signed_<?php echo $options['group_id'] ?>"><?php _e( "Unsigned (Integer)", 'apto' ) ?></label><br>
                                                
                                                <input type="radio" id="custom_field_type_signed_<?php echo $options['group_id'] ?>" <?php if ($options['data_set']['custom_field_type'] == 'float') {echo 'checked="checked"'; } ?> value="float" name="auto_custom_field_type[<?php echo $options['group_id'] ?>]" />
                                                <label for="custom_field_type_signed_<?php echo $options['group_id'] ?>"><?php _e( "Float (Decimal)", 'apto' ) ?></label><br>
                                                
                                                <input type="radio" id="custom_field_type_date_<?php echo $options['group_id'] ?>" <?php if ($options['data_set']['custom_field_type'] == 'DATE') {echo 'checked="checked"'; } ?> value="DATE" name="auto_custom_field_type[<?php echo $options['group_id'] ?>]" />
                                                <label for="custom_field_type_date_<?php echo $options['group_id'] ?>"><?php _e( "Date", 'apto' ) ?></label><br>
                                                
                                                <input type="radio" id="custom_field_type_datetime_<?php echo $options['group_id'] ?>" <?php if ($options['data_set']['custom_field_type'] == 'DATETIME') {echo 'checked="checked"'; } ?> value="DATETIME" name="auto_custom_field_type[<?php echo $options['group_id'] ?>]" />
                                                <label for="custom_field_type_datetime_<?php echo $options['group_id'] ?>"><?php _e( "Datetime", 'apto' ) ?></label><br>
                                                
                                                <input type="radio" id="custom_field_type_time_<?php echo $options['group_id'] ?>" <?php if ($options['data_set']['custom_field_type'] == 'TIME') {echo 'checked="checked"'; } ?> value="TIME" name="auto_custom_field_type[<?php echo $options['group_id'] ?>]" />
                                                <label for="custom_field_type_time_<?php echo $options['group_id'] ?>"><?php _e( "Time", 'apto' ) ?></label><br>
                                                
                                            </td></tr>
                                    
                                        </tbody>
                                    </table>
                                </div>
                                
                                <input type="radio" id="auto_order_by_custom_function_<?php echo $options['group_id'] ?>" <?php if ($options['data_set']['order_by'] == '_custom_function_') {echo 'checked="checked"'; } ?> onchange="APTO.apto_autosort_orderby_field_change(this)" value="_custom_function_" name="auto_order_by[<?php echo $options['group_id'] ?>]" />
                                <label for="auto_order_by_custom_function_<?php echo $options['group_id'] ?>"><?php _e( "Custom Function", 'apto' ) ?></label><br><br>
                                <div id="apto_custom_function_area_<?php echo $options['group_id'] ?>" <?php
                                    if ($options['data_set']['order_by'] != '_custom_function_')
                                        echo 'style="display: none"';
                                ?> class="toggle_area">
                                    <input id="auto_custom_function_name_<?php echo $options['group_id'] ?>" type="text" class="regular-text custom-field-text" value="<?php echo $options['data_set']['custom_function_name'] ?>" name="auto_custom_function_name[<?php echo $options['group_id'] ?>]">
                                </div>    

                            </td>
                            
                            <td class="buttons">
                                <?php if($options['default'] !== TRUE) { ?><a href="javascript: void(0);" onClick="APTO.RemoveAutomaticOrderFallback(this)" class="remove item"></a><?php } ?>
                            </td>
                        </tr>
                    
                        <tr class="automatic_order" data-id="<?php echo $options['group_id'] ?>">
                            <td class="label">
                                <label for=""><?php _e( "Order", 'apto' ) ?></label>
                                <p class="description"><?php _e( "More details about Order paramether can be found at", 'apto' ) ?> <a target="_blank" href="http://codex.wordpress.org/Class_Reference/WP_Query#Order_.26_Orderby_Parameters"><?php _e( "Order & Orderby Parameters", 'apto' ) ?></a></p>
                            </td>
                            <td>
                                <input type="radio" id="auto_order_desc_<?php echo $options['group_id'] ?>" <?php if ($options['data_set']['order'] == 'DESC') {echo 'checked="checked"'; } ?> value="DESC" name="auto_order[<?php echo $options['group_id'] ?>]" />
                                <label for="auto_order_desc_<?php echo $options['group_id'] ?>"><?php _e( "Descending", 'apto' ) ?></label><br>

                                <input type="radio" id="auto_order_asc_<?php echo $options['group_id'] ?>" <?php if ($options['data_set']['order'] == 'ASC') {echo 'checked="checked"'; } ?> value="ASC" name="auto_order[<?php echo $options['group_id'] ?>]" />
                                <label for="auto_order_asc_<?php echo $options['group_id'] ?>"><?php _e( "Ascending", 'apto' ) ?></label><br>  

                            </td>
                            
                            <td class="buttons">
                                <?php if($options['default'] !== TRUE) { ?><a href="javascript: void(0);" onClick="APTO.RemoveAutomaticOrderFallback(this)" class="remove item"></a><?php } ?>
                            </td>
                        </tr>

                    <?php
                    
                    $html_data  =   ob_get_contents();
                    ob_end_clean();
                    
                    return $html_data; 
                }    
                
            
            function pagination_navi_html($current, $total_pages, $total_items)
                {
                             
                    $output = '<span class="displaying-num">' . sprintf( _n( '1 item', '%s items', $total_items ), number_format_i18n( $total_items ) ) . '</span>';
                    $current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
                    $current_url = remove_query_arg( array( 'hotkeys_highlight_last', 'hotkeys_highlight_first' ), $current_url );

                    $page_links = array();
                    $disable_first = $disable_last = '';
                    if ( $current == 1 ) 
                        {
                            $disable_first = ' disabled';
                        }
                    if ( $current == $total_pages ) 
                        {
                            $disable_last = ' disabled';
                        }
                    $page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
                        'first-page' . $disable_first,
                        esc_attr__( 'Go to the first page' ),
                        esc_url( remove_query_arg( 'list_paged', $current_url ) ),
                        '&laquo;'
                    );

                    $page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
                        'prev-page' . $disable_first,
                        esc_attr__( 'Go to the previous page' ),
                        esc_url( add_query_arg( 'list_paged', max( 1, $current-1 ), $current_url ) ),
                        '&lsaquo;'
                    );

                    $html_current_page = $current;
                    
                    $html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
                    $page_links[] = '<span class="paging-input">' . sprintf( _x( '%1$s of %2$s', 'paging' ), $html_current_page, $html_total_pages ) . '</span>';

                    $page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
                        'next-page' . $disable_last,
                        esc_attr__( 'Go to the next page' ),
                        esc_url( add_query_arg( 'list_paged', min( $total_pages, $current+1 ), $current_url ) ),
                        '&rsaquo;'
                    );

                    $page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
                        'last-page' . $disable_last,
                        esc_attr__( 'Go to the last page' ),
                        esc_url( add_query_arg( 'list_paged', $total_pages, $current_url ) ),
                        '&raquo;'
                    );

                    $pagination_links_class = 'pagination-links';
              
                    $output .= "\n<span class='$pagination_links_class'>" . join( "\n", $page_links ) . '</span>';

                    if ( $total_pages ) {
                        $page_class = $total_pages < 2 ? ' one-page' : '';
                    } else {
                        $page_class = ' no-pages';
                    }
                    $pagination = "<div class='tablenav'><div class='tablenav-pages{$page_class}'>$output</div></div>";

                    echo $pagination;     
                    
                }
            
            function pagination_sortable_html($pagination_args, $additional_query_string)
                {
                    $defaults = array(
                                    'type'              =>  'top',
                                    'paged'             =>  1,
                                    'offset_objects'    =>  5
                                );

                    $pagination_args = wp_parse_args( $pagination_args, $defaults );
                                        
                    $query_args =   $additional_query_string; 
                    unset($query_args['posts_per_page']);
                    unset($query_args['paged']);
                    
                    $args   =   $this->get_interface_query_arguments($pagination_args['sort_view_ID'], $query_args);
                    $args   =   apply_filters('apto/interface_query_args', $args, $pagination_args['sort_view_ID']);
                                                    
                    $custom_query = new WP_Query($args);
                    $found_posts = $custom_query->posts;        
                    
                    if(count($found_posts) < 1)
                        return FALSE; 
                    
                    $current_page   =   $pagination_args['paged'];
                    $total_items    =   count($found_posts);
                    $total_pages    =   ceil($total_items / $pagination_args['posts_per_page']);
                    
                    if($total_pages < 2)
                        return FALSE;
                    
                    if($pagination_args['type'] ==  'top'   &&  $pagination_args['paged']   <   2)
                        {
                            $this->pagination_navi_html($current_page, $total_pages, $total_items);
                            return FALSE;
                        }

                    if($pagination_args['type'] ==  'bottom')
                        $this->pagination_navi_html($current_page, $total_pages, $total_items);
                    
                    if($pagination_args['type'] ==  'top')
                            {
                                $offset_start   =   ($current_page * $pagination_args['posts_per_page']) - $pagination_args['posts_per_page'] - $pagination_args['offset_objects'];
                                if($offset_start    <   0)   
                                    $offset_start   =   0;
                                    
                                $offet_end  =   $pagination_args['offset_objects'];
                                if($offet_end   >=  $current_page * $pagination_args['posts_per_page'] - $pagination_args['posts_per_page'])   
                                    $offet_end  =   $current_page * $pagination_args['posts_per_page'] - $pagination_args['posts_per_page'];
                                    
                                if($offset_start >= 0)
                                    $offset_posts = array_slice($found_posts, $offset_start, $offet_end);
                                    else
                                    $offset_posts   =   array();
                            }
                        else
                            {
                                $offset_start   =   ($current_page * $pagination_args['posts_per_page']);
                                if($offset_start < count($found_posts))
                                    $offset_posts = array_slice($found_posts, $offset_start, $pagination_args['offset_objects']);
                                    else
                                    $offset_posts   =   array();   
                                
                            }
                    
                    ?>
                        <ul id="sortable_<?php echo $pagination_args['type'] ?>" class="view-list sortable-list <?php echo $pagination_args['type'] ?>">
                            <?php 
                                
                                $walker = new Post_Types_Order_Walker;

                                $walker_args = array($offset_posts, $args['depth'], $args);
                                echo call_user_func_array(array(&$walker, 'walk'), $walker_args);
                                
                            ?>
                        </ul>
                    <?php
                    
                    if($pagination_args['type'] ==  'top')
                        $this->pagination_navi_html($current_page, $total_pages, $total_items);
                }
            
                
            
            function get_interface_query_arguments($sort_view_id, $args =   array())
                {
                    $sort_view_settings =   $this->functions->get_sort_view_settings($sort_view_id);
                    
                    $sort_view_data     =   get_post($sort_view_id);
                    if($sort_view_data->post_parent > 0)
                        $sortID             =   $sort_view_data->post_parent;
                        else
                        $sortID             =   $sort_view_id;
                        
                    $sort_settings      =   $this->functions->get_sort_settings($sortID);
                    
                    $is_hierarhical     = $this->get_is_hierarhical_by_settings($sortID);
                    
                    
                    $defaults = array(
                                    'depth'             =>  0,
                                    'post_status'       =>  'any',
                                    'sort_id'           =>  $sortID,
                                    'sort_view_id'      =>  $sort_view_id,
                                    'posts_per_page'    =>  -1,
                                    'filter_date'       =>  0,
                                    'search'            =>  '',
                                    
                                    'language'          =>  ''
                                );
                                
                    $args = wp_parse_args( $args, $defaults );

                    if ($sort_settings['_view_type'] == 'multiple')
                        {
                            $args['post_type']         =  $sort_settings['_rules']['post_type'];
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
                            if(empty($args['language'])  &&  defined('ICL_LANGUAGE_CODE'))
                                $args['language']       =   $this->functions->get_sort_view_language($sort_view_id);
                            
                            $args['post_type']         =  $sort_settings['_rules']['post_type'];
                            $args['orderby']           = 'menu_order';
                            $args['order']             = 'ASC';      

                            $sort_rules = $this->functions->get_sort_language_rules($sort_settings, $args['language'], FALSE);
                            
                            //set author if need
                            if(isset($sort_rules['author']) && is_array($sort_rules['author']) && count($sort_rules['author']) > 0)
                                $args['author'] =   implode(",",    $sort_rules['author']);
                            
                            //set taxonomy if need (deppends on current view_selection
                            $taxonomy_data              =   $sort_rules['taxonomy'];
                            
                            //convert the include_children argument to Boolean
                            if ( is_array($taxonomy_data)   &&  count($taxonomy_data) > 0)
                                {
                                    foreach ( $taxonomy_data    as  $key    =>  $taxonomy_data_block)
                                        {
                                            $taxonomy_data[$key]['include_children']    =   $taxonomy_data[$key]['include_children']    == 'TRUE'   ?   TRUE    :   FALSE;
                                        }
                                }
                            
                            $taxonomy_data['relation']  =   $sort_rules['taxonomy_relation'];                          
                            $args['tax_query']          =   $taxonomy_data;
                            
                            //set the meta
                            $meta_data              =   array();
                            
                            //prepare the meta data
                            if(count($sort_rules['meta']) > 0 )
                            foreach($sort_rules['meta']  as  $key    =>  $meta_data_item)
                                {
                                    $meta_data_item =   $this->functions->meta_data_prepare_value($meta_data_item);
                                    
                                    $meta_data[]    =   $meta_data_item;
                                }
                            
                            $meta_data['relation']  =   $sort_rules['meta_relation'];                          
                            $args['meta_query']     =   $meta_data;
                        }
                    
                    
                    //limit the returnds only to IDS to prevent memory exhaust
                    if( $this->get_is_hierarhical_by_settings($sortID) === TRUE || ($this->functions->is_woocommerce($sortID) === TRUE && $sort_view_settings['_view_selection'] == 'archive'))
                        $args['fields'] = 'id=>parent';
                        else
                        $args['fields'] = 'ids';
                        
                    if ($this->functions->is_woocommerce($sortID) === TRUE && $sort_view_settings['_view_selection'] == 'archive')
                        {
                            $r['depth'] = 0;
                        }
                        else if($this->functions->is_woocommerce($sortID) === TRUE)
                        {
                            
                            //only for WooCommerce version < 3.0
                                global $woocommerce;
                                if( version_compare( $woocommerce->version, '3.0', "<" ) ) 
                                    {
                                        $args['meta_query'] = array(
                                                                        array(
                                                                                'key'       => '_visibility',
                                                                                'value'     => array('visible','catalog'),
                                                                                'compare'   => 'IN'
                                                                            )
                                                                    );   
                                    }
                        }
                        
                    //Interface date filter
                    if ($args['filter_date'] != '0' )
                        {
                            if ($args['filter_date'] == 'last_week')
                                {
                                    $last_week   = strtotime('-8 days');
                                    $next_day   = strtotime('+1 day');
          
                                    $args['date_query']     = array(
                                                                        array(
                                                                                'after'     => array(
                                                                                                        'year'  => date("Y", $last_week),
                                                                                                        'month' => date("m", $last_week),
                                                                                                        'day'   => date("d", $last_week),
                                                                                                    ),
                                                                                'before'    => array(
                                                                                                        'year'  => date("Y", $next_day),
                                                                                                        'month' => date("m", $next_day),
                                                                                                        'day'   => date("d", $next_day),
                                                                                                    ),
                                                                                'inclusive' => FALSE,
                                                                            )
                                                                        );
                                }
                                else if ($args['filter_date'] == 'today')
                                {
                                    $time = current_time('timestamp');
                                    $year               = date("Y", $time);
                                    $month              = date("m", $time);
                                    $day                = date("d", $time);
                                    
                                    $args['date_query']     = array(
                                                                        array(
                                                                                'year'  =>  $year,
                                                                                'month' =>  $month,
                                                                                'day'   =>  $day
                                                                            )
                                                                    ); 
                                }
                                else if ($args['filter_date'] == 'yesterday')
                                {
                                    $time = current_time('timestamp');
                                    $time = $time - 86400;
                                    $year               = date("Y", $time);
                                    $month              = date("m", $time);
                                    $day                = date("d", $time);
                                    
                                    $args['date_query']     = array(
                                                                        array(
                                                                                'year'  =>  $year,
                                                                                'month' =>  $month,
                                                                                'day'   =>  $day
                                                                            )
                                                                    );
                                }
                                else
                                {
                                    $year   = substr($args['filter_date'], 0, 4);
                                    $month  = substr($args['filter_date'], 4, 2);
                                    
                                    $args['date_query']     = array(
                                                                        array(
                                                                                'year'  => $year,
                                                                                'month' => $month
                                                                            )
                                                                    );
                                }
                        }    
                    
                    //Search filter
                    if ($args['search'] != '')
                        {
                            $args['s'] = $args['search'];
                        }
                    
                    
                    //status filtering
                    $statuses   =   array();
                    foreach($sort_settings['_status']   as  $status   => $data)
                        {
                            if($status  !=  'all'   &&  $data['status'] ==  'show')   
                                $statuses[] =   $status;
                        }
                    $args['post_status']    =   $statuses;
                    
                        
                    //check for bbPress filter
                    if (apto_is_plugin_active('bbpress/bbpress.php') !==    FALSE && isset($_GET['bbpress_forum']) && $_GET['bbpress_forum'] != '')
                        {
                            $args['post_parent']    =   $_GET['bbpress_forum'];
                        }
                
                    $exclude    =   apply_filters('apto_exclude_posts_from_interface', array(), $sort_view_id, $args);
                    if(is_array($exclude) && count($exclude) > 0)
                        $args['post__not_in']   =   $exclude;
                
                    return $args;
                }    

               
            function metabox_toggle()
                {
                    $sort_id    =   intval($_POST['sort_id']);
                    $status     =   preg_replace( '/[^a-zA-Z0-9_\-]/', '', $_POST['status']);
                    $type       =   preg_replace( '/[^a-zA-Z0-9_\-]/', '', $_POST['type']);
                       
                    $metabox_toggle = get_post_meta($sort_id, '_metabox_toggle', TRUE);
                    if(!is_array($metabox_toggle))
                        $metabox_toggle = array();
                        
                    $metabox_toggle[$type]  =  $status;
                    update_post_meta($sort_id, '_metabox_toggle', $metabox_toggle);  
                                                                        
                    $this->response['html']             =   '';
                    $this->response['message']          =   '';
                    $this->response['response_code']    =   '0';
  
                    $this->output_response();
                    die();    
                    
                }

                
            function get_sort_view_type_by_settings($sort_rules)
                {
                    $view_type = 'simple';
                                        
                    if(
                        (!isset($sort_rules['taxonomy']) || (is_array($sort_rules['taxonomy']) && count($sort_rules['taxonomy']) < 1))
                        && (!isset($sort_rules['meta']) || (is_array($sort_rules['meta']) && count($sort_rules['meta']) < 1))
                        )
                        $view_type = 'multiple';
                        
                    return $view_type;    
                }
            
            function get_sort_view_type($sortID)
                {
                    if($sortID == '' )
                        return '';
                    
                    $view_type = get_post_meta($sortID, '_view_type', TRUE);
                           
                    return $view_type;
                }
                
            function get_last_sort_view_ID($sortID)
                {
                    if($sortID == '' )
                        return '';
                    
                    $current_sort_view_ID = '';
                    
                    if(absint($sortID) < 1)
                        return $current_sort_view_ID;
                        
                    $current_sort_view_ID = get_post_meta($sortID, '_last_sort_view_ID', TRUE);
                    
                    /*
                    if($this->get_sort_view_type($sortID)   ==  "simple")
                        $current_sort_view__view_selection  =   'simple';
                        else
                        $current_sort_view__view_selection  =   'archive';
                    */
                    
                    $current_sort_view__view_language   =   $this->functions->get_blog_language();
                    
                    //check if for another language WPML
                    if($current_sort_view_ID != '' && $this->functions->get_sort_view_language($current_sort_view_ID) != $this->functions->get_blog_language())
                        {
                            //$current_sort_view__view_selection  =   get_post_meta($current_sort_view_ID, '_view_selection', TRUE);      
                            $current_sort_view_ID = '';
                        }
                    
                    if(absint($current_sort_view_ID) > 0)
                        return $current_sort_view_ID;
                    
                    global $post;
                    $_wp_query_post =   $post;
                    
                    $view_selection     =   'archive';
                    if($this->get_sort_view_type($sortID)   ==  'simple')
                        $view_selection =   'simple';
                    
                    //fetch the archive one in case there is no current view
                    $args = array(
                                    'posts_per_page'    => 1,
                                    'post_type'         =>  'apto_sort',
                                    'orderby'           =>  'ID',
                                    'order'             =>  'ASC',
                                    'post_parent'       =>  $sortID,
                                    'ignore_supress_filters'    =>  TRUE,
                                    'ignore_custom_sort'    =>  TRUE,
                                    'meta_query'        => array(
                                                                    'relation' => 'AND',
                                                                    array(
                                                                            'key'       => '_view_selection',
                                                                            'value'     => $view_selection,
                                                                            'compare'   => '='
                                                                        ),
                                                                    array(
                                                                            'key'       => '_view_language',
                                                                            'value'     => $current_sort_view__view_language,
                                                                            'compare'   => '='
                                                                        )
                                                                )
                                );
                    $list = new WP_Query( $args );
                    if($list->have_posts())
                        {
                            $list->the_post();
                               
                            $current_sort_view_ID = $post->ID;
                        }
                    
                    //wp_reset_postdata();
                    //use this instead as using a setup_postdata() without any query will reset to nothing
                    $post   =   $_wp_query_post;
                    
                    return $current_sort_view_ID;
                }
             
            
                
            function get_sort_taxonomies_by_objects($sortID)
                {

                    //$sort_rules     =   get_post_meta($sortID, '_rules', TRUE);
                    //use translated instead
                    $sort_rules         =   $this->functions->get_sort_current_language_rules(  $this->functions->get_sort_settings($sortID)   );
                    
                    $post_types =   $sort_rules['post_type'];
                    $taxonomies = array();
                    foreach($post_types as $post_type)  
                        {
                            $post_types_taxonomies  =   get_object_taxonomies( $post_type );
                            if(count($taxonomies)   < 1)
                                $taxonomies = $post_types_taxonomies;
                            $taxonomies =    array_intersect($taxonomies, $post_types_taxonomies);
                            
                            if(count($taxonomies) < 1 )
                                break;
                        }
                    
                    //filter the taxonomies and remove the ones whithout any term
                    foreach ($taxonomies as $key    =>  $taxonomy)
                        {
                            $count  =   wp_count_terms( $taxonomy, array('hide_empty' => FALSE) );
                            if($count   <   1)
                                unset($taxonomies[$key]);
                        }
                    
                    //re-index the array    
                    $taxonomies =   array_values($taxonomies);    
                    
                    $taxonomies = apply_filters('apto/admin/sort-taxonomies', $taxonomies, $sortID); 
                                        
                    return $taxonomies;
                    
                }
                
            function get_is_hierarhical_by_settings($sortID)
                {
                    $is_hierarhical     = FALSE;
                    
                    //$sort_rules         = get_post_meta($sortID, '_rules', TRUE);
                    //use translated instead
                    $sort_rules         =   $this->functions->get_sort_current_language_rules(  $this->functions->get_sort_settings($sortID)   );
                       
                    if(count($sort_rules['post_type']) > 1)
                        return FALSE;
                        
                    if(isset($sort_rules['taxonomy']) && is_array($sort_rules['taxonomy']) && count($sort_rules['taxonomy']) > 0)
                        return FALSE;
                    
                    reset($sort_rules['post_type']);
                    $post_type          =   current($sort_rules['post_type']);
                    
                    if($post_type   ==  'any')
                        return FALSE;
                    
                    if(!post_type_exists($post_type))
                        return 'INVALID POST TYPE';
                    
                    $post_type_data     =   get_post_type_object($post_type);
                        
                    return $post_type_data->hierarchical;    
                }
            
            
            /**
            * 
            * Show the sticky info when in re-order interface
            * 
            */
            function apto_showsticky_info($additiona_details, $post_data)
                {
                    $sticky_list = get_option('sticky_posts');
                    
                    if(!is_array($sticky_list) || count($sticky_list) < 0)
                        return $additiona_details;
                        
                    if(in_array($post_data->ID, $sticky_list))
                        $additiona_details .= ' <span class="item-status">Sticky</span>';
                    
                    return $additiona_details;   
                }
            
            
            /**
            * Save de tabs order
            * 
            */
            function saveAjaxTabsOrder()
                {
                    global $wpdb, $blog_id;
                    
                    //check for nonce
                    if(! wp_verify_nonce($_POST['nonce'],  'update-sorting-menu-tabs'))
                        {
                            _e( 'Invalid Nonce', 'apto' );
                            die();   
                        }
                        
                    $menu_location  = $_POST['menu_location'];
                    $order_list     = $_POST['order_list'];
                    $order_list     =   array_filter($order_list);
                    
                    if(empty($menu_location)    ||  !is_array($order_list)   ||  count($order_list) < 2)
                        die();
                        
                    update_option('apto-menu-tabs-order-' . $menu_location, $order_list);
                }
            
            
            function saveAjaxOrder() 
                {
                                        
                    set_time_limit(600);
                    
                    global $wpdb;
                                        
                    //check for nonce
                    if(! wp_verify_nonce($_POST['nonce'],  'reorder-interface-' . get_current_user_id()))
                        {
                            _e( 'Invalid Nonce', 'apto' );
                            die();   
                        }

                    $_JSON_response =   array();
                        
                    $data_parsed           = array(
                                                        'offset_top'        =>  array(),
                                                        'list'              =>  array(),
                                                        'offset_bottom'     =>  array(),
                                                        );
                    $_data_sticky_parsed    = array();
                    
                    foreach($data_parsed   as  $key    =>  $list_data)
                        {
                            //avoid using parse_Str due to the max_input_vars for large amount of data
                            if(!isset($_POST['order_' . $key]))
                                continue;
                                
                            $_data = explode("&", $_POST['order_' . $key]);   
                            $_data  =   array_filter($_data);
                            
                            foreach ($_data as $_data_item)
                                {
                                    list($data_key, $value) = explode("=", $_data_item);
                                    
                                    if(strpos($data_key, 'item[') === 0)
                                        {
                                            $data_key = str_replace("item[", "", $data_key);
                                            $data_key = str_replace("]", "", $data_key);
                                            $data_parsed[$key][$data_key] = trim($value);
                                        }
                                        
                                    if(strpos($data_key, 'sticky_item[') === 0)
                                        {
                                            $data_key = str_replace("sticky_item[", "", $data_key);
                                            $data_key = str_replace("]", "", $data_key);
                                            $_data_sticky_parsed[$data_key] = trim($value);
                                        }
                                }   
                            
                        }
                    
                    $paged  =   isset($_POST['page']) ? intval($_POST['page']) : 1;
                    
                    $_IS_SEARCH         =   isset($_POST['is_search'])  &&  $_POST['is_search'] == 'true' ? TRUE : FALSE;
                        
                    $_data_sticky_parsed    =   array_flip($_data_sticky_parsed);
                    
                    $_USE_PAGED_AJAX    =   FALSE;
                    $ajax_total_pages   =   isset($_POST['ajax_total_pages']) ? intval($_POST['ajax_total_pages']) : '';    
                    $ajax_page          =   isset($_POST['ajax_page']) ? intval($_POST['ajax_page']) : '';
                    if($ajax_total_pages > 1)
                        $_USE_PAGED_AJAX    =   TRUE;
                                        
                    $sort_view_id       =   intval($_POST['sort_view_id']);
                    $sort_view_settings =   $this->functions->get_sort_view_settings($sort_view_id);
                    
                    $sort_view_data     =   get_post($sort_view_id);
                    if($sort_view_data->post_parent > 0)
                        $sortID             =   $sort_view_data->post_parent;
                        else
                        $sortID             =   $sort_view_id;
                        
                    $sort_settings      =   $this->functions->get_sort_settings($sortID);
                    
                    $is_hierarhical     = $this->get_is_hierarhical_by_settings($sortID);
                    
                    
                    $data_list   =   array();
                    
                    if(count($data_parsed['offset_top']) > 0 ||  count($data_parsed['offset_bottom']) > 0)
                        {
                                                                
                            $query_args =   array(); 
                                         
                            $args   =   $this->get_interface_query_arguments($sort_view_id, $query_args);
                            $args   =   apply_filters('apto/interface_query_args', $args, $sort_view_id);
              
                            $custom_query = new WP_Query($args);
                            $found_posts = $custom_query->posts;
                                            
                            //exclude all object ids whcih are found in the $data_parsed
                            foreach($found_posts    as  $key    =>  $object_id)
                                {
                                    if(isset($data_parsed['offset_top'][$object_id])    ||  isset($data_parsed['list'][$object_id])   ||  isset($data_parsed['offset_bottom'][$object_id]))
                                        {
                                            unset($found_posts[$key]);                                            
                                        }
                                }
                            
                            $_data_list  =   array();
                            foreach($found_posts    as  $key    =>  $object_id)
                                {
                                    $_data_list[$object_id]  =   "null";
                                }    
                            
                            $insert_list =  $data_parsed['offset_top']  +   $data_parsed['list']  +   $data_parsed['offset_bottom'];
                            
                            $insert_position    =   $paged  *   $sort_settings['_pagination_posts_per_page']  - $sort_settings['_pagination_posts_per_page'] - $sort_settings['_pagination_offset_posts'];
                            if($insert_position < 0)
                                $insert_position    =   0;
                            
                            $data_list  =   array_slice($_data_list, 0, $insert_position, TRUE) + $insert_list + array_slice($_data_list, $insert_position, null, TRUE);
                            unset($_data_list);
                            
                            
                        }
                        
                        
                    //when using search preserve sticky posts
                    if(count($data_parsed['offset_top']) > 0 ||  count($data_parsed['offset_bottom']) > 0   ||  $_IS_SEARCH)
                        {
                            
                            //process the sticky list, append to existing
                            $existing_sticky_data       =   isset($sort_view_settings['_sticky_data']) ?    (array)$sort_view_settings['_sticky_data']   :   array(); 
                            $existing_sticky_data       =   array_filter($existing_sticky_data);
                            //remove any ids within the existing list
                            foreach($insert_list    as  $object_id  =>  $parent)
                                {
                                    $key    =   array_search($object_id, $existing_sticky_data);
                                    if($key === FALSE)
                                        continue;
                                        
                                    unset($existing_sticky_data[$key]);
                                    
                                }
                                
                            foreach($existing_sticky_data   as  $key    =>  $object_id)
                                {
                                    $_data_sticky_parsed[$key]  =   $object_id;
                                }
                                
                            //sort array()
                            ksort($_data_sticky_parsed);    
                               
                        }
                        
                    
                    if(count($data_list) < 1 && count($data_parsed['list']) > 0)
                        $data_list = $data_parsed['list'];
                    
                    
                    $reference_sort_view_id   =   $sort_view_id;
                    
                    //split the list if $_USE_PAGED_AJAX
                    if($_USE_PAGED_AJAX === TRUE    &&  is_array($data_list)    && count($data_list) > 0)
                        {
                            $_AJAX_pagination_start_at  =   $ajax_page  *   APTO_AJAX_OBJECTS_PER_PAGE  -   APTO_AJAX_OBJECTS_PER_PAGE;
                            $_AJAX_pagination_end_at    =   $_AJAX_pagination_start_at  + APTO_AJAX_OBJECTS_PER_PAGE;
                            
                            $data_list  =   array_slice($data_list, $_AJAX_pagination_start_at, APTO_AJAX_OBJECTS_PER_PAGE, TRUE);
                            
                            $reference_sort_view_id =   "-" . $sort_view_id;
                        }
                        
                    if($_USE_PAGED_AJAX === TRUE    &&  $ajax_page  <   2)
                        {
                            //delete any old unnsuccess sort save
                            $this->functions->delete_sort_list_from_table($reference_sort_view_id);
                        }
                       
                    
                    $args   =   array(
                                                'sortID'                =>  $sortID,
                                                'sort_settings'         =>  $sort_settings,
                                                
                                                'sort_view_id'          =>  $sort_view_id,
                                                'sort_view_settings'    =>  $sort_view_settings,
                                                
                                                '_USE_PAGED_AJAX'       =>  $_USE_PAGED_AJAX,
                                                'is_hierarhical'        =>  $is_hierarhical,
                                                'reference_sort_view_id'    =>  $reference_sort_view_id
                                                    );
                    
                    if (is_array($data_list)    && count($data_list) > 0)
                        {
                            
                            $this->AjaxProcessSortList($data_list, $args);
                            
                            
                            //save the sticky data if any
                            update_post_meta($sort_view_id, '_sticky_data', $_data_sticky_parsed);    
                        }
                    
                    $this->multilingual_syncronize( $data_list , $args);
                    
                    //check if all items has been processed, to remove old sort and replace with new one
                    if($_USE_PAGED_AJAX === TRUE   &&  $ajax_total_pages    ==  $ajax_page)
                        {
                            //remove the old order
                            $this->functions->delete_sort_list_from_table($sort_view_id);
                            
                            $mysql_query = "UPDATE `". $wpdb->prefix ."apto_sort_list`
                                                SET sort_view_id    =   ".   $sort_view_id   ."
                                                WHERE `sort_view_id`    =   '".     $reference_sort_view_id   ."'";
                            $results =   $wpdb->get_var($mysql_query);      
                            
                        }
                        
                    if($_USE_PAGED_AJAX === FALSE   ||  $ajax_total_pages   ==  $ajax_page)
                        do_action('apto_order_update_complete', $sort_view_id); 
                    
                    $_JSON_response['messages'][] =   __( "Items Order Updated", 'apto' );
                    
                    echo json_encode($_JSON_response);
                    
                    die();                    
                }
                
                
            function AjaxProcessSortList($data_list, $args)
                {
                    global $wpdb;
                    
                    extract($args);
                    
                    //don't remove until order succesfully updated, to prevent any sort order to be lost
                    if($_USE_PAGED_AJAX === FALSE)
                        {
                            //remove the old order
                            $this->functions->delete_sort_list_from_table($sort_view_id);
                        }
                        
                    //prepare the var which will hold the item childs current order
                    $childs_current_order = array();
                    
                    $current_item_menu_order = 0;
                    
                    foreach($data_list as $post_id => $parent_id ) 
                        {
                            if(($is_hierarhical === TRUE || $this->functions->is_woocommerce($sortID) === TRUE) && $sort_view_settings['_view_selection'] == 'archive')
                                {
                                    $current_item_menu_order = '';
                                    if($parent_id != 'null')
                                        {
                                            if(!isset($childs_current_order[$parent_id]))
                                                $childs_current_order[$parent_id] = 1;
                                                else
                                                $childs_current_order[$parent_id] = $childs_current_order[$parent_id] + 1;
                                                
                                            $current_item_menu_order    = $childs_current_order[$parent_id];
                                            $post_parent                = $parent_id;
                                        }
                                        else
                                            {
                                                if(!isset($childs_current_order['root']))
                                                    $childs_current_order['root'] = 1;
                                                    else
                                                    $childs_current_order['root'] = $childs_current_order['root'] + 1;
                                                    
                                                $current_item_menu_order    = $childs_current_order['root'];
                                                $post_parent                = 0;
                                            }
                                        
                                    //update the menu_order and parent
                                    if(count($sort_settings['_conditionals']) <   1)
                                        $wpdb->update( $wpdb->posts, array('menu_order' => $current_item_menu_order), array('ID' => $post_id) );
                                        
                                    $wpdb->update( $wpdb->posts, array('post_parent' => $post_parent), array('ID' => $post_id) );
                                    
                                    $query = "INSERT INTO `". $wpdb->prefix ."apto_sort_list` 
                                                (`sort_view_id`, `object_id`) 
                                                VALUES ('".$reference_sort_view_id."', '".$post_id."');";
                                    $results = $wpdb->get_results($query);
                                    
                                    //deprecated since 2.6  Do not rely on this anymore
                                    do_action('apto_order_update_hierarchical', array('post_id' =>  $post_id, 'position' =>  $current_item_menu_order, 'page_parent'    =>  $post_parent));
                                    
                                    do_action('apto_object_order_update', array('post_id' =>  $post_id, 'position' =>  $current_item_menu_order, 'page_parent'    =>  $post_parent, 'sort_view_id'  =>  $sort_view_id));

                                    continue;
                                }
                                
                                                                
                            //maintain the simple order if is archive
                            if($sort_settings['_view_type']    ==  'multiple' && $sort_view_settings['_view_selection'] == 'archive' && count($sort_settings['_rules']['post_type']) < 2    &&  count($sort_settings['_conditionals']) <   1)
                                $wpdb->update( $wpdb->posts, array('menu_order' => $current_item_menu_order), array('ID' => $post_id) ); 
                                 
                            $query = "INSERT INTO `". $wpdb->prefix ."apto_sort_list` 
                                        (`sort_view_id`, `object_id`) 
                                        VALUES ('".$reference_sort_view_id."', '".$post_id."');";
                            $results = $wpdb->get_results($query);
                            
                            //deprecated since 2.6  Do not rely on this anymore
                            do_action('apto_order_update', array('post_id' => $post_id, 'position' => $current_item_menu_order));
                            
                            do_action('apto_object_order_update', array('post_id' =>  $post_id, 'position' =>  $current_item_menu_order, 'sort_view_id'  =>  $sort_view_id));
                            
                            $current_item_menu_order++;
        
                        }
                    
                }
                
                
        
            /**
            * Syncroniz the order to other languages
            * Runs if there's specific MultiLingual plugins e.g WPML, Polylang etc
            * 
            * @param mixed $data_list
            * @param mixed $args
            */
            function multilingual_syncronize ( $data_list, $args )
                {
                    
                    extract($args);
                       
                    //prccess the ored items for WPML if syncronized settings 
                    if(count($data_list) > 0 && $this->get_sort_meta($sortID, '_wpml_synchronize') ==  'yes' &&  $_USE_PAGED_AJAX    === FALSE  &&  defined('ICL_LANGUAGE_CODE') && defined('ICL_SITEPRESS_VERSION')    &&  $is_hierarhical === FALSE)
                        {
                            global $sitepress;
                            
                            $current_language   =   $this->functions->get_sort_view_language($sort_view_id);
                            
                            //check if current post type is translatable
                            $sort_rules =   $this->functions->get_sort_current_language_rules($sort_settings, FALSE);
                            $_wpml_post_types_are_translatable  =   TRUE;
                            foreach($sort_rules['post_type']    as  $post_type)
                                {
                                    if($post_type   ==  'any')
                                        continue;
                                    
                                    if(!APTO_WPML_utils::is_translatable_post_type($post_type))
                                        {
                                            $_wpml_post_types_are_translatable  =   FALSE;   
                                            break;
                                        }
                                }
                            
                            //get all languages to be syncronized
                            $wpml_languages     =   APTO_WPML_utils::get_wpml_languages();
                            foreach($wpml_languages as  $wpml_language)
                                {
                                                                        
                                    //skipp if the same language
                                    if($wpml_language['code']   ==  $current_language)
                                        continue;
                                        
                                    //check if translatable post type
                                    if($_wpml_post_types_are_translatable   === FALSE)
                                        {
                                            $_JSON_response['errors'][] =   __( "A syncronization could not be completed", 'apto' ) . ' ' .  __( "as post type is not translatable.", 'apto' );
                                            break;
                                        }
                                        
                                    $translated_objects =   APTO_WPML_utils::translate_objects_to_language($data_list, $wpml_language['code']);
                                
                                    //if false there's been an error, either no all objects are syncronized, or theres a difference.
                                    if($translated_objects  === FALSE)
                                        {
                                            //add the error
                                            $_JSON_response['errors'][] =   __( "A syncronization could not be completed", 'apto' ) . ' ' . __( "for", 'apto' ) . ' ' . strtoupper($wpml_language['code']) . ' ' . __( "language", 'apto' ). ' ' . __( "as it contain a different number of objects.", 'apto' );
                                            continue;   
                                        }
                                        
                                        
                                    //check the taxonomy if set
                                    if($sort_view_settings['_view_selection']   ==  'taxonomy'  &&  !APTO_WPML_utils::is_translatable_taxonomy($sort_view_settings['_taxonomy']))
                                        {
                                            $_JSON_response['errors'][] =   __( "A syncronization could not be completed", 'apto' ) . ' ' .  __( "as taxonomy is not translatable.", 'apto' );
                                            break;
                                        }
                                    
                                    //check if there's a translation of current term
                                    if($sort_view_settings['_view_selection']   ==  'taxonomy')
                                        {
                                            $term_id_translation    =   icl_object_id($sort_view_settings['_term_id'], $sort_view_settings['_taxonomy'], FALSE, $wpml_language['code']);
                                            
                                            if(empty($term_id_translation))
                                                {
                                                    $_JSON_response['errors'][] =   __( "A syncronization could not be completed", 'apto' ) . ' ' .  __( "as term is not translated.", 'apto' );
                                                    break;
                                                }
                                        }
                                    
                                                                        
                                    //identify the sort view for this sort and language
                                    $attr   =   array(
                                                '_view_selection'   =>  $sort_view_settings['_view_selection'],
                                                '_view_language'    =>  $wpml_language['code']
                                                );
                                    
                                    if($sort_view_settings['_view_selection']   ==  'taxonomy')
                                        {
                                            $attr['_taxonomy' ]     =    $sort_view_settings['_taxonomy'];
                                            $attr['_term_id' ]      =    $term_id_translation;
                                        }
                                    
                                    $lang_sort_view_ID   =   $this->functions->get_sort_view_id_by_attributes($sortID, $attr); 
                                    
                                    if(empty($lang_sort_view_ID))
                                        {
                                            //create the sort view
                                            $sort_view_meta     =   array(
                                                                            '_order_type'               =>  'manual',
                                                                            '_view_selection'           =>  $sort_view_settings['_view_selection'],
                                                                            '_view_language'            =>  $wpml_language['code']
                                                                            );
                                            if($sort_view_settings['_view_selection']   ==  'taxonomy')
                                                {
                                                    $sort_view_meta['_taxonomy']  =   $sort_view_settings['_taxonomy'];
                                                    $sort_view_meta['_term_id']   =   $term_id_translation;
                                                }
                                                
                                            $lang_sort_view_ID       =   $this->create_view($sortID, $sort_view_meta);     
                                        }
                                    
                                    //check if both languages contain the same number of objects to make sure on syncronization, no object is left outside
                                    $args   =   $this->get_interface_query_arguments($lang_sort_view_ID, array('ignore_custom_sort' =>  TRUE));
                                    $args   =   apply_filters('apto/interface_query_args', $args, $lang_sort_view_ID);
                                    
                                    $sitepress->switch_lang($wpml_language['code']);
                                                                   
                                    $custom_query = new WP_Query($args);
                                    $found_posts = $custom_query->posts;
                                                            
                                    $sitepress->switch_lang($current_language);
                                    
                                    //if count does not match then continue
                                    if(count($found_posts)  !=  count($data_list))
                                        {
                                            //add the error
                                            $_JSON_response['errors'][] =   __( "A syncronization could not be completed", 'apto' ) . ' ' . __( "for", 'apto' ) . ' ' . strtoupper($wpml_language['code']) . ' ' . __( "language", 'apto' ) . ' ' . __( "as it contain a different number of objects.", 'apto' );
                                            continue;
                                        }
                                    
                                    //++++
                                    //to compare the $found_posts with $translated_objects if they are the same???
                                    
                                    $sort_view_settings         =   $this->functions->get_sort_view_settings($lang_sort_view_ID);    
                                    $reference_sort_view_id     =   $lang_sort_view_ID;
                                                                   
                                    //update the sort for that language too
                                    $args   =   array(
                                                        'sortID'                =>  $sortID,
                                                        'sort_settings'         =>  $sort_settings,
                                                        
                                                        'sort_view_id'          =>  $lang_sort_view_ID,
                                                        'sort_view_settings'    =>  $sort_view_settings,
                                                        
                                                        '_USE_PAGED_AJAX'       =>  $_USE_PAGED_AJAX,
                                                        'is_hierarhical'        =>  $is_hierarhical,
                                                        'reference_sort_view_id'    =>  $reference_sort_view_id
                                                            );
                                    
                                    $this->AjaxProcessSortList($translated_objects, $args);
                                    
                                    
                                    //save the sticky data if any
                                    $lang_data_sticky   =   APTO_WPML_utils::translate_sticky_list($_data_sticky_parsed, $data_list, $translated_objects);
                                    update_post_meta($lang_sort_view_ID, '_sticky_data', $lang_data_sticky);
                                    
                                    
                                }
                            
                        }
                       
                    
                }
                
             
        }

?>