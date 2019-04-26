<?php
    
    class apto_shortcodes
        {
            function __construct()
                {
                    add_shortcode( 'apto_reorder', array($this, 'front_reorder' ) );   

                }
                  
            function front_reorder($atts)
                {
                    extract( shortcode_atts( array(
                                            'capability'    => 'manage_options',
                                            'sort_id'       =>  '',

                                            'taxonomy'      =>  '',
                                            'term_id'       =>  '',
                                            'term_slug'     =>  '',

                                            'hide_archive'  =>  'false'
                                        ), $atts ) );
                    
                    //check for provided sort_id
                    if($sort_id == '')
                        return;
                    
                    //check for capability
                    if(!is_user_logged_in() || !current_user_can($capability))
                        return;
                    
                    //load the js dependencies
                    wp_enqueue_script('jquery-ui-core');
                    wp_enqueue_script('jquery-ui-sortable');
                    wp_enqueue_script('jquery-ui-widget');
                    wp_enqueue_script('jquery-ui-mouse');
                    
                    $myJavascriptFile = APTO_URL . '/js/touch-punch.min.js';
                    wp_register_script('touch-punch.min.js', $myJavascriptFile, array(), '', TRUE);
                    wp_enqueue_script( 'touch-punch.min.js');
                       
                    $myJavascriptFile = APTO_URL . '/js/nested-sortable.js';
                    wp_register_script('nested-sortable.js', $myJavascriptFile, array(), '', TRUE);
                    wp_enqueue_script( 'nested-sortable.js');
                     
                    $myJavascriptFile = APTO_URL . '/js/apto-javascript.js';
                    wp_register_script('apto-javascript.js', $myJavascriptFile);
                    wp_enqueue_script( 'apto-javascript.js');
                    
                    //load the style dependencies 
                    wp_register_style('shortcodes-front_reorder.css', APTO_URL . '/css/shortcodes-front_reorder.css');
                    wp_enqueue_style( 'shortcodes-front_reorder.css');
                                        
                    include_once(APTO_PATH . '/include/apto_admin_functions-class.php'); 
                    include_once(APTO_PATH . '/include/apto_interface_helper-class.php');
                    include_once(APTO_PATH . '/include/apto_interface-class.php');
                    include_once(APTO_PATH . '/shortcodes/apto_shortcode_reorder_class.php');
                    
                    global $post;
                    
                    $this->post                 = $post;
                    
                    $APTO_shortcode_reorder_interface         = new APTO_shortcode_reorder_interface($sort_id);
                    $APTO_shortcode_reorder_interface->interface_hide_archive   =   'hide_archive'  ==  'false'   ? FALSE : TRUE;
                    
                    ob_start();                    
                    
                    $APTO_shortcode_reorder_interface->reorder_interface();
                    
                    ?>
                        <script type='text/javascript'>/* <![CDATA[ */
                            
                            var ajaxurl = '<?php echo admin_url() ?>/admin-ajax.php'; 
             
                        /* ]]> */</script>
                    
                    <?php

                    $html = ob_get_contents();
                    ob_end_clean();
                                                
                    return $html;   
                    
                }
         
        }

    new apto_shortcodes();

?>