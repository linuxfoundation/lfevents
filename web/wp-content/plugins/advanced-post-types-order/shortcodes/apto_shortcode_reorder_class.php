<?php
  
  
    class APTO_shortcode_reorder_interface extends APTO_interface
        {
               
            function __construct($sort_id) 
                {
                    $this->sortID   =   $sort_id;   
                }
                
            function init()
                {
                    $this->is_shortcode_interface   = TRUE;
                       
                    //load additional resources
                    include_once(APTO_PATH . '/include/walkers/post-types-order-walker-class.php');
                    include_once(APTO_PATH . '/include/walkers/taxonomies-terms-dropdown-class.php');
                    include_once(APTO_PATH . '/include/walkers/terms-dropdown-categories-class.php');
                    
                    $this->functions            =   new APTO_functions();
                    $this->admin_functions      =   new APTO_admin_functions();
                    $this->interface_helper     =   new APTO_interface_helper();

                    
                    $this->new_item_action      =   FALSE;
                      
                    //check for different interface settings changes like order_type
                    if($this->sortID != '')
                        $this->interface_helper->general_interface_update($this->sortID);
                          
                    //check for sort list update (automatic order as the manual is sent through ajax)
                    $this->interface_helper->automatic_sort_order_update();

                } 
        }
    
    
    
    
?>