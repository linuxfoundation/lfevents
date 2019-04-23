jQuery.each(fieldSettings, function(key) {
    //  No need to add settings for hidden fields
    if ( key != "hidden" && key != "hiddenproduct" && key != "page" ) {
        fieldSettings[key] += ", .gfsp_styles";
    }
    // Attach Icons to the following fields
    iconFields = 'address calculation date email fileupload name username number password phone post_category post_content post_excerpt post_image post_title price quantity select shipping text time website';
    if ( iconFields.indexOf(key) > -1 ) {
        fieldSettings[key] += ", .gfsp_icon";
    }
});


//  Folding Actions
jQuery('.box h3').click(function() {
    
    container = jQuery('#TB_ajaxContent');

    jQuery('.toggle_open').removeClass('toggle_open');
	if ( jQuery(this).next().css('display') != 'block' ) {
        jQuery('.box h3 + div').slideUp('fast');
        jQuery(this).addClass('toggle_open');

        container.animate({
            scrollTop: jQuery(this).offset().top - container.offset().top - 200 + container.scrollTop()
        });
    }
    jQuery(this).next().slideToggle();
});


//  Binding to the load field settings event to initialize the checkbox
jQuery(document).bind("gform_load_field_settings", function(event, field, form){
    /* Field Setting: gfStylesPro */
    jQuery("#gf_stylespro_value").attr("value", "");
    jQuery("#gf_stylespro_value").attr("value", field["gfStylesPro"]);
    jQuery('#gf_stylespro_current_modal input').attr('checked', false);
    jQuery('#gf_stylespro_current_modal .default').attr('checked', true);

    //  Prepopulate Checkboxes
    if ( field["gfStylesPro"] ) {
        pre_pop = field["gfStylesPro"].split(" ");
        // Combine gfsp_o_list values Since v2.0
        list_str = '';
        jQuery.each(pre_pop, function(key) {
            if ( pre_pop[key].indexOf('gfsp_o_') == 0 || pre_pop[key].indexOf('o-') == 0 ) {
                list_str = list_str + (list_str == '' ? '' : ' ') + pre_pop[key];
                pre_pop[key] = '';
            }
        });
        pre_pop.push(list_str);

        // Prepopulate now
        jQuery.each(pre_pop, function(key) {
            if (pre_pop[key] != "") {
                jQuery('#gf_stylespro_current_modal input[value="' + pre_pop[key] + '"]').attr('checked', true);
            }
        });
    }
    
    //  Hide irrelevant options
    field_type=field["type"];
    input_type=field["inputType"];
    $mod = jQuery(".gf_stylespro_selectors");
    $mod.find(".h").slideUp();
    $mod.find(".h_"+field_type).slideDown();
    $mod.find(".h_"+field_type+"-"+input_type).slideDown();

    /* Field Setting: gfStylesProIcon */
    jQuery("#gf_stylespro_icon_value").attr("value", "");
    jQuery("#gf_stylespro_icon_value").attr("value", field["gfStylesProIcon"]);
    if ( field["gfStylesProIcon"] )
        field_icon_preview_update( field["gfStylesProIcon"] );
    else
        field_icon_preview_update( '' );
    
});



