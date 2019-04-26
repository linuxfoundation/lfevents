<?php   
        
               
    class APTO_licence
        {
         
            function __construct()
                {
                    $this->licence_deactivation_check();   
                }
                
            function __destruct()
                {
                    
                }
                
            function licence_key_verify()
                {
                    $license_data = get_site_option('apto_license');
                    if(!is_array($license_data))
                        $license_data   =   array();
                    
                    if($this->is_local_instance())
                        return TRUE;
                             
                    if(!isset($license_data['kye']) || $license_data['kye'] == '')
                        return FALSE;
                        
                    return TRUE;
                }
                
            function is_local_instance()
                {
                    
                    if( defined('APTO_REQUIRE_KEY') &&  APTO_REQUIRE_KEY    === TRUE    )
                        return FALSE;
                    
                    $instance   =   trailingslashit(APTO_INSTANCE);
                    if(
                            stripos($instance, base64_decode('bG9jYWxob3N0Lw==')) !== FALSE
                        ||  stripos($instance, base64_decode('MTI3LjAuMC4xLw==')) !== FALSE
                        ||  stripos($instance, base64_decode('LmRldg==')) !== FALSE
                        ||  stripos($instance, base64_decode('c3RhZ2luZy53cGVuZ2luZS5jb20=')) !== FALSE
                        

                        
                        )
                        {
                            return TRUE;   
                        }
                        
                    return FALSE;
                    
                }
                
                
            function licence_deactivation_check()
                {
                    if(!$this->licence_key_verify() ||  $this->is_local_instance()  === TRUE)
                        return;
                    
                    $license_data = get_site_option('apto_license');
                    if(!is_array($license_data))
                        $license_data   =   array();
                    
                    if(isset($license_data['last_check']))
                        {
                            if(time() < ($license_data['last_check'] + 86400))
                                {
                                    return;
                                }
                        }
                    
                    global $wp_version;
                    
                    //update already the last_check to avoid multiple calls
                    $license_data['last_check']   = time();    
                    update_site_option('apto_license', $license_data);
                    
                    $license_key = $license_data['kye'];
                    $args = array(
                                        'woo_sl_action'         => 'status-check',
                                        'product_unique_id'     => APTO_PRODUCT_ID,
                                        'licence_key'           => $license_key,
                                        'domain'                => APTO_INSTANCE,
                                    );
                    $request_uri    = APTO_APP_API_URL . '?' . http_build_query( $args , '', '&');
                    $data           = wp_remote_get( $request_uri,  array(
                                                                            'timeout'     => 20,
                                                                            'user-agent'  => 'WordPress/' . $wp_version . '; APTO/' . APTO_VERSION .'; ' . get_bloginfo( 'url' ),
                                                                            ) );
                    
                    if(is_wp_error( $data ) || $data['response']['code'] != 200)
                        return;
                        
                    $response_block = json_decode($data['body']);

                    if(!is_array($response_block) || count($response_block) < 1)
                        return;    
                        
                    $response_block = $response_block[count($response_block) - 1];
                    if (is_object($response_block))
                        {
                            if($response_block->status_code == 'e312' || $response_block->status_code == 's203' ||  $response_block->status_code == 'e204')
                                {
                                    $license_data['kye']          = '';
                                }
                                
                            if($response_block->status == 'error')
                                {
                                    $license_data['kye']          = '';
                                }       
                        }
                          
                    update_site_option('apto_license', $license_data);
                    
                }
            
            
        }
            

        
    
?>