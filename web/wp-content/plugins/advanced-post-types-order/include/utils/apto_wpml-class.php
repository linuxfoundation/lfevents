<?php


    class APTO_WPML_utils
        {
            
            /**
            * return active languages
            * 
            */
            static function get_wpml_languages()
                {
                    $wpml_languages = icl_get_languages('skip_missing=0');
                    
                    return $wpml_languages;    
                    
                }
                
                
            
            /**
            * Check if the post type is translatable or not
            * 
            * @param mixed $post_type
            */
            static function is_translatable_post_type($post_type)
                {
                    
                    global $sitepress;
                    
                    if( $sitepress->is_translated_post_type($post_type) !== FALSE)
                        return TRUE;
                    
                    return FALSE;
                       
                }
                
                
            /**
            * Check if taxonomy is translatable or not
            * 
            * @param mixed $post_type
            */
            static function is_translatable_taxonomy($taxonomy)
                {
                    
                    global $sitepress;
                    
                    if( $sitepress->is_translated_taxonomy($taxonomy) !== FALSE)
                        return TRUE;
                    
                    return FALSE;
                       
                }
                
            
            static function translate_sort_rules($rules, $language_code)
                {
                    $translated_rules   =   $rules;

                    $translated_rules['taxonomy']   =   array();

                    foreach($rules['taxonomy']  as  $key    =>  $taxonomy_data)
                        {
                            $translated_taxonomy_data   =   $taxonomy_data;
                            $translated_taxonomy_data['terms']   =   array();
                            
                            foreach($taxonomy_data['terms'] as  $term_id)
                                {
                                    $term_id_translation    =   icl_object_id($term_id, $taxonomy_data['taxonomy'], FALSE, $language_code);
                                    if(empty($term_id_translation))
                                        {
                                            $translated_rules   =   FALSE;
                                            break 2;
                                        }
                                        
                                    $translated_taxonomy_data['terms'][]    =   $term_id_translation;
                                }
                                
                            $translated_rules['taxonomy'][$key] =   $translated_taxonomy_data;
                        }
                    
                    return $translated_rules;
                       
                }
                
            
            /**
            * Attempt to create a list of translated objects
            * 
            * @param mixed $data_list
            * @param mixed $sortID
            * @param mixed $sort_view_id
            */
            static function translate_objects_to_language($data_list, $lang_code)
                {
                    $translate_list =   array();
                    
                    foreach($data_list as $post_id => $parent_id)   
                        {
                            $post_data  =   get_post($post_id);
                            
                            $translated_post_id =   icl_object_id($post_id, $post_data->post_type, FALSE, $lang_code);
                            
                            //check if translated
                            if($translated_post_id < 1)
                                return FALSE;
                            
                            $translate_list[$translated_post_id]    =   "null";
                        }
                    
                    return $translate_list;
                }
                
            static function translate_sticky_list($_data_sticky, $data_list, $translated_objects)
                {
                    $lang_data_sticky   =   array();   
                    
                    if(count($_data_sticky) < 1)
                        return $lang_data_sticky;
                        
                    $translated_objects_keys    =   array_keys($translated_objects);
                        
                    foreach($_data_sticky   as  $position   =>  $_data_sticky_item)
                        {
                            $key    =   array_search($_data_sticky_item, array_keys($data_list));
                            $lang_data_sticky[$position] =   $translated_objects_keys[$key];
                        }
                    
                    return $lang_data_sticky;
                }
            
            
        }

?>