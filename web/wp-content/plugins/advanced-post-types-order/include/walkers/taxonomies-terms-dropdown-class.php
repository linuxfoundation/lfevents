<?php

    
    /**
    * 
    * Walker_CategoryDropdown extension for sort area Taxonomy selections
    * 
    */
    class APTO_Walker_TaxonomiesTermsDropdownCategories extends Walker_CategoryDropdown
        {
            function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
                    $pad = str_repeat('&nbsp;', $depth * 2);
                    $cat_name = apply_filters('list_cats', $category->name, $category);
                    
                    $term_objects  =   get_objects_in_term(array($category->term_id),$category->taxonomy);
          
                    $link_argv  =   array(
                                            'sort_id'           =>  $args['sortID'],
                                            'taxonomy'          =>  $category->taxonomy,
                                            'term_id'           =>  $category->term_id
                                            );
                    
                    if($args['apto_interface']->is_shortcode_interface === FALSE)
                        {
                            $link_argv['page'] =   'apto_' . $args['apto_interface']->interface_helper->get_current_menu_location_slug();
                            $value  =    $args['apto_interface']->interface_helper->get_tab_link($link_argv) ;
                        }
                        else
                        {
                            global $post;
                            $link_argv['base_url']      =   get_permalink($post->ID);
                            $value  =    $args['apto_interface']->interface_helper->get_item_link($link_argv) ;                            
                        }

                    $output .= "\t<option class=\"level-$depth\" value=\"" .$value."\"";
                    if ( (int)$category->term_id === (int) $args['selected'] )
                        { 
                            $output .= ' selected="selected"';
                        }
                    $output .= '>';
                    $output .= $pad . $cat_name;
                    
                    if ( $args['show_count'] )
                        $output .= '&nbsp;&nbsp;('. count($term_objects) .')';

                    $output .= "</option>\n";
                }
        }
        


?>