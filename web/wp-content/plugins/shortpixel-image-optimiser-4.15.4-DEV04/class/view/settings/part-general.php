<?php namespace ShortPixel; ?>
<section id="tab-settings" <?php echo ($this->display_part == 'settings') ? ' class="sel-tab" ' :''; ?> >
    <h2><a class='tab-link' href='javascript:void(0);' data-id="tab-settings">
      <?php _e('General','shortpixel-image-optimiser');?></a>
    </h2>

    <div class="wp-shortpixel-options wp-shortpixel-tab-content" style="visibility: hidden">

    <p><?php printf(__('New images uploaded to the Media Library will be optimized automatically.<br/>If you have existing images you would like to optimize, you can use the <a href="%supload.php?page=wp-short-pixel-bulk">Bulk Optimization Tool</a>.','shortpixel-image-optimiser'),get_admin_url());?></p>
    <table class="form-table">
        <tbody>
            <tr>
                <th scope="row"><label for="key"><?php _e('API Key:','shortpixel-image-optimiser');?></label></th>
                <td>
                  <?php
                  $canValidate = false;
                  // Several conditions for showing API key.
                  if ($this->hide_api_key)
                    $showApiKey = false;
                  elseif($this->is_multisite && $this->is_constant_key)
                    $showApiKey = false;
                  else {
                    $showApiKey = true;  // is_mainsite, multisite, no constant.
                  }

                  $editApiKey = (! $this->is_constant_key && $showApiKey) ? true : false; ;

                  if($showApiKey) {
                      $canValidate = true;?>
                      <input name="key" type="text" id="key" value="<?php echo( $view->data->apiKey );?>"
                         class="regular-text" <?php echo($editApiKey ? "" : 'disabled') ?> <?php echo $this->is_verifiedkey ? 'onkeyup="ShortPixel.apiKeyChanged()"' : '' ?>>
                    <?php
                      }
                      elseif(defined("SHORTPIXEL_API_KEY")) {
                        $canValidate = true;?>
                        <input name="key" type="text" id="key" disabled="true" placeholder="<?php
                        if( $this->hide_api_key ) {
                            echo("********************");
                        } else {
                            _e('Multisite API Key','shortpixel-image-optimiser');
                        }
                        ?>" class="regular-text">
                    <?php } ?>
                        <input type="hidden" name="validate" id="valid" value=""/>
                        <span class="spinner" id="pluginemail_spinner" style="float:none;"></span>
                         <button type="button" id="validate" class="button button-primary" title="<?php _e('Validate the provided API key','shortpixel-image-optimiser');?>"
                            onclick="ShortPixel.validateKey(this)" <?php echo $canValidate ? "" : "disabled"?> <?php echo $this->is_verifiedkey ? 'style="display:none;"' : '' ?>>
                            <?php _e('Save settings & validate','shortpixel-image-optimiser');?>
                        </button>
                        <span class="shortpixel-key-valid" <?php echo $this->is_verifiedkey ? '' : 'style="display:none;"' ?>>
                            <span class="dashicons dashicons-yes"></span><?php _e('Your API key is valid.','shortpixel-image-optimiser');?>
                        </span>
                    <?php if($this->is_constant_key) { ?>
                        <p class="settings-info"><?php _e('Key defined in wp-config.php.','shortpixel-image-optimiser');?></p>
                    <?php } ?>

                </td>
            </tr>
    <?php if (! $this->is_verifiedkey) { //if invalid key we display the link to the API Key ?>
        </tbody>
    </table>
    <?php } else { //if valid key we display the rest of the options ?>
            <tr>
                <th scope="row">
                    <label for="compressionType"><?php _e('Compression type:','shortpixel-image-optimiser');?></label>
                </th>
                <td>
                    <div class="shortpixel-compression">
                        <div class="shortpixel-compression-options">
                            <label class="lossy" title="<?php _e('This is the recommended option in most cases, producing results that look the same as the original to the human eye.','shortpixel-image-optimiser');?>">
                                <input type="radio" class="shortpixel-radio-lossy" name="compressionType" value="1"  <?php echo( $view->data->compressionType == 1 ? "checked" : "" );?>><span><?php _e('Lossy','shortpixel-image-optimiser');?></span>
                            </label>
                            <label class="glossy" title="<?php _e('Best option for photographers and other professionals that use very high quality images on their sites and want best compression while keeping the quality untouched.','shortpixel-image-optimiser');?>">
                                <input type="radio" class="shortpixel-radio-glossy" name="compressionType" value="2" <?php echo( $view->data->compressionType == 2 ? "checked" : "" );?>><span><?php _e('Glossy','shortpixel-image-optimiser');?></span>
                            </label>
                            <label class="lossless" title="<?php _e('Make sure not a single pixel looks different in the optimized image compared with the original. In some rare cases you will need to use this type of compression. Some technical drawings or images from vector graphics are possible situations.','shortpixel-image-optimiser');?>">
                                <input type="radio" class="shortpixel-radio-lossless" name="compressionType" value="0" <?php echo( $view->data->compressionType == 0 ? "checked" : "" );?>><span><?php _e('Lossless','shortpixel-image-optimiser');?></span>
                            </label>
                            <?php _e('<a href="https://shortpixel.com/online-image-compression" style="margin-left:20px;" target="_blank">Make a few tests</a> to help you decide.'); ?>
                        </div>
                        <p class="settings-info shortpixel-radio-info shortpixel-radio-lossy" <?php echo( $view->data->compressionType == 1 ? "" : 'style="display:none"' );?>>
                            <?php _e('<b>Lossy compression (recommended): </b>offers the best compression rate.</br> This is the recommended option for most users, producing results that look the same as the original to the human eye.','shortpixel-image-optimiser');?>
                        </p>
                        <p class="settings-info shortpixel-radio-info shortpixel-radio-glossy" <?php echo( $view->data->compressionType == 2 ? "" : 'style="display:none"' );?>>
                            <?php _e('<b>Glossy compression: </b>creates images that are almost pixel-perfect identical to the originals.</br> Best option for photographers and other professionals that use very high quality images on their sites and want best compression while keeping the quality untouched.','shortpixel-image-optimiser');?>
                            <a href="http://blog.shortpixel.com/glossy-image-optimization-for-photographers/" target="_blank" class="shortpixel-help-link">
                                <span class="dashicons dashicons-editor-help"></span><?php _e('More info about glossy','shortpixel-image-optimiser');?>
                            </a></p>
                        <p class="settings-info shortpixel-radio-info shortpixel-radio-lossless" <?php echo( $view->data->compressionType == 0 ? "" : 'style="display:none"' );?>>
                            <?php _e('<b>Lossless compression: </b> the resulting image is pixel-identical with the original image.</br>Make sure not a single pixel looks different in the optimized image compared with the original.
                            In some rare cases you will need to use this type of compression. Some technical drawings or images from vector graphics are possible situations.','shortpixel-image-optimiser');?>
                        </p>
                    </div>
                    <script>
                        // @todo Remove JS from interface
                        function shortpixelCompressionLevelInfo() {
                            jQuery(".shortpixel-compression p").css("display", "none");
                            jQuery(".shortpixel-compression p." + jQuery(".shortpixel-compression-options input:radio:checked").attr('class')).css("display", "block");
                        }
                        //shortpixelCompressionLevelInfo();
                        jQuery(".shortpixel-compression-options input:radio").change(shortpixelCompressionLevelInfo);
                    </script>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Also include thumbnails:','shortpixel-image-optimiser');?></th>
                <td><input name="processThumbnails" type="checkbox" id="thumbnails" value="1" <?php checked($view->data->processThumbnails, '1');?>>
                    <label for="thumbnails"><?php _e('Apply compression also to <strong>image thumbnails.</strong> ','shortpixel-image-optimiser');?></label>
                    <?php echo($view->thumbnailsToProcess > 0 ? "(" . number_format($view->thumbnailsToProcess) . " " . __('thumbnails to optimize','shortpixel-image-optimiser') . ")" : "");?>
                    <p class="settings-info">
                        <?php _e('It is highly recommended that you optimize the thumbnails as they are usually the images most viewed by end users and can generate most traffic.<br>Please note that thumbnails count up to your total quota.','shortpixel-image-optimiser');?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Image backup','shortpixel-image-optimiser');?></th>
                <td>
                    <input name="backupImages" type="checkbox" id="backupImages" value="1" <?php checked($view->data->backupImages,'1'); ?>>
                    <label for="backupImages"><?php _e('Save and keep a backup of your original images in a separate folder.','shortpixel-image-optimiser');?></label>
                    <p class="settings-info"><?php _e('You <strong>need to have backup active</strong> in order to be able to restore images to originals or to convert from Lossy to Lossless and back.','shortpixel-image-optimiser');?></p>
                </td>
            </tr>
            <tr class='view-notice-row backup_warning'>
              <th scope='row'>&nbsp;</th>
              <td><div class='view-notice warning'><p><?php _e('Make sure you have a backup in place. When optimizing Shortpixel will overwrite your images without recovery. This may result in lost images.', 'shortpixel-image-optimiser') ?></p></div></td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Remove EXIF','shortpixel-image-optimiser');?></th>
                <td>
                    <input name="removeExif" type="checkbox" id="removeExif" value="1" <?php checked($view->data->keepExif, 0);?>>
                    <label for="removeExif"><?php _e('Remove the EXIF tag of the image (recommended).','shortpixel-image-optimiser');?></label>
                    <p class="settings-info"> <?php _e('EXIF is a set of various pieces of information that are automatically embedded into the image upon creation. This can include GPS position, camera manufacturer, date and time, etc.
                        Unless you really need that data to be preserved, we recommend removing it as it can lead to <a href="http://blog.shortpixel.com/how-much-smaller-can-be-images-without-exif-icc" target="_blank">better compression rates</a>.','shortpixel-image-optimiser');?></p>
                </td>
            </tr>
            <tr class='exif_warning view-notice-row'>
                <th scope="row">&nbsp;</th>
                <td>
                  <div class='view-notice warning'><p><?php printf(__('Warning - Converting from PNG to JPG will %s not %s keep the EXIF-information!'), "<strong>","</strong>"); ?></p></div>
                </td>
            </tr>
            <tr>
              <?php  $resizeDisabled = (! $this->view->data->resizeImages) ? 'disabled' : '';
                 // @todo Inline styling here can be decluttered.
              ?>
                <th scope="row"><?php _e('Resize large images','shortpixel-image-optimiser');?></th>
                <td>
                    <input name="resizeImages" type="checkbox" id="resize" value="1" <?php checked( $view->data->resizeImages, true );?>>
                    <label for="resize"><?php _e('to maximum','shortpixel-image-optimiser');?></label>
                    <input type="text" name="resizeWidth" id="width" style="width:70px" class="resize-sizes"
                           value="<?php echo( $view->data->resizeWidth > 0 ? $view->data->resizeWidth : min(924, $view->minSizes['width']) );?>" <?php echo( $resizeDisabled );?>/> <?php
                           _e('pixels wide &times;','shortpixel-image-optimiser');?>
                    <input type="text" name="resizeHeight" id="height" class="resize-sizes" style="width:70px"
                           value="<?php echo( $view->data->resizeHeight > 0 ? $view->data->resizeHeight : min(924, $view->minSizes['height']) );?>" <?php echo( $resizeDisabled );?>/> <?php
                           _e('pixels high (original aspect ratio is preserved and image is not cropped)','shortpixel-image-optimiser');?>
                    <input type="hidden" id="min-resizeWidth" value="<?php echo($view->minSizes['width']);?>" data-nicename="<?php _e('Width', 'shortpixel-image-optimiser'); ?>" />
                    <input type="hidden" id="min-resizeHeight" value="<?php echo($view->minSizes['height']);?>" data-nicename="<?php _e('Height', 'shortpixel-image-optimiser'); ?>"/>
                    <p class="settings-info">
                        <?php _e('Recommended for large photos, like the ones taken with your phone. Saved space can go up to 80% or more after resizing.','shortpixel-image-optimiser');?>
                        <a href="https://blog.shortpixel.com/resize-images/" class="shortpixel-help-link" target="_blank">
                            <span class="dashicons dashicons-editor-help"></span><?php _e('Read more','shortpixel-image-optimiser');?>
                        </a><br/>
                    </p>
                    <div style="margin-top: 10px;">
                        <input type="radio" name="resizeType" id="resize_type_outer" value="outer" <?php echo($view->data->resizeType == 'inner' ? '' : 'checked') ?> style="margin: -50px 10px 60px 0;">
                        <img alt="<?php _e('Resize outer','shortpixel-image-optimiser'); ?>" src="<?php echo(wpSPIO()->plugin_url('res/img/resize-outer.png' ));?>"
                             srcset='<?php echo(wpSPIO()->plugin_url('res/img/resize-outer.png' ));?> 1x, <?php echo(wpSPIO()->plugin_url('res/img/resize-outer@2x.png' ));?> 2x'
                             title="<?php _e('Sizes will be greater or equal to the corresponding value. For example, if you set the resize dimensions at 1000x1200, an image of 2000x3000px will be resized to 1000x1500px while an image of 3000x2000px will be resized to 1800x1200px','shortpixel-image-optimiser');?>">
                        <input type="radio" name="resizeType" id="resize_type_inner" value="inner" <?php echo($view->data->resizeType == 'inner' ? 'checked' : '') ?> style="margin: -50px 10px 60px 35px;">
                        <img alt="<?php _e('Resize inner','shortpixel-image-optimiser'); ?>" src="<?php echo(wpSPIO()->plugin_url('res/img/resize-inner.png' ));?>"
                             srcset='<?php echo(wpSPIO()->plugin_url('res/img/resize-inner.png' ));?> 1x, <?php echo(wpSPIO()->plugin_url('res/img/resize-inner@2x.png' ));?> 2x'
                             title="<?php _e('Sizes will be smaller or equal to the corresponding value. For example, if you set the resize dimensions at 1000x1200, an image of 2000x3000px will be resized to 800x1200px while an image of 3000x2000px will be resized to 1000x667px','shortpixel-image-optimiser');?>">
                        <div style="display:inline-block;margin-left: 20px;"><a href="https://blog.shortpixel.com/resize-images/" class="shortpixel-help-link" target="_blank">
                            <span class="dashicons dashicons-editor-help"></span><?php _e('What is this?','shortpixel-image-optimiser');?></a>
                        </div>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>


  <?php } ?>
  <p class="submit">
      <input type="submit" name="save" id="save" class="button button-primary" title="<?php _e('Save Changes','shortpixel-image-optimiser');?>" value="<?php _e('Save Changes','shortpixel-image-optimiser');?>"> &nbsp;
      <input type="submit" name="save_bulk" id="bulk" class="button button-primary" title="<?php _e('Save and go to the Bulk Processing page','shortpixel-image-optimiser');?>" value="<?php _e('Save and Go to Bulk Process','shortpixel-image-optimiser');?>"> &nbsp;
  </p>
</div>

</section>
