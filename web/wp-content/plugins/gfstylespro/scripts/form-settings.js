jQuery(document).ready( function() {
    // Insert Warning  messages
    // jQuery('#gaddon-setting-row-theme').after('');
    jQuery('#gaddon-setting-row-theme').after('<tr class="warning no_theme"><th></th><td col="2"><i class="fa fa-exclamation-triangle"></i> No theme is selected, default Gravity Forms styles will be used. <span class="to_enable" style="display: none;">Select a theme to enable all options</span></td></tr>');
    jQuery('#gaddon-setting-row-theme').after('<tr class="warning default"><th></th><td col="2"><i class="fa fa-exclamation-triangle"></i> When using the option Default Theme, <i><b id="default_theme_name"></b> Theme</i> will be applied to this form, as selected in Styles Pro Settings. <span class="to_enable" style="display: none;">Select a theme to enable all options</span></td></tr>');
    jQuery('#gaddon-setting-row-iconsets').after('<tr class="warning icons"><th></th><td col="2"><i class="fa fa-exclamation-triangle"></i> Consider page load time when enabling more than one icon-sets.</td></tr>');

    // Warning for No Theme
    jQuery('select#theme').change( function(){
        theme = jQuery(this).val();
        if ( theme == '' ) {
            jQuery('.warning.default').show();
        } else {
            jQuery('.warning.default').hide();
        }

        if ( theme == 'none' ) {
            jQuery('.warning.no_theme').show();
        } else {
            jQuery('.warning.no_theme').hide();
        }

        jQuery('.themes_descriptions > *').slideUp();
        
        if ( theme == 'none'   ||   theme == ''   &&   jQuery(this).attr('default_theme') == '' ) {
            jQuery('span.to_enable').show();
            jQuery('#gaddon-setting-row-v_popup, #gaddon-setting-row-v_scroll, #gaddon-setting-row-v_enable, #gaddon-setting-row-v_message, #gaddon-setting-row-iconsets').addClass('disable');
        } else {
            jQuery('span.to_enable').hide();
            jQuery('#gaddon-setting-row-v_popup, #gaddon-setting-row-v_scroll, #gaddon-setting-row-v_enable, #gaddon-setting-row-v_message, #gaddon-setting-row-iconsets').removeClass('disable');
            jQuery('.themes_descriptions #desc_' + theme).slideDown();
        }


    });

    // Warning for more than one icon-sets
    jQuery('#gaddon-setting-row-iconsets input[type=checkbox]').on('change', function() {
        if ( jQuery('#gaddon-setting-row-iconsets input[type=checkbox]:checked').length > 1 ){
            jQuery('.warning.icons').show();
        }
        else {
            jQuery('.warning.icons').hide();
        }
    });

    // For Defaults
    jQuery('select#theme').change();
    jQuery('#gaddon-setting-row-iconsets input[type=checkbox]').change();

    if ( document.getElementById('v_enable').checked ){
        jQuery('#gaddon-setting-row-v_message').show();
        }
    else {
        jQuery('#gaddon-setting-row-v_message').hide();
    }

    // Default theme name
    default_theme_name = 'No';
    default_theme_val = jQuery("#theme").attr('default_theme');
    if ( default_theme_val != '' )
        default_theme_name = jQuery("#theme option[value='" + default_theme_val +"']").text()
    
    jQuery("#default_theme_name").text(default_theme_name);

});