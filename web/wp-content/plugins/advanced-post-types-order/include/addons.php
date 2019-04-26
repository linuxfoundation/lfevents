<?php

    //woocomerce archive fix
    add_action ('apto_object_order_update', 'wooc_apto_order_update_hierarchical', 10);
    function wooc_apto_order_update_hierarchical($data)
        {
            global $wpdb, $blog_id;
           
            $sort_view_id       =   $data['sort_view_id'];
            $sort_view_settings =   APTO_functions::get_sort_view_settings($sort_view_id);
            
            $sort_view_data     =   get_post($sort_view_id);
            $sortID             =   $sort_view_data->post_parent; 
                       
            //return if not woocommerce
            if (APTO_functions::is_woocommerce($sortID) === FALSE )
                return;
            
            //only for parents
            if($data['page_parent'] >   0)
                return;
                                
            // Clear product specific transients
            $post_transients_to_clear = array(
                                                //old field name
                                                '_transient_wc_product_children_ids_',
                                                
                                                //new field name
                                                '_transient_timeout_wc_product_children_'
                                            );

            foreach( $post_transients_to_clear as $transient ) 
                {
                    delete_transient( $transient . $data['post_id'] );
                    $wpdb->query( $wpdb->prepare( "DELETE FROM `$wpdb->options` WHERE `option_name` LIKE %s OR `option_name` = %s", $transient . $data['post_id'] . '%', '%' . $transient . $data['post_id'] . '%' ) );
                }

            clean_post_cache( $data['post_id'] );
        }
        
    //woocommerce grouped / simple icons
    add_filter ('apto_reorder_item_additional_details', 'wooc_apto_reorder_item_additional_details', 10, 2);
    function wooc_apto_reorder_item_additional_details($additiona_details, $post_data)
        {
            if ($post_data->post_type != "product" || !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) )
                return $additiona_details;
            
            //to be updated
                            
            return $additiona_details;
        }
    
    /**
    * Unnest query if WooCommerce. This is created by product_category shortcode
    * Conditions: only if nested include a single tax array
    * 
    * Why they do that ?!
    */
    add_filter('APTO/query_filter_valid_data', 'woocommerce_replace_nested_query', 5);
    function woocommerce_replace_nested_query( $query )
        {
            if (!in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) )
                return $query;
            
            if(empty($query->query_vars['post_type']))
                return $query;
                
            if( (is_string($query->query_vars['post_type'])   &&  $query->query_vars['post_type']   !=  'product' )
                    ||
                (is_array($query->query_vars['post_type'])   &&  ( count($query->query_vars['post_type']) > 1 ||  count($query->query_vars['post_type']) < 1 ||   $query->query_vars['post_type'][0]   !=  'product' ))
                )
                {
                    return $query;
                }
                
            //we ned 2 taxo arrays
            $tax_data   =   $query->tax_query->queries;
            if(isset($tax_data['relation']))
                unset($tax_data['relation']);
            
            //expect 2 elements
            if(count($tax_data) !=  2)
                return $query;
            
            foreach($tax_data   as  $key    =>  $data)
                {
                    if(isset($data['relation']))
                        unset( $tax_data[$key]['relation'] );
                }
            
            foreach($tax_data   as  $key    =>  $data)
                {
                    if(isset($data['taxonomy']) &&  $data['taxonomy']   ==  'product_visibility')
                        unset($tax_data[ $key ]);
                }
                
            //expect 1 elements
            if(count($tax_data) !==  1)
                return $query;
                
            //check if nested
            $found_nested   =   TRUE;
            reset($tax_data);
            foreach(current($tax_data)  as  $data)
                {
                    if(!is_array($data))
                        $found_nested   =   FALSE;
                }
                
            if($found_nested    === FALSE)
                return $query;

            //if multiple items in nested, ingore
            if(count(current($tax_data))    !== 1)
                return $query;
                
            $root_key   =   key($tax_data);
            
            $nested_data    =   current($tax_data);
            reset($nested_data);
            
            $query->tax_query->queries[ $root_key ] =  current($nested_data);
            
            return $query;
                
        }
    
    
    //WooCommerce 3.0 and up fix
    add_filter('APTO/query_filter_valid_data', 'woocommerce_query_filter_valid_data');
    function woocommerce_query_filter_valid_data( $query )
        {
            if (!in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) )
                return $query;
                
            //only WooCommerce 3.0 and up
            global $woocommerce;
            if( version_compare( $woocommerce->version, '3.0', "<" ) ) 
                return $query;
                
            //we ned 2 taxo arrays
            $tax_data   =   $query->tax_query->queries;
            if(isset($tax_data['relation']))
                unset($tax_data['relation']);
            
            /**
            * We expect 2 txonomies, product_visibility and a custom one
            */
                  
            //we need product_cat and product_visibility taxonomy
            //format is taxonomy => number of terms it should expect to be found, or false to replace
            $search_for_taxonomies  =   array();
            $product_taxonomies                          =   get_object_taxonomies( 'product');
            foreach ($product_taxonomies as  $key    =>  $product_taxonomy)
                {
                    $search_for_taxonomies[ $product_taxonomy ] =   1;
                }
            $search_for_taxonomies['product_visibility']    =   FALSE;
            
            $search_for_taxonomies  =   apply_filters( 'APTO/query_filter_valid_data/woocommerce_taxonomies', $search_for_taxonomies, $query );
            
            foreach($tax_data   as  $key    =>  $item_tax_data)
                {
                    if(isset($search_for_taxonomies[ $item_tax_data['taxonomy'] ]))
                        {
                            $expected_terms =   $search_for_taxonomies[ $item_tax_data['taxonomy'] ];
                            if($expected_terms  !== FALSE)
                                {
                                    if(count((array)$item_tax_data['terms'])    !=  $expected_terms)
                                        return $query;
                                }
                                
                            unset( $tax_data[ $key ] );
                        }
                    
                }
            
            //we expect an empty array
            if ( count($tax_data)   !== 0 )
                return $query;
                
            //At this point we are sure is the query we looking for. Unset the product_visibility
            foreach($query->tax_query->queries   as  $key    =>  $item_tax_data)
                {
                    if(is_array($item_tax_data) &&  isset($item_tax_data['taxonomy'])   &&  $item_tax_data['taxonomy']   ==  'product_visibility')
                        {
                             unset($query->tax_query->queries[$key]);
                        }
                }
            
            return $query;
                
        }
 
        
    //ignore the gallery edit images order which is set locally, independent from images archvie order
    add_filter('ajax_query_attachments_args', 'apto_ajax_query_attachments_args', 99);
    function apto_ajax_query_attachments_args($query)
        {
            $query['ignore_custom_sort'] = TRUE;

            return $query;    
        }
        
    //Shopp plugin compatibility    
    add_filter('shopp_collection_query', 'apto_shopp_collection_query');
    function apto_shopp_collection_query($options)
        {
            $orderby = shopp_setting('product_image_orderby');
            if($orderby !=  "sortorder")
                return $options;
            
            //create a csutom query then use the results as order
            $argv =     array(
                                'post_type'         =>  'shopp_product',
                                'posts_per_page'    =>  -1,
                                'fields'            =>  'ids'
                                );
            
            if(isset($options['joins']['wp_term_taxonomy']))
                {
                    preg_match('/.*tt.term_id=([0-9]+)?.*/i', $options['joins']['wp_term_taxonomy'], $matches);
                    if(isset($matches[1]))
                        {
                            $term_id = $matches[1];
                            
                            $argv['tax_query'] = array(
                                                            array(
                                                                'taxonomy' => 'shopp_category',
                                                                'field'    => 'term_id',
                                                                'terms'    => array($term_id),
                                                            ),
                                                        );    
                        }
                }
                
            $custom_query   =   new WP_Query($argv);
            if(!$custom_query->have_posts())
                return $options;    
            
            $posts_list =    $custom_query->posts;
            
            if(count($posts_list) > 0)
                {
                    global $wpdb; 
                    
                    $options['orderby'] =   " FIELD(p.ID, ". implode(",", $posts_list) .") ASC";
                    
                }
            
            return $options;
        }
        
    /**
    * Turn off the custom sorting when using "YITH WooCommerce Ajax Search Premium"  on AJAX calls
    */
    add_filter( 'ywcas_query_arguments', 'apto_ywcas_query_arguments', 99, 2 );
    function apto_ywcas_query_arguments( $args, $search_key )
        {
            
            $args['ignore_custom_sort']   = TRUE;
            
            return $args;
                
        }
    

?>