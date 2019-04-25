<?php
GFForms::include_addon_framework();

class StylesPro extends GFAddOn {

    protected $_version = GF_STYLES_PRO_ADDON_VERSION;
    protected $_min_gravityforms_version = '2.0';
    protected $_slug = 'gf_stylespro';
    protected $_path = 'gfstylespro/gfstylespro.php'; 
    protected $_full_path = __FILE__;
    protected $_title = 'Gravity Forms Styles Pro';
    protected $_short_title = 'Styles Pro';

    private static $_instance = null;

    private $_iconsets = array();

    private $_theme;

    public static function get_instance() {
        if ( self::$_instance == null ) {
            self::$_instance = new StylesPro();
        }

        return self::$_instance;
    }

    /**
    * Members plugin integration
    */
    protected $_capabilities = array(
        'gravityforms_stylespro',
        'gravityforms_stylespro_uninstall',
        'gravityforms_stylespro_settings',
    );

    /**
    * Permissions
    */
    protected $_capabilities_settings_page = 'gravityforms_stylespro_settings';
    protected $_capabilities_form_settings =  'gravityforms_stylespro';
    protected $_capabilities_plugin_page = 'gravityforms_stylespro';
    protected $_capabilities_uninstall = 'gravityforms_stylespro_uninstall';

    
    public function pre_init() {
        parent::pre_init();
        // Tasks or filters to perform during the class constructor - before WordPress has been completely initialized
    }

    public function init() {
        parent::init();
        // Tasks or filters to perform both in the backend and frontend and for ajax requests


        /*
        * Front-end
        * Add Ornaments to fields: Checkbox, Radio, Polls and Quiz.
        */
        add_filter( 'gform_field_choice_markup_pre_render', function ( $choice_markup, $choice, $field, $value ) {

            $field_type = $field->get_input_type();

            // Return if not a checkbox or radio field
            if ( $field_type != 'radio' && $field_type != 'checkbox' && $field_type != 'poll' && $field_type != 'quiz' )
                return $choice_markup;

            // Return if no value is set
            $gf_ornament = rgar( $choice, 'spOrnament' );

            if ( $gf_ornament == '' )
                return $choice_markup;

            /* Array indices:
            * 0    Type
            * 1    Data
            * 2    Iconset
            * 3    Color
            */
            $gf_icn_img = explode('|', $gf_ornament);
            
            // If icon
            if ( $gf_icn_img[0] == "icn" ) {
                // if has color
                $color = '';
                if ( isset($gf_icn_img[3]) )
                    $color = " style='color:" . $gf_icn_img[3] . "'";
                
                $ornamant = "<i{$color} class='" . $gf_icn_img[1] . "'></i>";
            }

            // If image
            if ( $gf_icn_img[0] == "img" ) {
                $ornamant = "<div class='o_img_wr'><img class='gfsp_label_img' src='" . $gf_icn_img[1] . "' /></div>";
            }
            // Add class to the markup
            $update_markup = str_replace( "<li class='", "<li class='gfsp_choice_". $gf_icn_img[0] . " ", $choice_markup );
            
            // Check ornament position, add markup
            if ( strpos($field['gfStylesPro'], 'o_after') ) {
                // check if choice label is empty
                if ($choice['text'] != '') {
                    $update_markup = str_replace( ">{$choice['text']}<", "><div class='o_label'><div class='o_text'>{$choice['text']}</div>{$ornamant}</div><", $update_markup );
                } else {
                    $update_markup = str_replace( "></label>", "><div class='o_label'><div class='o_text'>{$choice['text']}</div>{$ornamant}</div></label>", $update_markup );
                }
            }
            else {
                // check if choice label is empty
                if ($choice['text'] != '') {
                    $update_markup = str_replace( ">{$choice['text']}<", "><div class='o_label'>{$ornamant}<div class='o_text'>{$choice['text']}</div></div><", $update_markup );
                } else {
                    $update_markup = str_replace( "></label>", "><div class='o_label'>{$ornamant}<div class='o_text'>{$choice['text']}</div></div></label>", $update_markup );            
                }
            }

            return $update_markup;    
            
        }, 500, 4 );

    }



    public function init_admin() {
        parent::init_admin();
        // Tasks or filters to perform only in admin
        add_filter( 'gform_form_settings', array( $this, 'gf_stylespro_theme_select' ), 10, 2 );
        add_filter( 'gform_pre_form_settings_save', array( $this, 'save_gf_stylespro_form_settings' ) );

        // Ornamens for choices fields
        add_action( 'gform_field_standard_settings', array( $this, 'gf_sylespro_list_ornaments_settings' ), 10, 2 );

        // Register scripts and scripts for no-conflict mode
        add_filter( 'gform_noconflict_styles', 'register_style' );
        add_filter( 'gform_noconflict_scripts', 'register_script' );

        add_action( 'gform_field_appearance_settings', array( $this, 'gf_stylespro_appearance_settings' ), 10, 2 );
        add_action( 'gform_field_appearance_settings', array( $this, 'gf_stylespro_appearance_settings_icon'), 10, 2 );
        add_action( 'gform_field_advanced_settings', array( $this, 'gf_stylespro_add_field_size_option'), 10, 2 );


        function register_script( $scripts ) {
            // Add WP media manager scripts
            array_push ( $scripts, 
                'media-editor',
                'media-views',
                'media-audiovideo',
                'mce-view',
                'image-edit' );

            return $scripts;
        }


        function register_style( $styles ) {
            // Add WP media manager styles
            array_push( $styles,
                'media-views',
                'imgareaselect' );
            
            return $styles;
        }


        // Enque WP Media Library
        add_action( 'admin_enqueue_scripts', 'wp_enqueue_media');
        
        // Saperately load WP Color Picker to avoid conflicts
        add_action( 'admin_enqueue_scripts', function() {
            wp_enqueue_script( 'wp-color-picker' );
        } );

    }
    



