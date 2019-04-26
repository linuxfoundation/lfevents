<?php


    class APTO_query_utils
        {
            static function filter_valid_data($query)
                {
                    //filter the taxonomies
                    if(is_array($query) && count($query) > 0)
                        {
                            $query  =   self::data_sanitize($query);
                        }
                           
                    return $query;   
                    
                }
                
            static function data_sanitize($queries)
                {
                    $cleaned_query = array();
                    
                    $same_level_taxonomies  =   array();
                                
                    foreach($queries as $key  =>  $data)
                        {
                            if ( 'relation' === $key ) 
                                {
                                    $cleaned_query['relation'] = $data;

                                // First-order clause.
                                } 
                            else if ( self::tax_is_first_order_clause( $data ) ) 
                                {
                                    if((isset($data['terms']) && is_array($data['terms']) && count(array_filter($data['terms'])) > 0) 
                                            || (isset($data['terms']) && !is_array($data['terms']) && $data['terms'] != ''))
                                            {
                                                $data['terms']   =   array_filter($data['terms']);
                                                
                                                //check for duplicate
                                                if(self::level_query_same_query_exists($data, $same_level_taxonomies))
                                                    continue;
                                                
                                                $cleaned_query[] = $data;
                                                
                                                $same_level_taxonomies[]    =   $data;  
                                            }

                                // Otherwise, it's a nested query, so we recurse.
                                } 
                            else if ( is_array( $data ) ) 
                                {
                                    $cleaned_subquery = self::data_sanitize( $data );

                                    if ( ! empty( $cleaned_subquery ) ) 
                                        {
                                            $cleaned_query[] = $cleaned_subquery;
                                        }
                                }
                                             
                        }
                    
                                        
                    return $cleaned_query;
                }
            
            static function tax_is_first_order_clause($query)
                {
                    return is_array( $query ) && ( empty( $query ) || array_key_exists( 'terms', $query ) || array_key_exists( 'taxonomy', $query ) || array_key_exists( 'include_children', $query ) || array_key_exists( 'field', $query ) || array_key_exists( 'operator', $query ) );    
                }
            
            static function level_query_same_query_exists($data, $same_level_taxonomies)
                {
                    if(count($same_level_taxonomies) < 1)
                        return FALSE;
                    
                    foreach($same_level_taxonomies  as  $same_level_taxonomy)
                        {
                            if(isset($data['taxonomy']) && isset($same_level_taxonomy['taxonomy'])  && isset($data['terms'])  && isset($same_level_taxonomy['terms'])   && isset($data['field'])  && isset($same_level_taxonomy['field']))
                                {
                                    if($data['taxonomy']    !=  $same_level_taxonomy['taxonomy'])
                                        continue;
                                        
                                    //check against the operator if booth exists
                                    if(isset($data['operator']) && isset($same_level_taxonomy['operator'])  &&  $data['operator'] !=  $same_level_taxonomy['operator'])
                                        continue;
                                    
                                    $field_id               =   strtolower($data['field']);
                                    if($field_id    ==  'id')
                                        $field_id   =   'term_id';
                                    $field_id_same_level    =   strtolower($same_level_taxonomy['field']);
                                    if($field_id_same_level    ==  'id')
                                        $field_id_same_level   =   'term_id';
                                    if($field_id    !=  $field_id_same_level)
                                        continue;
                                        
                                    $terms                  =   (array)$data['terms'];
                                    $terms_same_level       =   (array)$same_level_taxonomy['terms'];
                                    if(count($terms) != count($terms_same_level))
                                        continue;
                                    
                                    if(count(array_diff($terms, $terms_same_level)) > 0)
                                        continue;
                                        
                                    return TRUE;
                                }
                        }
                        
                    return FALSE;                    
                }
                
            static function tax_queries_count($queries, $args = array())
                {
                    $filtred_queries    =   self::get_tax_queries($queries, $args);
                                  
                    return count($filtred_queries);
                }
                
            static function get_tax_queries($queries, $args = array())
                {
                    $filtred_queries    =   array();
                    
                    foreach($queries as $key  =>  $data)
                        {
                            if ( 'relation' === $key ) 
                                {
               
                                } 
                            else if ( self::tax_is_first_order_clause( $data ) ) 
                                {
                                    //this is a tax query
                                    $filtred_queries[]  =   $data;

                                } 
                            else if ( is_array( $data ) ) 
                                {
                                    //this is a nested subquery
                                    //TO BE IMPLEMENTED
                                }
                        }
                    
                    //check for duplicated
                    $filtred_queries    =   self::taxonomy_duplicate_clean(  $filtred_queries    );
                    
                    $filtred_queries    =   apply_filters('apto/query-utils/get_tax_queries', $filtred_queries, $args);
                        
                    return $filtred_queries;
                }
            

            /**
            * Remove duplicate entires within query array list
            * 
            * @param mixed $filtred_queries
            */
            static function taxonomy_duplicate_clean(  $filtred_queries    )
                {
                    if (count($filtred_queries) < 2)
                        return $filtred_queries;
                    
                    //convert everything to term_id to allow comparison
                    $terms_map  =   array();    
                    foreach($filtred_queries    as  $fq_key    =>  $meta_item)
                        {
                                
                            //identify the term
                            switch ($meta_item['field'])
                                {
                                    case 'term_id':
                                    case 'ID':
                                    case 'id':
                                                $query_tax_terms    = $meta_item['terms'];
                                                if(!is_array($query_tax_terms))
                                                    $query_tax_terms    =   array($query_tax_terms);
                                                break;
                                                
                                    case 'slug':
                                                
                                                $query_tax_terms    = $meta_item['terms'];
                                                if(!is_array($query_tax_terms))
                                                    $query_tax_terms    =   array($query_tax_terms);
                                                
                                                //switch terms to id 
                                                foreach($query_tax_terms as $key => $query_tax_term_slug)
                                                    {
                                                          $term_data                =   get_term_by('slug', $query_tax_term_slug, $meta_item['taxonomy']);
                                                          if(isset($term_data->term_id))
                                                            $query_tax_terms[$key]    =   $term_data->term_id;
                                                    }

                                                break;
                                    case 'name':
                                                
                                                $query_tax_terms    = $meta_item['terms'];
                                                if(!is_array($query_tax_terms))
                                                    $query_tax_terms    =   array($query_tax_terms);
                                                
                                                //switch terms to id 
                                                foreach($query_tax_terms as $key => $query_tax_term_slug)
                                                    {
                                                          $term_data                =   get_term_by('name', $query_tax_term_slug, $meta_item['taxonomy']);
                                                          $query_tax_terms[$key]    =   $term_data->term_id;
                                                    }

                                                break;
                                                
                                    case 'term_taxonomy_id':
                                                
                                                $query_tax_terms    = $meta_item['terms'];
                                                if(!is_array($query_tax_terms))
                                                    $query_tax_terms    =   array($query_tax_terms);
                                                
                                                //switch terms to id 
                                                foreach($query_tax_terms as $key => $query_tax_term_slug)
                                                    {
                                                          $term_data                =   get_term_by('term_taxonomy_id', $query_tax_term_slug, $meta_item['taxonomy']);
                                                          if(isset($term_data->term_id))
                                                            $query_tax_terms[$key]    =   $term_data->term_id;
                                                    }

                                                break;
                                }
                                
                            sort($query_tax_terms);
                                
                            $terms_map[$fq_key]    =   $query_tax_terms;
                        }
                    
                    
                    //filter the duplicates
                    $terms_map = array_map("unserialize", array_unique(array_map("serialize", $terms_map)));
                    
                    //remove items not found anymore within $terms_map after duplicate filtering
                    foreach($filtred_queries    as  $key    =>  $meta_item)
                        {
                            if(!isset($terms_map[$key]))
                                unset($filtred_queries[$key]);
                        }

                    $filtred_queries    =   array_values($filtred_queries);
                                            
                    return $filtred_queries;   
                }
                
                
            static function get_query_taxonomies($queries, $taxonomies = array())
                {
                    if(!is_array($queries)  || count($queries) < 1)
                        return $taxonomies;
                         
                    foreach($queries    as  $key    =>  $data)
                        {
                            if(is_array($data))
                                $taxonomies =   self::get_query_taxonomies($data, $taxonomies);
                                else
                                {
                                    if($key ===  'taxonomy'  && !in_array($data, $taxonomies))
                                        $taxonomies[]   =   $data;    
                                }
                        }
                    
                    return $taxonomies;   
                }
                
                
                
            
            static function meta_is_first_order_clause($query)
                {
                    return is_array( $query ) && ( empty( $query ) || array_key_exists( 'key', $query ) || array_key_exists( 'value', $query ));    
                }
                
                
            static function meta_queries_count($queries)
                {
                    $filtred_queries    =   self::get_meta_queries($queries);
                                  
                    return count($filtred_queries);
                }
                
            static function get_meta_queries($queries)
                {
                    $filtred_queries    =   array();
                    
                    foreach($queries as $key  =>  $data)
                        {
                            if ( 'relation' === $key ) 
                                {
               
                                } 
                            else if ( self::meta_is_first_order_clause( $data ) ) 
                                {
                                    //this is a tax query
                                    $filtred_queries[]  =   $data;

                                } 
                            else if ( is_array( $data ) ) 
                                {
                                    //this is a nested subquery
                                    //TO BE IMPLEMENTED
                                }
                        }
                    
                    //check for duplicated
                    $filtred_queries    =   self::meta_duplicate_clean(  $filtred_queries    );
                        
                    return $filtred_queries;
                }
                
            /**
            * Remove duplicate entires within query array list
            * 
            * @param mixed $filtred_queries
            */
            static function meta_duplicate_clean(  $filtred_queries    )
                {
                    if (count($filtred_queries) < 2)
                        return $filtred_queries;
                    
                    //filter the duplicates
                    $_filtred_queries = array_map("unserialize", array_unique(array_map("serialize", $filtred_queries)));
                    
                    //remove items not found anymore within $terms_map after duplicate filtering
                    foreach($filtred_queries    as  $key    =>  $meta_item)
                        {
                            if(!isset($_filtred_queries[$key]))
                                unset($filtred_queries[$key]);
                        }

                    $filtred_queries    =   array_values($filtred_queries);
                                            
                    return $filtred_queries;   
                }
            
        }

?>