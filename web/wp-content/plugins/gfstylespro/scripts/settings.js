// Reset Theme Settings
function resetToDefaults (theme, label) {
  var res = confirm('Reset ' + label + ' Theme to default settings?');
    if (res == true) {
    jQuery('.thm.' + theme + ' input[type=text], .thm.' + theme + ' select').each( function () { jQuery(this).val( jQuery(this).data('default')) } );
    jQuery('.thm.' + theme + ' input[type="checkbox"]').each( function () { if (   jQuery(this).prop('checked') == true ) { jQuery(this).trigger('click') } });
    jQuery('.thm.' + theme + ' .background').each( function () { if (jQuery(this).val() == 'default' ) { jQuery(this).trigger('click') } });

    // Advanced Settings

    jQuery('.thm.' + theme + ' .adv_field_options_wrapper').find('input, select').trigger('blur').css('background-color', '')
    
    // Fix for Color Picker
    if (typeof lastColorPicker !== 'undefined') {
        jQuery(lastColorPicker).trigger('change');
    }
  }
}

/* Hide all theme and Show @param: showTheme */
function toggleTheme (showTheme) {
  jQuery('.thm').slideUp();
  jQuery('.' + showTheme).slideDown();
}

/* Show and hide background options */
function toggleBgOption(theme, bg_type) {
    jQuery('#gaddon-setting-row-' + theme + '_bg_color, #gaddon-setting-row-' + theme + '_bg_image').hide();

    if (bg_type == 'color') {
        jQuery('#gaddon-setting-row-' + theme + '_bg_color').slideDown();
    }

    if (bg_type == 'image') {
        jQuery('#gaddon-setting-row-' + theme + '_bg_image').slideDown();
    }
}

// Media uploader
var gk_media_init = function(selector, button_selector)  {
    var clicked_button = false;

    jQuery(selector).each(function (i, input) {
        var button = jQuery(input).next(button_selector);
        button.click(function (event) {
            event.preventDefault();
            var selected_img;
            clicked_button = jQuery(this);

            // check for media manager instance
            if(wp.media.frames.gk_frame) {
                wp.media.frames.gk_frame.open();
                return;
            }
            // configuration of the media manager new instance
            wp.media.frames.gk_frame = wp.media({
                title: 'Select image',
                multiple: false,
                library: {
                    type: 'image'
                },
                button: {
                    text: 'Use selected image'
                }
            });

            // Function used for the image selection and media manager closing
            var gk_media_set_image = function() {
                var selection = wp.media.frames.gk_frame.state().get('selection');

                // no selection
                if (!selection) {
                    return;
                }

                // iterate through selected elements
                selection.each(function(attachment) {
                    var url = attachment.attributes.url;
                    clicked_button.prev(selector).val(url);
                });
            };

            // closing event for media manger
            wp.media.frames.gk_frame.on('close', gk_media_set_image);
            // image selection event
            wp.media.frames.gk_frame.on('select', gk_media_set_image);
            // showing media manager
            wp.media.frames.gk_frame.open();
        });
});
};

