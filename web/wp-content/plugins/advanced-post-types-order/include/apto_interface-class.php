<?php

class APTO_interface 
    {
        var $interface_helper;
        var $functions;
              
        var $sortID;
        var $sort_settings;
        
        var $current_sort_view_ID;
        var $current_sort_view_settings;
        
        var $menu_location  =   '';
        var $menu_tabs      =   array();
        
        var $is_shortcode_interface;
        
        var $interface_hide_archive  =   FALSE;
        
        function __construct() 
            {
                
            }
            
        function init()
            {
                $this->is_shortcode_interface   =   FALSE;
                   
                //load additional resources
                include_once(APTO_PATH . '/include/walkers/post-types-order-walker-class.php');
                include_once(APTO_PATH . '/include/walkers/taxonomies-terms-dropdown-class.php');
                include_once(APTO_PATH . '/include/walkers/terms-dropdown-categories-class.php');
                
                $this->functions            =   new APTO_functions();
                $this->admin_functions      =   new APTO_admin_functions();
                $this->interface_helper     =   new APTO_interface_helper();
                
                $this->menu_location        =   $this->interface_helper->get_current_menu_location();
                $this->menu_tabs            =   $this->admin_functions->get_tabs($this->menu_location);
                
                $this->new_item_action      =   isset($_GET['new-item']) ?  TRUE : FALSE;
                
                $this->sortID               =   $this->get_current_sort_id();
                                                
                //check for solrt list deletion
                $this->interface_helper->sort_list_delete();
                
                //check for different interface settings changes like order_type
                if($this->sortID != '')
                    $this->interface_helper->general_interface_update($this->sortID);
                
                //check for sort settings update
                $this->interface_helper->settings_update();
                   
                //check for sort list update (automatic order as the manual is sent through ajax)
                $this->interface_helper->automatic_sort_order_update();

            }
            
        /**
        * Get current interface sort_id 
        * 
        */
        function get_current_sort_id()
            {
                $sort_id    =   isset($_GET['sort_id']) ?  intval($_GET['sort_id']) : '';
                
                if($sort_id == '' && $this->new_item_action ===   TRUE)
                    return '';
                
                if($sort_id == '')
                    {
                             
                        if(count($this->menu_tabs) > 0)
                            {
                                foreach($this->menu_tabs as $menu_tab)
                                    {
                                        //check if user have capability to view this sort
                                        $sort_required_capability   =   get_post_meta($menu_tab->ID, '_capability', TRUE);
                                        $sort_required_capability   =   apply_filters('apto/wp-admin/reorder-interface/sort-view-required-capability', $sort_required_capability, $menu_tab->ID);
                                        
                                        if(!current_user_can($sort_required_capability))
                                            continue;
                                            
                                        $sort_id        =   $menu_tab->ID;
                                        break;        
                                    }
                            }
                    }
                    
                if($sort_id == '')
                    $this->new_item_action  =   TRUE;
                
                return $sort_id;
            }
                
        function reorder_interface()
            {
                $this->init();
                
                $this->sort_settings                =   $this->functions->get_sort_settings($this->sortID);
                
                $this->current_sort_view_ID         =   $this->interface_helper->get_last_sort_view_ID($this->sortID);
                
                $view_type  =    $this->interface_helper->get_sort_view_type($this->sortID);
                                
                //if there is no sort view create a default archive
                if($this->new_item_action   === FALSE && $this->current_sort_view_ID == '' && $this->sortID != '')
                    {
                        if($view_type   ==  'multiple')
                            {
                                $sort_view_meta =   array(
                                                            '_order_type'               =>  'manual',
                                                            '_view_selection'           =>  'archive',
                                                            '_view_language'            =>  $this->functions->get_blog_language()
                                                            );
                            }
                        if($view_type   ==  'simple')
                            {
                                $sort_view_meta =   array(
                                                            '_order_type'               =>  'manual',
                                                            '_view_selection'           =>  'simple',
                                                            '_view_language'            =>  $this->functions->get_blog_language()
                                                            );
                            }
                            
                        $this->current_sort_view_ID =   $this->interface_helper->create_view($this->sortID, $sort_view_meta);
                    }
                
                $this->current_sort_view_settings   =   $this->functions->get_sort_view_settings($this->current_sort_view_ID);
                
                $site_settings  = $this->functions->get_settings();
                
                $this->admin_functions->nottice_similar_sorts($this->sortID);
                
                ?>    
                
                    <div class="wrap" id="apto">
                        <div class="icon32" id="icon-edit"><br></div>
                        <h2><?php _e( "Re-order", 'apto' ) ?></h2>
                        
                        <noscript>
                            <div class="error message">
                                <p><?php _e( "This plugin can't work without javascript, because it's use drag and drop and AJAX.", 'apto' ) ?></p>
                            </div>
                        </noscript>

                        
                        
                        <div class="clear"></div>
                    <?php
                              
                        //WPML check the sort rules translation
                        if(defined('ICL_LANGUAGE_CODE') && $view_type   ==  'simple' && defined('ICL_SITEPRESS_VERSION'))
                            {
                                $sort_settings_update_languages =   get_post_meta($this->sortID, '_settings_update_languages', TRUE);
                                if(!is_array($sort_settings_update_languages))
                                    $sort_settings_update_languages =   array();
                                    
                                $blog_languages     =   icl_get_languages('skip_missing=N');
                                
                                $default_language   =   $this->functions->get_blog_default_language();
                                $WPML_message       =   '';
                                foreach($blog_languages as $blog_language   =>  $language_data)
                                    {
                                          if(!isset($sort_settings_update_languages[$blog_language]))
                                            $WPML_message   .=  ', ' . $language_data['native_name'];
                                    }
                                
                                $WPML_message   =   ltrim($WPML_message, ", ");
                                if($WPML_message != '')   
                                    {
                                        ?>
                                            <div class="error message">
                                                <p><?php _e( "WPML: The Sort Rules couldn't be translated automatically. You need to set/translate this manually for", 'apto' ) ?> <?php echo $WPML_message .'. Change language, set appropriate rules and click sort settings Update.' ?></p>
                                            </div>
                                        <?php
                                    }                                
                                
                                
                            }

                        if($this->is_shortcode_interface !== TRUE)
                            {
                            ?>
                            
                            <h2 class="nav-tab-wrapper" id="apto-nav-tab-wrapper">
                                <?php
                                                            
                                    foreach( $this->menu_tabs as $apto_sort_data)
                                        {
                                            //check if use have capability to view this sort
                                            $sort_required_capability   =   get_post_meta($apto_sort_data->ID, '_capability', TRUE);
                                            $sort_required_capability   =   apply_filters('apto/wp-admin/reorder-interface/sort-view-required-capability', $sort_required_capability, $apto_sort_data->ID);
                                            
                                            if(!current_user_can($sort_required_capability))
                                                continue;
                                            
                                            //use the first item if $this->sortID is empty
                                            if($this->sortID == '' && $this->new_item_action    === FALSE)
                                                $this->sortID = $apto_sort_data->ID;
                                            
                                            ?><a data-sort-id="<?php echo $apto_sort_data->ID ?>" class="nav-tab<?php if($this->sortID == $apto_sort_data->ID) { echo ' nav-tab-active';} ?>" href="<?php 
                                                
                                                $link_argv  =   array(
                                                                        'sort_id' => $apto_sort_data->ID
                                                                        );
                                                $link_argv['page'] =   'apto_' . $this->interface_helper->get_current_menu_location_slug();
                                                
                                                $link_argv['base_url'] =   $this->interface_helper->get_current_menu_location();
                                                                                        
                                                echo $this->interface_helper->get_tab_link($link_argv) ;
                                                
                                                ?>"><?php echo $apto_sort_data->post_title  ?></a><?php
                                        }
                                
                                $admin_settings_view_capability = apply_filters('apto/wp-admin/reorder-interface/view-settings-capability', 'manage_options');
                                
                                //add also empty selection to allow new sort creation
                                if(current_user_can($admin_settings_view_capability))
                                    {
                                        ?>
                                            <a class="new-item nav-tab<?php if($this->sortID == '') { echo ' nav-tab-active';} ?>" href="<?php 
                                                
                                                $link_argv  =   array(
                                                                        'new-item' => 'true'
                                                                        );
                                                $link_argv['page'] =   'apto_' . $this->interface_helper->get_current_menu_location_slug();
                                                
                                                $link_argv['base_url'] =   $this->interface_helper->get_current_menu_location();
                                                echo $this->interface_helper->get_tab_link($link_argv) ;
                                                
                                        ?>">+</a>
                                        <?php
                                    }
                                
                                ?>
                                <span class="clear">&nbsp;</span>
                            </h2>
                            
                            
                            <script type="text/javascript">
                                                    
                                jQuery(document).ready(function() {
                                        
                                        jQuery( "h2#apto-nav-tab-wrapper" ).sortable({
                                              items: "a.nav-tab",
                                              cancel: 'a.new-item',
                                              update: function( event, ui ) {
                                                  
                                                  var order = jQuery(this).sortable('toArray', {attribute: 'data-sort-id'});
                                                  
                                                  var queryString = { 
                                                            action:             'update-sorting-menu-tabs', 
                                                            order_list:         order,
                                                            menu_location:      '<?php echo $this->interface_helper->get_current_menu_location_slug() ?>',
                                                            nonce:              '<?php echo wp_create_nonce( 'update-sorting-menu-tabs') ?>'
                                                                };
                                                  
                                                  jQuery.ajax({
                                                      type: 'POST',
                                                      url: ajaxurl,
                                                      data: queryString,
                                                      cache: false,
                                                      dataType: "html",
                                                      success: function(response){

                                                      },
                                                      error: function(html){

                                                          }
                                                    });
                                                  
                                              }
                                            });
                                    
                                    })
                                        
                            </script>
                            
                            <?php 
                            }
                    
                        if(current_user_can($admin_settings_view_capability) && $this->is_shortcode_interface === FALSE)
                            $this->settings();
                            else
                            $this->sort_description();
                    
                        //output the sort interface only if there is a sort id
                        if($this->sortID != '')
                            $this->sort_area();
                            
                    ?>
                
                    </div>
                
                <?php
    

            }
            
        
        function settings()
            {
                $site_settings  = $this->functions->get_settings();
                
                //this helds information about a query change, i.e. a term has been removed and does not appear anymore on the intface.
                $found_changes  =   FALSE;
                
                if($this->new_item_action   === TRUE)
                    $sort_rules =   array();
                    else
                    $sort_rules =   $this->functions->get_sort_current_language_rules($this->sort_settings);
                
                ?>
                <form action="" method="post">

                    <input type="hidden"  name="sort_id" value="<?php echo $this->sortID ?>" id="sort_id" />
                    <input type="hidden"  name="apto_sort_settings_form_submit" value="1" />
                    <?php wp_nonce_field( 'APTO/sort-settings' ) ?>
                    
                    <div id="poststuff" class="meta-box-sortables">
                    <div class="postbox apto_metabox<?php
                        
                            //check the status of metabox
                            $metabox_toggle = get_post_meta($this->sortID, '_metabox_toggle', TRUE);
                            if(!is_array($metabox_toggle))
                                $metabox_toggle = array();
                            
                            if(isset($metabox_toggle['settings']) && $metabox_toggle['settings'] == 'closed')
                                echo ' closed';
                        
                        ?>" id="apto_options">
                        <div title="<?php _e( "Click to toggle", 'apto' ) ?>" class="handlediv"><br></div>
                        <h3 class="handle"><span class="icon settings">&nbsp;</span><span><?php _e( "Sort List Settings", 'apto' ) ?><?php if(!empty($this->sortID)) { echo ' <small>( ID '. $this->sortID .' )</small>';}  ?></span></h3>
                        <div class="inside"<?php
                                
                            if(isset($metabox_toggle['settings']) && $metabox_toggle['settings'] == 'closed')
                                echo ' style="display: none"';
                        
                        ?>>
                            <table class="apto_input widefat _top">
                                <tbody>
                                    <tr id="query_rules">
                                        <td class="label">
                                            <label for=""><?php _e( "Query Rules", 'apto' ) ?></label>
                                            <p class="description"><?php _e( "Create a set of criteria rules which match your query. This will determine what to show on the following sort list and the order will apply on front side.", 'apto' ) ?></p>
                                            <p class="description"><?php _e( "All rules are compared to a query using AND operator. For more details check", 'apto' ) ?> <a target="_blank" href="http://www.nsp-code.com/advanced-post-types-order-description-and-usage/understanding-sort-list-settings-area/"><?php _e( "Query Rules examples", 'apto' ) ?></a></p>
                                        </td>
                                        <td>
                                            <div id="rules-post-type">
                                                
                                                <?php
                                                    
                                                    $button_show_advanced   =   '';
                                                    $view_type  =    $this->interface_helper->get_sort_view_type($this->sortID);
                                                      
                                                    if($this->sortID == '' || ($this->sortID != '' && (
                                                        (!isset($sort_rules['taxonomy']) || (is_array($sort_rules['taxonomy']) && count($sort_rules['taxonomy']) < 1)) && 
                                                        (!isset($sort_rules['meta']) || (is_array($sort_rules['meta']) && count($sort_rules['meta']) < 1)) && 
                                                        (!isset($sort_rules['author']) || ( is_array($sort_rules['author']) && count($sort_rules['author']) < 1))
                                                        )))
                                                        {
                                                            $button_show_advanced = true;
                                                        }
                                                    
                                                    if($button_show_advanced === TRUE)
                                                        {
                                                            ?><a id="button_show_adv" data-status="simple" onClick="APTO.interface_query_advanced_toggle()" href="javascript: void(0)"><?php _e( "Show Advanced", 'apto' ) ?></a><?php            
                                                        }
                                                ?>
                                                <h4>Post Type</h4>
                                                <table class="apto_input widefat apto_rules apto_table">
                                                    <tbody>
                                                        <?php
                                                        
                                                            
                                                            if(isset($sort_rules['post_type']) && count($sort_rules['post_type']) > 0)
                                                                {
                                                                    $rule_id = 1;
                                                                    foreach($sort_rules['post_type'] as $rule_post_type)   
                                                                        {
                                                                            //check if post_type still exists
                                                                            if($rule_post_type == 'any')
                                                                                $exists =   TRUE;
                                                                                else
                                                                                $exists =   post_type_exists($rule_post_type);
                                                                                
                                                                            if($exists === FALSE)
                                                                                {
                                                                                    $found_changes[]  =   'Custom Post Type '.  $rule_post_type .' invalid'; 
                                                                                    continue;
                                                                                }
                                                                            
                                                                            $argv = array();
                                                                            
                                                                            if($rule_id < 2)
                                                                                $argv['default']    =   TRUE;
                                                                            
                                                                            $argv['selected_value'] =   $rule_post_type;
    
                                                                            $rule_box = $this->interface_helper->get_rule_post_type_html_box($argv);
                                                                            echo $rule_box;
                                                                            
                                                                            $rule_id++;   
                                                                        }
                                                                }
                                                                else
                                                                    {
                                                                        $interface_post_type    =   isset($_GET['post_type']) ?  preg_replace( '/[^a-zA-Z0-9_\-]/', '', $_GET['post_type'])   :   '';
                                                                        
                                                                        $argv   =   array(
                                                                                            'default'           =>  TRUE,
                                                                                            'selected_value'    =>  $interface_post_type
                                                                                            );
                                                                        $rule_box = $this->interface_helper->get_rule_post_type_html_box($argv);
                                                                        echo $rule_box;
                                                                        
                                                                        unset($interface_post_type);
                                                                    }
                                                        
                                                        ?>
                                                    </tbody>
                                                </table>
                                                
                                                <table class="apto_input widefat apto_more">
                                                    <tbody>
                                                        <tr><td><a class="button-secondary" href="javascript: void(0)" onClick="APTO.add_rule_post_type()"><?php _e( "Add Post Type", 'apto' ) ?></a> &nbsp;&nbsp;<img class="ajax_loading" src="<?php echo APTO_URL ?>/images/ajax-loader.gif" alt="Loading" /></td></tr>
                                                    </tbody>
                                                 </table>
                                                 
                                             </div>
                                             
                                            <div id="rules-taxonomy"<?php if($button_show_advanced === TRUE) {echo ' style="display: none"';} ?>>
                                                <h4><?php _e( "Taxonomy", 'apto' ) ?></h4>

                                                <?php
                                                        
                                                    if(isset($sort_rules['taxonomy']) && count($sort_rules['taxonomy']) > 0)
                                                        {
                                                            $group_id = 1;
                                                            foreach($sort_rules['taxonomy'] as $rule_block)   
                                                                {
                                                                    
                                                                    //check if the taxonomy still exists
                                                                    $exists =   taxonomy_exists($rule_block['taxonomy']);
                                                                    if($exists === FALSE)
                                                                        {
                                                                            $found_changes[]  =   __( "Taxonomy", 'apto' ) . ' '.  $rule_block['taxonomy'] .    ' ' . __( "invalid", 'apto' ); 
                                                                            continue;
                                                                        }
                                                                    
                                                                    foreach($rule_block['terms'] as $rule_term)
                                                                        {
                                                                            $exists =   term_exists( (int)$rule_term, $rule_block['taxonomy']);
                                                                            if($exists === FALSE || $exists == NULL)
                                                                                $found_changes[]  =   __( "Term", 'apto' ) . ' '.  $rule_term .' '. __( "invalid", 'apto' );   
                                                                        }
                                                                    
                                                                    $argv   =   array(
                                                                                        'group_id'              =>  $group_id,
                                                                                        'taxonomy'              =>  $rule_block['taxonomy'],
                                                                                        'operator'              =>  $rule_block['operator'],
                                                                                        'include_children'      =>  $rule_block['include_children'],
                                                                                        'selected'              =>  $rule_block['terms']
                                                                                        ); 
                                                                    
                                                                    $argv['html_alternate'] =   FALSE;
                                                                    if($group_id % 2 == 0)
                                                                        $argv['html_alternate'] =   TRUE;
                                                                    
                                                                    echo $this->interface_helper->get_rule_taxonomy_html_box($argv);
                                                                    
                                                                    $group_id++;   
                                                                }
                                                        }
                                                
                                                ?>
                                    
                                                <div class="insert_root"></div>
                                                
                                                <table class="apto_input widefat apto_more">
                                                    <tbody>
                                                        <tr><td><a class="button-secondary" href="javascript: void(0)" onClick="APTO.add_rule_taxonomy()"><?php _e( "Add Taxonomy", 'apto' ) ?></a> &nbsp;&nbsp;<img class="ajax_loading" src="<?php echo APTO_URL ?>/images/ajax-loader.gif" alt="Loading" /></td></tr>
                                                    </tbody>
                                                </table>
                                                
                                                <table class="apto_input widefat taxonomy_relationship">
                                                    <tbody>
                                                        <tr>
                                                            <td class="param">
                                                                <h5><?php _e( "Taxonomy Relation", 'apto' ) ?></h5>
                                                                <select class="select" name="rules[taxonomy_relation]">
                                                                    <?php
                                            
                                                                        $operator_values = array(
                                                                                                   'AND',
                                                                                                   'OR'
                                                                                                    );
                                                                        foreach($operator_values as $operator_value)
                                                                            {
                                                                                ?><option <?php if(isset($sort_rules['taxonomy_relation']) && $operator_value == $sort_rules['taxonomy_relation']) { echo 'selected="selected"'; }?>    value="<?php echo $operator_value ?>"><?php echo $operator_value ?></option><?php
                                                                            }
                                                                    ?>

                                                                </select>
                                                            </td>
                                                            <td class="value"></td>
                                                        </tr>
                                                        
                                                    </tbody>
                                                </table>
                                            
                                            </div>    
                                            
                                            
                                            <div id="rules-meta"<?php if($button_show_advanced === TRUE) {echo ' style="display: none"';} ?>>
                                                <h4><?php _e( "Meta", 'apto' ) ?></h4>

                                                <?php
                                                    
                                                    if(isset($sort_rules['meta']) && count($sort_rules['meta']) > 0)
                                                        {
                                                            $group_id = 1;
                                                            foreach($sort_rules['meta'] as $rule_block)   
                                                                {
                                                                    $argv   =   array(
                                                                                        'group_id'      =>  $group_id,
                                                                                        'key'      =>  $rule_block['key'],
                                                                                        'value_type'    =>  $rule_block['value_type'],
                                                                                        'value'         =>  $rule_block['value'],
                                                                                        'compare'       =>  $rule_block['compare'],
                                                                                        'type'          =>  $rule_block['type'],
                                                                                        ); 
                                                                    
                                                                    $argv['html_alternate'] =   FALSE;
                                                                    if($group_id % 2 == 0)
                                                                        $argv['html_alternate'] =   TRUE;
                                                                    
                                                                    echo $this->interface_helper->get_rule_meta_html_box($argv);
                                                                    
                                                                    $group_id++;   
                                                                }
                                                        }
                                                            
                                                ?>
                                                
                                                <div class="insert_root"></div>
                                                
                                                <table class="apto_input widefat apto_more">
                                                    <tbody>
                                                        <tr><td><a class="button-secondary" href="javascript: void(0)" onClick="APTO.add_rule_meta()"><?php _e( "Add Meta", 'apto' ) ?></a> &nbsp;&nbsp;<img class="ajax_loading" src="<?php echo APTO_URL ?>/images/ajax-loader.gif" alt="Loading" /></td></tr>
                                                    </tbody>
                                                </table>
                                                
                                                <table class="apto_input widefat meta_relationship">
                                                    <tbody>
                                                        <tr>
                                                            <td class="param">
                                                                <h5><?php _e( "Meta Relation", 'apto' ) ?></h5>
                                                                <select class="select" name="rules[meta_relation]">
                                                                    <?php
                                            
                                                                        $operator_values = array(
                                                                                                   'AND',
                                                                                                   'OR'
                                                                                                    );
                                                                        foreach($operator_values as $operator_value)
                                                                            {
                                                                                ?><option <?php if(isset($sort_rules['meta_relation']) && $operator_value == $sort_rules['meta_relation']) { echo 'selected="selected"'; }?>    value="<?php echo $operator_value ?>"><?php echo $operator_value ?></option><?php
                                                                            }
                                                                    ?>

                                                                </select>
                                                            </td>
                                                            <td class="value"></td>
                                                        </tr>
                                                        
                                                    </tbody>
                                                </table>
                                                                                                 
                                            </div>     
                                            
                                            <div id="rules-author"<?php if($button_show_advanced === TRUE) {echo ' style="display: none"';} ?>>
                                                <h4><?php _e( "Author", 'apto' ) ?></h4>
                                                
                                                <table class="apto_input widefat apto_rules apto_table">
                                                    <tbody>
                                                        <?php
                                                        
                                                            if(isset($sort_rules['author']) && count($sort_rules['author']) > 0)
                                                                {
                                                                    foreach($sort_rules['author'] as $authorID)   
                                                                        {
                                                                            $argv = array();
                                                                            $argv['selected'] =   $authorID;
                                                                            
                                                                            $rule_box = $this->interface_helper->get_rule_author_html_box($argv);
                                                                            echo $rule_box; 
                                                                        }
                                                                }
                                                        
                                                        ?>
                                                    </tbody>
                                                </table>
                                                
                                                <table class="apto_input widefat apto_more">
                                                    <tbody>
                                                        <tr><td><a class="button-secondary" href="javascript: void(0)" onClick="APTO.add_rule_author()"><?php _e( "Add Author", 'apto' ) ?></a> &nbsp;&nbsp;<img class="ajax_loading" src="<?php echo APTO_URL ?>/images/ajax-loader.gif" alt="Loading" /></td></tr>
                                                    </tbody>
                                                </table>

                                           </div>                                                
                                           
                                           
                                           
                                           <?php
                                            
                                                if($found_changes !== FALSE)
                                                    {
                                                        ?>
                                                            <div id="found_changes" class="updated">
                                                                <p><?php _e( "Certain changes has been done to your site and some of Query Rules cannot be displayed anymore. You should review the settings Query Rules area and click Update button.", 'apto' ) ?></p>
                                                                <p><?php echo implode("<br />", $found_changes) ?></p>
                                                            </div>
                                                        <?php   
                                                    }
                                            
                                            ?>
                                        </td>
                                    </tr>
                                    
                                    

                                    
                                    <tr id="conditional_rules">
                                        <td class="label">
                                            <label for=""><?php _e( "Conditionals", 'apto' ) ?></label>
                                            <p class="description"><?php _e( "Apply the order only if conditions are true. For more details check", 'apto' ) ?> <a target="_blank" href="http://www.nsp-code.com/advanced-post-types-order-description-and-usage/using-conditionals-for-sorting-apply/"><?php _e( "Conditionals Usage", 'apto' ) ?></a></p>
                                        </td>
                                        <td>
                                            <h4><?php _e( "Apply if", 'apto' ) ?></h4>
                                            
                                            <?php
                                            
                                                $sort_conditionals = get_post_meta($this->sortID, '_conditionals', TRUE);
                                                if(is_array($sort_conditionals)  && count($sort_conditionals) > 0)
                                                    {
                                                        $group_id = 1;
                                                        foreach($sort_conditionals as $key  =>  $group_data)
                                                            {
                                                                $argv   =   array(
                                                                                    'group_id'      =>  $group_id,
                                                                                    'data'          =>  $group_data
                                                                                    );      
                                                                echo $this->interface_helper->get_html_conditional_group($argv);
                                                                
                                                                $group_id++;
                                                            }
                                                    }
                                            
                                            ?>
                                            
                                  
                                            <table class="apto_input widefat apto_more" id="add_conditional_group">
                                                <tbody>
                                                    <tr><td><a class="button-secondary" href="javascript: void(0)" onClick="APTO.add_conditional_group(this)"><?php _e( "Add Group", 'apto' ) ?> </a> &nbsp;&nbsp;<img class="ajax_loading" src="<?php echo APTO_URL ?>/images/ajax-loader.gif" alt="Loading" /></td></tr>
                                                </tbody>
                                            </table>

                                    </tr>
                                    

                                    <tr>
                                        <td class="label">
                                            <label for=""><?php _e( "Interface", 'apto' ) ?></label>
                                            <p class="description"><?php _e( "This sort interface settings. For more details check", 'apto' ) ?> <a target="_blank" href="http://www.nsp-code.com/advanced-post-types-order-description-and-usage/understanding-sort-list-settings-area/"><?php _e( "Sort List Settings", 'apto' ) ?></a></p>
                                        </td>
                                        <td class="np">
                                            
                                            <table class="apto_input inner_table widefat">
                                                <tbody>
                                                    <tr><td>
                                                        <h4><?php _e( "Title", 'apto' ) ?></h4>
                                                        <p class="description"><?php _e( "Sort list tab title", 'apto' ) ?></p>
                                                        <input type="text" value="<?php echo $this->interface_helper->get_sort_meta($this->sortID, '_title'); ?>" class="text" name="interface[_title]">
                                                    </td></tr>
                                                    <tr><td>
                                                        <h4><?php _e( "Description", 'apto' ) ?></h4>
                                                        <p class="description"><?php _e( "Sort list description. This will appear for others (non-admin users) when doing re-sort, it should include a description for what area this sort will apply.", 'apto' ) ?></p>
                                                        <textarea class="large-text" cols="50" rows="3" name="interface[_description]"><?php echo htmlspecialchars($this->interface_helper->get_sort_meta($this->sortID, '_description')); ?></textarea>
                                                    </td></tr>
                                                    <tr><td>
                                                        <h4><?php _e( "Menu Location", 'apto' ) ?></h4>
                                                        <p class="description"><?php _e( "Relocate this Sort Interface to another menu", 'apto' ) ?></p>
                                                        <select name="interface[_location]" class="select">
                                                            <?php
                                                            
                                                                foreach ($this->admin_functions->get_available_menu_locations() as $location    =>  $location_data)
                                                                    {
                                                                        //check for hide
                                                                        if(isset($site_settings['show_reorder_interfaces'][$location]) && $site_settings['show_reorder_interfaces'][$location] == 'hide')
                                                                            continue;
                                                                                                                                                
                                                                        ?>
                                                                        <option <?php if(!empty($this->sort_settings['_location']) && $location == $this->sort_settings['_location']
                                                                            || (empty($this->sort_settings['_location']) && $this->interface_helper->get_current_menu_location_slug() == $location_data['slug'])
                                                                            
                                                                        ) { ?>selected="selected" <?php } ?> value="<?php echo $location ?>"><?php echo $location_data['name'] ?></option>
                                                                        <?php
                                                                    }
                                                            
                                                            ?>
                                                        </select>
                                                    </td></tr>
                                                    <tr><td>
                                                        <h4><?php _e( "Auto Apply Sort", 'apto' ) ?></h4>
                                                        <p class="description"><?php _e( "Automatically apply the sort to theme queries if match.", 'apto' ) ?></p>
                                                        <fieldset>
                                                            <label><input type="radio" <?php if($this->interface_helper->get_sort_meta($this->sortID, '_autosort') == 'yes' || $this->interface_helper->get_sort_meta($this->sortID, '_autosort') == '') { ?>checked="checked"<?php } ?> value="yes" name="interface[_autosort]"> <span><?php _e( "Yes", 'apto' ) ?></span></label><br>
                                                            <label><input type="radio" <?php if($this->interface_helper->get_sort_meta($this->sortID, '_autosort') == 'no') { ?>checked="checked"<?php } ?> value="no" name="interface[_autosort]"> <span><?php _e( "No", 'apto' ) ?></span></label><br>
                                                        </fieldset>
                                                    </td></tr>
                                                    <tr><td>
                                                        <h4><?php _e( "Admin Sort", 'apto' ) ?></h4>
                                                        <p class="description"><?php _e( "Automatically apply the sort to admin queries if match.", 'apto' ) ?></p>
                                                        <fieldset>
                                                            <label><input type="radio" <?php if($this->interface_helper->get_sort_meta($this->sortID, '_adminsort') == 'yes' || $this->interface_helper->get_sort_meta($this->sortID, '_adminsort') == '') { ?>checked="checked"<?php } ?> value="yes" name="interface[_adminsort]"> <span><?php _e( "Yes", 'apto' ) ?></span></label><br>
                                                            <label><input type="radio" <?php if($this->interface_helper->get_sort_meta($this->sortID, '_adminsort') == 'no') { ?>checked="checked"<?php } ?> value="no" name="interface[_adminsort]"> <span><?php _e( "No", 'apto' ) ?></span></label><br>
                                                        </fieldset>
                                                    </td></tr>
                                                    <?php if($view_type == 'multiple'   &&  $this->interface_helper->get_is_hierarhical_by_settings($this->sortID) !== TRUE) { ?>
                                                    <tr><td>
                                                        <h4><?php _e( "Allow sorting within default post type interface", 'apto' ) ?></h4>
                                                        <p class="description"><?php _e( "A Drag & Drop functionality is available to be used within default WordPress post type interface.", 'apto' ) ?></p>
                                                        <?php
                                                        
                                                            //check for exceptions, when this option will be disabled
                                                            $disabled           =   FALSE;
                                                            $disable_reasons    =   array();
                                                            
                                                            //this sort need to be previouslly created
                                                            if($this->sortID == '')
                                                                {
                                                                    $disabled   =   TRUE;
                                                                    $disable_reasons[]  =   'new_sort';
                                                                }
                                                            
                                                            //check if not primary sort
                                                            $primary_sort   =   $this->admin_functions->get_primary_from_similar_sorts($this->sortID);
                                                            if($primary_sort    !=  $this->sortID)
                                                                {
                                                                    $disabled   =   TRUE;
                                                                    $disable_reasons[]  =   'no_primary_sort';
                                                                }
                                                            
                                                            //admin sort need to be yes
                                                            if($this->interface_helper->get_sort_meta($this->sortID, '_adminsort') != 'yes')
                                                                {
                                                                    $disabled   =   TRUE;
                                                                    $disable_reasons[]  =   'no_adminsort';
                                                                }
                                                                
                                                            //no conditionals
                                                            if(count($this->sort_settings['_conditionals']) >   0)
                                                                {
                                                                    $disabled   =   TRUE;
                                                                    $disable_reasons[]  =   'no_conditionals';
                                                                }
                                                                
                                                                
                                                            $current_option_value   =   $this->interface_helper->get_sort_meta($this->sortID, '_pto_interface_sort');
                                                            if($disabled    === TRUE)
                                                                $current_option_value   =   "no";
                                                        
                                                        ?>
                                                        
                                                        <fieldset>
                                                            <label><input type="radio" <?php  if($disabled    === TRUE) { ?>disabled="disabled"<?php }  ?> <?php if($current_option_value == 'yes') { ?>checked="checked"<?php } ?> value="yes" name="interface[_pto_interface_sort]"> <span><?php _e( "Yes", 'apto' ) ?></span></label><br>
                                                            <label><input type="radio" <?php  if($disabled    === TRUE) { ?>disabled="disabled"<?php }  ?> <?php if($current_option_value == 'no' || $current_option_value == '') { ?>checked="checked"<?php } ?> value="no" name="interface[_pto_interface_sort]"> <span><?php _e( "No", 'apto' ) ?></span></label><br>
                                                        </fieldset>
                                                        <?php
                                                        
                                                            if($disabled    === TRUE)
                                                                {
                                                                    ?><br /><p class="description"><strong>*Disabled</strong> for the following reasons:<?php     
                                                                }
                                                        
                                                            if(in_array('new_sort', $disable_reasons))
                                                                {
                                                                    ?><br />-<?php _e( "Need to Create Sort first", 'apto' ) ?><?php   
                                                                }
                                                                else
                                                                    {
                                                                        
                                                                        if(in_array('no_primary_sort', $disable_reasons))
                                                                            {
                                                                                $other_sort_settings  =   APTO_functions::get_sort_settings($primary_sort);
                                                                                $link_argv  =   array(
                                                                                                                    'sort_id' => $primary_sort
                                                                                                                    );
                                                                                $link_argv['page'] =   'apto_' . sanitize_title($other_sort_settings['_location']);
                                                                                
                                                                                $link_argv['base_url'] =   admin_url( $other_sort_settings['_location'] );;
                                                                                                                        
                                                                                $url  = APTO_interface_helper::get_item_link($link_argv) ;
                                                                                
                                                                                ?><br /><?php _e( "Not a primary sort, this feature can apply only to", 'apto' ) ?> <a href="<?php echo $url ?>">this sort</a><?php   
                                                                            }
                                                                        
                                                                        if(in_array('no_conditionals', $disable_reasons))
                                                                            {
                                                                                ?><br /><?php _e( "This feature can't apply when conditionals set", 'apto' ) ?><?php   
                                                                            }
                                                                        
                                                                        if(in_array('no_adminsort', $disable_reasons))
                                                                            {
                                                                                ?><br /><?php _e( "Admin Sort need to be Yes", 'apto' ) ?><?php   
                                                                            }
                                                                    }
                                                        
                                                            if($disabled    === TRUE)
                                                                {
                                                                    ?></p><?php     
                                                                }
                                                        
                                                        ?>
                                                    </td></tr>
                                                    <?php } ?>
                                                    <tr><td>
                                                        <h4><?php _e( "Send new items to bottom of list", 'apto' ) ?></h4>
                                                        <p class="description"><?php _e( "All new posts / custom types will append at the end instead top. This will apply when manual ordering.", 'apto' ) ?></p>
                                                        <fieldset>
                                                            <label><input type="radio" <?php if($this->interface_helper->get_sort_meta($this->sortID, '_new_items_to_bottom') == 'yes') { ?>checked="checked"<?php } ?> value="yes" name="interface[_new_items_to_bottom]"> <span><?php _e( "Yes", 'apto' ) ?></span></label><br>
                                                            <label><input type="radio" <?php if($this->interface_helper->get_sort_meta($this->sortID, '_new_items_to_bottom') == 'no' || $this->interface_helper->get_sort_meta($this->sortID, '_new_items_to_bottom') == '') { ?>checked="checked"<?php } ?> value="no" name="interface[_new_items_to_bottom]"> <span><?php _e( "No", 'apto' ) ?></span></label><br>
                                                        </fieldset>
                                                    </td></tr>
                                                    <tr><td>
                                                        <h4><?php _e( "Thumbnails", 'apto' ) ?></h4>
                                                        <p class="description"><?php _e( "Show thumbnails on sort list", 'apto' ) ?></p>
                                                        <fieldset>
                                                            <label><input type="radio" <?php if($this->interface_helper->get_sort_meta($this->sortID, '_show_thumbnails') == 'yes') { ?>checked="checked"<?php } ?> value="yes" name="interface[_show_thumbnails]"> <span><?php _e( "Yes", 'apto' ) ?></span></label><br>
                                                            <label><input type="radio" <?php if($this->interface_helper->get_sort_meta($this->sortID, '_show_thumbnails') == 'no' || $this->interface_helper->get_sort_meta($this->sortID, '_show_thumbnails') == '') { ?>checked="checked"<?php } ?> value="no" name="interface[_show_thumbnails]"> <span><?php _e( "No", 'apto' ) ?></span></label><br>
                                                        </fieldset>
                                                    </td></tr>
                                                    <?php 
                                                    if( $this->sortID ==  ''  ||  ($this->interface_helper->get_is_hierarhical_by_settings($this->sortID) !== TRUE || ($this->functions->is_woocommerce($this->sortID) === TRUE && $this->current_sort_view_settings['_view_selection'] != 'archive'))) { ?>
                                                    <tr><td>
                                                        <h4><?php _e( "Pagination", 'apto' ) ?></h4>
                                                        <p class="description"><?php _e( "Set pagination for sort list", 'apto' ) ?></p>
                                                        <fieldset>
                                                            <label><input type="radio" <?php if($this->interface_helper->get_sort_meta($this->sortID, '_pagination') == 'yes') { ?>checked="checked"<?php } ?> value="yes" name="interface[_pagination]" onclick="APTO.ElementTrigger_Change('show', '.visibility_related_option_pagination');"> <span><?php _e( "Yes", 'apto' ) ?></span></label><br>
                                                            <label><input type="radio" <?php if($this->interface_helper->get_sort_meta($this->sortID, '_pagination') == 'no' || $this->interface_helper->get_sort_meta($this->sortID, '_pagination') == '') { ?>checked="checked"<?php } ?> value="no" name="interface[_pagination]" onclick="APTO.ElementTrigger_Change('hide', '.visibility_related_option_pagination');"> <span><?php _e( "No", 'apto' ) ?></span></label><br>
                                                        </fieldset>
                                                        
                                                        <fieldset class="visibility_related_option_pagination" <?php if($this->interface_helper->get_sort_meta($this->sortID, '_pagination') == 'no' || $this->interface_helper->get_sort_meta($this->sortID, '_pagination') == '') { ?>style="display: none"<?php } ?>>
                                                            <br />
                                                            <h4><?php _e( "Posts Per Page", 'apto' ) ?></h4>
                                                            <p class="description"><?php _e( "The number of posts to show on page", 'apto' ) ?></p>
                                                            <input type="text" value="<?php echo $this->interface_helper->get_sort_meta($this->sortID, '_pagination_posts_per_page'); ?>" class="text" name="interface[_pagination_posts_per_page]">
                                                        </fieldset>
                                                        
                                                        <fieldset class="visibility_related_option_pagination" <?php if($this->interface_helper->get_sort_meta($this->sortID, '_pagination') == 'no' || $this->interface_helper->get_sort_meta($this->sortID, '_pagination') == '') { ?>style="display: none"<?php } ?>>
                                                            <br />
                                                            <h4><?php _e( "Offset Posts", 'apto' ) ?></h4>
                                                            <p class="description"><?php _e( "The offset number of posts", 'apto' ) ?></p>
                                                            <input type="text" value="<?php echo $this->interface_helper->get_sort_meta($this->sortID, '_pagination_offset_posts'); ?>" class="text" name="interface[_pagination_offset_posts]">
                                                        </fieldset>
                                                    </td></tr>
                                                    <?php } ?>
                                                    
                                                    <?php if(defined('ICL_LANGUAGE_CODE') && defined('ICL_SITEPRESS_VERSION') && $this->sortID !=  '' && $this->interface_helper->get_is_hierarhical_by_settings($this->sortID) === FALSE) { ?>
                                                    <tr id="_wpml_synchronize" class="visibility_related_option_pagination_invert" <?php if($this->interface_helper->get_sort_meta($this->sortID, '_pagination') == 'yes') { ?>style="display: none"<?php } ?>><td>
                                                        <h4><?php _e( "WPML Synchronize", 'apto' ) ?></h4>
                                                        <p class="description"><?php _e( "Synchronize the order across all other languages. This will overwrite other languages sorting. For this to work, all other languages must contain the same translated objects.", 'apto' ) ?></p>
                                                        <fieldset>
                                                            <label><input type="radio" <?php if($this->interface_helper->get_sort_meta($this->sortID, '_wpml_synchronize') == 'yes') { ?>checked="checked"<?php } ?> value="yes" name="interface[_wpml_synchronize]"> <span><?php _e( "Yes", 'apto' ) ?></span></label><br>
                                                            <label><input type="radio" <?php if($this->interface_helper->get_sort_meta($this->sortID, '_wpml_synchronize') == 'no' || $this->interface_helper->get_sort_meta($this->sortID, '_wpml_synchronize') == '') { ?>checked="checked"<?php } ?> value="no" name="interface[_wpml_synchronize]"> <span><?php _e( "No", 'apto' ) ?></span></label><br>
                                                        </fieldset>
                                                    </td></tr>
                                                    <?php } ?>
                                                    
                                                    <tr><td>
                                                        <h4><?php _e( "Capability / Role", 'apto' ) ?></h4>
                                                        <p class="description"><?php _e( "Minimum Capability to see this Sort", 'apto' ) ?></p>
                                                        <select name="interface[_capability]" class="select">
                                                            <?php
                                                            
                                                                $roles_capability = $this->functions->roles_capabilities();
                                                            
                                                                foreach($roles_capability as $role_default_name => $role_info)
                                                                    {
                                                                        ?><option value="<?php echo $role_info['capability'] ?>" <?php 
                                                                            if (
                                                                                    ($this->interface_helper->get_sort_meta($this->sortID, '_capability') == $role_info['capability']) ||
                                                                                    //make default select for Administrator when no capability was previously set
                                                                                    ($this->interface_helper->get_sort_meta($this->sortID, '_capability') == '' & $role_info['capability'] == 'manage_options')
                                                                                )
                                                                                echo 'selected="selected"';
                                                                            
                                                                        ?>><?php echo $role_info['title'] ?></option><?php
                                                                    }
                                                            
                                                            ?>
                                                        </select>
                                                    </td></tr>
               
                                                    <tr class="setting_status"><td>
                                                        <h4><?php _e( "Status", 'apto' ) ?></h4>
                                                        <p class="description"><?php _e( "Show objects status", 'apto' ) ?></p>
                                                        <?php
                                                        
                                                            foreach($this->sort_settings['_status'] as  $status   => $data)
                                                                {
                                                                    ?> <label for="_status_<?php echo $status ?>"><input type="checkbox" <?php  if($data['status']  ==  'show') { checked('show', 'show');} ?> id="_status_<?php echo $status ?>" class="setting_status" name="interface[_status][]" value="<?php echo $status ?>" /><?php echo $data['label'] ?></label> &nbsp;&nbsp;<?php   
                                                                }
                                                        
                                                        ?>
                                                    </td></tr>
                                                    
                                                </tbody>
                                            </table>
                                                
                                        </td>
                                    </tr>
                                    
                                    <tr class="submit">
                                        <td class="label">&nbsp;</td>
                                        <td>
                                            <input type="submit" class="save-sort-options button-primary alignright" value="<?php
                                            
                                                if($this->sortID > 0)
                                                    echo __( "Settings Update", 'apto' );
                                                    else
                                                    echo __( "Create", 'apto' );
                                            
                                            ?>" /> 
                                            <?php if($this->sortID != '') { ?><a href="<?php 
                                                
                                                $link_argv                          =   array();
                                                $link_argv['page']                  =   'apto_' . $this->interface_helper->get_menu_slug_from_menu_id($this->interface_helper->get_current_menu_location());
                                                $link_argv['base_url']              =   $this->interface_helper->get_current_menu_location();
                                                $link_argv['delete_sort']           =   1;
                                                $link_argv['sort_id']               =   $this->sortID;
                                                $link_argv['_wpnonce']              =   wp_create_nonce( 'APTO/sort-delete' );
                                                echo $this->interface_helper->get_tab_link($link_argv);
                                                
                                            ?>" onClick="return APTO.sort_list_delete(this)" class="submitdelete deletion"><?php _e( "Delete Sort", 'apto' ) ?></a> <?php } ?>
                                        </td>    
                                    </tr>
                                    
                                </tbody>
                            </table>
                        </div>
                    </div>
                    </div>
                    
                    </form>
                    
                    <?php
            
            }    
            
        
        function sort_description()
            {
                ?>
                    <div id="sort_description">
                        <?php echo wpautop($this->sort_settings['_description']) ?>
                    </div>
                    
                <?php
            }
        
        function sort_area() 
            {
                global $post; 
                
                //show Archive and Taxonomy (if need)
                $view_type  =    $this->interface_helper->get_sort_view_type($this->sortID);
                $sort_taxonomies = $this->interface_helper->get_sort_taxonomies_by_objects($this->sortID);
                $show_sort_taxonomy_selection = apply_filters('apto/admin/sort-taxonomy-selection', TRUE, $this->sortID);
                if($view_type == 'multiple' && count($sort_taxonomies) > 0  && $show_sort_taxonomy_selection !== FALSE)
                    {
                        //show the hint arrow only if the there are 
                        $this->sort_hint_arrow();
                
                        $this->sort_area_archive_taxonomies();
                    }
                                           
                ?>
                
                <div id="ajax-response"></div> 
                
                <h2 id="apto-nav-tab-wrapper" class="nav-tab-wrapper">
                <?php
                                                                   
                    //output the automatic / manual order tabs menu
                    $tabs = array(
                                    'auto'      =>  __('Automatic Order' , 'apto' ),
                                    'manual'    =>  __('Manual Order' , 'apto' )
                                    );
                                    
                    $tabs   = apply_filters('apto/admin/sort-order-tabs', $tabs, $this->current_sort_view_ID);
                    
                    foreach($tabs as $key => $tab)
                        {
                            ?>
                                <a class="nav-tab<?php if($this->current_sort_view_settings['_order_type'] == $key) { echo ' nav-tab-active';} ?>" href="<?php 
                                
                                $link_argv  =   array(
                                                        'sort_id'       =>  $this->sortID,
                                                        'order_type'    =>  $key,
                                                        'sort_view_id'  =>  $this->current_sort_view_ID
                                                        );
                                
                                if($this->is_shortcode_interface === FALSE)
                                    {
                                        $link_argv['page'] =   'apto_' . $this->interface_helper->get_current_menu_location_slug();
                                        echo $this->interface_helper->get_tab_link($link_argv) ;
                                    }
                                    else
                                    {
                                        $link_argv['base_url']      =   get_permalink($post->ID);
                                        echo $this->interface_helper->get_item_link($link_argv) ;   
                                    }

                                ?>"><?php echo $tab  ?></a>
                            <?php
                        }
               ?>    
               </h2>
               
               <?php
                            
                    //output the re-order interface list
                    if($this->current_sort_view_settings['_order_type'] == 'auto')
                        $this->automatic_interface();
                    
                    if($this->current_sort_view_settings['_order_type'] == 'manual')
                        $this->manual_interface();

            }

        
        function sort_hint_arrow()
            {
                ?>
                    <div id="hint_arrow">
                        <span id="arrow">&nbsp;</span>
                        <p><?php _e( "Select area and customise your order list <br />or switch to automatic", 'apto' ) ?></p>
                        <div class="clear"></div>
                    </div>
                <?php
            }
            
    
        /**
        * Output Archive and Taxonomies for current sort id
        * 
        */
        function sort_area_archive_taxonomies()
            {
                global $wpdb, $post;

                //check the taxonomies.
                $sort_taxonomies = $this->interface_helper->get_sort_taxonomies_by_objects($this->sortID);

                if($this->interface_hide_archive !== TRUE)
                    {
                ?>
                <table cellspacing="0" class="wp-list-taxonomy widefat fixed">
                    <thead>
                    <tr>
                        <th style="" class="column-cb check-column" scope="col">&nbsp;</th>
                        <th style="" class="" scope="col"><?php _e( "Archive", 'apto' ) ?></th><th style="" class="manage-column" scope="col"><?php _e( "Total Archive Objects", 'apto' ) ?></th>    
                    </tr>
                    </thead>
                    <tr valign="top" class="alternate">
                            <th class="check-column" scope="row">
                                <input type="radio" onclick="APTO.change_view_selection(this)" value="<?php
                                
                                    $link_argv  =   array(
                                                            'sort_id'           =>  $this->sortID,
                                                            'view_selection'    =>  'archive'
                                                            );
                                    
                                    if($this->is_shortcode_interface === FALSE)
                                        {
                                            $link_argv['page'] =   'apto_' . $this->interface_helper->get_current_menu_location_slug();
                                            echo $this->interface_helper->get_tab_link($link_argv) ;
                                        }
                                        else
                                        {
                                            $link_argv['base_url']      =   get_permalink($post->ID);
                                            echo $this->interface_helper->get_item_link($link_argv) ;   
                                        }
                                
                                
                                ?>" <?php if ($this->current_sort_view_settings['_view_selection'] == 'archive' && !isset($_GET['bbpress_forum'])) {echo 'checked="checked"';} ?> name="view_selection">
                            </th>
                            <td class="categories column-categories"><?php _e( "Archive", 'apto' ) ?></td>
                            <td class="categories column-categories"><?php 
                                
                                $count = 0;
                                foreach($this->sort_settings['_rules']['post_type'] as $post_type)
                                    {
                                        $post_type_count    =   (array)wp_count_posts($post_type);
                                        
                                        //unset the auto-draft
                                        if(isset($post_type_count['auto-draft']))
                                            unset($post_type_count['auto-draft']);
                                            
                                        //unset the trash
                                        if(isset($post_type_count['trash']))
                                            unset($post_type_count['trash']);
                                           
                                        $count += array_sum($post_type_count);
                                    }
                                    
                                echo $count;
                                
                                ?></td>
                    </tr>
                </tbody>
                </table>
                <?php  } ?>
                    
                <table cellspacing="0" class="wp-list-taxonomy widefat fixed">
                    <thead>
                    <tr>
                        <th style="" class="column-cb check-column" scope="col">&nbsp;</th><th style="" class="" scope="col"><?php _e( "Taxonomy Title", 'apto' ) ?></th><th style="" class="manage-column" scope="col"><?php _e( "Total", 'apto' ) ?> <?php _e( "Posts", 'apto' ) ?></th>    </tr>
                    </thead>
             
                    <tbody id="the-list">
                    <?php
                        
                        $alternate = FALSE;
                        
                        foreach ($sort_taxonomies as $key => $taxonomy)
                            {
                                $alternate = $alternate === TRUE ? FALSE :TRUE;
                                $taxonomy_info = get_taxonomy($taxonomy);
                                
                                $args   =   array(
                                                    'fields'        =>  'ids',
                                                    'hide_empty'    =>  false,
                                                    );
                                $taxonomy_terms_ids = get_terms($taxonomy, $args);

                                if (count($taxonomy_terms_ids) > 0)
                                    {
                                        $term_ids = array_map('intval', $taxonomy_terms_ids );
                                                                                                      
                                        $term_ids = "'" . implode( "', '", $term_ids ) . "'";
                                                                                                                 
                                        $query = "SELECT COUNT(DISTINCT tr.object_id) as count FROM $wpdb->term_relationships AS tr 
                                                        INNER JOIN $wpdb->term_taxonomy AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id 
                                                        INNER JOIN $wpdb->posts as posts ON tr.object_id = posts.ID
                                                        WHERE tt.taxonomy IN ('$taxonomy') AND tt.term_id IN ($term_ids) AND  posts.post_type IN ('". implode("', '", $this->sort_settings['_rules']['post_type'])   ."') AND posts.post_status NOT IN('auto-draft', 'trash')" ;
                                        $count = $wpdb->get_var($query);
                                    }
                                    else
                                        {
                                            $count = 0;   
                                        }
                                
                                ?>
                                    <tr valign="top" class="<?php if ($alternate === TRUE) {echo 'alternate ';} ?>" id="taxonomy-<?php echo $taxonomy  ?>">
                                            <th class="check-column" scope="row"><input type="radio" onclick="APTO.change_view_selection(this)" value="<?php
                                
                                                $link_argv  =   array(
                                                                        'sort_id'           =>  $this->sortID,
                                                                        'view_selection'    =>  'taxonomy',
                                                                        'taxonomy'          =>  $taxonomy
                                                                        );
                                                
                                                if($this->is_shortcode_interface === FALSE)
                                                    {
                                                        $link_argv['page'] =   'apto_' . $this->interface_helper->get_current_menu_location_slug();
                                                        echo $this->interface_helper->get_tab_link($link_argv) ;
                                                    }
                                                    else
                                                    {
                                                        $link_argv['base_url']      =   get_permalink($post->ID);
                                                        echo $this->interface_helper->get_item_link($link_argv) ;   
                                                    }
                                                
                                            
                                            ?>" <?php if ($this->current_sort_view_settings['_view_selection'] == 'taxonomy' && $this->current_sort_view_settings['_taxonomy'] == $taxonomy) {echo 'checked="checked"';} ?> name="view_selection">&nbsp;</th>
                                            <td class="categories column-categories"><p><span><?php echo $taxonomy_info->label ?></span>
                                            
                                                <?php
                                                    if ($this->current_sort_view_settings['_taxonomy'] == $taxonomy)
                                                        {
                                                            //check if there are any terms in that taxonomy before ouptut the dropdown
                                                            $argv = array(
                                                                            'hide_empty'    =>   0
                                                                            );
                                                            $terms = get_terms($this->current_sort_view_settings['_taxonomy'], $argv);
                                                            
                                                            $dropdown_options = array(
                                                                                        'echo'              =>  0,
                                                                                        'hide_empty'        =>  0, 
                                                                                        'hierarchical'      =>  1,
                                                                                        'show_count'        =>  1, 
                                                                                        'orderby'           =>  'name', 
                                                                                        'taxonomy'          =>  $this->current_sort_view_settings['_taxonomy'],
                                                                                        'selected'          =>  $this->current_sort_view_settings['_term_id'],
                                                                                        'class'             =>  'taxonomy_terms',
                                                                                        'walker'            =>  new APTO_Walker_TaxonomiesTermsDropdownCategories(),
                                                                                        'sortID'            =>  $this->sortID,
                                                                                        'apto_interface'    =>  $this
                                                                                        );
                                                            
                                                            if (count($terms) > 0)
                                                                {
                                                                    $select_html = wp_dropdown_categories($dropdown_options);
                                                                    if(!empty($select_html))
                                                                        {
                                                                            $select_html = str_replace("<select ", "<select onchange='APTO.change_view_selection(this)' ", $select_html);
                                                                            echo $select_html;   
                                                                        }
                                                                    
                                                                    $found_action = TRUE;
                                                                }

                                                        } ?></p></td>
                                            <td class="categories column-categories"><?php echo $count ?></td>
                                    </tr>
                                
                                <?php
                            }
                    ?>
                    </tbody>
                </table>
                
                <?php
                
                $sort_taxonomies = $this->interface_helper->get_sort_taxonomies_by_objects($this->sortID);

                if( $this->functions->is_BBPress_topic_simple($this->sortID) === TRUE)
                    {
                
                ?>
                <table cellspacing="0" class="wp-list-taxonomy widefat fixed">
                    <thead>
                    <tr>
                        <th style="" class="column-cb check-column" scope="col">&nbsp;</th><th style="" class="" scope="col"><?php _e( "Forum Title", 'apto' ) ?></th><th style="" class="manage-column" scope="col"><?php _e( "Total Topics", 'apto' ) ?> <?php _e( "Posts", 'apto' ) ?></th>    </tr>
                    </thead>
             

                    <tbody id="the-list">
                    <?php
                        
                        $alternate = FALSE;
                        
                        //get forum posts
                        $argv =     array(
                                            'posts_per_page'    =>  -1,
                                            'post_type'         =>  'forum',
                                            'orderby'           =>  'menu_order',
                                            'order'             =>  'ASC',
                                            );
                        $custom_query       =   new WP_Query($argv);
                        while($custom_query->have_posts())
                            {
                                $custom_query->the_post(); 
                                
                                $alternate = $alternate === TRUE ? FALSE :TRUE;  
                                
                                ?>
                                    <tr valign="top" class="<?php if ($alternate === TRUE) {echo 'alternate ';} ?>" id="forum-id-<?php echo $post->ID  ?>">
                                            <th class="check-column" scope="row"><input type="radio" onclick="APTO.change_view_selection(this)" value="<?php
                                
                                                $link_argv  =   array(
                                                            'sort_id'           =>  $this->sortID,
                                                            'view_selection'    =>  'archive',
                                                            'bbpress_forum'     =>  $post->ID
                                                            );
                                    
                                                if($this->is_shortcode_interface === FALSE)
                                                    {
                                                        $link_argv['page'] =   'apto_' . $this->interface_helper->get_current_menu_location_slug();
                                                        echo $this->interface_helper->get_tab_link($link_argv) ;
                                                    }
                                                    else
                                                    {
                                                        $link_argv['base_url']      =   get_permalink($post->ID);
                                                        echo $this->interface_helper->get_item_link($link_argv) ;   
                                                    }
                                                
                                            
                                            ?>" <?php if ($this->current_sort_view_settings['_view_selection'] == 'archive' && isset($_GET['bbpress_forum']) && $_GET['bbpress_forum'] == $post->ID) {echo 'checked="checked"';} ?> name="view_selection">&nbsp;</th>
                                            <td class="categories column-categories"><p><span><?php echo $post->post_title ?></span></p></td>
                                            <td class="categories column-categories"><?php 
                                                
                                                $argv =     array(
                                                                    'posts_per_page'    =>  -1,
                                                                    'post_type'         =>  'topic',
                                                                    'orderby'           =>  'menu_order',
                                                                    'order'             =>  'ASC',
                                                                    'fields'            =>  'ids',
                                                                    'ignore_supress_filters'    =>  TRUE,
                                                                    'post_parent'            =>   $post->ID,
                                                                    'post_status'       =>  'any'
                                                                    );
                                                $forum_custom_query       =   new WP_Query($argv);

                                                echo $forum_custom_query->found_posts; 
                                                    
                                                
                                            ?></td>
                                    </tr>
                                
                                <?php
                            }

                    ?>
                    </tbody>
                </table>
                
                <?php
                
                    }
                    
                ?>
                
                
                <div class="spacer">&nbsp;</div>
                <?php

            }
            
        function automatic_interface()
            {
                global $wpdb, $post;
                ?>
                <form action="<?php 
                    
                         $link_argv  =   array(
                                                'sort_id'       =>  $this->sortID,
                                                );
                        
                        if($this->is_shortcode_interface === FALSE)
                            {
                                $link_argv['page'] =   'apto_' . $this->interface_helper->get_current_menu_location_slug();
                                echo $this->interface_helper->get_tab_link($link_argv) ;
                            }
                            else
                            {
                                $link_argv['base_url']      =   get_permalink($post->ID);
                                echo $this->interface_helper->get_item_link($link_argv) ;   
                            }
                        
                        
                        
                    ?>" method="post" id="apto_form_order">
                    
                    <input type="hidden"  name="sort_id" value="<?php echo $this->sortID ?>" id="sort_id" />
                    <input type="hidden" value="<?php echo $this->current_sort_view_ID ?>" name="sort_view_ID" />  
                    <input type="hidden"  name="apto_sort_form_order_update" value="1" />
                    
                    
                    <div id="order-post-type">

                                        
                        <div class="postbox apto_metabox">         
                            <div class="inside">
                                
                                <table class="apto_input widefat apto_table" id="apto_settings">
                                    <tbody>
                                        
                                        <?php 
                                            
                                            $data_set = array(
                                                                'order_by'              =>  (array)$this->current_sort_view_settings['_auto_order_by'],
                                                                'custom_field_name'     =>  (array)$this->current_sort_view_settings['_auto_custom_field_name'],
                                                                'custom_field_type'     =>  (array)$this->current_sort_view_settings['_auto_custom_field_type'],
                                                                'custom_function_name'  =>  (array)$this->current_sort_view_settings['_auto_custom_function_name'],
                                                                'order'                 =>  (array)$this->current_sort_view_settings['_auto_order']
                                                                );
                                                                                        
                                            foreach($data_set['order_by']   as $key =>  $data)
                                                {
                                        
                                                    $options    =   array(
                                                                            'default'           =>  ($key < 1) ? TRUE : FALSE,
                                                                            'group_id'          =>  ($key +  1)
                                                                            );
                                                                                                            
                                                    if(!isset($data_set['order_by'][$key]) || $data_set['order_by'][$key] == '')
                                                        {
                                                            $options['data_set']    =   array(
                                                                                                    'order_by'              =>  '_default_',
                                                                                                    'custom_field_name'     =>  '',
                                                                                                    'custom_field_type'     =>  '',
                                                                                                    'custom_function_name'  =>  '',
                                                                                                    'order'                 =>  'DESC'
                                                                                                );
                                                        }
                                                        else
                                                            {
                                                                $options['data_set']    =   array(
                                                                                                            'order_by'              =>  $data_set['order_by'][$key],
                                                                                                            'custom_field_name'     =>  $data_set['custom_field_name'][$key],
                                                                                                            'custom_field_type'     =>  $data_set['custom_field_type'][$key],
                                                                                                            'custom_function_name'  =>  $data_set['custom_function_name'][$key],
                                                                                                            'order'                 =>  $data_set['order'][$key],
                                                                                                            );
                                                            }

                                                    echo $this->interface_helper->html_automatic_add_falback_order($options);
                                                }
                                        ?>
                                    
                                      
                                        
                                        <tr id="automatic_insert_mark">
                                            <td class="label">&nbsp;</td>
                                            <td>
                                                <a onclick="APTO.AddFallBackAutomaticOrder()" href="javascript: void(0)" class="button-secondary"><?php _e( "Add Fallback", 'apto' ) ?></a> &nbsp;&nbsp;<img class="ajax_loading" src="<?php echo APTO_URL ?>/images/ajax-loader.gif" alt="Loading" />
                                            </td>    
                                        </tr>
                                        
                                        <?php 
                            
                                            $view_type  =    $this->interface_helper->get_sort_view_type($this->sortID);
                                            if($view_type == 'multiple' && $this->current_sort_view_settings['_view_selection'] != 'archive')
                                            {
                                        
                                        ?>
                                                                                
                                        <tr>
                                            <td class="label">&nbsp;</td>
                                            <td>
                                                &nbsp;
                                            </td>
                                            <td>
                                                &nbsp;
                                            </td>    
                                        </tr>
                                        
                                        <tr>
                                            <td class="label">
                                                <label for=""><?php _e( "Batch Terms Automatic Update", 'apto' ) ?></label>
                                                <p class="description"><?php _e( "<b>WARNING!</b></i> using this option all existing", 'apto' ) ?> <?php 
                                                    
                                                    $current_taxonomy_info = get_taxonomy($this->current_sort_view_settings['_taxonomy']);
                                                    echo $current_taxonomy_info->label;
                                                    
                                                    ?> <?php _e( "terms order type will update to Automatic Order and change for currrent settings.", 'apto' ) ?> <?php _e( "Existing manual/custom sort lists will be kept, but order type will be switched to Automatic Order.", 'apto' ) ?></p>
                                            </td>
                                            <td>
                                                <input type="radio" checked="checked" value="no" name="batch_order_update" />
                                                <label for="blog-public"><?php _e( "No", 'apto' ) ?></label><br>

                                                <input type="radio" value="yes" name="batch_order_update" />
                                                <label for="blog-public"><?php _e( "Yes", 'apto' ) ?></label><br>  

                                            </td> 
                                            <td>
                                                &nbsp;
                                            </td>  
                                        </tr>
                                        <?php } ?>
                                        <tr class="submit">
                                            <td class="label">&nbsp;</td>
                                            <td>
                                                <a id="send_to_manual" class="button-primary alignleft" href="Javascript:void(0);" onClick="APTO.automatic_order_Send_to_Manual(<?php echo $this->current_sort_view_ID ?>)">Send order to Manual Order List</a>
                                            </td>
                                            <td>
                                                <input type="submit" value="Update" class="button-primary" name="update">
                                                
                                            </td>    
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                            
                        
                    </div>
                    </form>
                    <script type="text/javascript">
                        APTO.automatic_order_Watch_input_change();
                    </script>                  
                    <?php
                   
            }
            
        function manual_interface()
            {
                global $wpdb, $wp_locale;
     
                $is_hierarchical = $this->interface_helper->get_is_hierarhical_by_settings($this->sortID);
                
                $filter_date        = isset($_POST['filter_date']) ? $_POST['filter_date'] : 0;
                $search             = isset($_POST['search']) ? sanitize_text_field(stripslashes($_POST['search'])) : '';
                                
                if (($is_hierarchical === TRUE || $this->functions->is_woocommerce($this->sortID) === TRUE) && $this->current_sort_view_settings['_view_selection'] == 'archive')
                    $hierarhical_sortable   =   TRUE;
                    else
                    $hierarhical_sortable   =   FALSE;
                    
                ?>
                    <form action="" method="post" id="apto_form_order">
                       
                        <div id="order-post-type">
                            
                            <div id="nav-menu-header">
                                <div class="major-publishing-actions">

                                    <?php do_action('APTO/re-order-interface/header-html', $this->current_sort_view_ID); ?>
                                
                                        <div class="alignleft actions"> 
                                        <?php
                                            
                                            $found_action = FALSE;
                                            
                                            ob_start();
                                                        
                                            if ($hierarhical_sortable)
                                                {
                                                }
                                                else
                                                {
                                        
                                                    $arc_query  = "SELECT DISTINCT YEAR(post_date) AS yyear, MONTH(post_date) AS mmonth FROM $wpdb->posts WHERE post_type IN ('". implode("', '", $this->sort_settings['_rules']['post_type'])   ."') ORDER BY post_date DESC";
                                                    $arc_result = $wpdb->get_results( $arc_query );

                                                    $month_count = count($arc_result);

                                                    if ( $month_count && !( 1 == $month_count && 0 == $arc_result[0]->mmonth ) ) 
                                                        {
                                                            ?>
                                                                <select name="filter_date">
                                                                    <option<?php selected( $filter_date, 0 ); ?> value='0'><?php _e('Show all dates'); ?></option>
                                                                    <option<?php selected( $filter_date, 'today' ); ?> value='today'><?php _e('Today'); ?></option>
                                                                    <option<?php selected( $filter_date, 'yesterday' ); ?> value='yesterday'><?php _e('Yesterday'); ?></option>
                                                                    <option<?php selected( $filter_date, 'last_week' ); ?> value='last_week'><?php _e('Last Week'); ?></option>
                                                                    <?php
                                                                        foreach ($arc_result as $arc_row) 
                                                                            {
                                                                                if ( $arc_row->yyear == 0 )
                                                                                    continue;
                                                                                    
                                                                                $arc_row->mmonth = zeroise( $arc_row->mmonth, 2 );

                                                                                if ( $arc_row->yyear . $arc_row->mmonth == $filter_date )
                                                                                    $default = 'selected="selected"';
                                                                                else
                                                                                    $default = '';

                                                                                echo "<option $default value='" . esc_attr("$arc_row->yyear$arc_row->mmonth") . "'>" . $wp_locale->get_month($arc_row->mmonth) . " ". $arc_row->yyear . "</option>\n";
                                                                            }
                                                                    ?>
                                                                </select>
                                                            <?php 
                                                            
                                                        }

                                                }
                                            
                                                                                            
                                            $filter_html   =    ob_get_contents();
                                            ob_end_clean();
                                            
                                            $filter_html    =   apply_filters('apto/wp-admin/reorder-interface/filter-area-html', $filter_html, $this);
                                        
                                            if($filter_html != '')
                                                {
                                                    $found_action = TRUE;
                                                    echo $filter_html;
                                                }
                                        
                                            if($found_action === TRUE)
                                                {
                                                    ?>
                                                     <input type="submit" class="button-secondary" value="Filter" id="post-query-submit">
                                            <?php } ?>
                                        </div>
                                        
                                        <div class="alignright actions">
                                            <p class="actions">
                                                
                                                <a class="button-secondary alignleft toggle_thumbnails" title="<?php _e( "Toggle Thumbnails", 'apto' ) ?>" href="javascript:;" onclick="APTO.toggle_thumbnails(); return false;"><?php _e( "Toggle Thumbnails", 'apto' ) ?></a>
                                                
                                                <?php if ($is_hierarchical === FALSE && $this->functions->is_woocommerce($this->sortID) === FALSE)
                                                    {
                                                        ?>
                                                        <input type="text" value="<?php echo htmlspecialchars($search); ?>" name="search" id="post-search-input" class="fl">
                                                        <input type="submit" class="button fl" value="Search">
                                                <?php  } ?>
                                                <span class="ajax_loading"><span class="progress"></span> <img alt="" src="<?php echo APTO_URL ?>/images/wpspin_light.gif" class="waiting pto_ajax_loading"></span>
                                                <a href="javascript:;" class="save-order button-primary"><?php _e( "Update", 'apto' ) ?></a>
                                            </p>
                                        </div>
                                        
                                        <div class="clear"></div>

                                </div><!-- END .major-publishing-actions -->
                            </div><!-- END #nav-menu-header -->

                                                    
                            <div id="post-body">                    
                                
                                <div id="sort_options">
                                    <a href="javascript: void(0)" onClick="APTO.interface_reverse_order()"><?php _e( "Reverse", 'apto' ) ?></a> <span>|</span>
                                    <a href="javascript: void(0)" onClick="APTO.interface_title_order('ASC')"><?php _e( "Title Asc", 'apto' ) ?></a> <span>|</span>
                                    <a href="javascript: void(0)" onClick="APTO.interface_title_order('DESC')"><?php _e( "Title Desc", 'apto' ) ?></a> <span>|</span>
                                    <a href="javascript: void(0)" onClick="APTO.interface_id_order('ASC')"><?php _e( "Id order Asc", 'apto' ) ?></a> <span>|</span>
                                    <a href="javascript: void(0)" onClick="APTO.interface_id_order('DESC')"><?php _e( "Id order Desc", 'apto' ) ?></a>
                                </div>
                                
                                <?php if (!$is_hierarchical ||  ($is_hierarchical   &&  $this->current_sort_view_settings['_view_selection'] != 'archive')) { ?>
                                <div id="sort_list_type" class="view-switch">
                                    <a class="view-list current" href="javascript: void(0)" onClick="APTO.ChangeViewType('view-list')" title="<?php _e( "List view", 'apto' ) ?>"><span class="screen-reader-text"><?php _e( "List view", 'apto' ) ?></span></a>
                                    <a class="view-grid" href="javascript: void(0)" onClick="APTO.ChangeViewType('view-grid')" title="<?php _e( "Grid view", 'apto' ) ?>"><span class="screen-reader-text" title="<?php _e( "Grid view", 'apto' ) ?>">title="<?php _e( "Grid view", 'apto' ) ?>"</span></a>
                                </div>
                                <?php } ?>
                                
                                <div class="clear"></div>
                                
                                <script type="text/javascript">    
                                
                                    var sort_id         = '<?php echo $this->sortID ?>';
                                    var sort_view_id    = '<?php echo $this->current_sort_view_ID ?>';

                                </script>
                               
                                <?php
                                
                                    $additional_query_string                    =   array();
                                    $additional_query_string['search']          =   $search;
                                    $additional_query_string['filter_date']     =   $filter_date;
                                    if ($hierarhical_sortable)
                                        {
                                        }
                                        else
                                        $additional_query_string['depth'] = '-1';
                                
                                
                                    //pagination active
                                    if($this->sort_settings['_pagination'] ==  'yes')
                                        {
                                            $additional_query_string['posts_per_page']  =   $this->sort_settings['_pagination_posts_per_page'];
                                            $additional_query_string['paged']           =   isset($_GET['list_paged']) ? intval($_GET['list_paged']) : 1;

                                        
                                            $pagination_args    =   array(
                                                                            'type'              =>  'top',
                                                                            'offset_objects'    =>  $this->sort_settings['_pagination_offset_posts'],
                                                                            'paged'             =>  $additional_query_string['paged'],
                                                                            'posts_per_page'    =>  $additional_query_string['posts_per_page'],
                                                                            'sort_view_ID'      =>  $this->current_sort_view_ID
                                                                            );
                                            $this->interface_helper->pagination_sortable_html($pagination_args, $additional_query_string);
                                        }
                                
                                
                                $html_list_type  =   apply_filters('apto/sort_interface/list_type_tag', 'ul');
                                
                                $additional_query_string['html_list_type']   =   $html_list_type;
                                
                                ?>
                                                               
                                <<?php echo $html_list_type; ?> class="view-list sortable-list<?php if($hierarhical_sortable) {echo ' hierarhical-list';} ?>" id="sortable"<?php
                            
                                            if ($hierarhical_sortable)
                                                {
                                                    ?> class="nested_sortable"<?php
                                                } ?>>
                                            
                                            <?php 
                                                $found_posts  =   $this->listPostTypeObjects($additional_query_string); 
                                            ?>
                                            
                                </<?php echo $html_list_type; ?>>
                                <?php
                                
                                    if($this->sort_settings['_pagination'] ==  'yes')
                                        {
                                            $pagination_args['type']    =   'bottom';
                                            $this->interface_helper->pagination_sortable_html($pagination_args, $additional_query_string);
                                        }
                                    
                                ?>
                                                       
                                <div class="clear"></div>
                            </div>
                            
                            <div id="nav-menu-footer">
                                <div class="major-publishing-actions">
                                        
                                        <?php do_action('APTO/re-order-interface/footer-html', $this->current_sort_view_ID); ?>
                                            
                                        <div class="alignright actions">
                                            <p class="submit">
                                                <img alt="" src="<?php echo APTO_URL ?>/images/wpspin_light.gif" class="waiting pto_ajax_loading" style="display: none;">
                                                <a href="javascript:;" class="save-order button-primary"><?php _e( "Update", 'apto' ) ?></a>
                                            </p>
                                        </div>
                                        
                                        <div class="clear"></div>

                                </div><!-- END .major-publishing-actions -->
                            </div><!-- END #nav-menu-header -->  
                            
                        </div> 

                        
                        <br />
                        <a id="order_Reset" class="button-primary" href="javascript: void(0)" onclick="confirmSubmit()"><?php _e( "Reset Order", 'apto' ) ?></a>
                        
                        <script type="text/javascript">
                            
                            function confirmSubmit()
                                {
                                    var agree=confirm("<?php _e( "Are you sure you want to reset the order??", 'apto' ) ?>");
                                    if (agree)
                                        {
                                            jQuery('#apto_form_order_reset').submit();   
                                        }
                                        else
                                        {
                                            return false ;
                                        }
                                }
                            
                            function APTO_AJAX_save_complete(response)
                                {
                                    APTO_AJAX_Current_Page  =   1;
                                    
                                    for (var prop in response.messages) 
                                        {
                                            jQuery("#ajax-response").append('<div class="message updated fade"><p>' + response.messages[prop] + '</p></div>');
                                        }

                                    if(typeof response.errors !== 'undefined'  &&   response.errors.length > 0)
                                        {
                                            for (var prop in response.errors) 
                                                {
                                                    jQuery("#ajax-response").append('<div class="message error fade"><p>' + response.errors[prop] + '</p></div>');
                                                }
                                        }
                                        
                                    jQuery("#ajax-response > div").delay(5000).hide("slow");
                                    jQuery('#order-post-type .ajax_loading').hide();
                                    
                                    jQuery('#order-post-type a.save-order').removeClass('disabled');   
                                    
                                }
                            
                            var APTO_AJAX_Pages =   <?php
                                
                                //never do ajax paged when pagination is turned off
                                if($this->sort_settings['_pagination'] ==  'yes')
                                    {
                                        //allow pagination for ajax order save to prevent server timouts
                                        echo ceil($found_posts /  APTO_AJAX_OBJECTS_PER_PAGE);
                                    }
                                    else
                                    echo 1;
                            
                            ?>;
                            var APTO_AJAX_Current_Page  =   1;
                            var APTO_AJAX_Query_String  =   {};
                            
                            jQuery(document).ready(function() {
                                 
                                //jQuery( "#sortable" ).sortable();
                                jQuery('#sortable, #sortable_top, #sortable_bottom').nestedSortable({
                                        handle:             'div',
                                        tabSize:            30,
                                        listType:           '<?php echo $html_list_type ?>',
                                        items:              'li',
                                        toleranceElement:   '> div',
                                        placeholder:        'ui-sortable-placeholder',
                                        disableNesting:     'no-nesting',
                                        connectWith: ".sortable-list"
                    
                                        <?php
                                     
                                                    ?>
                                                    ,helper: function (event, item) {

                                                        if (!item.hasClass('multi-select')) 
                                                            {
                                                                item.addClass('multi-select').siblings().removeClass('multi-select');
                                                            }
                                                        
                                                        var elements = item.parent().children('.multi-select').clone();

                                                        item.data('multidrag', elements).siblings('.multi-select').remove();

                                                        var helper = jQuery('<li/>');
                                                        return helper.append(elements);
                                                    },
                                                    stop: function (event, ui) {

                                                        var elements = ui.item.data('multidrag');

                                                        jQuery(elements).removeClass('multi-select');
                                                        ui.item.after(elements).remove();
                                                    }
                                                    <?php
                                                    
                                                    if (($is_hierarchical === TRUE || $this->functions->is_woocommerce($this->sortID) === TRUE) && $this->current_sort_view_settings['_view_selection'] == 'archive')
                                                        {
                                                        }
                                                        else
                                                        {
                                                            ?>
                                                                ,disableNesting      :true<?php  
                                                        }
                                    
                                        ?>
                                    });
                                
                                  
                                jQuery(".save-order").bind( "click", function() {
                                    
                                    //check if in progress
                                    if(jQuery(this).hasClass('disabled'))
                                        return;
                                    
                                    jQuery('#order-post-type a.save-order').addClass('disabled');
                                    jQuery('#order-post-type .ajax_loading').show();
                                    
                                    APTO_AJAX_Query_String = { 
                                                            action:         'update-custom-type-order', 
                                                            order_list:          jQuery("#sortable").nestedSortable("serialize"),<?php
                                
                                                                if($this->sort_settings['_pagination'] ==  'yes')
                                                                {   ?>
                                                            page:          <?php echo $additional_query_string['paged'] ?>, 
                                                            <?php } ?>
                                                            sort_view_id:        sort_view_id, 
                                                            nonce:          '<?php echo wp_create_nonce( 'reorder-interface-' . get_current_user_id()) ?>'
                                                                };
                                                                
                                    if(jQuery("#sortable_top").length > 0)                            
                                        APTO_AJAX_Query_String.order_offset_top    =   jQuery("#sortable_top").nestedSortable("serialize");
                                    
                                    if(jQuery("#sortable_bottom").length > 0)                            
                                        APTO_AJAX_Query_String.order_offset_bottom    =   jQuery("#sortable_bottom").nestedSortable("serialize");
                                        
                                    APTO_AJAX_Query_String.ajax_total_pages     =   APTO_AJAX_Pages;
                                    
                                    APTO_AJAX_Query_String.is_search     =   <?php  echo (empty($search) ?  'false'   :   'true')    ?>;
                                                                
                                    APTO.SavePaginatedSort(APTO_AJAX_Current_Page); 
                                    });
                                });
                            
                        </script>
                        </form>  
    
                        <form action="" method="post" id="apto_form_order_reset">
                            <input type="hidden" name="order_reset" value="true" />
                            <input type="hidden" value="<?php echo $this->current_sort_view_ID ?>" name="sort_view_ID" /> 
                            
                            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce( 'reorder-interface-reset-' . get_current_user_id()) ?>" />
                        </form>

                        <?php
            }
    
        function listPostTypeObjects($args = '') 
            {
                $args   =   $this->interface_helper->get_interface_query_arguments($this->current_sort_view_ID, $args);
                $args   =   apply_filters('apto/interface_query_args', $args, $this->current_sort_view_ID);
                                                
                $custom_query = new WP_Query($args);
                $found_posts = $custom_query->posts;
                $total_posts = $custom_query->found_posts;
                
                if ( !empty($found_posts) ) 
                    {
                        $walker = new Post_Types_Order_Walker;

                        $walker_args = array($found_posts, $args['depth'], $args);
                        echo call_user_func_array(array(&$walker, 'walk'), $walker_args);
                    }
                    
                return  $total_posts; 

            }

    }





?>
