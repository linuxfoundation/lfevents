<?php

    
    /**
    * General admin class
    */
    class APTO_admin
        {
            var $APTO;
            
            var $interface_helper;
                  
            function __construct()
                {
                    global $APTO;
                    $this->APTO             =   $APTO;
                    
                    $this->interface_helper     =   new APTO_interface_helper();
                    
                    //load archive drag&drop sorting dependencies
                    add_action( 'admin_enqueue_scripts',                    array(&$this, 'pto_interface_sort_check'), 10 );
                    
                    add_action( 'wp_ajax_update-post-type-interface-sort',  array(&$this, 'savePostTypeInterfaceOrder') );
                    
                }
            
            function __destruct()
                {
                
                }
        
            
            /**
            * Attempt to return a sort id which match the current
            * 
            */
            function get_sort_match($post_type, $args)
                {

                    //check if there's any sort with this post type                                        
                    $available_sorts    =   $this->APTO->functions->get_sorts_by_filters($args);
                    $available_sorts    =   $this->APTO->functions->filter_sorts_list_by_post_type($available_sorts, $post_type);
                    
                    if (count($available_sorts) <   0)
                        return;
                    
                    $matched_sort_id    =   '';
                    
                    //exclude the ones with conditionals as they will not apply
                    foreach($available_sorts    as  $key    =>  $available_sort)
                        {
                            $sort_settings  =   $this->APTO->functions->get_sort_settings($available_sort->ID);   
                            
                            if(count($sort_settings['_conditionals']) >   0)
                                continue;
                                
                            if(empty($matched_sort_id)  ||  $matched_sort_id    >   $available_sort->ID)
                                $matched_sort_id    =   $available_sort->ID;
                            
                        }    
                    
                    return $matched_sort_id;
                }
        
        
            /**
            * Load archive drag&drop sorting dependencies
            * 
            * Since version 3.9.3
            */
            function pto_interface_sort_check()
                {
                    $screen = get_current_screen();
                    
                    //check if the right interface
                    if(!isset($screen->post_type)   ||  empty($screen->post_type))
                        return;
                    
                    $args   =   array(
                                        '_adminsort'     =>  array('yes'),
                                        '_view_type'    =>  array('multiple')
                                        );
                                  
                    $matched_sort_id    =   $this->get_sort_match($screen->post_type, $args);
                    
                    if (empty($matched_sort_id))
                        return;
                    
                    //no hierarhical yet
                    if($this->interface_helper->get_is_hierarhical_by_settings($matched_sort_id) === TRUE)
                        return;
                    
                    //check the option of this sort if allow interface sorting
                    $sort_settings  =   $this->APTO->functions->get_sort_settings($matched_sort_id); 
                        
                    //check if post type is sortable
                    if($sort_settings['_pto_interface_sort']    !=  'yes')
                        return;
                    
                    //if is search filter return
                    $search =   get_query_var('s');
                    if(!empty($search))
                        return;
                    
                    //return if use orderby columns
                    if (isset($_GET['orderby']) && $_GET['orderby'] !=  'menu_order')
                        return false;
                        
                    //return if post status filtering
                    if (isset($_GET['post_status']) &&  $_GET['post_status']    !=  'all')
                        return false;
                        
                    //return if post author filtering
                    if (isset($_GET['author']))
                        return false;
                    
                    
                    global $wp_query;
                    
                    //identify the query taxonomy and term id
                    $taxonomy   =   '';
                    $term_id    =   '';
                    
                    $filtered_query  =   apply_filters('apto/default_interface_sort/filtered_query', $wp_query);
                    
                    if($wp_query->is_category()  ||  $wp_query->is_tax())
                        {
                            //check for polylang
                            if (is_plugin_active('polylang/polylang.php'))
                                {
                                    //we need 2 queryes, one will bethe language
                                    if (count($wp_query->tax_query->queries)    <   2  ||   count($wp_query->tax_query->queries)    >   2)
                                        return false;    
                                    
                                    $queries      =   $wp_query->tax_query->queries;
                                    
                                    foreach($queries    as  $key    =>  $query)
                                        {
                                            if($query['taxonomy']   ==  'language')
                                                unset($queries[$key]);
                                        }
                                    
                                    //we need a single query
                                    if (count($queries)    <   1  ||   count($queries)    >   1)
                                        return false;
                                    
                                    reset($queries);
                                    $query      =   current($queries);
                                    
                                    $taxonomy    =   $query['taxonomy'];
                                    
                                    if(count($query['terms'])   >   1)
                                        return false;
                                    
                                    reset($query['terms']);
                                    
                                    $term_el    =   current($query['terms']);   
                                        
                                    $term_data  =   get_term_by($query['field'], $term_el,  $taxonomy);
                                    $term_id    =   $term_data->term_id;
                                    
                                }
                                else
                                {
                                    //we need a single query
                                    if (count($wp_query->tax_query->queries)    <   1  ||   count($wp_query->tax_query->queries)    >   1)
                                        return false;
                                    
                                    reset($wp_query->tax_query->queries);
                                    $query      =   current($wp_query->tax_query->queries);
                                    
                                    $taxonomy    =   $query['taxonomy'];
                                    
                                    if(count($query['terms'])   >   1)
                                        return false;
                                    
                                    reset($query['terms']);
                                    
                                    $term_el    =   current($query['terms']);   
                                        
                                    $term_data  =   get_term_by($query['field'], $term_el,  $taxonomy);
                                    $term_id    =   $term_data->term_id;
                                }
                        }
                    
                    $paged  =   get_query_var('paged', 1);
                    if($paged   <   1)
                        $paged  =   1;
                    
                    //load required dependencies
                    wp_enqueue_style('cpt-archive-dd', APTO_URL . '/css/apto-pt-interface.css');
                    
                    wp_enqueue_script('jquery');
                    wp_enqueue_script('jquery-ui-sortable');
                    wp_enqueue_script('cpt', APTO_URL . '/js/apto-pt-interface.js', array('jquery'));    
                    
                    $post_type  =   isset( $wp_query->query['post_type'] ) ?  $wp_query->query['post_type'] :   '';
                    if (empty ( $post_type ) )
                        $post_type  =   $screen->post_type;
                    
                    $vars = array(
                                    'post_type'     =>  $post_type, // get_query_var('post_type'),
                                    'taxonomy'      =>  $taxonomy,
                                    'term_id'       =>  $term_id,
                                    'paged'         =>  $paged,
                                    'sort_id'       =>  $matched_sort_id,
                                    
                                    'nonce'         =>  wp_create_nonce( 'default-interface-sort-update' )
                                );
                    wp_localize_script( 'cpt', 'APTO_vars', $vars );
                    
                }
                
            
            /**
            * Process the AJAX call
            * 
            */
            function savePostTypeInterfaceOrder()
                {

                    set_time_limit(600);
                    
                    global $wpdb;
                    
                    $post_type  =   filter_var ( $_POST['post_type'], FILTER_SANITIZE_STRING);
                    $taxonomy   =   filter_var ( $_POST['taxonomy'], FILTER_SANITIZE_STRING);
                    $term_id    =   filter_var ( $_POST['term_id'], FILTER_SANITIZE_NUMBER_INT);
                    $paged      =   filter_var ( $_POST['paged'], FILTER_SANITIZE_NUMBER_INT);
                    $sort_id    =   filter_var ( $_POST['sort_id'], FILTER_SANITIZE_NUMBER_INT);
                    
                    //check the nonce
                    if ( ! wp_verify_nonce( $_POST['nonce'], 'default-interface-sort-update' ) ) 
                        die();
                    
                    parse_str($_POST['order'], $data);
                    
                    if (!is_array($data)    ||  count($data)    <   1)
                        die();
                    
                    
                    //get the sort settings
                    $sort_settings  =   $this->APTO->functions->get_sort_settings( $sort_id );
                    
                    //do not save if the AdminSort is not On, as it will save wrong order
                    if( $sort_settings['_adminsort']    !=  'yes')
                        die();
                                        
                    //retrieve a list of all objects for current POST data
                    //++ Maybe All ?!
                    $args   =   array(
                                        'post_type'         =>  $post_type,
                                        'posts_per_page'    =>  -1,
                                        'post_status'       =>  array('publish', 'pending', 'draft', 'private', 'future', 'inherit'),
                                        'orderby'           =>  array( 'menu_order' => 'ASC', 'post_date' => 'DESC' ),
                                        'fields'            =>  'ids'
                                            );
                    if(!empty($taxonomy))
                        {
                            $args['tax_query']  =   array(
                                                            array(
                                                                'taxonomy'  =>  $taxonomy,
                                                                'field'     =>  'term_id',
                                                                'terms'     =>  $term_id,
                                                            )
                                                        );   
                            
                        }
                    
                    $custom_query   =   new WP_Query( $args );
                    $results        =   $custom_query->posts;
                    
                    /*
                    $mysql_query    =   $wpdb->prepare("SELECT ID FROM ". $wpdb->posts ." 
                                                            WHERE post_type = %s AND post_status IN ('publish', 'pending', 'draft', 'private', 'future')
                                                            ORDER BY menu_order, post_date DESC", $post_type);
                    $results        =   $wpdb->get_results($mysql_query);
                    */
                    
                    if (!is_array($results)    ||  count($results)    <   1)
                        die();
                    
                    //create the list of ID's
                    $objects_ids    =   array();
                    foreach($results    as  $object_ID)
                        {
                            $objects_ids[]  =   $object_ID;   
                        }
                    
                    global $userdata;
                    $objects_per_page   =   get_user_meta($userdata->ID ,'edit_' . $post_type . '_per_page', TRUE);
                    if(empty($objects_per_page))
                        $objects_per_page   =   20;
                    
                    $edit_start_at      =   $paged  *   $objects_per_page   -   $objects_per_page;
                    $index              =   0;
                    for($i  =   $edit_start_at; $i  <   ($edit_start_at +   $objects_per_page); $i++)
                        {
                            if(!isset($objects_ids[$i]))
                                break;
                                
                            $objects_ids[$i]    =   $data['post'][$index];
                            $index++;
                        }
                    
                    //identify the sort view
                    if(!empty($taxonomy))
                        {
                            $attr   =   array(
                                       '_view_selection'   =>  'taxonomy',
                                       '_taxonomy'         =>  $taxonomy,
                                       '_term_id'          =>  $term_id,
                                       '_view_language'    =>  $this->APTO->functions->get_blog_language()
                                       );
                        }
                        else
                        {
                            $attr   =   array(
                                       '_view_selection'   =>  'archive',
                                       '_view_language'    =>  $this->APTO->functions->get_blog_language()
                                       );
                        }                   
                    
                    $sort_view_id   =   $this->APTO->functions->get_sort_view_id_by_attributes($sort_id, $attr);
                                                 
                    //if sort view is empty, create it
                    if(empty($sort_view_id))
                        {
                            //create an instance of APTO Interface Helper
                            include_once(APTO_PATH . '/include/apto_interface_helper-class.php');
                            $APTO_interface_helper  =   new APTO_interface_helper();
                            
                            if(!empty($taxonomy))
                                {
                                    $sort_view_meta     =   array(
                                                           '_order_type'               =>  'manual',
                                                           '_view_selection'           =>  'taxonomy',
                                                           '_taxonomy'                 =>  $taxonomy,
                                                           '_term_id'                  =>  $term_id,
                                                           '_view_language'            =>  $this->APTO->functions->get_blog_language()
                                                           );
                                }
                                else
                                {
                                    $sort_view_meta     =   array(
                                                           '_order_type'               =>  'manual',
                                                           '_view_selection'           =>  'archive',
                                                           '_view_language'            =>  $this->APTO->functions->get_blog_language()
                                                           );
                                }                     
                            $sort_view_id       =   $APTO_interface_helper->create_view($sort_id, $sort_view_meta);
                        }
                    
                    $this->APTO->functions->delete_sort_list_from_table($sort_view_id);
                    
                    $query = "INSERT INTO `". $wpdb->prefix ."apto_sort_list` 
                                (`sort_view_id`, `object_id`) 
                                VALUES ";
                                
                    foreach( $objects_ids as $menu_order   =>  $id ) 
                        {
                            if ($menu_order >   0)
                                $query  .=  ",\n";
                                
                            $query  .=  "('".$sort_view_id."', '".$id."')";
                        }
                    $results = $wpdb->get_results($query);
                    
                    
                    //update the menu_order within database if archive
                    if(empty($taxonomy))
                        {
                            foreach( $objects_ids as $menu_order   =>  $id ) 
                                {
                                    $data = array(
                                                    'menu_order' => $menu_order
                                                    );
                                    $data = apply_filters('post-types-order_save-ajax-order', $data, $menu_order, $id);
                                    
                                    $wpdb->update( $wpdb->posts, $data, array('ID' => $id) );
                                }
                        }   
                    
                }
            
        }



?>