    /**
     * Creates a custom page for Styles Pro add-on
     */
    public function plugin_page() {
        ?>
        <div class="gforms_box"><h3>Go to</h3>
            <ul style="list-style-type:disc; margin-top: 0em; margin-left: 2em; font-size: 1.2em;">
                <li><a class="stylespro_btn" href="<?php echo esc_url( admin_url( 'admin.php?page=gf_settings&subview=gf_stylespro' ) ) ?>"><strong>Styles Pro Settings:</strong> Customize themes</a></li>
            </ul>
        </div>
        <div class="gforms_helpbox gforms_box"><h3>Help</h3>
            <ul class="resource_list" style="list-style-type:disc; margin-top: 0em; margin-left: 1em;">
                <li><a href="http://gravitystylespro.com/docs/" target="_blank">Help: Using Gravity Forms Styles Pro</a></li>
                <li><a href="http://gravitystylespro.com/faq/" target="_blank">FAQ</a></li>
                <li><a href="http://gravitystylespro.com/themes/custom/" target="_blank">Order a custom theme</a></li>
                <li><a href="http://gravitystylespro.com/ideas/" target="_blank">Have some ideas about new features or improvements? Share with us, we're all ears!</a></li>
                <li><a href="http://gravitystylespro.com/support/" target="_blank" style="color: #38bc31;">Get support</a></li>
            </ul>
        </div>
        <style>
            .gforms_box li a {border: 1px solid silver;display: block;width: 270px;padding: 5px 10px;text-decoration: none;background: white;color: #36a5e0;}
            .gforms_box li { list-style: none; }
            .gforms_box li a.stylespro_btn {width: 278px; border: 1px solid white; box-shadow: 2px 2px 0px, 3px 3px 0px; text-align: center; border: 1px solid}
            .gforms_box a.stylespro_btn:active { box-shadow: none; border: 2px solid; }
            a.stylespro_btn:hover, .gforms_box a:hover {background: #f2fbff;}

        </style>
        <?php 
    }

    public function init_frontend() {

        parent::init_frontend();

        /*
        * Front-end actions and filters
        */
        add_action( 'gform_enqueue_scripts', array($this, 'gf_stylespro_enq') );
        add_filter( 'gform_pre_render', array($this, 'gf_stylespro_add') );
        // add_filter( 'gform_pre_render', array($this, 'gf_stylespro_add_other_label') );        
        add_filter( 'gform_validation_message', array($this, 'gf_stylespro_validation'), 9, 2);

        /**
        * Add init scripts on form render
        */
        add_action( 'gform_register_init_scripts', array($this, 'gf_stylespro_init_scripts') );
        add_action( 'gform_pre_enqueue_scripts', array($this, 'action_gform_pre_enqueue_scripts'), 10, 2 );

        /**
        * Clean up extra styles by some themes
        */
        function gf_stylespro_dusting() {
            wp_dequeue_style( 'x-gravity-forms' );
            wp_dequeue_style( 'us-gravityforms' );
            wp_dequeue_style( 'woo-gravity-forms' );
        }
        add_action( 'wp_enqueue_scripts', 'gf_stylespro_dusting', 999 );

        /*
        * Front End
        * Add Styles Pro classes to the field container li
        */
        add_action("gform_field_css_class", "gf_stylespro_add_css_classes", 10, 3);
        
        function gf_stylespro_add_css_classes($classes, $field, $form) {
            if( $field["gfStylesPro"] ) {
                $classes .= " " . $field["gfStylesPro"];
                // $field["value"] .= $field["gfStylesPro"];
            }

            return $classes;
        }

    }



    function action_gform_pre_enqueue_scripts( $form, $is_ajax ){
        // $theme = $form['gf_stylespro_theme'];
        $theme = self::get_styles_pro_theme($form);

        if (!empty($theme) && $theme != "none") {
            add_filter( 'pre_option_rg_gforms_disable_css', function() { return true; } );
        }
    }





    /**
    * Configures the settings which should be rendered on the Form Settings > Styles Pro tab.
    *
    * @return array
    */
    public function form_settings_fields( $form ) {

        $themes_data = array(
            'label' => 'Theme Name',
            'value' => 'Theme Slug',
            'desc' => 'Description',
            'scripts' => 'Features',
        );

        $desc_str = '';        
        
        foreach ( glob( plugin_dir_path( __FILE__ ) . "themes/*.css" ) as $file ) {
            $gfsp_themes = get_file_data( $file, $themes_data );
            
            // Make sure the file returns values in headers to exclude minified files
            if ( $gfsp_themes['value'] != '' ) {
                $options[$gfsp_themes['value']] = $gfsp_themes['label'];
                if ( $gfsp_themes['desc'] != '' ) {
                    
                    $has_scripts_str = '';
                    if ( $gfsp_themes['scripts'] != '' ) 
                        $has_scripts_str = '<p><i style="font-size: .9em">(Contains scripts for advanced features that can be enabled from Customization options)</i></p>';
                    
                    $desc_str .= '<div id="desc_'.$gfsp_themes['value'].'">' . $gfsp_themes['desc'] . $has_scripts_str . '</div>';
                }

            }
        }

        // Sort theme list
        asort($options);

        // Get currently selected theme form settings (v1)
        $sel_form = rgar( $form, 'gf_stylespro_theme' );

        $theme_choices = array(
            array(
                'label' => 'Default Theme',
                'value' => '',
            ),
            array(
                'label' => 'No theme',
                'value' => 'none',
            )
        );
    
        foreach ( $options as $value => $name ) {
            array_push($theme_choices, array(
                'label' => htmlentities($name),
                'value' => htmlentities($value),
                ));
        }


        $gfsp_plugin_settings = $this->get_plugin_settings();
        
        $default_theme = '';

        if ( is_array($gfsp_plugin_settings) && array_key_exists('default_theme', $gfsp_plugin_settings) && $gfsp_plugin_settings['default_theme'] != '' )
            $default_theme = $gfsp_plugin_settings['default_theme'];


        $settings = array(
            array(
                'title'  => esc_html__( 'Styles Pro: Form Settings', 'gf_stylespro' ),
                'fields' => array(
                    array(
                        'label'   => esc_html__( 'Theme', 'gf_stylespro' ),
                        'type'    => 'select',
                        'name'    => 'theme',
                        'default_value'=> $sel_form,
                        'default_theme' => $default_theme,
                        'tooltip' => esc_html__( '<h6>Styles Pro Theme</h6>This setting will apply the selected visual theme for this form. Choose <i>No theme</i> to use default Gravity Forms styles.<br>Multiple forms with different themes can be used on the same page, except when <i>No theme</i> or <i>*Inherit Theme Styles</i> are loaded with other themes.', 'gf_stylespro' ),
                        'after_select' =>  ' &nbsp; <a class="button" href="' . esc_url( admin_url( 'admin.php?page=gf_settings&subview=gf_stylespro' ) ) . '">Edit themes <i>in Styles Pro Settings</i></a><div class="themes_descriptions">'.$desc_str.'</div>',
                        'choices' => $theme_choices,
                    ),
                    array(
                        'label'   => esc_html__( 'Validation popup', 'gf_stylespro' ),
                        'type'    => 'checkbox',
                        'name'    => 'v_popup',
                        'tooltip' => esc_html__( 'This setting will place the validation message in a CSS popup, that can be closed by clicking/tapping anywhere on the page.', 'gf_stylespro' ),
                        'choices' => array(
                            array(
                                'label' => esc_html__( 'Show validation error in a CSS popup', 'gf_stylespro' ),
                                'name'  => 'v_popup',
                            ),
                        ),
                    ),
                    array(
                        'label'   => esc_html__( 'Validation auto-scroll', 'gf_stylespro' ),
                        'type'    => 'checkbox',
                        'name'    => 'v_scroll',
                        'tooltip' => esc_html__( 'This setting will scroll the page to the first field with errors that need to be changed. This makes for a great User Experience, specially in conjunction with popup message.', 'gf_stylespro' ),
                        'choices' => array(
                            array(
                                'label' => esc_html__( 'Auto-scroll to the first error field', 'gf_stylespro' ),
                                'name'    => 'v_scroll',
                            ),
                        ),
                    ),
                    array(
                        'label'   => esc_html__( 'Customize validation message', 'gf_stylespro' ),
                        'type'    => 'checkbox',
                        'name'    => 'v_enable',
                        'after_input' => '',
                        'onclick' => "if(this.checked){jQuery('#gaddon-setting-row-v_message').show();} else{jQuery('#gaddon-setting-row-v_message').hide();}",
                        'choices' => array(
                            array(
                                'label' => esc_html__( 'Enable', 'gf_stylespro' ),
                                'name'    => 'v_enable',
                            ),
                        ),
                    ),
                    array(
                        'label'   => esc_html__( 'Validation message', 'gf_stylespro' ),
                        'type'    => 'textarea',
                        'placeholder' => 'There was a problem with your submission. Errors have been highlighted below.',
                        'name'    => 'v_message',
                        'tooltip' => esc_html__( 'Enter a new validation message here. HTML is allowed.', 'gf_stylespro' ),
                        'class'   => 'medium',
                    ),
                    array(
                        'label'   => esc_html__( 'Enable icon set', 'gf_stylespro' ),
                        'type'    => 'checkbox',
                        'name'    => 'iconsets',
                        'tooltip' => esc_html__( 'Select icons-sets to enable for this form.', 'gf_stylespro' ),
                        'choices' => array(
                            array(
                                'label' => esc_html__( 'Font Awesome', 'gf_stylespro' ),
                                'name'  => 'icn_fa',
                                'tooltip' => esc_html__( '<h6>785 icons</h6><i>By <a href="http://fontawesome.io/" rel="nofollow" target="_blank">Fort Awesome</a> (v 4.7)</i>', 'gf_stylespro' ),
                            ),
                            array(
                                'label' => esc_html__( 'Elegant Font Icons', 'gf_stylespro' ),
                                'name'  => 'icn_et',
                                'tooltip' => esc_html__( '<h6>360 icons</h6><i>By <a href="https://www.elegantthemes.com/blog/resources/elegant-icon-font" target="_blank">Elegant Themes</a></i>', 'gf_stylespro' ),
                            ),
                            array(
                                'label' => esc_html__( 'Elegant Line Style Icons', 'gf_stylespro' ),
                                'name'  => 'icn_et_line',
                                'tooltip' => esc_html__( '<h6>100 icons</h6><i>By <a href="https://www.elegantthemes.com/blog/freebie-of-the-week/free-line-style-icons" target="_blank">Elegant Themes</a></i>', 'gf_stylespro' ),
                            ),
                            array(
                                'label' => esc_html__( 'Material Icons', 'gf_stylespro' ),
                                'name'  => 'icn_md',
                                'tooltip' => esc_html__( '<h6>932 icons</h6><i>By <a href="https://material.io/icons/" rel="nofollow" target="_blank">Google</a> (v 3.0.1)</i>', 'gf_stylespro' ),
                            ),
                        ),
                    ),
                ),
            ),
        );

        return $settings;
    }

    function gf_stylespro_init_scripts( $form ) {
        // Move the icon after the field
        $script = 'jQuery(document).ready(function(){ $wrapper = jQuery("#gform_wrapper_'. $form['id'] .'"); $wrapper.find(".gf_icon_after .gfsp_icon").each(function() { jQuery(this).next().after(this); });  $wrapper.find(".gf_icon_after .ginput_container_date .gfsp_icon, .gf_icon_after .ginput_container_time .gfsp_icon").each(function() { jQuery(this).closest(".ginput_container").siblings(":last").find("input, select").after(this); }) });';
        GFFormDisplay::add_init_script( $form['id'], 'icon_after_field', GFFormDisplay::ON_PAGE_RENDER, $script );

        // Add classes on Product Add-on form
        if ( class_exists('WC_GFPA_Main') ) {
            $theme = self::get_styles_pro_theme($form);
            
            if ( ! empty($theme) && $theme != "none" ) {
                $add_classes_to_form = 'jQuery("#gform_wrapper_'. $form['id'] .'").closest("form").addClass("gf_stylespro '. $theme .'");';
                GFFormDisplay::add_init_script( $form['id'], 'gfsp_classes_to_form', GFFormDisplay::ON_PAGE_RENDER, $add_classes_to_form );
            }
        }
    }


    /**
     * Front-end
     * Customized validation message
     */
    function gf_stylespro_validation( $message, $form ) {

        $gfsp_form_settings  = $this->get_form_settings( $form );

        // Do nothing, if no theme is selected
        if ( !is_array($gfsp_form_settings) || $gfsp_form_settings['theme'] == ""){
                    return $message;
        }

        // If message customization is true
        if ( $gfsp_form_settings['v_enable'] ) {
            $message =  "<div class='validation_error'>" . $gfsp_form_settings['v_message'] . '</div>';
        }

        // If popup is true
        if ( $gfsp_form_settings['v_popup'] ) {
            $message = '<div class="gfsp_popup" onclick="jQuery(this).fadeOut()">'. $message .'</div>';
        }

        // If scrolling is true
        if ( $gfsp_form_settings['v_scroll'] ) {
            $message .= "<script>
// For AJAX forms
jQuery(document).bind('gform_page_loaded', function(event, form_id, current_page){
    if ( jQuery('.gfield_error')[0] != undefined )
  jQuery('html, body').animate({scrollTop: jQuery('.gfield_error').offset().top - 250}, 800);
});
//For Non-AJAX forms
jQuery(window).load( function(){
    if ( jQuery('.gfield_error')[0] != undefined )
    jQuery('html, body').animate({scrollTop: jQuery('.gfield_error').offset().top - 150}, 800); }); </script>";
        }
        
        return $message;
    }



    /**
     * Front-end
     * Manage stylesheets
     */
    function gf_stylespro_enq( $form ) {
        // $theme = $form['gf_stylespro_theme'];
        $theme = self::get_styles_pro_theme($form);
        
        if ( ! empty($theme) && $theme != "none" ) {

            $gfsp_dir = plugin_dir_url( __FILE__ );

            $min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || isset( $_GET['gform_debug'] ) ? '' : '.min';

            add_filter( 'option_widget_gform_widget', array($this, 'update_gform_widget') );
            

            /*
             * Essemble customized theme settings CSS and Custom CSS
             */
            $style_out = "";
            $gfsp_plugin_settings = $this->get_plugin_settings();

            // add customized styles if present
            if ( isset($gfsp_plugin_settings[$theme . '_theme_css']) && $gfsp_plugin_settings[$theme . '_theme_css'] ) {
                $style_out .=$gfsp_plugin_settings[$theme.'_theme_css'];
            }

            // add custom CSS if enabled
            if ( $gfsp_plugin_settings['enable_css'] == true ) {
                $style_out .= '/* Custom CSS */ ' . $gfsp_plugin_settings['gfsp_custom_css'];
            }



            /*
             * Handle Styles Queue
             */
            // Shouldn't be enqued. But just in case
            wp_dequeue_style( 'gforms_css' );
            wp_dequeue_style( 'gforms_reset_css' );
            wp_dequeue_style( 'gforms_formsmain_css' );
            wp_dequeue_style( 'gforms_ready_class_css' );
            wp_dequeue_style( 'gforms_browsers_css' );

            if ( $theme == 'sp_inherit' ) {
                // Enque Styles Pro and Styles Pro Theme
                wp_enqueue_style( 'gforms_stylespro_css', $gfsp_dir . "styles/gfstylespro_inherit{$min}.css" );
                
                // Add inline CSS
                if ($style_out != '') {
                    wp_add_inline_style('gforms_stylespro_css', $style_out);
                }

            } else {
                // Enque Styles Pro and Styles Pro Theme
                wp_enqueue_style( 'gforms_stylespro_css', $gfsp_dir . "styles/gfstylespro{$min}.css", array(), $this->_version );

                if ( file_exists(  plugin_dir_path( __FILE__ ) . 'themes/' . $theme . '.min.css') ) {
                    wp_enqueue_style( 'gforms_stylespro_theme_'. $theme, $gfsp_dir . "themes/{$theme}{$min}.css" );
                }
                else {
                    wp_enqueue_style( 'gforms_stylespro_theme_'. $theme, $gfsp_dir . "themes/{$theme}.css" );
                }
                
                // Add inline CSS
                if ($style_out != '') {
                   wp_add_inline_style('gforms_stylespro_theme_'. $theme, $style_out);
                }

            }

            if ( self::has_datepicker_field( $form ) ) {
               wp_enqueue_style( 'gforms_datepicker_css', GFCommon::get_base_url() . "/css/datepicker{$min}.css", null, GFCommon::$version );
            }

            if ( isset($gfsp_plugin_settings[$theme . '_scripts'] ) ) {
                $theme_scripts = $gfsp_plugin_settings[$theme . '_scripts'];
                wp_enqueue_script( 'gforms_stylespro_'. $theme_scripts, $gfsp_dir . "themes/{$theme_scripts}.js" );
            }
            /*
             * Font icons
             */
            $icons_enq = array();
            $gfsp_form_settings  = $this->get_form_settings( $form );
            
            if ( is_array($gfsp_form_settings) ) {
                foreach( $gfsp_form_settings as $icon => $val ) {
                    // if key starts with 'icn_' and is set to true
                    if ( strpos( $icon, 'icn_' ) === 0 && $val ) {
                        array_push( $this->_iconsets, $icon );
                        array_push( $icons_enq, $icon );
                    }
                }
            }

            foreach( $icons_enq as $icon ) {
                $icon_src = self::get_icn_source($icon);
                wp_enqueue_style( $icon_src['handle'], $gfsp_dir . $icon_src['src'] );
            }

        }   // Ends if theme
    }       // Ends funtion




    /**
     * Front-end
     * Makes sure styles are disabled in widgets
     */
    function update_gform_widget( $options ) {

        foreach ($options as $key => $sub_array) {
            if ( is_array($sub_array) && array_key_exists('disable_scripts', $sub_array) ) {
                    $options[$key]['disable_scripts'] = 1;
            }
        }

        return $options;
    }



    /**
     * Front-end
     * Applies theme to the form and add theme classes
     */
    function gf_stylespro_add( $form ) {
        // Add Conditions here, if Icons are enabled, then use filter only
        if ( is_array( $this->_iconsets ) ) {
            add_filter( 'gform_field_content', array($this, 'gf_stylespro_field_icon' ), 10, 5 );
        }
        // $theme = $form['gf_stylespro_theme'];
        $theme = self::get_styles_pro_theme($form);
        $theme_scripts = self::get_styles_pro_theme_scripts( $theme );

        $current_class = isset( $form['cssClass'] ) ? $form['cssClass'] . ' ' : '';

        if (!empty($theme) && $theme != "none") {
            $form['cssClass'] = $current_class . 'gf_stylespro ' . $theme . ($theme_scripts?' '.$theme_scripts : '');
            self::gf_stylespro_add_other_label(); // Hooks the filter to add label in Other radio option
        }

        return $form;
    }



    /**
    * Add Icons to fields on form render
    * Hooked from Pre-render
    */
    function gf_stylespro_field_icon( $field_content, $field, $value, $lead_id, $form_id ) {

        if ( $field->gfStylesProIcon ) {

            $icon_data = explode('|', $field->gfStylesProIcon);
            $icon_is_img = ($icon_data[0] == "img") ? true : false;
            $icon = $icon_data[1];
            $iconset = isset( $icon_data[2] ) ? $icon_data[2] : '';

            // Return if the iconset is not enabled to avoid bad charachters
            if ( !$icon_is_img && !in_array( 'icn_'.$iconset, $this->_iconsets ) )
                return $field_content;

            // Return if field type is checkbox or radio for fields with multiple inputType
            if ( $field->inputType == 'checkbox' || $field->inputType == 'radio' )
                return $field_content;

            // if has color
            $color = '';
            if ( isset($icon_data[3]) )
                $color = " style='color:" . $icon_data[3] . "'";

            $icon_is_img_class = $icon_is_img?'has_gfsp_icn_img ':'';
            $field_content = str_replace( 'ginput_container ', 'ginput_container has_gfsp_icn ' . $icon_is_img_class , $field_content );

            $icon_string = '';
            if ( $icon_is_img ) {
                $icon_string = '<i style=\'background-image:url("'.$icon.'")\' class="gfsp_icn_img"></i>';
            } else {
                $icon_string = '<i '. $color .'class="'. $icon . '"></i>';
            }

            if ( $field->type == 'select') {
                    $field_content = str_replace( '<select', '<span class="gfsp_icon">'. $icon_string .'</span><select', $field_content );
            }

            else if ( $field->type == 'date' || $field->type == 'address' || $field->type == 'name' || $field->type == 'time' ) {
                // Since date, address and similar fields have more than 1 input fields, place icon on only the first occurance
                $replace_input  = false;
                $replace_select = false;
                $pos_input  = strpos($field_content, '<input');
                $pos_select = strpos($field_content, '<select');

                // Place the icon only to the first field, whether it be an input or a select
                // Determine if input or select appears first if either or both are present
                if ($pos_input !== false && $pos_select !== false){
                    if ($pos_input < $pos_select)
                        $replace_input = true;
                    else
                        $replace_select = true;
                    }

                else if ($pos_input !== false)
                    $replace_input = true;
                
                else if ($pos_select !== false)
                    $replace_select = true;

                // If input appears first
                if ( $replace_input ) {
                        $replace = '<span class="gfsp_icon">'.$icon_string.'</span><input';

                    $field_content = substr_replace($field_content, $replace, $pos_input, strlen('<input'));
                }

                // If select appears first
                if ($replace_select) {                  
                        $replace = '<span class="gfsp_icon">'.$icon_string.'</span><select';

                    $field_content = substr_replace($field_content, $replace, $pos_select, strlen('<select'));
                }
            }
            
            else {
                $field_content = str_replace( '<input', '<span class="gfsp_icon">'.$icon_string.'</span><input', $field_content );
            }
        }

        return $field_content;
    }

    /*
    * Front-end
    * Add label element to the 'Other' option in radio field
    */        
    function gf_stylespro_add_other_label() {

        add_filter( 'gform_field_choice_markup_pre_render', function ( $choice_markup, $choice, $field, $value ) {
            if ( $field['enableOtherChoice'] && isset( $choice['isOtherChoice'] ) && $choice['isOtherChoice']) {
                $lastChoiceId = count($field['choices']) - 1;
                $findInput = "<input id='input_{$field['formId']}_{$field['id']}_other'";
                
                // Adjust inline jQ to target the right field, since another field is added in the middle
                $choice_markup = str_replace( '.prev', '.prev().prev', $choice_markup );
                $choice_markup = str_replace( '.next', '.next().next', $choice_markup );
                
                $choice_markup = str_replace( $findInput, "<label class='gchoice_other_label' id=\"choice_{$field['formId']}_{$field['id']}_{$lastChoiceId}\" for=\"choice_{$field['formId']}_{$field['id']}_{$lastChoiceId}\"></label>".$findInput, $choice_markup );
            }
            return $choice_markup;
        }, 10, 4 );
    
    }



    public function init_ajax() {
        parent::init_ajax();
        // Tasks or filters to perform only during ajax requests
    }



    public function plugin_settings_fields() {
        return gf_stylespro_get_all_theme_options();
    }


	// # SCRIPTS & STYLES -----------------------------------------------------------------------------------------------

	/**
	 * Return the scripts which should be enqueued.
	 *
	 * @return array
	 */
    public function scripts() {
        $scripts = array(
            array(
                'handle'    => 'jq_color_picker',
                'src'       => $this->get_base_url() . '/scripts/jqColorPicker.min.js',
                // 'version'   => $this->_version,
                'deps'      => array( 'jquery' ),
                'in_footer' => true,
                'callback'  => array( $this, 'localize_scripts' ),
                'enqueue'   => array(
                    array(
                        'admin_page' => array( 'plugin_settings' ),
                        'tab'        => 'gf_stylespro'
                    )
                )
            ),
            array(
                'handle'    => 'gfsp_settings',
                'src'       => $this->get_base_url() . '/scripts/settings.js',
                'deps'      => array( 'jquery' ),
                'version'   => $this->_version,
                'in_footer' => true,
                'callback'  => array( $this, 'localize_scripts' ),
                'enqueue'   => array(
                    array(
                        'admin_page' => array( 'plugin_settings' ),
                        'tab'        => 'gf_stylespro'
                    )
                )
            ),
            array(
                'handle'    => 'gfsp_form_settings',
                'src'       => $this->get_base_url() . '/scripts/form-settings.js',
                'deps'      => array( 'jquery' ),
                'in_footer' => true,
                'callback'  => array( $this, 'localize_scripts' ),
                'enqueue'   => array(
                    array(
                        'admin_page' => array( 'form_settings' ),
                        'tab'        => 'gf_stylespro'
                    )
                )
            ),
            array(
                'handle'    => 'gfsp_form_editor',
                'src'       => $this->get_base_url() . '/scripts/form-editor.js',
                'deps'      => array( 'jquery' ),
                'in_footer' => true,
                'callback'  => array( $this, 'localize_scripts' ),
                'enqueue'   => array(
                    array(
                        'admin_page' => array( 'form_editor' ),
                        'tab'        => 'gf_stylespro'
                    )
                )
            ),
            // array(
			// 	'handle'  => 'wp-color-picker',
			// 	'enqueue' => array(
			// 		array( 'admin_page' => array( 'form_editor' ) )
			// 	)
			// )
        );

        return array_merge( parent::scripts(), $scripts );
    }


	/**
	 * Return the stylesheets which should be enqueued.
	 *
	 * @return array
	 */
	public function styles() {
        $styles = array(
			array(
				'handle'  => 'gforms-styles-pro-admin',
				'src'     => $this->get_base_url() . '/styles/admin-editor.css',
				'version' => $this->_version,
				'enqueue' => array(
					array( 'admin_page' => array( 'form_editor', 'form_settings' ) )
				)
			),
            array(
				'handle'  => 'wp-color-picker',
				'enqueue' => array(
					array( 'admin_page' => array( 'form_editor' ) )
				)
			)
		);

        /*
         * Font icons
         */
        if ( is_admin() ) {
            $gfsp_dir = plugin_dir_url( __FILE__ );

            $styles_icons = array();
            $gfsp_form_settings  = $this->get_form_settings( $this->get_current_form() );

            if (  is_array( $gfsp_form_settings ) ) {

                foreach( $gfsp_form_settings as $icon => $val ) {
                    // if key starts with 'icn_' and is set to true
                    if ( strpos( $icon, 'icn_' ) === 0 && $val ) {
                        
                        $icon_src = self::get_icn_source($icon);
                        
                        array_push( $styles_icons, array(
                                    'handle' => $icon_src['handle'],
                                    'src' => $gfsp_dir . $icon_src['src'],
                                    'enqueue' => array( array( 'admin_page' => array( 'form_editor' ) ) )
                            )
                        );

                        array_push ( $this->_iconsets, $icon );
                    }
                }
            }
            $styles_all = array_merge($styles, $styles_icons);
        } else {
            $styles_all = $styles;
        }

		return array_merge( parent::styles(), $styles_all );
	}




    public function gf_sylespro_list_ornaments_settings( $position, $form_id ) {
        // Create settings on position 1362
        if ( $position == 1362 ) {
            ?>
            <!-- Modal -->
            <div id="add_gf_stylespro_choice_modal" style="display: none">
            <div class="gf_stylespro_selectors" id="gf_stylespro_current_modal">
            <div class="gf_stylespro_bg"></div>
            <div class="box">
                    <?php
                    if ( empty ( $this->_iconsets ) ){
                        $gfsp_settings_link = admin_url( "admin.php?page=gf_edit_forms&view=settings&subview=gf_stylespro&id={$form_id}" );
                        echo '<h4>No icon-sets are enabled. To enable icon-sets for this form, go to <a href="' . $gfsp_settings_link . '">Form Settings > Styles Pro</a>.</h4>';
                    } else {
                        ?>
                        <div style="text-align: center; margin-top: 10px">
                            <input id="gfsp_search_icons" type="text" placeholder="Search icons"><button id="gfsp_search_icons_clear" onclick="jQuery('#gfsp_search_icons').val(''); searchIcons()">Clear</button>
                        </div>
                        <?php
                        self::pupulate_icons();
                    }
                    //h h_checkbox h_radio h_poll h_quiz h_option h_post_tags-checkbox h_post_tags-radio h_post_custom_field-checkbox h_post_custom_field-radio h_product-checkbox h_product-radio
                    ?>
            <h3 class="gfsp_image">Image</h3>
            <div class="gfsp_image h" style="display: none">
                <input class="ornament_image fieldwidth-2" type="text" value="" />
                <button class="media-button button">Select image</button>
            </div>
            <input id="gfsp_icon_temp" type="hidden" value='' />
            <div style="text-align: center;">
                <span class="sp_choice_preview" style="display: inline-block"></span>
                <span class="gfsp_color_wrapper" style="display: inline-block; margin-top: 10px; text-align: center"><input id="gfsp_icon_color" type="text" value /></span>
            </div>
            <div class="gfsp_footer">
                <a href="http://gravitystylespro.com/docs/" target="_blank" style="float: left;">Need help?</a>
                <input type="button" class="button-primary" value="Save" onclick="gfsp_ornament_save();">
                <input type="button" class="button" value="Cancel" onclick="tb_remove();">
                </div>
            </div>
            </div>
            </div>
            <!-- Modal ends -->
        <?php
        }
    }




    public function pupulate_icons() {
        if ( in_array( 'icn_fa' , $this->_iconsets) ) {
            $icon_list = 'address-book,address-book-o,address-card,address-card-o,adjust,adn,align-center,align-justify,align-left,align-right,amazon,ambulance,american-sign-language-interpreting,anchor,android,angellist,angle-double-down,angle-double-left,angle-double-right,angle-double-up,angle-down,angle-left,angle-right,angle-up,apple,archive,area-chart,arrow-circle-down,arrow-circle-left,arrow-circle-o-down,arrow-circle-o-left,arrow-circle-o-right,arrow-circle-o-up,arrow-circle-right,arrow-circle-up,arrow-down,arrow-left,arrow-right,arrow-up,arrows,arrows-alt,arrows-h,arrows-v,asl-interpreting,assistive-listening-systems,asterisk,at,audio-description,automobile,backward,balance-scale,ban,bandcamp,bank,bar-chart,bar-chart-o,barcode,bars,bath,bathtub,battery,battery-0,battery-1,battery-2,battery-3,battery-4,battery-empty,battery-full,battery-half,battery-quarter,battery-three-quarters,bed,beer,behance,behance-square,bell,bell-o,bell-slash,bell-slash-o,bicycle,binoculars,birthday-cake,bitbucket,bitbucket-square,bitcoin,black-tie,blind,bluetooth,bluetooth-b,bold,bolt,bomb,book,bookmark,bookmark-o,braille,briefcase,btc,bug,building,building-o,bullhorn,bullseye,bus,buysellads,cab,calculator,calendar,calendar-check-o,calendar-minus-o,calendar-o,calendar-plus-o,calendar-times-o,camera,camera-retro,car,caret-down,caret-left,caret-right,caret-square-o-down,caret-square-o-left,caret-square-o-right,caret-square-o-up,caret-up,cart-arrow-down,cart-plus,cc,cc-amex,cc-diners-club,cc-discover,cc-jcb,cc-mastercard,cc-paypal,cc-stripe,cc-visa,certificate,chain,chain-broken,check,check-circle,check-circle-o,check-square,check-square-o,chevron-circle-down,chevron-circle-left,chevron-circle-right,chevron-circle-up,chevron-down,chevron-left,chevron-right,chevron-up,child,chrome,circle,circle-o,circle-o-notch,circle-thin,clipboard,clock-o,clone,close,cloud,cloud-download,cloud-upload,cny,code,code-fork,codepen,codiepie,coffee,cog,cogs,columns,comment,comment-o,commenting,commenting-o,comments,comments-o,compass,compress,connectdevelop,contao,copy,copyright,creative-commons,credit-card,credit-card-alt,crop,crosshairs,css3,cube,cubes,cut,cutlery,dashboard,dashcube,database,deaf,deafness,dedent,delicious,desktop,deviantart,diamond,digg,dollar,dot-circle-o,download,dribbble,drivers-license,drivers-license-o,dropbox,drupal,edge,edit,eercast,eject,ellipsis-h,ellipsis-v,empire,envelope,envelope-o,envelope-open,envelope-open-o,envelope-square,envira,eraser,etsy,eur,euro,exchange,exclamation,exclamation-circle,exclamation-triangle,expand,expeditedssl,external-link,external-link-square,eye,eye-slash,eyedropper,fa,facebook,facebook-f,facebook-official,facebook-square,fast-backward,fast-forward,fax,feed,female,fighter-jet,file,file-archive-o,file-audio-o,file-code-o,file-excel-o,file-image-o,file-movie-o,file-o,file-pdf-o,file-photo-o,file-picture-o,file-powerpoint-o,file-sound-o,file-text,file-text-o,file-video-o,file-word-o,file-zip-o,files-o,film,filter,fire,fire-extinguisher,firefox,first-order,flag,flag-checkered,flag-o,flash,flask,flickr,floppy-o,folder,folder-o,folder-open,folder-open-o,font,font-awesome,fonticons,fort-awesome,forumbee,forward,foursquare,free-code-camp,frown-o,futbol-o,gamepad,gavel,gbp,ge,gear,gears,genderless,get-pocket,gg,gg-circle,gift,git,git-square,github,github-alt,github-square,gitlab,gittip,glass,glide,glide-g,globe,google,google-plus,google-plus-circle,google-plus-official,google-plus-square,google-wallet,graduation-cap,gratipay,grav,group,h-square,hacker-news,hand-grab-o,hand-lizard-o,hand-o-down,hand-o-left,hand-o-right,hand-o-up,hand-paper-o,hand-peace-o,hand-pointer-o,hand-rock-o,hand-scissors-o,hand-spock-o,hand-stop-o,handshake-o,hard-of-hearing,hashtag,hdd-o,header,headphones,heart,heart-o,heartbeat,history,home,hospital-o,hotel,hourglass,hourglass-1,hourglass-2,hourglass-3,hourglass-end,hourglass-half,hourglass-o,hourglass-start,houzz,html5,i-cursor,id-badge,id-card,id-card-o,ils,image,imdb,inbox,indent,industry,info,info-circle,inr,instagram,institution,internet-explorer,intersex,ioxhost,italic,joomla,jpy,jsfiddle,key,keyboard-o,krw,language,laptop,lastfm,lastfm-square,leaf,leanpub,legal,lemon-o,level-down,level-up,life-bouy,life-buoy,life-ring,life-saver,lightbulb-o,line-chart,link,linkedin,linkedin-square,linode,linux,list,list-alt,list-ol,list-ul,location-arrow,lock,long-arrow-down,long-arrow-left,long-arrow-right,long-arrow-up,low-vision,magic,magnet,mail-forward,mail-reply,mail-reply-all,male,map,map-marker,map-o,map-pin,map-signs,mars,mars-double,mars-stroke,mars-stroke-h,mars-stroke-v,maxcdn,meanpath,medium,medkit,meetup,meh-o,mercury,microchip,microphone,microphone-slash,minus,minus-circle,minus-square,minus-square-o,mixcloud,mobile,mobile-phone,modx,money,moon-o,mortar-board,motorcycle,mouse-pointer,music,navicon,neuter,newspaper-o,object-group,object-ungroup,odnoklassniki,odnoklassniki-square,opencart,openid,opera,optin-monster,outdent,pagelines,paint-brush,paper-plane,paper-plane-o,paperclip,paragraph,paste,pause,pause-circle,pause-circle-o,paw,paypal,pencil,pencil-square,pencil-square-o,percent,phone,phone-square,photo,picture-o,pie-chart,pied-piper,pied-piper-alt,pied-piper-pp,pinterest,pinterest-p,pinterest-square,plane,play,play-circle,play-circle-o,plug,plus,plus-circle,plus-square,plus-square-o,podcast,power-off,print,product-hunt,puzzle-piece,qq,qrcode,question,question-circle,question-circle-o,quora,quote-left,quote-right,ra,random,ravelry,rebel,recycle,reddit,reddit-alien,reddit-square,refresh,registered,remove,renren,reorder,repeat,reply,reply-all,resistance,retweet,rmb,road,rocket,rotate-left,rotate-right,rouble,rss,rss-square,rub,ruble,rupee,s15,safari,save,scissors,scribd,search,search-minus,search-plus,sellsy,send,send-o,server,share,share-alt,share-alt-square,share-square,share-square-o,shekel,sheqel,shield,ship,shirtsinbulk,shopping-bag,shopping-basket,shopping-cart,shower,sign-in,sign-language,sign-out,signal,signing,simplybuilt,sitemap,skyatlas,skype,slack,sliders,slideshare,smile-o,snapchat,snapchat-ghost,snapchat-square,snowflake-o,soccer-ball-o,sort,sort-alpha-asc,sort-alpha-desc,sort-amount-asc,sort-amount-desc,sort-asc,sort-desc,sort-down,sort-numeric-asc,sort-numeric-desc,sort-up,soundcloud,space-shuttle,spinner,spoon,spotify,square,square-o,stack-exchange,stack-overflow,star,star-half,star-half-empty,star-half-full,star-half-o,star-o,steam,steam-square,step-backward,step-forward,stethoscope,sticky-note,sticky-note-o,stop,stop-circle,stop-circle-o,street-view,strikethrough,stumbleupon,stumbleupon-circle,subscript,subway,suitcase,sun-o,superpowers,superscript,support,table,tablet,tachometer,tag,tags,tasks,taxi,telegram,television,tencent-weibo,terminal,text-height,text-width,th,th-large,th-list,themeisle,thermometer,thermometer-0,thermometer-1,thermometer-2,thermometer-3,thermometer-4,thermometer-empty,thermometer-full,thermometer-half,thermometer-quarter,thermometer-three-quarters,thumb-tack,thumbs-down,thumbs-o-down,thumbs-o-up,thumbs-up,ticket,times,times-circle,times-circle-o,times-rectangle,times-rectangle-o,tint,toggle-down,toggle-left,toggle-off,toggle-on,toggle-right,toggle-up,trademark,train,transgender,transgender-alt,trash,trash-o,tree,trello,tripadvisor,trophy,truck,try,tty,tumblr,tumblr-square,turkish-lira,tv,twitch,twitter,twitter-square,umbrella,underline,undo,universal-access,university,unlink,unlock,unlock-alt,unsorted,upload,usb,usd,user,user-circle,user-circle-o,user-md,user-o,user-plus,user-secret,user-times,users,vcard,vcard-o,venus,venus-double,venus-mars,viacoin,viadeo,viadeo-square,video-camera,vimeo,vimeo-square,vine,vk,volume-control-phone,volume-down,volume-off,volume-up,warning,wechat,weibo,weixin,whatsapp,wheelchair,wheelchair-alt,wifi,wikipedia-w,window-close,window-close-o,window-maximize,window-minimize,window-restore,windows,won,wordpress,wpbeginner,wpexplorer,wpforms,wrench,xing,xing-square,y-combinator,y-combinator-square,yahoo,yc,yc-square,yelp,yen,yoast,youtube,youtube-play,youtube-square';

            echo '<h3>Font Awesome Icons <span class="search_count"></span></h3>
            <div class="gfsp_icons gfsp_fontawesome" style="display: none">
                <div data-iconset="fa" class="all_icons">
                    <span><i class=""></i>No icon</span>';
            
            $icons = explode(',', $icon_list);
            foreach($icons as $icon){
                echo '<span><i class="fa fa-' . $icon . '"></i>' . $icon . '</span>';
            }
            echo '</div></div>';
        }

        if ( in_array( 'icn_et' , $this->_iconsets) ) {
            $icon_list = 'arrow_up,arrow_down,arrow_left,arrow_right,arrow_left-up,arrow_right-up,arrow_right-down,arrow_left-down,arrow-up-down,arrow_up-down_alt,arrow_left-right_alt,arrow_left-right,arrow_expand_alt2,arrow_expand_alt,arrow_condense,arrow_expand,arrow_move,arrow_carrot-up,arrow_carrot-down,arrow_carrot-left,arrow_carrot-right,arrow_carrot-2up,arrow_carrot-2down,arrow_carrot-2left,arrow_carrot-2right,arrow_carrot-up_alt2,arrow_carrot-down_alt2,arrow_carrot-left_alt2,arrow_carrot-right_alt2,arrow_carrot-2up_alt2,arrow_carrot-2down_alt2,arrow_carrot-2left_alt2,arrow_carrot-2right_alt2,arrow_triangle-up,arrow_triangle-down,arrow_triangle-left,arrow_triangle-right,arrow_triangle-up_alt2,arrow_triangle-down_alt2,arrow_triangle-left_alt2,arrow_triangle-right_alt2,arrow_back,icon_minus-06,icon_plus,icon_close,icon_check,icon_minus_alt2,icon_plus_alt2,icon_close_alt2,icon_check_alt2,icon_zoom-out_alt,icon_zoom-in_alt,icon_search,icon_box-empty,icon_box-selected,icon_minus-box,icon_plus-box,icon_box-checked,icon_circle-empty,icon_circle-slelected,icon_stop_alt2,icon_stop,icon_pause_alt2,icon_pause,icon_menu,icon_menu-square_alt2,icon_menu-circle_alt2,icon_ul,icon_ol,icon_adjust-horiz,icon_adjust-vert,icon_document_alt,icon_documents_alt,icon_pencil,icon_pencil-edit_alt,icon_pencil-edit,icon_folder-alt,icon_folder-open_alt,icon_folder-add_alt,icon_info_alt,icon_error-oct_alt,icon_error-circle_alt,icon_error-triangle_alt,icon_question_alt2,icon_question,icon_comment_alt,icon_chat_alt,icon_vol-mute_alt,icon_volume-low_alt,icon_volume-high_alt,icon_quotations,icon_quotations_alt2,icon_clock_alt,icon_lock_alt,icon_lock-open_alt,icon_key_alt,icon_cloud_alt,icon_cloud-upload_alt,icon_cloud-download_alt,icon_image,icon_images,icon_lightbulb_alt,icon_gift_alt,icon_house_alt,icon_genius,icon_mobile,icon_tablet,icon_laptop,icon_desktop,icon_camera_alt,icon_mail_alt,icon_cone_alt,icon_ribbon_alt,icon_bag_alt,icon_creditcard,icon_cart_alt,icon_paperclip,icon_tag_alt,icon_tags_alt,icon_trash_alt,icon_cursor_alt,icon_mic_alt,icon_compass_alt,icon_pin_alt,icon_pushpin_alt,icon_map_alt,icon_drawer_alt,icon_toolbox_alt,icon_book_alt,icon_calendar,icon_film,icon_table,icon_contacts_alt,icon_headphones,icon_lifesaver,icon_piechart,icon_refresh,icon_link_alt,icon_link,icon_loading,icon_blocked,icon_archive_alt,icon_heart_alt,icon_printer,icon_calulator,icon_building,icon_floppy,icon_drive,icon_search-2,icon_id,icon_id-2,icon_puzzle,icon_like,icon_dislike,icon_mug,icon_currency,icon_wallet,icon_pens,icon_easel,icon_flowchart,icon_datareport,icon_briefcase,icon_shield,icon_percent,icon_globe,icon_globe-2,icon_target,icon_hourglass,icon_balance,icon_star_alt,icon_star-half_alt,icon_star,icon_star-half,icon_tools,icon_tool,icon_cog,icon_cogs,arrow_up_alt,arrow_down_alt,arrow_left_alt,arrow_right_alt,arrow_left-up_alt,arrow_right-up_alt,arrow_right-down_alt,arrow_left-down_alt,arrow_condense_alt,arrow_expand_alt3,arrow_carrot_up_alt,arrow_carrot-down_alt,arrow_carrot-left_alt,arrow_carrot-right_alt,arrow_carrot-2up_alt,arrow_carrot-2dwnn_alt,arrow_carrot-2left_alt,arrow_carrot-2right_alt,arrow_triangle-up_alt,arrow_triangle-down_alt,arrow_triangle-left_alt,arrow_triangle-right_alt,icon_minus_alt,icon_plus_alt,icon_close_alt,icon_check_alt,icon_zoom-out,icon_zoom-in,icon_stop_alt,icon_menu-square_alt,icon_menu-circle_alt,icon_document,icon_documents,icon_pencil_alt,icon_folder,icon_folder-open,icon_folder-add,icon_folder_upload,icon_folder_download,icon_info,icon_error-circle,icon_error-oct,icon_error-triangle,icon_question_alt,icon_comment,icon_chat,icon_vol-mute,icon_volume-low,icon_volume-high,icon_quotations_alt,icon_clock,icon_lock,icon_lock-open,icon_key,icon_cloud,icon_cloud-upload,icon_cloud-download,icon_lightbulb,icon_gift,icon_house,icon_camera,icon_mail,icon_cone,icon_ribbon,icon_bag,icon_cart,icon_tag,icon_tags,icon_trash,icon_cursor,icon_mic,icon_compass,icon_pin,icon_pushpin,icon_map,icon_drawer,icon_toolbox,icon_book,icon_contacts,icon_archive,icon_heart,icon_profile,icon_group,icon_grid-2x2,icon_grid-3x3,icon_music,icon_pause_alt,icon_phone,icon_upload,icon_download,icon_rook,icon_printer-alt,icon_calculator_alt,icon_building_alt,icon_floppy_alt,icon_drive_alt,icon_search_alt,icon_id_alt,icon_id-2_alt,icon_puzzle_alt,icon_like_alt,icon_dislike_alt,icon_mug_alt,icon_currency_alt,icon_wallet_alt,icon_pens_alt,icon_easel_alt,icon_flowchart_alt,icon_datareport_alt,icon_briefcase_alt,icon_shield_alt,icon_percent_alt,icon_globe_alt,icon_clipboard,social_facebook,social_twitter,social_pinterest,social_googleplus,social_tumblr,social_tumbleupon,social_wordpress,social_instagram,social_dribbble,social_vimeo,social_linkedin,social_rss,social_deviantart,social_share,social_myspace,social_skype,social_youtube,social_picassa,social_googledrive,social_flickr,social_blogger,social_spotify,social_delicious,social_facebook_circle,social_twitter_circle,social_pinterest_circle,social_googleplus_circle,social_tumblr_circle,social_stumbleupon_circle,social_wordpress_circle,social_instagram_circle,social_dribbble_circle,social_vimeo_circle,social_linkedin_circle,social_rss_circle,social_deviantart_circle,social_share_circle,social_myspace_circle,social_skype_circle,social_youtube_circle,social_picassa_circle,social_googledrive_alt2,social_flickr_circle,social_blogger_circle,social_spotify_circle,social_delicious_circle,social_facebook_square,social_twitter_square,social_pinterest_square,social_googleplus_square,social_tumblr_square,social_stumbleupon_square,social_wordpress_square,social_instagram_square,social_dribbble_square,social_vimeo_square,social_linkedin_square,social_rss_square,social_deviantart_square,social_share_square,social_myspace_square,social_skype_square,social_youtube_square,social_picassa_square,social_googledrive_square,social_flickr_square,social_blogger_square,social_spotify_square,social_delicious_square';

            echo '<h3>Elegant Theme\'s Font Icons <span class="search_count"></span></h3>
            <div class="gfsp_icons gfsp_elegantfont" style="display: none">
                <div data-iconset="et" class="all_icons">
                    <span><i class=""></i>No icon</span>';
            
            $icons = explode(',', $icon_list);
            foreach($icons as $icon){
                echo '<span><i class="et ' . $icon . '"></i>' . $icon . '</span>';
            }
            echo '</div></div>';
        }

        if ( in_array( 'icn_et_line' , $this->_iconsets) ) {
            $icon_list = 'icon-mobile,icon-laptop,icon-desktop,icon-tablet,icon-phone,icon-document,icon-documents,icon-search,icon-clipboard,icon-newspaper,icon-notebook,icon-book-open,icon-browser,icon-calendar,icon-presentation,icon-picture,icon-pictures,icon-video,icon-camera,icon-printer,icon-toolbox,icon-briefcase,icon-wallet,icon-gift,icon-bargraph,icon-grid,icon-expand,icon-focus,icon-edit,icon-adjustments,icon-ribbon,icon-hourglass,icon-lock,icon-megaphone,icon-shield,icon-trophy,icon-flag,icon-map,icon-puzzle,icon-basket,icon-envelope,icon-streetsign,icon-telescope,icon-gears,icon-key,icon-paperclip,icon-attachment,icon-pricetags,icon-lightbulb,icon-layers,icon-pencil,icon-tools,icon-tools-2,icon-scissors,icon-paintbrush,icon-magnifying-glass,icon-circle-compass,icon-linegraph,icon-mic,icon-strategy,icon-beaker,icon-caution,icon-recycle,icon-anchor,icon-profile-male,icon-profile-female,icon-bike,icon-wine,icon-hotairballoon,icon-glob,icon-genius,icon-map-pin,icon-dial,icon-chat,icon-heart,icon-cloud,icon-upload,icon-download,icon-traget,icon-hazardous,icon-piechart,icon-speedometer,icon-global,icon-compass,icon-lifesaver,icon-clock,icon-aperture,icon-quote,icon-scope,icon-alarmclock,icon-refresh,icon-happy,icon-sad,icon-facebook,icon-twitter,icon-googleplus,icon-rss,icon-tumblr,icon-linkedin,icon-dribbble';

            echo '<h3>Elegant Theme\'s Line Icons <span class="search_count"></span></h3>
            <div class="gfsp_icons gfsp_elegantlinefont" style="display: none">
                <div data-iconset="et_line" class="all_icons">
                    <span><i class=""></i>No icon</span>';
            
            $icons = explode(',', $icon_list);
            foreach($icons as $icon){
                echo '<span><i class="et ' . $icon . '"></i>' . $icon . '</span>';
            }
            echo '</div></div>';
        }

        if ( in_array( 'icn_md' , $this->_iconsets) ) {
            $icon_list = '3d_rotation,ac_unit,access_alarm,access_alarms,access_time,accessibility,accessible,account_balance,account_balance_wallet,account_box,account_circle,adb,add,add_a_photo,add_alarm,add_alert,add_box,add_circle,add_circle_outline,add_location,add_shopping_cart,add_to_photos,add_to_queue,adjust,airline_seat_flat,airline_seat_flat_angled,airline_seat_individual_suite,airline_seat_legroom_extra,airline_seat_legroom_normal,airline_seat_legroom_reduced,airline_seat_recline_extra,airline_seat_recline_normal,airplanemode_active,airplanemode_inactive,airplay,airport_shuttle,alarm,alarm_add,alarm_off,alarm_on,album,all_inclusive,all_out,android,announcement,apps,archive,arrow_back,arrow_downward,arrow_drop_down,arrow_drop_down_circle,arrow_drop_up,arrow_forward,arrow_upward,art_track,aspect_ratio,assessment,assignment,assignment_ind,assignment_late,assignment_return,assignment_returned,assignment_turned_in,assistant,assistant_photo,attach_file,attach_money,attachment,audiotrack,autorenew,av_timer,backspace,backup,battery_alert,battery_charging_full,battery_full,battery_std,battery_unknown,beach_access,beenhere,block,bluetooth,bluetooth_audio,bluetooth_connected,bluetooth_disabled,bluetooth_searching,blur_circular,blur_linear,blur_off,blur_on,book,bookmark,bookmark_border,border_all,border_bottom,border_clear,border_color,border_horizontal,border_inner,border_left,border_outer,border_right,border_style,border_top,border_vertical,branding_watermark,brightness_1,brightness_2,brightness_3,brightness_4,brightness_5,brightness_6,brightness_7,brightness_auto,brightness_high,brightness_low,brightness_medium,broken_image,brush,bubble_chart,bug_report,build,burst_mode,business,business_center,cached,cake,call,call_end,call_made,call_merge,call_missed,call_missed_outgoing,call_received,call_split,call_to_action,camera,camera_alt,camera_enhance,camera_front,camera_rear,camera_roll,cancel,card_giftcard,card_membership,card_travel,casino,cast,cast_connected,center_focus_strong,center_focus_weak,change_history,chat,chat_bubble,chat_bubble_outline,check,check_box,check_box_outline_blank,check_circle,chevron_left,chevron_right,child_care,child_friendly,chrome_reader_mode,class,clear,clear_all,close,closed_caption,cloud,cloud_circle,cloud_done,cloud_download,cloud_off,cloud_queue,cloud_upload,code,collections,collections_bookmark,color_lens,colorize,comment,compare,compare_arrows,computer,confirmation_number,contact_mail,contact_phone,contacts,content_copy,content_cut,content_paste,control_point,control_point_duplicate,copyright,create,create_new_folder,credit_card,crop,crop_16_9,crop_3_2,crop_5_4,crop_7_5,crop_din,crop_free,crop_landscape,crop_original,crop_portrait,crop_rotate,crop_square,dashboard,data_usage,date_range,dehaze,delete,delete_forever,delete_sweep,description,desktop_mac,desktop_windows,details,developer_board,developer_mode,device_hub,devices,devices_other,dialer_sip,dialpad,directions,directions_bike,directions_boat,directions_bus,directions_car,directions_railway,directions_run,directions_subway,directions_transit,directions_walk,disc_full,dns,do_not_disturb,do_not_disturb_alt,do_not_disturb_off,do_not_disturb_on,dock,domain,done,done_all,donut_large,donut_small,drafts,drag_handle,drive_eta,dvr,edit,edit_location,eject,email,enhanced_encryption,equalizer,error,error_outline,euro_symbol,ev_station,event,event_available,event_busy,event_note,event_seat,exit_to_app,expand_less,expand_more,explicit,explore,exposure,exposure_neg_1,exposure_neg_2,exposure_plus_1,exposure_plus_2,exposure_zero,extension,face,fast_forward,fast_rewind,favorite,favorite_border,featured_play_list,featured_video,feedback,fiber_dvr,fiber_manual_record,fiber_new,fiber_pin,fiber_smart_record,file_download,file_upload,filter,filter_1,filter_2,filter_3,filter_4,filter_5,filter_6,filter_7,filter_8,filter_9,filter_9_plus,filter_b_and_w,filter_center_focus,filter_drama,filter_frames,filter_hdr,filter_list,filter_none,filter_tilt_shift,filter_vintage,find_in_page,find_replace,fingerprint,first_page,fitness_center,flag,flare,flash_auto,flash_off,flash_on,flight,flight_land,flight_takeoff,flip,flip_to_back,flip_to_front,folder,folder_open,folder_shared,folder_special,font_download,format_align_center,format_align_justify,format_align_left,format_align_right,format_bold,format_clear,format_color_fill,format_color_reset,format_color_text,format_indent_decrease,format_indent_increase,format_italic,format_line_spacing,format_list_bulleted,format_list_numbered,format_paint,format_quote,format_shapes,format_size,format_strikethrough,format_textdirection_l_to_r,format_textdirection_r_to_l,format_underlined,forum,forward,forward_10,forward_30,forward_5,free_breakfast,fullscreen,fullscreen_exit,functions,g_translate,gamepad,games,gavel,gesture,get_app,gif,golf_course,gps_fixed,gps_not_fixed,gps_off,grade,gradient,grain,graphic_eq,grid_off,grid_on,group,group_add,group_work,hd,hdr_off,hdr_on,hdr_strong,hdr_weak,headset,headset_mic,healing,hearing,help,help_outline,high_quality,highlight,highlight_off,history,home,hot_tub,hotel,hourglass_empty,hourglass_full,http,https,image,image_aspect_ratio,import_contacts,import_export,important_devices,inbox,indeterminate_check_box,info,info_outline,input,insert_chart,insert_comment,insert_drive_file,insert_emoticon,insert_invitation,insert_link,insert_photo,invert_colors,invert_colors_off,iso,keyboard,keyboard_arrow_down,keyboard_arrow_left,keyboard_arrow_right,keyboard_arrow_up,keyboard_backspace,keyboard_capslock,keyboard_hide,keyboard_return,keyboard_tab,keyboard_voice,kitchen,label,label_outline,landscape,language,laptop,laptop_chromebook,laptop_mac,laptop_windows,last_page,launch,layers,layers_clear,leak_add,leak_remove,lens,library_add,library_books,library_music,lightbulb_outline,line_style,line_weight,linear_scale,link,linked_camera,list,live_help,live_tv,local_activity,local_airport,local_atm,local_bar,local_cafe,local_car_wash,local_convenience_store,local_dining,local_drink,local_florist,local_gas_station,local_grocery_store,local_hospital,local_hotel,local_laundry_service,local_library,local_mall,local_movies,local_offer,local_parking,local_pharmacy,local_phone,local_pizza,local_play,local_post_office,local_printshop,local_see,local_shipping,local_taxi,location_city,location_disabled,location_off,location_on,location_searching,lock,lock_open,lock_outline,looks,looks_3,looks_4,looks_5,looks_6,looks_one,looks_two,loop,loupe,low_priority,loyalty,mail,mail_outline,map,markunread,markunread_mailbox,memory,menu,merge_type,message,mic,mic_none,mic_off,mms,mode_comment,mode_edit,monetization_on,money_off,monochrome_photos,mood,mood_bad,more,more_horiz,more_vert,motorcycle,mouse,move_to_inbox,movie,movie_creation,movie_filter,multiline_chart,music_note,music_video,my_location,nature,nature_people,navigate_before,navigate_next,navigation,near_me,network_cell,network_check,network_locked,network_wifi,new_releases,next_week,nfc,no_encryption,no_sim,not_interested,note,note_add,notifications,notifications_active,notifications_none,notifications_off,notifications_paused,offline_pin,ondemand_video,opacity,open_in_browser,open_in_new,open_with,pages,pageview,palette,pan_tool,panorama,panorama_fish_eye,panorama_horizontal,panorama_vertical,panorama_wide_angle,party_mode,pause,pause_circle_filled,pause_circle_outline,payment,people,people_outline,perm_camera_mic,perm_contact_calendar,perm_data_setting,perm_device_information,perm_identity,perm_media,perm_phone_msg,perm_scan_wifi,person,person_add,person_outline,person_pin,person_pin_circle,personal_video,pets,phone,phone_android,phone_bluetooth_speaker,phone_forwarded,phone_in_talk,phone_iphone,phone_locked,phone_missed,phone_paused,phonelink,phonelink_erase,phonelink_lock,phonelink_off,phonelink_ring,phonelink_setup,photo,photo_album,photo_camera,photo_filter,photo_library,photo_size_select_actual,photo_size_select_large,photo_size_select_small,picture_as_pdf,picture_in_picture,picture_in_picture_alt,pie_chart,pie_chart_outlined,pin_drop,place,play_arrow,play_circle_filled,play_circle_outline,play_for_work,playlist_add,playlist_add_check,playlist_play,plus_one,poll,polymer,pool,portable_wifi_off,portrait,power,power_input,power_settings_new,pregnant_woman,present_to_all,print,priority_high,public,publish,query_builder,question_answer,queue,queue_music,queue_play_next,radio,radio_button_checked,radio_button_unchecked,rate_review,receipt,recent_actors,record_voice_over,redeem,redo,refresh,remove,remove_circle,remove_circle_outline,remove_from_queue,remove_red_eye,remove_shopping_cart,reorder,repeat,repeat_one,replay,replay_10,replay_30,replay_5,reply,reply_all,report,report_problem,restaurant,restaurant_menu,restore,restore_page,ring_volume,room,room_service,rotate_90_degrees_ccw,rotate_left,rotate_right,rounded_corner,router,rowing,rss_feed,rv_hookup,satellite,save,scanner,schedule,school,screen_lock_landscape,screen_lock_portrait,screen_lock_rotation,screen_rotation,screen_share,sd_card,sd_storage,search,security,select_all,send,sentiment_dissatisfied,sentiment_neutral,sentiment_satisfied,sentiment_very_dissatisfied,sentiment_very_satisfied,settings,settings_applications,settings_backup_restore,settings_bluetooth,settings_brightness,settings_cell,settings_ethernet,settings_input_antenna,settings_input_component,settings_input_composite,settings_input_hdmi,settings_input_svideo,settings_overscan,settings_phone,settings_power,settings_remote,settings_system_daydream,settings_voice,share,shop,shop_two,shopping_basket,shopping_cart,short_text,show_chart,shuffle,signal_cellular_4_bar,signal_cellular_connected_no_internet_4_bar,signal_cellular_no_sim,signal_cellular_null,signal_cellular_off,signal_wifi_4_bar,signal_wifi_4_bar_lock,signal_wifi_off,sim_card,sim_card_alert,skip_next,skip_previous,slideshow,slow_motion_video,smartphone,smoke_free,smoking_rooms,sms,sms_failed,snooze,sort,sort_by_alpha,spa,space_bar,speaker,speaker_group,speaker_notes,speaker_notes_off,speaker_phone,spellcheck,star,star_border,star_half,stars,stay_current_landscape,stay_current_portrait,stay_primary_landscape,stay_primary_portrait,stop,stop_screen_share,storage,store,store_mall_directory,straighten,streetview,strikethrough_s,style,subdirectory_arrow_left,subdirectory_arrow_right,subject,subscriptions,subtitles,subway,supervisor_account,surround_sound,swap_calls,swap_horiz,swap_vert,swap_vertical_circle,switch_camera,switch_video,sync,sync_disabled,sync_problem,system_update,system_update_alt,tab,tab_unselected,tablet,tablet_android,tablet_mac,tag_faces,tap_and_play,terrain,text_fields,text_format,textsms,texture,theaters,thumb_down,thumb_up,thumbs_up_down,time_to_leave,timelapse,timeline,timer,timer_10,timer_3,timer_off,title,toc,today,toll,tonality,touch_app,toys,track_changes,traffic,train,tram,transfer_within_a_station,transform,translate,trending_down,trending_flat,trending_up,tune,turned_in,turned_in_not,tv,unarchive,undo,unfold_less,unfold_more,update,usb,verified_user,vertical_align_bottom,vertical_align_center,vertical_align_top,vibration,video_call,video_label,video_library,videocam,videocam_off,videogame_asset,view_agenda,view_array,view_carousel,view_column,view_comfy,view_compact,view_day,view_headline,view_list,view_module,view_quilt,view_stream,view_week,vignette,visibility,visibility_off,voice_chat,voicemail,volume_down,volume_mute,volume_off,volume_up,vpn_key,vpn_lock,wallpaper,warning,watch,watch_later,wb_auto,wb_cloudy,wb_incandescent,wb_iridescent,wb_sunny,wc,web,web_asset,weekend,whatshot,widgets,wifi,wifi_lock,wifi_tethering,work,wrap_text,youtube_searched_for,zoom_in,zoom_out,zoom_out_map';

            echo '<h3>Material Icons <span class="search_count"></span></h3>
            <div class="gfsp_icons gfsp_materialicons" style="display: none">
                <div data-iconset="md" class="all_icons">
                    <span><i class=""></i>No icon</span>';
            
            $icons = explode(',', $icon_list);
            foreach($icons as $icon){
                echo '<span><i class="md md-' . $icon . '"></i>' . $icon . '</span>';
            }
            echo '</div></div>';
        }
    }






    /*
    # ---  FORM SETTINGS ----------------------------------------------------------------------------
    */

    /*
    * Create Form Settings
    */
    function gf_stylespro_theme_select( $settings, $form ) {
        
        // Return if v2 settings are found
        if ( is_array( $this->get_form_settings($form) ) ) {
            return $settings;
        }

        // v1
        $themes_data = array(
        'label' => 'Theme Name',
        'value' => 'Theme Slug'
        );

        foreach ( glob( plugin_dir_path( __FILE__ ) . "themes/*.css" ) as $file ) {
            $gfsp_themes = get_file_data( $file, $themes_data );
            $options[$gfsp_themes['value']] = $gfsp_themes['label'];
        }

        // Get currently selected theme form settings
        $sel_form = rgar( $form, 'gf_stylespro_theme' );
        
        // Create dropdown options with a loop including selected
        $opts = "<option value=''>No theme</option>";

        foreach ( $options as $value => $name ) {
            $opts = $opts . '<option value="' . htmlentities($value) . '"' . (($sel_form === $value) ? ' selected="selected"' : "") . '>' . htmlentities($name) . '</option>';
        }

        $url = admin_url( "admin.php?page=gf_edit_forms&view=settings&subview=gf_stylespro&id={$form['id']}" );

        $settings['Styles Pro']['gf_stylespro_theme'] = '
            <tr>
                <td colspan="2">Theme selection option has been moved to <strong>Form settings > <a href='. $url . '>Styles Pro</a></strong>.</td>
            </tr>
            <tr style="display: none;">
                <th> </th>
                <td><select name="gf_stylespro_theme" id="gf_stylespro_theme">' . $opts . ' </select> &nbsp; <a class="button" href="' . esc_url( admin_url( 'admin.php?page=gf_settings&subview=gf_stylespro' ) ) . '">Edit themes</a>
                </td>
            </tr>';


        return $settings;
    }



    /*
    * Save Form Settings
    */
    function save_gf_stylespro_form_settings( $form ) {
        // Relic from v1
        $form['gf_stylespro_theme'] = rgpost( 'gf_stylespro_theme' );

        return $form;
    }

    /*
    * Check if form has Date field
    */
	private static function has_datepicker_field( $form ) {
		if ( is_array( $form['fields'] ) ) {
			foreach ( $form['fields'] as $field ) {

				if ( RGFormsModel::get_input_type( $field ) == 'date' && $field->dateType == 'datepicker' ) {
					return true;
				}
			}
		}

		return false;
	}

    /*
    * Get handle and source to enque icons
    */
	private static function get_icn_source( $icon ) {
        
        $icon_src['icn_fa'] = array(
            'handle' => 'font_awesome',
            'src' => 'fonts/font-awesome-4.7.0/css/font-awesome.min.css',
            'local' => 'fonts/font-awesome-4.7.0/css/font-awesome.css',
            'cdn' => 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css',
        );
        $icon_src['icn_md'] = array(
            'handle' => 'material_icons',
            'src' => 'fonts/material_icons/md-icons.css',
            'local' => 'fonts/material_icons/md-icons.css',
            'cdn' => 'https://fonts.googleapis.com/icon?family=Material+Icons',
        );
        $icon_src['icn_et'] = array(
            'handle' => 'elegenat_font_icons',
            'src' => 'fonts/elegant_font/style.min.css',
            'local' => 'fonts/elegant_font/style.css',
            'cdn' => false,
        );
        $icon_src['icn_et_line'] = array(
            'handle' => 'elegenat_line_icons',
            'src' => 'fonts/et_line_font/style.min.css',
            'local' => 'fonts/et_line_font/style.css',
            'cdn' => false,
        );

        return array( 'handle' => $icon_src[$icon]['handle'], 'src' => $icon_src[$icon]['src'] );
    }

    function get_styles_pro_theme( $form ) {
        $gfsp_plugin_settings = $this->get_plugin_settings();
        $gfsp_form_settings  = $this->get_form_settings($form);
        $default_theme = '';

        // Get default theme
        if ( is_array( $gfsp_plugin_settings ) && array_key_exists('default_theme', $gfsp_plugin_settings) )
            $default_theme = $gfsp_plugin_settings['default_theme'];

        // Get theme from Form settings
        if( ! is_array($gfsp_form_settings) ) {
            // Version 1.0
            $theme = !empty($form['gf_stylespro_theme']) ? $form['gf_stylespro_theme'] : '' ;
        } else {
            $theme = $gfsp_form_settings['theme'];
        }

        // Fallback to default theme; if no theme selected
        if ( empty($theme) )
            $theme = $default_theme;

        return $theme;
    }

    function get_styles_pro_theme_scripts( $theme ) {
        
        $gfsp_plugin_settings = $this->get_plugin_settings();
        
        if ( isset($gfsp_plugin_settings[$theme . '_scripts_load']) && $gfsp_plugin_settings[$theme . '_scripts_load'] == true ) {
            $theme_scripts = $gfsp_plugin_settings[$theme . '_scripts'];
            
            return $theme_scripts;
        } else {
            
            return false;
        }
    }



    /*
    # ---  FIELD SETTINGS -------------------------------------------
    */

    function gf_stylespro_appearance_settings( $position, $form_id ) {
        // Create settings on position 250 (right before Custom CSS Style Label)
        if ( $position == 250 ) {
            $gfsp_dir = plugin_dir_url( __FILE__ );
            $form = GFAPI::get_form( $form_id );
            // $theme = ($form['gf_stylespro']['theme'] !='' ? $form['gf_stylespro']['theme'] : $form['gf_stylespro_theme'] );
            $theme = self::get_styles_pro_theme($form);
    ?>
            <li class="gfsp_styles field_setting">
                <label for="field_admin_label" class="section_label">
                    <?php _e( 'Styles Pro', 'gf_stylespro' ); ?>
                    <?php gform_tooltip( 'gf_stylespro_value' ) ?>
                </label>
                <button id="add_gf_stylespro_styles" class="button" onclick="tb_show('Styes Pro: Style Selector', '#TB_inline?height=500&width=600&inlineId=gf_stylespro_modal', '');">Styles Selector</button>
                <input type="hidden" id="gf_stylespro_value" value="" />
                <!-- Modal -->
                <div id="gf_stylespro_modal" style="display: none">
                <div class="gf_stylespro_selectors" id="gf_stylespro_current_modal">
                <div class="gf_stylespro_bg"></div>
                <div class="box">
                <?php

                // If not theme is selected; show warning
                if ( empty($theme) || $theme == "none" ) {
                    echo '<div style="border: 1px solid red; padding: 5px 10px;">
                    <i>Styles Pro is not enabled for this form. These styles only work in conjunction with it.<br>Go to Form settings > <a href='.
                    admin_url( "admin.php?page=gf_edit_forms&view=settings&subview=gf_stylespro&id={$form_id}" ) . '>Styles Pro</a> to enable it.</i>
                    </div>';
                }

                ?>
                <h3 class="toggle_open">Layout</h3>
                <div class="layout">
                    <input id="gfsp_full" class="has_img default" type="radio" name="gfsp_layout" value="">
                        <label for="gfsp_full" data-hover="Full width"><img src="<?php echo $gfsp_dir ?>icons/full.png"></label>
                    <input id="gf_half" class="has_img" type="radio" name="gfsp_layout" value="gf_half">
                        <label for="gf_half" data-hover="Half"><img src="<?php echo $gfsp_dir ?>icons/half.png"></label>
                    <input id="gf_third" class="has_img" type="radio" name="gfsp_layout" value="gf_third">
                        <label for="gf_third" data-hover="Third"><img src="<?php echo $gfsp_dir ?>icons/third.png"></label>
                    <input id="gf_third_two" class="has_img" type="radio" name="gfsp_layout" value="gf_third_two">
                        <label for="gf_third_two" data-hover="2 Thirds"><img src="<?php echo $gfsp_dir ?>icons/twothird.png"></label>
                    <input id="gf_quarter" class="has_img" type="radio" name="gfsp_layout" value="gf_quarter">
                        <label for="gf_quarter" data-hover="Quarter"><img src="<?php echo $gfsp_dir ?>icons/fourth.png"></label>
                    <input id="gf_inline" class="has_img" type="radio" name="gfsp_layout" value="gf_inline">
                        <label for="gf_inline" data-hover="Inline"><img src="<?php echo $gfsp_dir ?>icons/inline.png"></label>
                </div>
                <h3>Position</h3>
                <div class="float" style="display: none">
                    <input id="gf_newline" class="has_img" type="checkbox" name="gf_newline" value="gf_newline">
                        <label for="gf_newline" data-hover="New line"><img src="<?php echo $gfsp_dir ?>icons/newline.png"></label>
                    <br/>
                    <input id="float_none" class="has_img default" type="radio" name="gfsp_float" value="">
                        <label for="float_none" data-hover="None"><img src="<?php echo $gfsp_dir ?>icons/none.png"></label>
                    <input id="gf_left" class="has_img" type="radio" name="gfsp_float" value="gf_left">
                        <label for="gf_left" data-hover="Float left"><img src="<?php echo $gfsp_dir ?>icons/float-left.png"></label>
                    <input id="gf_right" class="has_img" type="radio" name="gfsp_float" value="gf_right">
                        <label for="gf_right" data-hover="Float right"><img src="<?php echo $gfsp_dir ?>icons/float-right.png"></label>
                    <input id="gf_invisible" type="radio" name="gfsp_float" value="gf_invisible">
                        <label for="gf_invisible" title="Off screen">Make invisible</label>
                    <input id="gf_hidden" type="radio" name="gfsp_float" value="gf_hidden">
                        <label for="gf_hidden" title="Uses display: none">Hidden</label>
                </div>
                            
                <h3 class="gfsp_field_icon h h_time h_date h_tel h_phone h_text h_select h_address h_name h_number h_tel h_textarea h_email h_title">Field icon</h3>
                <div class="gfsp_field_icon h" style="display: none">
                    
                    <input id="gf_icn_default" class="has_img default" type="radio" name="gf_icn" value="">
                        <label for="gf_icn_default" data-hover="Default"><img src="<?php echo $gfsp_dir ?>icons/icn-default.png"></label>
                    
                    <input id="gf_icn_theme" class="has_img" type="radio" name="gf_icn" value="gf_icn_theme">
                        <label for="gf_icn_theme" style="display: none;" data-hover="Theme"><img src="<?php echo $gfsp_dir ?>icons/icn-default.png"></label>
                        
                    <input id="gf_icn_border" class="has_img" type="radio" name="gf_icn" value="gf_icn_border">
                        <label for="gf_icn_border" data-hover="Border"><img src="<?php echo $gfsp_dir ?>icons/icn-border.png"></label>

                    <input id="gf_icn_bs" class="has_img" type="radio" name="gf_icn" value="gf_icn_bs">
                        <label for="gf_icn_bs" data-hover="Bootstrap-like"><img src="<?php echo $gfsp_dir ?>icons/icn-bs.png"></label>

                    <input id="gf_icn_inset" class="has_img" type="radio" name="gf_icn" value="gf_icn_inset">
                        <label for="gf_icn_inset" data-hover="Inside the field"><img src="<?php echo $gfsp_dir ?>icons/icn-inset.png"></label>
                    
                    <br />

                    <input id="gf_icn_large" type="checkbox" name="gf_icn_large" value="gf_icn_large">
                        <label for="gf_icn_large">Larger icon</label>

                    <h4>Placement</h4>
                    
                    <input id="gf_icon_before" type="radio" class="default has_img" name="gf_icon_after" value="">
                        <label for="gf_icon_before" data-hover="Before the field"><img src="<?php echo $gfsp_dir ?>icons/icn-before.png"></label>

                    <input id="gf_icon_after" type="radio" class="has_img" name="gf_icon_after" value="gf_icon_after">
                        <label for="gf_icon_after" data-hover="After the field"><img src="<?php echo $gfsp_dir ?>icons/icn-after.png"></label>


                </div>

                <h3 class="gfsp_special_tab">Special</h3>
                <div class="gfsp_special" style="display: none">

                    <input id="gf_scroll_text" type="checkbox" name="gf_scroll_text" value="gf_scroll_text">
                        <label for="gf_scroll_text" class="h h_html h_section" style="display: none;">Scroll text</label>

                    <input id="gf_hide_ampm" type="checkbox" name="gf_hide_ampm" value="gf_hide_ampm">
                        <label for="gf_hide_ampm" class="h h_time" style="display: none;">Hide am/pm</label>

                    <input id="gf_hide_charleft" type="checkbox" name="gf_hide_charleft" value="gf_hide_charleft">
                        <label for="gf_hide_charleft" class="h h_text h_textarea h_number" style="display: none;">Hide charachters left</label>

                    <input id="gf_hide_label" type="checkbox" name="gf_hide_label" value="gf_hide_label">
                        <label for="gf_hide_label">Hide label</label>

                    <input id="gf_hide_complex_label" type="checkbox" name="gf_hide_complex_label" value="gf_hide_complex_label">
                        <label for="gf_hide_complex_label" class="h h_address h_name h_creditcard" style="display: none;">Hide inner labels</label>

                </div>

                <h3 class="gfsp_lists_tab h h_checkbox h_radio h_poll h_quiz h_option h_post_tags-checkbox h_post_tags-radio h_post_custom_field-checkbox h_post_custom_field-radio h_product-checkbox h_product-radio">Columns</h3>
                <div class="gfsp_lists h" style="display: none">
                    <input id="gf_list_full" class="has_img default" type="radio" name="gfsp_list_col" value="">
                        <label for="gf_list_full" data-hover="1 column"><img src="<?php echo $gfsp_dir ?>icons/list-none.png"></label>
                    <input id="gf_list_2col" class="has_img" type="radio" name="gfsp_list_col" value="gf_list_2col">
                        <label for="gf_list_2col" data-hover="2 columns"><img src="<?php echo $gfsp_dir ?>icons/list2.png"></label>
                    <input id="gf_list_3col" class="has_img" type="radio" name="gfsp_list_col" value="gf_list_3col">
                        <label for="gf_list_3col" data-hover="3 columns"><img src="<?php echo $gfsp_dir ?>icons/list3.png"></label>
                    <input id="gf_list_4col" class="has_img" type="radio" name="gfsp_list_col" value="gf_list_4col">
                        <label for="gf_list_4col" data-hover="4 columns"><img src="<?php echo $gfsp_dir ?>icons/list4.png"></label>
                    <input id="gf_list_5col" class="has_img" type="radio" name="gfsp_list_col" value="gf_list_5col">
                        <label for="gf_list_5col" data-hover="5 columns"><img src="<?php echo $gfsp_dir ?>icons/list5.png"></label>
                    <input id="gf_list_6col" class="has_img" type="radio" name="gfsp_list_col" value="gf_list_6col">
                        <label for="gf_list_6col" data-hover="6 columns"><img src="<?php echo $gfsp_dir ?>icons/list6.png"></label>
                    <input id="gf_list_inline" class="has_img" type="radio" name="gfsp_list_col" value="gf_list_inline" title="Inline">
                        <label for="gf_list_inline" data-hover="Automatic (inline)"><img src="<?php echo $gfsp_dir ?>icons/listinline.png"></label>
                </div>

                <h3 class="gfsp_lists_tab h h_checkbox h_radio h_poll h_quiz h_option h_post_tags-checkbox h_post_tags-radio h_post_custom_field-checkbox h_post_custom_field-radio h_product-checkbox h_product-radio">Style</h3>
                <div class="gfsp_lists h" style="display: none">
                    <input id="gfsp_theme_default" class="has_img default" type="radio" name="gfsp_o_style" value="">
                        <label for="gfsp_theme_default" data-hover="Theme default"><img src="<?php echo $gfsp_dir ?>icons/none.png"></label>
                    <input id="gfsp_toggle" class="has_img" type="radio" name="gfsp_o_style" value="gfsp_toggle">
                        <label for="gfsp_toggle" data-hover="Material Toggle"><img src="<?php echo $gfsp_dir ?>icons/list-toggle.png"></label>
                    <input id="gfsp_ios" class="has_img" type="radio" name="gfsp_o_style" value="gfsp_ios">
                        <label for="gfsp_ios" data-hover="iOS Toggle"><img src="<?php echo $gfsp_dir ?>icons/list-ios.png"></label>
                    <input id="gfsp_draw" class="has_img" type="radio" name="gfsp_o_style" value="gfsp_draw">
                        <label for="gfsp_draw" data-hover="Draw"><img src="<?php echo $gfsp_dir ?>icons/list-draw.png"></label>
                    <input id="gfsp_dot" class="has_img" type="radio" name="gfsp_o_style" value="gfsp_dot">
                        <label for="gfsp_dot" data-hover="Dot"><img src="<?php echo $gfsp_dir ?>icons/list-dot.png"></label>
                    <input id="gfsp_flip" class="has_img" type="radio" name="gfsp_o_style" value="gfsp_flip">
                        <label for="gfsp_flip" data-hover="Flip"><img src="<?php echo $gfsp_dir ?>icons/list-flip.png"></label>
                    <input id="gfsp_default" class="has_img" type="radio" name="gfsp_o_style" value="gfsp_default">
                        <label for="gfsp_default" data-hover="Browser default"><img src="<?php echo $gfsp_dir ?>icons/list-default.png"></label>
                    <p>Overall styles <i>(designed to be used with icons and images)</i></p>
                    <input id="gfsp_o_frame" class="has_img" type="radio" name="gfsp_o_style" value="gfsp_o_frame o-custom-bg">
                        <label for="gfsp_o_frame" data-hover="Frame"><img src="<?php echo $gfsp_dir ?>icons/o-frame.png"></label>
                    <input id="gfsp_o_list" class="has_img" type="radio" name="gfsp_o_style" value="gfsp_o_list o-custom-bg">
                        <label for="gfsp_o_list" data-hover="List"><img src="<?php echo $gfsp_dir ?>icons/o-list.png"></label>
                    <input id="gfsp_o_list-round" class="has_img" type="radio" name="gfsp_o_style" value="gfsp_o_list o-round o-custom-bg">
                        <label for="gfsp_o_list-round" data-hover="List (Round)"><img src="<?php echo $gfsp_dir ?>icons/o-list-round.png"></label>
                    <input id="gfsp_o_list-join" class="has_img" type="radio" name="gfsp_o_style" value="gfsp_o_list o-join o-custom-bg">
                        <label for="gfsp_o_list-join" data-hover="List (Joined)"><img src="<?php echo $gfsp_dir ?>icons/o-list-join.png"></label>
                    <input id="gfsp_o_list-round-join" class="has_img" type="radio" name="gfsp_o_style" value="gfsp_o_list o-round o-join o-custom-bg">
                        <label for="gfsp_o_list-round-join" data-hover="List (Round &amp; Joined)"><img src="<?php echo $gfsp_dir ?>icons/o-list-round-join.png"></label>
                    <input id="gfsp_o_shade" class="has_img" type="radio" name="gfsp_o_style" value="gfsp_o_shade">
                        <label for="gfsp_o_shade" data-hover="Shade"><img src="<?php echo $gfsp_dir ?>icons/o-shade.png"></label>
                    <input id="gfsp_o_shade-round" class="has_img" type="radio" name="gfsp_o_style" value="gfsp_o_shade o-round">
                        <label for="gfsp_o_shade-round" data-hover="Shade (Round)"><img src="<?php echo $gfsp_dir ?>icons/o-shade-round.png"></label>
                    <input id="gfsp_o_frame-shadowbox" class="has_img" type="radio" name="gfsp_o_style" value="gfsp_o_frame o-shadowbox o-notick o-custom-border">
                        <label for="gfsp_o_frame-shadowbox" data-hover="Shadow box"><img src="<?php echo $gfsp_dir ?>icons/o_frame_shadowbox.png"></label>
                </div>
                <h3 class="gfsp_lists_ornament_tab h h_checkbox h_radio h_poll h_quiz h_option h_post_tags-checkbox h_post_tags-radio h_post_custom_field-checkbox h_post_custom_field-radio h_product-checkbox h_product-radio">Image/icon settings</h3>
                <div class="gfsp_lists_ornament h" style="display: none">
                    <h4>Placement</h4>
                    <input id="gfsp_o_before" class="default" type="radio" name="gfsp_o_pos" value="">
                        <label for="gfsp_o_before">Image/icon first</label>
                    <input id="gfsp_o_after" class="" type="radio" name="gfsp_o_pos" value="o_after">
                        <label for="gfsp_o_after">Text first</label>
                    <h4>Size</h4>
                    <input id="gfsp_o_small" class="" type="radio" name="gfsp_o_size" value="o_small">
                        <label for="gfsp_o_small">Small</label>
                    <input id="gfsp_o_medium" class="default" type="radio" name="gfsp_o_size" value="o_medium">
                        <label for="gfsp_o_medium">Medium</label>
                    <input id="gfsp_o_large" class="" type="radio" name="gfsp_o_size" value="o_large">
                        <label for="gfsp_o_large">Large</label>

                </div>

                <div class="gfsp_footer">
                    <a href="http://gravitystylespro.com/docs/" target="_blank" style="float: left;">Need help?</a>
                    <input type="button" class="button-primary" value="Save" onclick="gfsp_save();">
                    <input type="button" class="button" value="Cancel" onclick="tb_remove();">
                    </div>
                </div>
                </div>
                </div>
                <!-- Modal ends -->
                <script type='text/javascript'>
                /* Save settings to field */
                function gfsp_save() {
                    var classes = [];
                    jQuery(".gf_stylespro_selectors input:checked").each( function(){
                        if ( jQuery(this).val() ) {
                        var cls=jQuery(this).val();
                        classes.push(cls);
                        }
                        });

                    jQuery('#gf_stylespro_value').val( classes.join(" ") );
                    SetFieldProperty('gfStylesPro', classes.join(" "));
                    tb_remove();
                    /* Update choices preview incase ornaments position changes */
                    UpdateFieldChoices(GetInputType(field));
                }
                </script>
            </li>
    <?php
        }
    }



    // Append option in Field Size dropdown for Full Width field size
    function gf_stylespro_add_field_size_option( $position, $form_id ) {    
        if ( $position == 0 ) {
            ?>
            <script>jQuery('#field_size').append( '<option value="large full">Full width</option>' );</script>
            <?php
        }
    }



    function gf_stylespro_appearance_settings_icon( $position, $form_id ) {
        // Create settings on position 250 (right before Custom CSS Style Label)
        if ( $position == 250 ) {
    ?>
            <li class="gfsp_icon field_setting">
                <input type="hidden" id="gf_stylespro_icon_value" value="" />
                <button id="add_gf_stylespro_icon" class="button" onclick="field_icon_tb_show()">Field Icon <span class="sp_field_icon_preview"></span></button>
            </li>
    <?php
        }
    }


    public function settings_adv_field_styles($field) {
        $theme = $field['theme_name'];
        echo '<a class="button show_adv_styles" onclick="jQuery(this).slideUp(); jQuery(this).next().slideDown();">Show advanced options</a>
        <div class="adv_field_options_wrapper">
            <h5>Advanced field styling</h5>
            <div class="adv_fields">
                <b>Field</b>';

        echo '<div class="adv_field"><span>Height <i>(top & buttom padding)</i></span>';
        $this->settings_text(
            array(
                'label'         => 'Vertical Padding',
                'name'          => $theme.'_field_v_padding',
                'default_value' => '',
                'class'         => 'small adv_v_padding',
                'placeholder'   => 'ex. 5',
                'after_input'   =>' <i>px</i>',
            )
        );
        echo '</div>';

        echo '<div class="adv_field"><span>Background color</span>';
        $this->settings_text(
            array(
                'label'         => 'Background color',
                'name'          => $theme.'_field_bg_color',
                'default_value' => '',
                'class'         => 'color adv_bg_color',            
            )
        );
        echo '</div>';

        echo '<hr><b>Border</b>';
        echo '<div class="adv_field"><span>Color</span>';
        $this->settings_text(
            array(
                'label'         => 'Border color',
                'name'          => $theme.'_field_border_color',
                'default_value' => '',
                'class'         => 'color adv_border_color'
            )
        );
        echo '</div>';

        echo '<div class="adv_field"><span>Width</span>';
        $this->settings_text(
            array(
                'label'         => 'Border width',
                'name'          => $theme.'_field_border_width',
                'default_value' => '',
                'class'         => 'small adv_border_width',
                'placeholder'   => 'ex. 1',
                'after_input'   => ' <i>px</i> '
            )
        );
        echo '</div>';

        echo '<div class="adv_field"><span>Radius</span>';        
        $this->settings_text(
            array(
                'label'         => 'Radius',
                'name'          => $theme.'_field_border_radius',
                'default_value' => '',
                'class'         => 'small adv_border_radius',
                'after_input'   => ' <i>px</i> '                
            )
        );
        echo '</div>';
        

        echo '<div class="adv_field"><span>Style</span>';
        $this->settings_select(
            array(
                'label'         => 'Style',
                'name'          => $theme.'_field_border_style',
                'default_value' => '',
                'class'         => 'adv_border_style',
                'choices'       => array(
                    array(  'label' => 'Default',   'value' => '' ),
                    array(  'label' => 'Solid',     'value' => 'solid' ),
                    array(  'label' => 'Dashed',    'value' => 'dashed' ),
                    array(  'label' => 'Dotted',    'value' => 'dotted' ),
                    array(  'label' => 'Double',    'value' => 'double' ),
                    array(  'label' => 'Ridge',     'value' => 'ridge' ),
                    array(  'label' => 'Inset',     'value' => 'inset' ),
                    array(  'label' => 'Outset',    'value' => 'outset' ),
                    array(  'label' => 'Groove',    'value' => 'groove' ),
                    array(  'label' => 'None',      'value' => 'none' ),
                )
            )
        );
        echo '</div>';

        echo '<hr><b>On Focus</b>';

        echo '<div class="adv_field"><span>Border color</span>';
        $this->settings_text(
            array(
                'label'         => 'Border color (focus)',
                'name'          => $theme.'_field_focus_border_color',
                'default_value' => '',
                'class'         => 'color adv_focus_border_color',
            )
        );
        echo '</div>';

        echo '<div class="adv_field"><span>Background color</span>';
        $this->settings_text(
            array(
                'label'         => 'Background color (focus)',
                'name'          => $theme.'_field_focus_bg_color',
                'default_value' => '',
                'class'         => 'color adv_focus_bg_color'
                
            )
        );
        echo '</div>';
        echo '</div>
        </div>';
    }


    public function settings_adv_btn_styles($field) {
        $theme = $field['theme_name'];
        echo '<a class="button show_adv_btn_styles" onclick="jQuery(this).slideUp(); jQuery(this).next().slideDown();">Show advanced options</a>
        <div class="adv_field_options_wrapper">
            <h5>Advanced button styling</h5>
            <div class="adv_fields">
                <b>Size</b>';

        echo '<div class="adv_field"><span>Height <i>(top & buttom padding)</i></span>';
        $this->settings_text(
            array(
                'label'         => 'Vertical Padding',
                'name'          => $theme.'_btn_v_padding',
                'default_value' => '',
                'class'         => 'small adv_btn_v_padding',
                'placeholder'   => 'ex. 5',
                'after_input'   =>' <i>px</i>',
            )
        );
        echo '</div>';

        echo '<div class="adv_field"><span>Width <i>(left & right padding)</i></span>';
        $this->settings_text(
            array(
                'label'         => 'Horizontal Padding',
                'name'          => $theme.'_btn_h_padding',
                'default_value' => '',
                'class'         => 'small adv_btn_h_padding',
                'placeholder'   => 'ex. 5',
                'after_input'   =>' <i>px</i>',
            )
        );
        echo '</div>';

        echo '<hr><b>Border</b>';
        echo '<div class="adv_field"><span>Color</span>';
        $this->settings_text(
            array(
                'label'         => 'Border color',
                'name'          => $theme.'_btn_border_color',
                'default_value' => '',
                'class'         => 'color adv_btn_border_color'
            )
        );
        echo '</div>';

        echo '<div class="adv_field"><span>Width</span>';
        $this->settings_text(
            array(
                'label'         => 'Border width',
                'name'          => $theme.'_btn_border_width',
                'default_value' => '',
                'class'         => 'small adv_btn_border_width',
                'placeholder'   => 'ex. 1',
                'after_input'   => ' <i>px</i> '
            )
        );
        echo '</div>';

        echo '<div class="adv_field"><span>Radius</span>';        
        $this->settings_text(
            array(
                'label'         => 'Radius',
                'name'          => $theme.'_btn_border_radius',
                'default_value' => '',
                'class'         => 'small adv_btn_border_radius',
                'after_input'   => ' <i>px</i> '                
            )
        );
        echo '</div>';
        
        echo '<div class="adv_field"><span>Style</span>';
        $this->settings_select(
            array(
                'label'         => 'Style',
                'name'          => $theme.'_btn_border_style',
                'default_value' => '',
                'class'         => 'adv_btn_border_style',
                'choices'       => array(
                    array(  'label' => 'Default',   'value' => '' ),
                    array(  'label' => 'Solid',     'value' => 'solid' ),
                    array(  'label' => 'Dashed',    'value' => 'dashed' ),
                    array(  'label' => 'Dotted',    'value' => 'dotted' ),
                    array(  'label' => 'Double',    'value' => 'double' ),
                    array(  'label' => 'Ridge',     'value' => 'ridge' ),
                    array(  'label' => 'Inset',     'value' => 'inset' ),
                    array(  'label' => 'Outset',    'value' => 'outset' ),
                    array(  'label' => 'Groove',    'value' => 'groove' ),
                    array(  'label' => 'None',      'value' => 'none' ),
                )
            )
        );
        echo '</div>';

        echo '<hr><b>On Hover</b>';
        echo '<div class="adv_field"><span>Text color</span>';
        $this->settings_text(
            array(
                'label'         => 'Text color (hover)',
                'name'          => $theme.'_btn_hover_color',
                'default_value' => '',
                'class'         => 'color adv_btn_hover_color'
                
            )
        );
        echo '</div>';
    
        echo '<div class="adv_field"><span>Border color</span>';
        $this->settings_text(
            array(
                'label'         => 'Border color (hover)',
                'name'          => $theme.'_btn_hover_border_color',
                'default_value' => '',
                'class'         => 'color adv_btn_hover_border_color',
            )
        );
        echo '</div>';

        echo '<div class="adv_field"><span>Background color</span>';
        $this->settings_text(
            array(
                'label'         => 'Background color (hover)',
                'name'          => $theme.'_btn_hover_bg_color',
                'default_value' => '',
                'class'         => 'color adv_btn_hover_bg_color'
                
            )
        );
        echo '</div>';
        echo '</div>
        </div>';

    }
}   // class StylesPro








/*
# --- FUNCTIONS FOR CLASS -------------------------------------------
*/



/*
* Reads data from all all theme files, return array
*/
function gfsp_get_themes() {
    $themes_data = array(
        'label' => 'Theme Name',
        'value' => 'Theme Slug',
        'font'  => 'Font',
        'font_label' => 'Font Label',
		'field_margin' => 'Field Margin',
        'field_icon_color' => 'Icon',
        'desc'  => 'Description',
        'scripts' => "Features"
        );

    foreach ( glob( plugin_dir_path( __FILE__ ) . "themes/*.css" ) as $file ) {
        $gfsp_themes = get_file_data($file, $themes_data, $context='');

        if ( $gfsp_themes['value'] != '' ){
                $all_themes[] = $gfsp_themes;
        }

    }
    // Sort theme list
    asort($all_themes);

    return $all_themes;
}



/*
* Create, Read and Handle options for all themes
*/
function gf_stylespro_get_all_theme_options() {
    $g_fonts = array( "Open Sans","Roboto","Lato","Slabo 27px","Roboto Condensed","Oswald","Montserrat","Source Sans Pro",
    "Lora","Raleway","PT Sans","Droid Sans","Open Sans Condensed","Ubuntu","Roboto Slab","Droid Serif","Arimo","Fjalla One",
    "Merriweather","PT Sans Narrow","Noto Sans","PT Serif","Titillium Web","Alegreya Sans","Passion One","Poiret One","Candal",
    "Playfair Display","Bitter","Indie Flower","Dosis","Yanone Kaffeesatz","Cabin","Oxygen","Lobster","Nunito","Fira Sans",
    "Hind","Arvo","Inconsolata","Noto Serif","Anton","Abel","Bree Serif","Muli","Ubuntu Condensed","Josefin Sans","Crimson Text"
    ,"Pacifico","Libre Baskerville","Exo 2","Francois One","Shadows Into Light","Asap","Play","Archivo Narrow","Signika",
    "Quicksand","Vollkorn","Merriweather Sans","Cuprum","Alegreya","Cinzel","Varela Round","Amatic SC","Maven Pro","Orbitron",
    "Karla","Dancing Script","Questrial","Righteous","Rokkitt","PT Sans Caption","Bangers","Architects Daughter","Exo","Chewy",
    "Patua One","Pathway Gothic One","BenchNine","Abril Fatface","Quattrocento Sans","Monda","Crete Round","Sigmar One","Josefin Slab"
    ,"Ropa Sans","News Cycle","Russo One","Istok Web","Kaushan Script","Covered By Your Grace","Lobster Two","EB Garamond","Comfortaa"
    ,"Pontano Sans","Cantarell","Didact Gothic","Gudea","Gloria Hallelujah","Poppins","Noticia Text","Coming Soon","Rambla",
    "Fredoka One","Philosopher","Old Standard TT","Armata","Archivo Black","Satisfy","ABeeZee","Hammersmith One","Rubik",
    "Cabin Condensed","Source Code Pro","Sanchez","Tinos","Acme","Handlee","Economica","Domine","Voltaire","Alfa Slab One",
    "Quattrocento","Kreon","Courgette","Rock Salt","Ruda","Cookie","Work Sans","Permanent Marker","Roboto Mono","Varela","Jura",
    "Shadows Into Light Two","Gentium Book Basic","Pinyon Script","Cardo","Actor","Vidaloka","Bevan","Great Vibes","Fauna One",
    "Paytone One","Tangerine","Playball","Luckiest Guy","Antic Slab","Volkhov","Amaranth","Changa One","Droid Sans Mono","Nobile",
    "Sorts Mill Goudy","Audiowide","Sintony","Bad Script","Marck Script","Special Elite","Niconne","Enriqueta","Arapey","Basic","Amiri"
    ,"Oleo Script","Fugaz One","Damion","Chivo","Squada One","Playfair Display SC","Kanit","Molengo","Signika Negative","Boogaloo",
    "Source Serif Pro","Scada","Calligraffitti","Viga","Glegoo","Marmelad","Copse","Just Another Hand","Gochi Hand","Coda","Doppio One",
    "Antic","Limelight","Nixie One","Cantata One","Allerta Stencil","Black Ops One","Aldrich","Patrick Hand","Homemade Apple","Neuton",
    "Overlock","Alice","Nothing You Could Do","Crafty Girls","Alegreya Sans SC","Carme","Jockey One","Khula","Waiting for the Sunrise",
    "Michroma","Average","Gentium Basic","Share","Lusitana","Days One","Spinnaker","Ultra","Marcellus","Rajdhani","Khand","Sacramento",
    "Julius Sans One","Walter Turncoat","Cherry Cream Soda","Electrolize","Bubblegum Sans","Contrail One","Catamaran","Homenaje",
    "Allerta","Syncopate","Alex Brush","Radley","Magra","Teko","Ceviche One","Montserrat Alternates","Fontdiner Swanky",
    "Fredericka the Great","Ubuntu Mono","Puritan","Kameron","PT Mono","Kalam","Adamina","Allura","Grand Hotel","Quantico","Marvel",
    "Telex","Rancho","Unica One","Reenie Beanie","PT Serif Caption","Finger Paint","Average Sans","Oranienbaum","Freckle Face",
    "Six Caps","Coustard","Prata","Carrois Gothic","Advent Pro","Neucha","Yellowtail","Rochester","Chelsea Market","Annie Use Your Telescope",
    "Merienda","Sansita One","Cabin Sketch","Pragati Narrow","Cutive","Lekton","Forum","Parisienne","Marcellus SC","Aclonica","Denk One",
    "Goudy Bookletter 1911","Alef","Cinzel Decorative","Berkshire Swash","Rosario","Ek Mukta","Convergence","Montez","Cambay","Schoolbell",
    "Trocchi","Arbutus Slab","Port Lligat Slab","Halant","Kelly Slab","Corben","Alegreya SC","Cousine","Leckerli One","Yesteryear",
    "Short Stack","Press Start 2P","Lustria","Belleza","Caudex","Mako","Lemon","Timmana","Frijole","Merienda One","Tenor Sans","Inder"
    ,"Engagement","Metrophobic","Graduate","Alike","Slackey","Fenix","Capriola","Gruppo","Carter One","Duru Sans","VT323","Prosto One"
    ,"Unkempt","Tauri","Sue Ellen Francisco","Strait","Monoton","Lilita One","Baumans","Give You Glory","Petit Formal Script","Ovo",
    "Racing Sans One","Gilda Display","Creepster","UnifrakturMaguntia","Italianno","Anaheim","Headland One","Just Me Again Down Here",
    "Quando","The Girl Next Door","Bowlby One","Oxygen Mono","Mr De Haviland","Mouse Memoirs","Allan","Lateef","Londrina Solid",
    "Hind Siliguri","Skranji","Delius","Anonymous Pro","Imprima","Sarala","Brawler","Nova Square","Wire One","Rufina",
    "Oleo Script Swash Caps","Averia Sans Libre","Cutive Mono","IM Fell DW Pica","Andika","Bowlby One SC","Sofia","Gravitas One","Crushed",
    "Judson","La Belle Aurore","Fanwood Text","Oregano","Bentham","Mr Dafoe","Herr Von Muellerhoff","Andada","IM Fell English SC","Knewave",
    "Arizonia","Loved by the King","Slabo 13px","Mate","Kadwa","Love Ya Like A Sister","Oldenburg","Kotta One","Megrim","Pompiere",
    "Happy Monkey","Yantramanav","Englebert","Yeseva One","Orienta","Belgrano","Simonetta","Patrick Hand SC","Euphoria Script","Balthazar",
    "Gafata","Geo","Karma","Aladin","Lily Script One","Kranky","Qwigley","Fjord One","Rationale","Norican","Podkova","Seaweed Script",
    "Mountains of Christmas","Caesar Dressing","Gabriela","Griffy","Poly","Quintessential","Dorsa","IM Fell English","Averia Serif Libre",
    "Shanti","Over the Rainbow","Stardos Stencil","Carrois Gothic SC","Expletus Sans","Life Savers","Mate SC","Bilbo Swash Caps","Fondamento",
    "Chau Philomene One","Share Tech","Salsa","Inika","Meddon","Italiana","Federo","Stalemate","Tienne","Buenard","Fira Mono",
    "Holtwood One SC","Hind Vadodara","Voces","Delius Swash Caps","Nova Mono","Titan One","Kristi","Unna","Tulpen One","Share Tech Mono",
    "Dawning of a New Day","Coda Caption","Clicker Script","Itim","Sniglet","Galindo","Maiden Orange","Sail","Kite One",
    "Stint Ultra Condensed","Poller One","Concert One","Codystar","Cambo","IM Fell Double Pica","Amethysta","Shojumaru","Cedarville Cursive"
    ,"Rouge Script","Ledger","Mallanna","NTR","Cantora One","Amarante","Cherry Swash","Raleway Dots","Nosifer","IM Fell French Canon",
    "Milonga","Sancreek","Ruslan Display","Vast Shadow","Medula One","Aguafina Script","Rubik One","Swanky and Moo Moo","Metamorphous",
    "Zeyada","Gurajada","Flamenco","Biryani","Sumana","Condiment","Kurale","Iceland","Martel","IM Fell Double Pica SC","Rammetto One","Junge",
    "Esteban","McLaren","Palanquin","Overlock SC","Rye","Martel Sans","Monofett","Caveat Brush","Cagliostro","Butcherman","Prociono",
    "Jolly Lodger","Donegal One","Dynalight","Habibi","IM Fell DW Pica SC","Artifika","Henny Penny","IM Fell French Canon SC","Ramabhadra",
    "Stint Ultra Expanded","Rosarivo","Sarina","Jacques Francois","Nova Round","Redressed","Trade Winds","Ruthie","Stoke","Kavoon","Paprika",
    "Averia Libre","Ruluko","Krona One","Buda","Sunshiney","Montserrat Subrayada","Piedra","Vibur","Wendy One","IM Fell Great Primer SC",
    "Delius Unicase","Wallpoet","Numans","Text Me One","IM Fell Great Primer","Pirata One","Bilbo","Irish Grover","Chango","Caveat",
    "Wellfleet","Glass Antiqua","Sonsie One","Smythe","Port Lligat Sans","Rubik Mono One","Linden Hill","Asul","Alike Angular","Offside",
    "Scheherazade","Mrs Saint Delafield","Ribeye","Snippet","New Rocker","Elsie","Nova Slim","League Script","Julee","Antic Didone",
    "Della Respira","Mystery Quest","Peralta","Trochut","MediSharp","UnifrakturCook","Dr Sugiyama","Palanquin Dark","Trykker","Bigshot One",
    "Snowburst One","Germania One","Kenia","Iceberg","Miniver","Joti One","Lovers Quarrel","Suranna","Autour One","Astloch","Keania One",
    "Londrina Shadow","Ribeye Marrow","Combo","Bubbler One","Akronim","Sura","Miltonian Tattoo","Croissant One","Elsie Swash Caps",
    "Jacques Francois Shadow","Fresca","Gorditas","Nova Flat","Modern Antiqua","Original Surfer","Spicy Rice","Purple Purse","Petrona",
    "Monsieur La Doulaise","Emilys Candy","Sarpanch","Faster One","Devonshire","Galdeano","Fascinate","Rozha One","Ranchers","Montaga",
    "Passero One","Jaldi","Lancelot","Atomic Age","Eagle Lake","Underdog","Diplomata","Averia Gruesa Libre","Londrina Sketch","Almendra",
    "Asset","Aubrey","Warnes","Laila","Geostar","Geostar Fill","Margarine","Sofadi One","Vampiro One","Mrs Sheppards","Miltonian","Ewert",
    "Dekko","Meie Script","Goblin One","Smokum","Mandali","Butterfly Kids","Chicle","Vesper Libre","Seymour One","Almendra SC","Chela One",
    "Felipa","Nova Script","Rum Raisin","Ria","Metal Mania","Princess Sofia","Spirax","Marko One","Romanesco","Nova Oval","Almendra Display",
    "Nova Cut","Federant","Uncial Antiqua","Plaster","Chonburi","Molle","Londrina Outline","Ramaraja","Supermercado One","Ranga",
    "Macondo Swash Caps","Dhurjati","Diplomata SC","Miss Fajardose","Eater","Arbutus","Fascinate Inline","Rhodium Libre","Stalinist One",
    "Arya","Sirin Stencil","Bigelow Rules","Macondo","Sevillana","Mr Bedfort","Gidugu","Risque","Amita","Ruge Boogie","Erica One","Flavors",
    "Jim Nightshade","Emblema One","Bonbon","Tenali Ramakrishna","Tillana","Suravaram","Sree Krushnadevaraya","Sahitya","Unlock",
    "Hanalei Fill","Hanalei","Eczar","Lakki Reddy","Inknut Antiqua","Asar","Fruktur","Peddana","Modak","Ravi Prakash"
    );

    $safe_fonts = array(
      "Arial",
      "Georgia",
      "Times New Roman",
      "Courier New",
      "Sans Serif",
      "Verdana",
      "Trebuchet MS",
      "Tahoma"
    );

    foreach ($g_fonts as $i) {
        $g_font_list[] = array(
          "label" => $i,
          "value" => $i
        );
    }

    foreach ($safe_fonts as $i) {
        $safe_font_list[] = array(
          "label" => $i,
          "value" => $i . '/Native'
        );
    }

    // Get CSS file headers
    $gfsp_themes = gfsp_get_themes();

    $all_themes_options[] =
    array(
        "fields" =>
            array(
                array(
                "label"   => "Select a theme to customize",
                "type"    => "select",
                "onchange" => "toggleTheme( jQuery( this ).val() )",
                "name"    => "theme",
                "choices" => $gfsp_themes
                )
            )
    );


    if ( is_array( $gfsp_themes ) || is_object( $gfsp_themes ) )
    {
        
        foreach ( $gfsp_themes as $key => $theme ) {

            $font = explode( "/", $theme['font'] );
            $font_label = explode( "/", $theme['font_label'] );

            $font_size = isset( $font[0] ) ? $font[0] : '';
            $font_name = isset( $font[1] ) ? $font[1] : '';
            $font_color = isset( $font[2] ) ? $font[2] : '';

            $font_label_size = isset( $font_label[0] ) ? $font_label[0] : '';
            $font_label_name = isset( $font_label[1] ) ? $font_label[1] : '';
            $font_label_color = isset( $font_label[2] ) ? $font_label[2] : '';
                        
            $fields_array =
            array(
                    /*
                    *   Label Font Settings 
                    */
                    array(
                        'label'         => esc_html__( 'Label Font', 'gf_stylespro' ),
                        'type'          => 'select',
                        "name"          => $theme['value']."_label_font",
                        "class"         => 'label_font',
                        'default_value' => $font_label_name,
                        'data-default'  => $font_label_name,
                        'choices'       =>
                            array(
                                array(
                                    'label'   => esc_html__( 'Inherit theme fonts', 'gf_stylespro' ),
                                    'value'  => 'inherit/Native',
                                ),
                                array(
                                    'label'   => esc_html__( 'Web safe/Native fonts', 'gf_stylespro' ),
                                    'disabled'  => 'disabled',
                                    'choices' => $safe_font_list
                                ),
                                array(
                                    'label' => esc_html__( 'Google Fonts', 'gf_stylespro' ),
                                    'disabled' => 'disabled',
                                    'choices' => $g_font_list
                                )
                            )
                    ),

                    array(
                        "type"    => "checkbox",
                        "class"   => "google_font",
                        "name"  => $theme['value']."_label_font_load_cb",
                        "choices" =>
                            array(
                                array(
                                    "label" => "Don't load the font",
                                    "tooltip" => "Useful only when the selected Google font is already loaded on the page. If in doubt, leave unchecked.",
                                    "class" =>  "label_font_load",
                                    "name"  => $theme['value']."_label_font_load"
                                )
                            )
                    ),

                    array(
                        "label"   => "Size",
                        "type"    => "text",
                        "class"   => "label_font_size",
                        "name"    => $theme['value']."_label_font_size",
                        "default_value" => $font_label_size,
                        "data-default" => $font_label_size,
                        "after_input" => "<i> add units: em/px/pt etc.</i>",
                    ),

                    array(
                        "type"    => "checkbox",
                        "class"   => "label_font_style",
                        "name"    => $theme['value']."_label_font_styles",
                        "label"   => "Style",
                        "horizontal" => true,
                        "choices" =>
                            array(
                                array(
                                    "label" => "Bold",
                                    "class" =>  "label_font_bold",
                                    "name"  => $theme['value']."_label_font_bold",
                                    "require" => false
                                ),
                                array(
                                    "label" => "Italic",
                                    "class" =>  "label_font_italic",
                                    "name"  => $theme['value']."_label_font_italic",
                                    "require" => false
                                ),
                                array(
                                    "label" => "Underlined",
                                    "class" =>  "label_font_underline",
                                    "name"  => $theme['value']."_label_font_underline",
                                    "require" => false
                                )
                            )
                    ),

                    array(
                        "label"   => "Color",
                        "type"    => "text",
                        "class"   => "label_font_color color",
                        "name"    => $theme['value']."_label_font_color",
                        "default_value" => $font_label_color,
                        "data-default" => $font_label_color,
                    ),



                    /*
                    *   Font Settings
                    */
                    array(
                        'label'         => esc_html__( 'Field Font', 'gf_stylespro' ),
                        'type'          => 'select',
                        "name"    => $theme['value']."_font",
                        'default_value' => $font_name,
                        'data-default' => $font_name,
                        'class'         => 'font',
                        'choices'       =>
                            array(
                                array(
                                    'label'   => esc_html__( 'Inherit theme fonts', 'gf_stylespro' ),
                                    'value'  => 'inherit/Native',
                                ),
                                array(
                                    'label'   => esc_html__( 'Web safe/Native fonts', 'gf_stylespro' ),
                                    'disabled'  => 'disabled',
                                    'choices' => $safe_font_list
                                ),
                                array(
                                    'label' => esc_html__( 'Google Fonts', 'gf_stylespro' ),
                                    'disabled' => 'disabled',
                                    'choices' => $g_font_list
                                )
                            )
                    ),

                    array(
                        "type"    => "checkbox",
                        "class"   => "google_font",
                        "name"  => $theme['value']."_font_load_cb",
                        "choices" =>
                            array(
                                array(
                                    "label" => "Don't load the font",
                                    "tooltip" => "Useful only when the selected Google font is already loaded on the page. If in doubt, leave unchecked.",
                                    "class" =>  "font_load",
                                    "name"  => $theme['value']."_font_load"
                                )
                            )
                    ),

                    array(
                        "label"   => "Size",
                        "type"    => "text",
                        "class"   => "font_size",
                        "default_value" => $font_size,
                        "data-default" => $font_size,
                        "after_input" => "<i> add units: em/px/pt etc.</i>",
                        "name"    => $theme['value']."_font_size"
                    ),

                    array(
                        "type"    => "checkbox",
                        "label"   => "Style",
                        "class"   => "font_style",
                        "name"    => $theme['value']."_font_styles",
                        "horizontal" => true,
                        "choices" =>
                            array(
                                array(
                                    "label" => "Bold",
                                    "class" => "font_bold",
                                    "name"  => $theme['value']."_font_bold"
                                ),
                                array(
                                    "label" => "Italic",
                                    "class" => "font_italic",
                                    "name"  => $theme['value']."_font_italic"
                                ),
                                array(
                                    "label" => "Underlined",
                                    "class" =>  "font_underline",
                                    "name"  => $theme['value']."_font_underline"
                                )
                            )
                    ),

                    array(
                        "label"   => "Color",
                        "type"    => "text",
                        "class"   => "font_color color",
                        "default_value" => $font_color,
                        "data-default" => $font_color,
                        "name"    => $theme['value']."_font_color"
                    ),



                    /*
                    Field Icon Color
                    */
                    array(
                        "label"   => "Field Icon Color",
                        "type"    => "text",
                        "class"   => "field_icon_color color",
                        "default_value" => $theme['field_icon_color'],
                        "data-default" => $theme['field_icon_color'],
                        'tooltip'    => esc_html__( 'Default color for field icons <i>(does not affect radio/checkbox icons)</i>.<br>Icons for individial fields can be changed from field settings.', 'gf_stylespro' ),
                        "name"    => $theme['value']."_field_icon_color"
                    ),



                    /*
                    Field Margin
                    */
                    array(
                        "label"   => "Field Margin <i>(Bottom)</i>",
                        "type"    => "text",
                        "class"   => "field_margin_bottom",
                        "default_value" => ( $theme['field_margin'] != '' ? $theme['field_margin'] : '1em' ),
                        "data-default" => ( $theme['field_margin'] != '' ? $theme['field_margin'] : '1em' ),
                        'tooltip'    => esc_html__( 'A lower value makes the fields closer, usually handy on smaller forms when you need fields to be tighter together.', 'gf_stylespro' ),
                        "after_input" => "<i> add units: em/px/pt etc.</i>",
                        "name"    => $theme['value']."_field_margin_bottom"
                    ),

                    /* 
                    Advanced Field Styling
                    */
                    array(
                        'label' => 'Advanced field styling',
                        'type'  => 'adv_field_styles',
                        'name'  => $theme['value'].'_adv_field_styles',
                        'theme_name' => $theme['value']
                    ),

                    /*
                    Button background color
                    */
                    array(
                        "label"   => "Button text color",
                        "type"    => "text",
                        "class"   => "btn_color color",
                        'tooltip'    => esc_html__( 'Customize text color for form buttons like Next, Previous, Submit. Leave blank to use default.', 'gf_stylespro' ),
                        "name"    => $theme['value']."_btn_color"
                    ),

                    /*
                    Button font color
                    */
                    array(
                        "label"   => "Button background",
                        "type"    => "text",
                        "class"   => "btn_bg color",
                        'tooltip'    => esc_html__( 'Customize background color for form buttons like Next, Previous, Submit. Leave blank to use default.', 'gf_stylespro' ),
                        "name"    => $theme['value']."_btn_bg"
                    ),

                    /*
                    List highlight background color
                    */
                    array(
                        "label"   => "List highlight background",
                        "type"    => "text",
                        "class"   => "o_custom_bg color",
                        "after_input" => "<i> (only Frame and List styles are supported)</i>",
                        'tooltip'    => esc_html__( 'Highlight color for selected items when using Frame and List styles on Checkboxes and Radio feilds.<br>Use a nice contrasting color with white, which is the default text color', 'gf_stylespro' ),
                        "name"    => $theme['value']."_o_custom_bg"
                    ),

                    /* 
                    Advanced Button Styling
                    */
                    array(
                        'label' => 'Advanced button styling',
                        'type'  => 'adv_btn_styles',
                        'name'  => $theme['value'].'_adv_btn_styles',
                        'theme_name' => $theme['value']
                    ),


                    /*
                    Background
                    */
                    array(
                        'label'      => esc_html__( 'Background', 'gf_stylespro' ),
                        'type'       => 'radio',
                        'horizontal' => true,
                        'name'       => $theme['value'].'_background',
                        'class'      => 'background',
                        'data-theme' => $theme['value'],
                        'tooltip'    => esc_html__( 'Form background settings.', 'gf_stylespro' ),
                        'default_value' => 'default',
                        'onchange'   => 'toggleBgOption("'.$theme['value'].'", jQuery(this).val() )',
                        'choices'    =>
                            array(
                                array(
                                    'label' => esc_html__( 'Default', 'gf_stylespro' ),
                                    'value' => 'default'
                                ),
                                array(
                                    'label' => esc_html__( 'None/transparent', 'gf_stylespro' ),
                                    'value' => 'none'
                                ),
                                array(
                                    'label' => esc_html__( 'Color', 'gf_stylespro' ),
                                    'value' => 'color'
                                ),
                                array(
                                    'label' => esc_html__( 'Image', 'gf_stylespro' ),
                                    'value' => 'image'
                                )
                            )
                    ),

                    array(
                        "label"   => "Background Color",
                        "type"    => "text",
                        "class"   => "bg_color color",
                        "name"    => $theme['value']."_bg_color",
                        "default_value" => "#fff",
                        "data-default" => "#fff"
                    ),
                    
                    array(
                        "label"   => "Background Image",
                        "type"    => "text",
                        "class"   => "bg_image",
                        "name"    => $theme['value']."_bg_image",
                        "after_input" => '<button class="media-button">Select image</button>',
                    ),

                    array(
                        "type"    => "textarea",
                        "class"   => "save_css",
                        "style"   =>  "display:none!important",
                        "name"    => $theme['value']."_theme_css"
                    ),

            );

            $desc_script = '';
            if ( $theme['scripts'] ) {

                $scripts_option = array(
                array(
                    "type"    => "checkbox",
                    "name"  => $theme['value']."_scripts_load",
                    "choices" =>
                        array(
                            array(
                                "label" => "Enable additional sctipts",
                                "tooltip" => "This theme includes additional scripts that can be enabled here to enhance experience.",
                                "name"  => $theme['value']."_scripts_load"
                            )
                        )
                ),
                array(
                    "type"    => "hidden",
                    "name"  => $theme['value']."_scripts",
                    /**
                     * FIXME: The value is only fetched once, once the settings are saved, the followig doesn't work
                     * */ 
                    "value" => preg_replace("/[^a-z-_]/i", "", $theme['scripts']) // Keep it simple and secure
                    )
                );

                $fields_array = array_merge($scripts_option, $fields_array);
                $desc_script = '<br><br>This theme includes additional scripts that can be enabled to add functionality.';
            }

            $all_themes_options[] =
            array(
                "title" => "Customize theme: " . $theme['label'],
                "class" => "thm " . $theme['value'],
                "id"    =>  $theme['value'],
                "description"   => $theme['desc'] . ' <span class="theme_additional">theme slug: '.$theme['value'].' | <a href="#" onclick="resetToDefaults(\'' . $theme['value'] . '\',\'' . $theme['label'] . '\'">reset theme changes</a></span> <br><br>You may use any Google Font from the lists (<a target="_blank" href="https://fonts.google.com/">preview Google Fonts</a>). Please note that GF Styles Pro will only load fonts on pages which use a form with this theme, ensuring best practices for speed.'. $desc_script,
                "fields" => $fields_array 
            );


        }

        // Add None option to the theme list for Default Theme select options
        array_unshift ($gfsp_themes, array("label" => "None", "value" => "") );

        $all_themes_options[] =
        array(
            "title"  => "Universal Settings",
            "fields" =>
            array(
                array(
                    "label"   => "Default Theme",
                    "type"    => "select",
                    "name"    => "default_theme",
                    "tooltip" => "All forms will use this theme by default, unless overridden from Form Settings",
                    "choices" => $gfsp_themes
                ),
                array(
                    "label"   => "Custom CSS",
                    "tooltip" => "Add your custom CSS overrides here",
                    "type"    => "checkbox",
                    "name"    => "custom_css_checkbox",
                    "choices" =>
                        array(
                            array(
                                "label" => "Enabled",
                                "name"  => "enable_css"
                            )
                        )
                ),
                array(
                    "type"    => "textarea",
                    "name"    => "gfsp_custom_css",
                    "class"   => "medium merge-tag-support mt-position-right"
                ),
                array(
                    "label"   => "Reinforce customization",
                    "type"    => "checkbox",
                    "name"    => "reinforce_styles_checkbox",
                    "choices" =>
                    array(
                        array(
                                "tooltip" => "Sometimes useful for avoiding styling conflicts with themes and other plugins. Leaving it unchecked allows for writing easier custom CSS.",
                                "label" => "Reinforce styles",
                                "name"  => "reinforce_styles"
                            )
                        )
                ),
                array(
                    "type"    => "save",
                    "value"   => "Update All Settings"
                )
            )
        )
        ?>
<style>
.theme_additional {
float: right;
font-style: italic;
font-weight: 100;
}
.theme_additional a {
font-weight: 500;
}
.button.show_adv_styles { margin-bottom: 27px; }
.adv_field_options_wrapper { border: 1px solid #e7ebed; background-color: #f3f7f9;
border-radius: 0 0 5px 5px; margin-bottom: 30px; box-shadow: 0px 1px 1px 0px rgba(0,0,0,0.1); }
.adv_field_options_wrapper h5 { margin: 0; padding: 1em; background: #fcfefe; font-size: .9em; font-weight: 700; }
.adv_fields { padding: 30px }
.adv_fields span { margin-right: 5px; }
.adv_field {
display: inline-block; margin-left: 0px;
margin-right: 15px; margin-top: 5px; margin-bottom: 5px; }
.adv_fields b {
float: left; display: inline-block; color: #358ab2;
font-weight: 700; min-width: 110px; margin-bottom: 15px; margin-top: 10px; }
.adv_fields hr { margin: 30px 0; }
.adv_fields i { font-weight: 100; }
.adv_field { display: inline-block; margin-left: 0px; margin-right: 15px; }
input.small {width: 4em;}
tr[id$=theme_css] { display: none }
h4.gaddon-section-title { font-size: 1.3em }
tr[id$=label_font_color] > *, tr[id$=o_custom_bg] > * { padding-bottom: 38px !important; }
table.form-table.gforms_form_settings { margin-top: 18px }
.gaddon-setting-inline { margin-right: 1.5em }
div[id$=_bold].gaddon-setting-inline label { font-weight: bold }
div[id$=_italic].gaddon-setting-inline label { font-style: italic }
div[id$=_underline].gaddon-setting-inline label { text-decoration: underline }
tr[id$=_font] th,tr[id$=btn_color] th,tr[id$=background] th, tr[id$=adv_field_styles] th, tr[id$=adv_btn_styles] th { font-weight: bold !important; }
tr[id$=_font] > *, tr[id$=btn_color] > *, tr[id$=background] > * { border-top: 1px inset #ced6d6; padding-top: 38px !important; }
tr#gaddon-setting-row-theme th, tr#gaddon-setting-row-theme td, tr#gaddon-setting-row-theme select { line-height: 1.7; color: #0074a2 }
tr#gaddon-setting-row-theme select { border-left: 2px solid #2ea2cc }
.gaddon-section.thm{ padding-top: 0px }
.thm > h4 { color: #0074a2 }
</style>
        <?php
        ;
      return $all_themes_options;
    }
}



add_filter( 'gform_tooltips', 'gf_stylespro_tooltips' );

function gf_stylespro_tooltips( $tooltips ) {
	/*
	* Register tooltip for Field Settings
	*/
	$tooltips['gf_stylespro_value'] = "<h6>" . __( "Advanced Styles</h6>", "gf_stylespro" )  . __( "Choose advanced styling options for this field.", "gf_stylespro");
    
    return $tooltips;
}



/*
* Add a wrapper for GravityView plugin
*/
add_action('gravityview/edit-entry/render/before', 'gfsp_gravityview_wrapper', 10, 1);
function gfsp_gravityview_wrapper( $instance ) {

    $default_theme = '';
    $theme = '';

    if ( class_exists('StylesPro') ) {
        $StylesPro = StylesPro::get_instance();
        $gfsp_plugin_settings = $StylesPro->get_plugin_settings();

        // Get default theme
        if ( array_key_exists('default_theme', $gfsp_plugin_settings) )
            $default_theme = $gfsp_plugin_settings['default_theme'];
    }
    
    $theme = $default_theme;

    if ( ! empty ( $instance->form['gf_stylespro']['theme'] ) )
        $theme = $instance->form['gf_stylespro']['theme'];
    
    else if ( ! empty ( $instance->form['gf_stylespro_theme'] ) )
        $theme = $instance->form['gf_stylespro_theme'];
    
    if ( ! empty($theme) && $theme != "none" ) {
        echo '<div class="gf_stylespro ' . $theme . '">';

        add_action('gravityview/edit-entry/render/after', function() {
            echo '</div>';
        });

    }
}


