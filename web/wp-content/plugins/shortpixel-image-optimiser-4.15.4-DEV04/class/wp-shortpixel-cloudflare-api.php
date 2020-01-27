<?php

class ShortPixelCloudFlareApi {
    private $_cloudflareEmail = ''; // $_cloudflareEmail
    private $_cloudflareAuthKey = ''; // $_cloudflareAuthKey
    private $_cloudflareZoneID = ''; // $_cloudflareZoneID
    
    public function __construct($cloudflareEmail, $cloudflareAuthKey, $cloudflareZoneID) {
        $this->set_up($cloudflareEmail, $cloudflareAuthKey, $cloudflareZoneID);
        $this->set_up_required_hooks();
    }
    
    public function set_up($cloudflareEmail, $cloudflareAuthKey, $cloudflareZoneID) {
        $this->_cloudflareEmail   = $cloudflareEmail;
        $this->_cloudflareAuthKey = $cloudflareAuthKey;
        $this->_cloudflareZoneID  = $cloudflareZoneID;
    }
    
    /**
     * @desc A list of hooks needed for actions towards CloudFlare
     */
    private function set_up_required_hooks() {
        // After the image is optimized we apply the code that will purge cached URL(img) from CloudFlare
        add_action( 'shortpixel_image_optimised', array( $this, 'start_cloudflare_cache_purge_process' ) );
    }
    
    /**
     * Method taken from @class WPShortPixelSettings
     *
     * @param string $key
     * @param string|array $val
     */
    public function save_to_wp_options( $key = '', $val = '' ) {
        $ret = update_option( $key, $val, 'no' );

        //hack for the situation when the option would just not update....
        if ( $ret === false && ! is_array( $val ) && $val != get_option( $key ) ) {
            delete_option( $key );
            $alloptions = wp_load_alloptions();
            if ( isset( $alloptions[ $key ] ) ) {
                wp_cache_delete( 'alloptions', 'options' );
            } else {
                wp_cache_delete( $key, 'options' );
            }
            add_option( $key, $val, '', 'no' );

            // still not? try the DB way...
            if ( $ret === false && $val != get_option( $key ) ) {
                global $wpdb;
                $sql  = "SELECT * FROM {$wpdb->prefix}options WHERE option_name = '" . $key . "'";
                $rows = $wpdb->get_results( $sql );
                if ( count( $rows ) === 0 ) {
                    $wpdb->insert( $wpdb->prefix . 'options',
                        array( "option_name" => $key, "option_value" => ( is_array( $val ) ? serialize( $val ) : $val ), "autoload" => "no" ),
                        array( "option_name" => "%s", "option_value" => ( is_numeric( $val ) ? "%d" : "%s" ) ) );
                } else { //update
                    $sql  = "update {$wpdb->prefix}options SET option_value=" .
                            ( is_array( $val )
                                ? "'" . serialize( $val ) . "'"
                                : ( is_numeric( $val ) ? $val : "'" . $val . "'" ) ) . " WHERE option_name = '" . $key . "'";
                    $rows = $wpdb->get_results( $sql );
                }

                if ( $val != get_option( $key ) ) {
                    //tough luck, gonna use the bomb...
                    wp_cache_flush();
                    add_option( $key, $val, '', 'no' );
                }
            }
        }
    }

    /**
     * @desc Start the process of purging all cache for image URL (includes all the image sizes/thumbnails)f1
     *
     * @param $image_id - WordPress image media ID
     */
    function start_cloudflare_cache_purge_process( $image_id ) {

        // Fetch CloudFlare API credentials
        $cloudflare_auth_email = $this->_cloudflareEmail;
        $cloudflare_auth_key   = $this->_cloudflareAuthKey;
        $cloudflare_zone_id    = $this->_cloudflareZoneID;

        if ( ! empty( $cloudflare_auth_email ) && ! empty( $cloudflare_auth_key ) && ! empty( $cloudflare_zone_id ) ) {

            // Fetch all WordPress install possible thumbnail sizes ( this will not return the full size option )
            $fetch_images_sizes   = get_intermediate_image_sizes();
            $image_url_for_purge  = array();
            $prepare_request_info = array();

            // if full image size tag is missing, we need to add it
            if ( ! in_array( 'full', $fetch_images_sizes ) ) {
                $fetch_images_sizes[] = 'full';
            }

            // Fetch the URL for each image size
            foreach ( $fetch_images_sizes as $size ) {
                // 0 - url; 1 - width; 2 - height
                $image_attributes = wp_get_attachment_image_src( $image_id, $size );
                // Append to the list
                array_push( $image_url_for_purge, $image_attributes[0] );
            }

            if ( ! empty( $image_url_for_purge ) ) {
                $prepare_request_info['files'] = $image_url_for_purge;
                // Encode the data into JSON before send
                $dispatch_purge_info = function_exists('wp_json_encode') ? wp_json_encode( $prepare_request_info ) : json_encode( $prepare_request_info );
                // Set headers for remote API to authenticate for the request
                $dispatch_header = array(
                    'X-Auth-Email: ' . $cloudflare_auth_email,
                    'X-Auth-Key: ' . $cloudflare_auth_key,
                    'Content-Type: application/json'
                );

                // Start the process of cache purge
                $request_response = $this->delete_url_cache_request_action( "https://api.cloudflare.com/client/v4/zones/" . $cloudflare_zone_id . "/purge_cache", $dispatch_purge_info, $dispatch_header );
                
                if ( ! is_array( $request_response ) ) {
                    WPShortPixel::log( 'Shortpixel - CloudFlare: The CloudFlare API is not responding correctly' );
                } elseif ( isset( $request_response['success'] ) && isset( $request_response['errors'] ) && false === (bool) $request_response['success'] ) {
                    WPShortPixel::log( 'Shortpixel - CloudFlare, Error messages: '
                        . (isset($request_response['errors']['message']) ? $request_response['errors']['message'] : json_encode($request_response['errors'])) );
                } else {
                    WPShortPixel::log('Shortpixel - CloudFlare successfully requested clear cache for: ' . json_encode($image_url_for_purge));
                }
            } else {
                // No use in running the process
            }
        } else {
            // CloudFlare credentials do not exist
        }
    }

    /**
     * @desc Send a delete cache request to CloudFlare for specified URL(s)
     *
     * @param string $request_url - The url to which we need to send the DELETE request
     * @param string $parameters_as_json - This JSON will contain the required parameters for DELETE request
     * @param array $request_headers - Authentication information and type of request
     *
     * @return array|mixed|object - Request response as decoded JSON
     */
    private function delete_url_cache_request_action( $request_url = '', $parameters_as_json = '', $request_headers = array() ) {
        if(!function_exists('curl_init')) return false;

        $curl_connection = curl_init();
        curl_setopt( $curl_connection, CURLOPT_URL, $request_url );
        curl_setopt( $curl_connection, CURLOPT_CUSTOMREQUEST, "DELETE" );
        curl_setopt( $curl_connection, CURLOPT_POSTFIELDS, $parameters_as_json );
        curl_setopt( $curl_connection, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $curl_connection, CURLOPT_HTTPHEADER, $request_headers );
        curl_setopt( $curl_connection, CURLOPT_USERAGENT, '"User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.87 Safari/537.36"' );

        $request_response = curl_exec( $curl_connection );
        $result           = json_decode( $request_response, true );
        curl_close( $curl_connection );

        return $result;
    }
}