jQuery(document).ready(function() {

    jQuery(".thm").css('display', 'none');
    jQuery("." + jQuery('#theme').val() ).slideDown();

    /* Generate CSS from settings and submit form */
    jQuery('#gform-settings').submit(function() {

        var arThm = new Array();
        var reinforce = jQuery('#reinforce_styles').is(':checked') ? ' !important;' : ';';
        // console.log( reinforce )
        jQuery('.thm').each(function () {
            // assign values for theme options to vairables
            theme = jQuery(this).attr('id');

            font_get = jQuery(this).find('.font').val();

            font_bold = jQuery(this).find('.font_bold').is(':checked') ? 'font-weight: bold'+ reinforce : ' ';
            font_italic = jQuery(this).find('.font_italic').is(':checked') ? 'font-style: italic'+ reinforce : ' ';
            font_underline = jQuery(this).find('.font_underline').is(':checked') ? 'text-decoration: underline'+ reinforce : ' ';
            font_size = jQuery(this).find('.font_size').val();
            font_color = jQuery(this).find('.font_color').val();
            font_ph_color = jQuery(this).find('.font_color').data('ph-color');

            o_custom_bg = jQuery(this).find('.o_custom_bg').val();
            field_icon_color = jQuery(this).find('.field_icon_color').val();
            field_margin_bottom = jQuery(this).find('.field_margin_bottom').val();

            btn_color = jQuery(this).find('.btn_color').val();
            btn_bg = jQuery(this).find('.btn_bg').val();

            label_font_get = jQuery(this).find('.label_font').val();
            label_font_bold = jQuery(this).find('.label_font_bold').is(':checked')  ? 'font-weight: bold'+ reinforce : ' ';
            label_font_italic = jQuery(this).find('.label_font_italic').is(':checked') ? 'font-style: italic'+ reinforce : ' ';
            label_font_underline = jQuery(this).find('.label_font_underline').is(':checked') ? 'text-decoration: underline'+ reinforce : ' ';
            label_font_size = jQuery(this).find('.label_font_size').val();
            label_font_color = jQuery(this).find('.label_font_color').val();

            font = font_get.split('/')
            label_font = label_font_get.split('/')

            label_font_load = jQuery(this).find('.label_font_load').is(':checked');
            font_load = jQuery(this).find('.font_load').is(':checked');

            bg_type = jQuery(this).find('.background:checked').val();
            bg_color = jQuery(this).find('.bg_color').val();
            bg_image = jQuery(this).find('.bg_image').val();

            adv_bg_color = jQuery(this).find('.adv_bg_color').val();
            adv_v_padding = jQuery(this).find('.adv_v_padding').val();
            adv_border_width = jQuery(this).find('.adv_border_width').val();
            adv_border_radius = jQuery(this).find('.adv_border_radius').val();
            adv_border_style = jQuery(this).find('.adv_border_style').val();
            adv_border_color = jQuery(this).find('.adv_border_color').val();
            adv_focus_bg_color = jQuery(this).find('.adv_focus_bg_color').val();
            adv_focus_border_color = jQuery(this).find('.adv_focus_border_color').val();

            adv_btn_v_padding = jQuery(this).find('.adv_btn_v_padding').val();
            adv_btn_h_padding = jQuery(this).find('.adv_btn_h_padding').val();
            adv_btn_border_width = jQuery(this).find('.adv_btn_border_width').val();
            adv_btn_border_radius = jQuery(this).find('.adv_btn_border_radius').val();
            adv_btn_border_style = jQuery(this).find('.adv_btn_border_style').val();
            adv_btn_border_color = jQuery(this).find('.adv_btn_border_color').val();
            adv_btn_hover_color = jQuery(this).find('.adv_btn_hover_color').val();
            adv_btn_hover_border_color = jQuery(this).find('.adv_btn_hover_border_color').val();
            adv_btn_hover_bg_color = jQuery(this).find('.adv_btn_hover_bg_color').val();
            
            
            // generate CSS from options to enqueue
            enq = '';

                if (font[1] != 'Native' && font_load != true) {
                    enq += '@import url(https://fonts.googleapis.com/css?family=' + encodeURIComponent(font[0]) + '); ';
                }

                if (label_font[1] != 'Native' && label_font_load != true) {
                    enq += '@import url(https://fonts.googleapis.com/css?family=' + encodeURIComponent(label_font[0]) + '); ';
                }

                if ( bg_type == 'color' ) {
                    enq += '.' + theme + '_wrapper{background-color:' + bg_color + '} ';
                }

                if ( bg_type == 'image' ) {
                    enq += '.' + theme + '_wrapper{background:url(' + bg_image + ')} ';
                }

                if ( bg_type == 'none' ) {
                    enq += '.' + theme + '_wrapper{background: none}';
                }

                if (font[1] != 'Native') {
                    font[0] = '"' + font[0] + '"';
                }

                if (label_font[1] != 'Native') {
                    label_font[0] = '"' + label_font[0] + '"';
                }

enq += '.gf_stylespro.'+theme+' input,\
.gf_stylespro.'+theme+' select,\
.gf_stylespro.'+theme+' textarea,\
.gf_stylespro.'+theme+' .ginput_total,\
.gf_stylespro.'+theme+' .ginput_product_price,\
.gf_stylespro.'+theme+' .ginput_shipping_price,\
.'+theme+' .gfsp_icon,\
.gf_stylespro.'+theme+' input[type=checkbox]:not(old) + label,\
.gf_stylespro.'+theme+' input[type=radio   ]:not(old) + label,\
.gf_stylespro.'+theme+' .ginput_container {\
font-family: ' + font[0] + reinforce + ' color: '+ font_color + reinforce + ' font-size: ' + font_size + reinforce +' ' + font_bold + font_italic + font_underline +' }\
.gf_stylespro.'+theme+' *::-webkit-input-placeholder { font-family: ' + font[0] + reinforce + ' color: ' + font_ph_color + reinforce + ' }\
.gf_stylespro.'+theme+' *:-moz-placeholder { font-family: ' + font[0] +  reinforce +' color: ' + font_ph_color + reinforce + ' }\
.gf_stylespro.'+theme+' *::-moz-placeholder { font-family: ' + font[0] + reinforce + ' color: ' + font_ph_color + reinforce + ' }\
.gf_stylespro.'+theme+' *:-ms-input-placeholder { font-family: ' + font[0] + reinforce + ' color: ' + font_ph_color + reinforce + ' }\
.gf_stylespro.'+theme+' placeholder, .gf_stylespro.'+theme+' .gf_placeholder{ font-family: ' + font[0] + reinforce + ' color: ' + font_ph_color + reinforce + ' }\
.gf_stylespro.'+theme+' {\
font-family: ' + label_font[0] +  reinforce +' color: ' + label_font_color + reinforce + ' font-size: ' + label_font_size + reinforce + ' }\
.gf_stylespro.'+theme+' .button,\
.gf_stylespro.'+theme+' .gfield_label {\
font-family: ' + label_font[0] + reinforce + ' color: ' + label_font_color + reinforce + ' font-size: ' + label_font_size + reinforce + ' ' + label_font_bold + label_font_italic + label_font_underline +' }\
.gf_stylespro.'+theme+' .ginput_complex label {\
font-family: ' + label_font[0] + reinforce + ' color: ' + label_font_color + reinforce + ' }\
.'+theme+' .gfield_description,\
.gf_stylespro.'+theme+' .ginput_counter {\
font-family: ' + label_font[0] +  reinforce +' color: ' + label_font_color +  reinforce +'}\
.gf_stylespro.'+ theme +' .gfield {\
margin-bottom: '+ field_margin_bottom +  reinforce +'}';

if (field_icon_color != ''){
    enq+= '.'+theme+' .gfsp_icon { color: '+ field_icon_color +  reinforce +'}';    
}
if (o_custom_bg != '') {
enq += '.'+theme +' .o-custom-bg input:checked + label,\
.'+theme +' .o-custom-bg li:not(.gfsp_choice_icn):not(.gfsp_choice_img) :checked + label { background: '+ o_custom_bg + '}\
.'+theme +' .o-custom-bg input:checked + label:after{ color: '+ o_custom_bg +'}\
.'+theme +' .o-custom-border input:checked + label,\
.'+theme +' .o-custom-border li:not(.gfsp_choice_icn):not(.gfsp_choice_img) :checked + label { border-color: '+ o_custom_bg +' }';
}

adv_inset_margin = '';

/* Advanced Field */
if (adv_bg_color != '')
    adv_bg_color = 'background-color:'+adv_bg_color+''+ reinforce;

if (adv_v_padding != '')
    adv_v_padding = 'padding-top:'+adv_v_padding+'px '+ reinforce +' padding-bottom:'+adv_v_padding+'px '+ reinforce;

if ( adv_border_width != ''){
    adv_inset_margin = 'margin-top:'+adv_border_width+'px '+  reinforce;
    adv_border_width = 'border-width:'+adv_border_width+'px'+  reinforce;
}

if (adv_border_radius != '')
    adv_border_radius = 'border-radius:'+adv_border_radius+'px'+ reinforce;

if (adv_border_style != '')
    adv_border_style = 'border-style:'+adv_border_style + reinforce;

if (adv_border_color != '')
    adv_border_color = 'border-color:'+adv_border_color+reinforce;

if (adv_focus_bg_color != '')
    adv_focus_bg_color = 'background-color:'+adv_focus_bg_color+reinforce;

if (adv_focus_border_color != '')
    adv_focus_border_color = 'border-color:'+adv_focus_border_color+reinforce;

if (   adv_bg_color != ''
    || adv_v_padding != ''
    || adv_border_width != ''
    || adv_border_radius != ''
    || adv_border_style != ''
    || adv_border_color != ''
) {
    enq += '.'+theme+' input,.'+theme+' select,.'+theme+' textarea,.'+theme+' input[type="text"],.'+theme+' input[type="email"],\
.'+theme+' input[type="url"],.'+theme+' input[type="password"],.'+theme+' input[type="search"],.'+theme+' .chosen-choices {'
+ adv_bg_color + adv_v_padding + adv_border_width + adv_border_radius + adv_border_style + adv_border_color + '}';
}

if ( adv_v_padding != '' || adv_border_width != '' || adv_border_color != '' ) {
    enq += '.'+theme+' .gfsp_icon {'+adv_v_padding+adv_border_width+adv_border_color+'}';
}

if ( adv_border_width != ''){
    enq += '.'+theme+' .gf_icn_inset .gfsp_icon{'+adv_inset_margin+'}';
}

if ( adv_focus_bg_color != '' || adv_focus_border_color != '' ) {
    enq += '.'+theme+' input:focus,.'+theme+' select:focus,.'+theme+' textarea:focus,.'+theme+' input[type="text"]:focus,.'+theme+' input[type="email"]:focus,\
.'+theme+' input[type="url"]:focus,.'+theme+' input[type="password"]:focus,.'+theme+' input[type="search"]:focus{'+adv_focus_bg_color+adv_focus_border_color+'}';
}


/* Advanced Button Options */
if (adv_btn_v_padding != '')
    adv_btn_v_padding = 'padding-top:'+adv_btn_v_padding+'px '+ reinforce +' padding-bottom:'+adv_btn_v_padding+'px '+ reinforce;

if (adv_btn_h_padding != '')
    adv_btn_h_padding = 'padding-left:'+adv_btn_h_padding+'px '+ reinforce +' padding-right:'+adv_btn_h_padding+'px '+ reinforce;

if ( adv_btn_border_width != '')
    adv_btn_border_width = 'border-width:'+adv_btn_border_width+'px'+  reinforce;

if (adv_btn_border_radius != '')
    adv_btn_border_radius = 'border-radius:'+adv_btn_border_radius+'px'+ reinforce;

if (adv_btn_border_style != '')
    adv_btn_border_style = 'border-style:'+adv_btn_border_style + reinforce;

if (adv_btn_border_color != '')
    adv_btn_border_color = 'border-color:'+adv_btn_border_color+reinforce;
    
if (adv_btn_hover_color != '')
    adv_btn_hover_color = 'color:'+adv_btn_hover_color+reinforce;
    
if (adv_btn_hover_border_color != '')
    adv_btn_hover_border_color = 'border-color:'+adv_btn_hover_border_color+reinforce;
    
if (adv_btn_hover_bg_color != '')
    adv_btn_hover_bg_color = 'background-color:'+adv_btn_hover_bg_color+''+ reinforce;

/* Button Options */
if (btn_color != '')
    btn_color = 'color:'+btn_color+reinforce;

if (btn_bg != '')
    btn_bg = 'background:'+btn_bg+reinforce;

if (btn_color != '' ||
    btn_bg != '' ||
    adv_btn_v_padding != '' ||
    adv_btn_h_padding != '' ||
    adv_btn_border_width != '' ||
    adv_btn_border_radius != '' ||
    adv_btn_border_style != '' ||
    adv_btn_border_color != ''
) {
    enq += '.gf_stylespro.'+ theme +' .button {' + btn_bg + btn_color + adv_btn_v_padding + adv_btn_h_padding + adv_btn_border_width + adv_btn_border_radius + adv_btn_border_style + adv_btn_border_color + '}';
}

if (adv_btn_hover_color != '' || adv_btn_hover_border_color != '' || adv_btn_hover_bg_color != '' ) {
    enq += '.' + theme + ' .button:hover, .'+theme+' input[type=button]:hover,.'+theme+' input[type=submit]:hover {' + adv_btn_hover_color + adv_btn_hover_border_color + adv_btn_hover_bg_color + '}';
}

            jQuery( this ).find( '.save_css' ).val( enq );

            // Disable empty fields to exclude from saving
            jQuery( 'input, select').each( function() {
                if (jQuery(this).val() == '') {
                    jQuery(this).attr('disabled', 'disabled')
                }
            });

            arThm.push( jQuery( this ) );

        });
    });

    /* Initiate color picker */
    myColorPicker = jQuery(".color").colorPicker({
        opacity: true,

        convertCallback: function(colors, type) {
            rgb = colors.RND.rgb;
            placeholder_color = 'rgba(' + rgb['r'] + ', ' + rgb['g'] + ', ' + rgb['b'] + ', ' + (colors.alpha*0.47).toFixed(2) + ')';
        },
        renderCallback: function($elm, toggled) {
            if (typeof placeholder_color !== undefined && placeholder_color != '' && placeholder_color != null) {
                $elm.attr('data-ph-color', placeholder_color);
            }
            window.lastColorPicker = $elm;
        }
    });

    /* Calculate placeholder colors and save to the field's data-ph-color */
    jQuery(".font_color").each( function() {

        rgb = jQuery(this).colorPicker().colorPicker.color.colors.RND.rgb;
        alpha = jQuery(this).colorPicker().colorPicker.color.colors.alpha;
        placeholder_color = 'rgba(' + rgb['r'] + ', ' + rgb['g'] + ', ' + rgb['b'] + ', ' + (alpha*0.47).toFixed(2) + ')';

        jQuery(this).attr('data-ph-color', placeholder_color);
    });


    /* Apply Media Uploader to upload background image fields */
    gk_media_init('.bg_image', '.media-button');


    /* Set background options per saved or default settings */
    jQuery('.background:checked').each ( function() {
        toggleBgOption(jQuery( this ).data('theme'), jQuery( this ).val())
    });

    /* Empty Fields Styles */
    
    jQuery("input, select").each( function() {
        if (jQuery(this).val() == '')
            jQuery(this).css('opacity', '.7');
    });
    
    jQuery("input, select").on('blur', function() {
        if (jQuery(this).val() == '')
            jQuery(this).css('opacity', '.7');
    });
    
    jQuery("input, select").on('focus', function() {
            jQuery(this).css('opacity', '1');
    });

    /* If any field has value; Show Advanced Fields */
    adv_has_vals = [];
    jQuery('.adv_field_options_wrapper').each( function(i, $wrapper){
        jQuery(this).hide();
        jQuery.each(jQuery($wrapper).find('input, select'), function() {
                console.log( jQuery(this) );
            if ( jQuery(this).val() != '' ){
                adv_has_vals.push( $wrapper );
                return false;
            }
        })
    });
    
    jQuery(adv_has_vals).each(function() {
        jQuery(this).slideDown();
        jQuery(this).siblings('.button').slideUp();
    })
}); // Document ready ends