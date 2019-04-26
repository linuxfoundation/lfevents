<?php


    class APTO_Polylang
        {
            
            function __construct()
                {
                
                    add_filter('apto/admin/sort-taxonomies',         array($this, 'apto_admin_sort_taxonomies'), 10, 2);
                    add_filter('apto/query-utils/get_tax_queries',    array($this, 'apto_query_utils_get_tax_queries'), 10, 2);
                
                }
                
                
                
            function apto_admin_sort_taxonomies( $taxonomies, $sortID )
                {
                    
                    //replace the language taxonomy
                    foreach ( $taxonomies   as  $key    =>  $taxonomy)
                        {
                            
                            if(in_array($taxonomy, array('language', 'post_translations')))
                                unset($taxonomies[$key]);
                            
                        }
                        
                    $taxonomies =   array_values($taxonomies);
                    
                    return $taxonomies;
                       
                }   
            
            
            function apto_query_utils_get_tax_queries($filtred_queries, $args)
                {
                    
                    //filter out the language taxonomy if Polylang
                    if(!isset($args['clean_polylang_queries'])  ||  (isset($args['clean_polylang_queries'])   &&  $args['clean_polylang_queries'] !== FALSE))
                        {
                            foreach($filtred_queries    as  $key    =>  $filtred_query)
                                {
                                    if($filtred_query['taxonomy']   ==  'language')
                                        unset($filtred_queries[$key]);
                                }
                                
                            $filtred_queries    =   array_values($filtred_queries);
                        }
                        
                    return $filtred_queries;
                       
                }
            
            
        }


    new APTO_Polylang();



?>