/**
*  Ornament Editor
*
**/

        jQuery('.choices_setting')
                .on('input propertychange', '.field-choice-sp_ornament', function () {
                    var $this = jQuery(this);
                    var i = $this.closest('li.field-choice-row, li.gquiz-choice-row').data('index');

                    field = GetSelectedField();
                    field.choices[i].spOrnament = $this.val();
                });
        gform.addFilter('gform_append_field_choice_option', function (str, field, i) {
            if (field.type != 'radio' && field.type != 'checkbox' && field.type != 'poll' && field.type != 'quiz' && field.type != 'option' && field.type != 'post_tags' && field.type != 'post_custom_field' && field.type != 'product') {
                return '';
            }

            // For fields with multiple input types, we continue only if checkbox or radio
            if (field.type == 'poll' || field.type == 'quiz' || field.type == 'option' || field.type == 'post_tags' || field.type == 'post_custom_field' || field.type == 'product') {
                if (field.inputType != 'checkbox' && field.inputType != 'radio'){
                    return '';
                }
            }

            var inputType = GetInputType(field);
            var spOrnament = field.choices[i].spOrnament ? field.choices[i].spOrnament : '';
            

            return "<button id=\"add_gf_stylespro_choice\" class=\"button\" onclick=\"choice_tb_show('" + inputType + "_choice_sp_ornament_" + i + "') \"><i class=\"fa fa-picture-o\"></i></button><input type='hidden' id='" + inputType + "_choice_sp_ornament_" + i + "' value='" + spOrnament + "' class='field-choice-input field-choice-sp_ornament' />";
        });

        function choice_preview_update(spIcnImg){
            
            jQuery('#gfsp_icon_temp').val(spIcnImg);

            spIcnImg = spIcnImg.split('|');
            
            // remove index 3 (color) if exists; we append it while saving
            if (spIcnImg[3] !== undefined){
                spIcnImg.pop();
                jQuery('#gfsp_icon_temp').val( spIcnImg.join('|') );
            }

            if ( spIcnImg[0] == 'icn' ){
                jQuery('.sp_choice_preview').html('<i class="'+ spIcnImg[1] +'"></i>');
                jQuery('.gfsp_color_wrapper').show();
            }
            else if ( spIcnImg[0] == 'img' ){
                jQuery('.sp_choice_preview').html('<img src="'+ spIcnImg[1] +'"></i>');
                jQuery('.ornament_image').val(spIcnImg[1]);
                jQuery('.gfsp_color_wrapper').hide();
            }
            else {
                jQuery('.sp_choice_preview').html(' ');
            }
        }

        function field_icon_preview_update(spIcnImg){
            jQuery('#gfsp_icon_temp').val(spIcnImg);

            spIcnImg = spIcnImg.split('|');

            // remove index 3 (color) if exists; we add it while saving
            spIcnColor = '';
            if (spIcnImg[3] !== undefined){
                spIcnColor = spIcnImg.pop();
                jQuery('#gfsp_icon_temp').val( spIcnImg.join('|') );
            }

            if ( spIcnImg[0] == 'icn' ){
                jQuery('.sp_field_icon_preview').html('<i style="color:' + spIcnColor +';"  class="'+ spIcnImg[1] +'"></i>');
                jQuery('.gfsp_color_wrapper').show();
            }
            else if ( spIcnImg[0] == 'img' ){
                jQuery('.sp_field_icon_preview').html('<img src="'+ spIcnImg[1] +'"></i>');
                jQuery('.ornament_image').val(spIcnImg[1]);
                jQuery('.gfsp_color_wrapper').hide();
            }
            else {
                jQuery('.sp_field_icon_preview').html(' ');
            }
            gfspFieldIconUpdate( GetSelectedField() );
        }
        
        function choice_tb_show(choice_field){
            window.spChoice = jQuery('#' + choice_field);
            jQuery('.ornament_image').val('');
            jQuery('#gfsp_icon_color').val('').change();
            choice_preview_update( spChoice.val() );
            
            // since: spChoice.val = icn|class|iconset|color
            color = spChoice.val().split('|')[3];
            jQuery('#gfsp_icon_color').val(color).change();
            jQuery('.sp_choice_preview').css('color', (color != undefined ? color: 'inherit') );        
            
            tb_show('Styes Pro: Icon / Image Selector', '#TB_inline?height=500&width=600&inlineId=add_gf_stylespro_choice_modal', '');
        }

        function field_icon_tb_show(){
                jQuery('.ornament_image').val('');
                jQuery('#gfsp_icon_color').val('').change();

            if ( field['gfStylesProIcon'] ) {
                choice_preview_update( field['gfStylesProIcon'] );
                jQuery("#gf_stylespro_icon_value").attr("value", field["gfStylesProIcon"]);
                
                // Because: spChoice.val = icn|class|iconset|color
                color = field['gfStylesProIcon'].split('|')[3];
                jQuery('#gfsp_icon_color').val(color).change();
                jQuery('.sp_choice_preview').css('color', (color != undefined ? color: 'inherit') );
            } else {
                choice_preview_update( '' );
            }

            tb_show('Styes Pro: Icon / Image Selector', '#TB_inline?height=500&width=600&inlineId=add_gf_stylespro_choice_modal', '');
        }



        function searchIcons() {
            searchTerm = jQuery('#gfsp_search_icons').val().toLowerCase();

            if (searchTerm == '') {
                jQuery('.all_icons span').removeClass('hide');
                jQuery('.search_count').text('');
                return;
            }
            jQuery('.all_icons span').addClass('hide');
            jQuery('.all_icons span:contains("' + searchTerm + '")').removeClass('hide');

            // Count v 2.0.3
            jQuery('.gfsp_icons').each( function()
                {
                    total = jQuery(this).find('.all_icons span').length;
                    count = jQuery(this).find('span:contains("' + searchTerm + '")').length;
                    count = ( (count > 0 && count < total) ? '('+count+')' : '')  	
                    jQuery(this).prev().find('.search_count').text(count);
                }
            );
        }

        jQuery('#gfsp_search_icons').on('input', function() { searchIcons() });



    jQuery('.ornament_image').on('change blur', function() {
        choice_preview_update('img|' + jQuery(this).val());
    });



    // Media uploader
    var sp_media_init = function(selector, button_selector)  {
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
                        choice_preview_update('img|' + url);
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
    sp_media_init('.ornament_image', '.media-button');

    jQuery(document).ready( function(){
        jQuery('#gfsp_icon_color').wpColorPicker({
            change: function(event, ui){
                jQuery('.sp_choice_preview').css('color', ui.color.toString() );
            },
            clear: function(event){
                jQuery('.sp_choice_preview').removeAttr('style')
            }
        });
    } );



    /* Save settings to the choice */
    function gfsp_ornament_save() {
        
        color = jQuery('#gfsp_icon_color').val();
        temp = jQuery('#gfsp_icon_temp').val();

        // add color if present and icon
        if (color != '' && color != undefined && temp.indexOf('icn') == 0)
            jQuery('#gfsp_icon_temp').val(temp + "|" + color);

        fieldType = GetSelectedField().type;
        inputType = GetSelectedField().inputType;
        if ( fieldType == 'radio' || fieldType == 'checkbox' || fieldType == 'poll' || fieldType == 'quiz' || fieldType == 'option' || (((fieldType == 'post_tags' || fieldType == 'post_custom_field' || fieldType == 'product') && (inputType == 'checkbox' || inputType == 'radio'))) )
        {
            spChoice.val( jQuery('#gfsp_icon_temp').val() ).trigger('input');
        }
        else
        {
            tempVal = jQuery('#gfsp_icon_temp').val();
            SetFieldProperty('gfStylesProIcon', tempVal);
            jQuery("#gf_stylespro_icon_value").attr("value", tempVal);
            field_icon_preview_update(tempVal);
        }
        tb_remove();
    }



    // Icon Selection
    jQuery('.all_icons span').click( function(){
        icnClass = jQuery(this).find('i').attr('class');
        if (icnClass != ''){
            type = jQuery(this).closest('.all_icons').data('iconset');
            choice_preview_update('icn|' + icnClass + "|" + type);
            }
        else
            choice_preview_update('');
    } );








/**
 * Live update choices ornaments
 * */
function gfspChoicesObserve(fld){
    // create an observer instance
    var observer = new MutationObserver(function(mutations) {
      mutations.forEach(function(mutation) {
        index = 0;
        oAfter = (field.gfStylesPro.indexOf('o_after') > -1);
        jQuery(mutation.addedNodes).each( function(){
            
            if (field.choices[index].spOrnament != undefined && field.choices[index].spOrnament != '') {

                ornamentArr = field.choices[index].spOrnament.split('|');
                if (ornamentArr[0] == 'icn'){
                    ornament = '<i class="o_icn_preview '+ ornamentArr[1] +'" style="color:'+ ornamentArr[3] +'"></i>'
                }
                if (ornamentArr[0] == 'img'){
                    ornament = '<div class="o_img_wr"><img class="gfsp_label_img" src="'+ ornamentArr[1]+'"></div>';
                }

                if (oAfter) {
                    jQuery(ornament).appendTo( jQuery(this).find('label') );		
                } else {
                    jQuery(ornament).prependTo( jQuery(this).find('label') );		
                }
            }

            index += 1;
        });
      });
    });
    // configuration of the observer:
    var config = { attributes: false, childList: true, characterData: false };

    // pass in the target node, as well as the observer options
    observer.observe(fld, config);
}

// Observe on form load
jQuery('.ginput_container > ul').each( function() {
	gfspChoicesObserve(this);
});

// Observe choices in newly added fields
jQuery(document).bind( 'gform_field_added', function( event, form, field ) {
    jQuery( '#field_' + field['id'] + ' .ginput_container > ul').each( function() {
        gfspChoicesObserve(this);
    });
} );


/*
 * Update Field Icon
 * */

// Use this function for field loop on page load and on icon change save
function gfspFieldIconUpdate(fld) {
	// If the value was never defined, stop
    if ( fld.gfStylesProIcon == undefined || fld.inputType == 'checkbox' || fld.inputType == 'radio') {
    return true;
    }

    addTo = jQuery('#field_' + fld.id + ' .ginput_container');
	addTo.removeClass('has_icon');
	addTo.find('.gfsp_icon').remove();

	// If the value is changed to empty, stop
    if ( fld.gfStylesProIcon == '' ) {
    return true;
    }

    icnHtml = '';
    // since: spChoice.val = icn|class|iconset|color
    icnArr = fld.gfStylesProIcon.split('|');
    if (icnArr[0] == 'icn') {
        icnHtml = '<span class="gfsp_icon"><i class="'+ icnArr[1] +' iconset_'+icnArr[2]+'" style="color: '+ icnArr[3] +'"></i></span>'
    } else if (icnArr[0] == 'img') {
        icnHtml = '<span class="gfsp_icon"><i class="gfsp_icn_img" style=\'background-image:url("'+ icnArr[1] +')"\'></i></span>'        
    }

	if (fld.type == 'date'){
		if (fld.dateType == "datepicker")
			addTo = jQuery('#field_' + fld.id + ' #gfield_input_datepicker');
		else
			addTo = jQuery('#field_' + fld.id + ' #input_' + fld.id);
	}
    if (fld.type == 'time'){
			addTo = jQuery('#field_' + fld.id + ' #input_' + fld.id);
	}

	addTo.addClass('has_icon');
        jQuery(addTo).prepend(icnHtml);
}

jQuery(form.fields).each( function() {
    gfspFieldIconUpdate(this);
});
