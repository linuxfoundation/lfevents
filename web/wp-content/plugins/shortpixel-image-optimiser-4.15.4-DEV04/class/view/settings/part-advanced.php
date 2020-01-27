<?php
namespace ShortPixel;
?>

<section id="tab-adv-settings" class="clearfix <?php echo ($this->display_part == 'adv-settings') ? ' sel-tab ' :''; ?> ">
    <h2><a class='tab-link' href='javascript:void(0);' data-id="tab-adv-settings"><?php _e('Advanced','shortpixel-image-optimiser');?></a></h2>

    <?php
    $deliverWebpAlteredDisabled = '';
    $deliverWebpUnalteredDisabled = '';
    $deliverWebpAlteredDisabledNotice = false;
    $deliverWebpUnalteredLabel ='';

    if( $this->is_nginx ){
        $deliverWebpUnaltered = '';                         // Uncheck
        $deliverWebpUnalteredDisabled = 'disabled';         // Disable
        $deliverWebpUnalteredLabel = __('It looks like you\'re running your site on an NginX server. This means that you can only achieve this functionality by directly configuring the server config files. Please follow this link for instructions on how to achieve this:','shortpixel-image-optimiser')." <a href=\"javascript:void(0)\" data-beacon-article=\"5bfeb9de2c7d3a31944e78ee\">Open article</a>";
    } else {
        if( !$this->is_htaccess_writable ){
            $deliverWebpUnalteredDisabled = 'disabled';     // Disable
            if( $deliverWebp == 3 ){
                $deliverWebpAlteredDisabled = 'disabled';   // Disable
                $deliverWebpUnalteredLabel = __('It looks like you recently moved from an Apache server to an NGINX server, while the option to use .htacces was in use. Please follow this tutorial to see how you could implement by yourself this functionality, outside of the WP plugin. ','shortpixel-image-optimiser');
            } else {
                $deliverWebpUnalteredLabel = __('It looks like your .htaccess file cannot be written. Please fix this and then return to refresh this page to enable this option.','shortpixel-image-optimiser');
            }
        } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Chrome') !== false) {
            // Show a message about the risks and caveats of serving WEBP images via .htaccess
            $deliverWebpUnalteredLabel = '<span style="color: initial;">'.__('Based on testing your particular hosting configuration, we determined that your server','shortpixel-image-optimiser').
                '&nbsp;<img alt="can or can not" src="'. plugins_url( 'res/img/test.jpg' , SHORTPIXEL_PLUGIN_FILE) .'">&nbsp;'.
                __('serve the WEBP versions of the JPEG files seamlessly, via .htaccess.','shortpixel-image-optimiser').' <a href="javascript:void(0)" data-beacon-article="5c1d050e04286304a71d9ce4">Open article to read more about this.</a></span>';
        }
    }



    $excludePatterns = '';
    if($view->data->excludePatterns) {
        foreach($view->data->excludePatterns as $item) {
            $excludePatterns .= $item['type'] . ":" . $item['value'] . ", ";
        }
        $excludePatterns = substr($excludePatterns, 0, -2);
    }
    ?>

    <div class="wp-shortpixel-options wp-shortpixel-tab-content" style='visibility: hidden'>
    <table class="form-table">
        <tbody>
            <tr>
                <th scope="row"><label for="additional-media"><?php _e('Additional media folders','shortpixel-image-optimiser');?></label></th>
                <td>
                    <span style="display:none;">Current PHP version: <?php echo(phpversion()) ?></span>
                    <?php if($view->customFolders) { ?>
                        <table class="shortpixel-folders-list">
                            <tr style="font-weight: bold;">
                                <td><?php _e('Folder name','shortpixel-image-optimiser');?></td>
                                <td><?php _e('Type &amp;<br>Status','shortpixel-image-optimiser');?></td>
                                <td><?php _e('Files','shortpixel-image-optimiser');?></td>
                                <td><?php _e('Last change','shortpixel-image-optimiser');?></td>
                                <td></td>
                            </tr>
                        <?php foreach($view->customFolders as $folder) {
                            $typ = $folder->getType();
                            $typ = $typ ? $typ . "<br>" : "";
                            $stat = $this->shortPixel->getSpMetaDao()->getFolderOptimizationStatus($folder->getId());
                            $cnt = $folder->getFileCount();
                            $st = ($cnt == 0
                                ? __("Empty",'shortpixel-image-optimiser')
                                : ($stat->Total == $stat->Optimized
                                    ? __("Optimized",'shortpixel-image-optimiser')
                                    : ($stat->Optimized + $stat->Pending > 0 ? __("Pending",'shortpixel-image-optimiser') : __("Waiting",'shortpixel-image-optimiser'))));

                            $err = $stat->Failed > 0 && !$st == __("Empty",'shortpixel-image-optimiser') ? " ({$stat->Failed} failed)" : "";

                            $action = ($st == __("Optimized",'shortpixel-image-optimiser') || $st == __("Empty",'shortpixel-image-optimiser') ? __("Stop monitoring",'shortpixel-image-optimiser') : __("Stop optimizing",'shortpixel-image-optimiser'));

                            $fullStat = $st == __("Empty",'shortpixel-image-optimiser') ? "" : __("Optimized",'shortpixel-image-optimiser') . ": " . $stat->Optimized . ", "
                                    . __("Pending",'shortpixel-image-optimiser') . ": " . $stat->Pending . ", " . __("Waiting",'shortpixel-image-optimiser') . ": " . $stat->Waiting . ", "
                                    . __("Failed",'shortpixel-image-optimiser') . ": " . $stat->Failed;
                            ?>
                            <tr>
                                <td>
                                    <?php echo($folder->getPath()); ?>
                                </td>
                                <td>
                                    <?php if(!($st == "Empty")) { ?>
                                    <a href="javascript:none();"  title="<?php echo $fullStat; ?>" style="text-decoration: none;">
                                        <img alt='Info icon' src='<?php echo( wpSPIO()->plugin_url('res/img/info-icon.png' ));?>' style="margin-bottom: -2px;"/>
                                    </a>&nbsp;<?php  } echo($typ.$st.$err); ?>

                                </td>
                                <td>
                                    <?php echo($cnt); ?> files
                                </td>
                                <td>
                                    <?php echo($folder->getTsUpdated()); ?>
                                </td>
                                <td>
                                    <input type="button" class="button remove-folder-button" data-value="<?php echo($folder->getPath()); ?>" title="<?php echo($action . " " . $folder->getPath()); ?>" value="<?php echo $action;?>">
                                    <input type="button" style="display:none;" class="button button-alert recheck-folder-button" data-value="<?php echo($folder->getPath()); ?>"
                                           title="<?php _e('Full folder refresh, check each file of the folder if it changed since it was optimized. Might take up to 1 min. for big folders.','shortpixel-image-optimiser');?>"
                                           value="<?php _e('Refresh','shortpixel-image-optimiser');?>">
                                </td>
                            </tr>
                        <?php }?>
                        </table>
                    <?php } ?>

                    <div class='addCustomFolder'>

                      <input type="hidden" name="removeFolder" id="removeFolder"/>
                      <p class='add-folder-text'><strong><?php _e('Add a custom folder', 'shortpixel-image-optimiser'); ?></strong></p>
                      <input type="text" name="addCustomFolderView" id="addCustomFolderView" class="regular-text" value="" disabled style="">&nbsp;
                      <input type="hidden" name="addCustomFolder" id="addCustomFolder" value=""/>
                      <input type="hidden" id="customFolderBase" value="<?php echo $this->shortPixel->getCustomFolderBase(); ?>">

                      <a class="button select-folder-button" title="<?php _e('Select the images folder on your server.','shortpixel-image-optimiser');?>" href="javascript:void(0);">
                          <?php _e('Select ...','shortpixel-image-optimiser');?>
                      </a>
                    <input type="submit" name="save" id="saveAdvAddFolder" class="button button-primary hidden" title="<?php _e('Add this Folder','shortpixel-image-optimiser');?>" value="<?php _e('Add this Folder','shortpixel-image-optimiser');?>">
                    <p class="settings-info">
                        <?php _e('Use the Select... button to select site folders. ShortPixel will optimize images and PDFs from the specified folders and their subfolders. The optimization status for each image or PDF in these folders can be seen in the <a href="upload.php?page=wp-short-pixel-custom">Other Media list</a>, under the Media menu.','shortpixel-image-optimiser');?>
                        <a href="https://blog.shortpixel.com/optimize-images-outside-media-library/" target="_blank" class="shortpixel-help-link">
                            <span class="dashicons dashicons-editor-help"></span><?php _e('More info','shortpixel-image-optimiser');?>
                        </a>
                    </p>

                    <div class="sp-modal-shade sp-folder-picker-shade"></div>
                        <div class="shortpixel-modal modal-folder-picker shortpixel-hide">
                            <div class="sp-modal-title"><?php _e('Select the images folder','shortpixel-image-optimiser');?></div>
                            <div class="sp-folder-picker"></div>
                            <input type="button" class="button button-info select-folder-cancel" value="<?php _e('Cancel','shortpixel-image-optimiser');?>" style="margin-right: 30px;">
                            <input type="button" class="button button-primary select-folder" value="<?php _e('Select','shortpixel-image-optimiser');?>">
                        </div>

                    <script>
                        jQuery(document).ready(function () {
                            ShortPixel.initFolderSelector();
                        });
                    </script>
                  </div> <!-- end of AddCustomFolder -->
                </td>
            </tr>
            <?php if($this->has_nextgen) { ?>
            <tr>
                <th scope="row"><?php _e('Optimize NextGen galleries','shortpixel-image-optimiser');?></th>
                <td>
                    <input name="includeNextGen" type="checkbox" id="nextGen" value='1' <?php echo  checked($view->data->includeNextGen,'1' );?>> <label for="nextGen"><?php _e('Optimize NextGen galleries.','shortpixel-image-optimiser');?></label>
                    <p class="settings-info">
                        <?php _e('Check this to add all your current NextGen galleries to the custom folders list and to also have all the future NextGen galleries and images optimized automatically by ShortPixel.','shortpixel-image-optimiser');?>
                    </p>
                </td>
            </tr>
            <?php } ?>
            <tr>
                <th scope="row"><?php _e('Convert PNG images to JPEG','shortpixel-image-optimiser');?></th>
                <td>
                    <input name="png2jpg" type="checkbox" id="png2jpg" value="1" <?php checked( ($view->data->png2jpg > 0), true);?> <?php echo($this->is_gd_installed ? '' : 'disabled') ?>>
                    <label for="png2jpg"><?php _e('Automatically convert the PNG images to JPEG if possible.','shortpixel-image-optimiser');
                        if(!$this->is_gd_installed) {echo("&nbsp;<span style='color:red;'>" . __('You need PHP GD for this. Please ask your hosting to install it.','shortpixel-image-optimiser') . "</span>");}
                    ?></label>
                    <p class="settings-info">
                        <?php _e('Converts all PNGs that don\'t have transparent pixels to JPEG. This can dramatically reduce the file size, especially if you have camera pictures that are saved in PNG format. The plugin will also search for references of the image in posts and will replace them.','shortpixel-image-optimiser');?>
                        <strong><?php _e('The image will NOT be converted if the resulting JPEG is larger than the original PNG.','shortpixel-image-optimiser');?></strong>
                    </p><br>
                    <?php // @todo Issue with this. png2jpg > 0, is force ?>
                    <input name="png2jpgForce" type="checkbox" id="png2jpgForce" value="1" <?php checked(($view->data->png2jpg > 1), true);?> <?php echo($this->is_gd_installed ? '' : 'disabled') ?>>
                    <label for="png2jpgForce">
                        <?php _e('Also force the conversion of images with transparency.','shortpixel-image-optimiser'); ?>
                    </label>
                </td>
            </tr>
            <tr class='exif_warning view-notice-row'>
                <th scope="row">&nbsp;</th>
                <td>
                  <div class='view-notice warning'><p><?php printf(__('Warning - Converting from PNG to JPG will %s not %s keep the EXIF-information!'), "<strong>","</strong>"); ?></p></div>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e('CMYK to RGB conversion','shortpixel-image-optimiser');?></th>
                <td>
                    <input name="cmyk2rgb" type="checkbox" id="cmyk2rgb" value="1" <?php checked( $view->data->CMYKtoRGBconversion, "1" );?>>
                    <label for="cmyk2rgb"><?php _e('Adjust your images\' colours for computer and mobile screen display.','shortpixel-image-optimiser');?></label>
                    <p class="settings-info"><?php _e('Images for the web only need RGB format and converting them from CMYK to RGB makes them smaller.','shortpixel-image-optimiser');?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e('WebP Images:','shortpixel-image-optimiser');?></th>
                <td>
                    <input name="createWebp" type="checkbox" id="createWebp" value="1" <?php checked( $view->data->createWebp, "1" );?>>
                    <label for="createWebp">
                        <?php _e('Also create <a href="http://blog.shortpixel.com/how-webp-images-can-speed-up-your-site/" target="_blank">WebP versions</a> of the images, <strong>for free</strong>.','shortpixel-image-optimiser');?>
                    </label>
                    <p class="settings-info">
                        <?php _e('WebP images can be up to three times smaller than PNGs and 25% smaller than JPGs. Choosing this option <strong>does not use up additional credits</strong>.','shortpixel-image-optimiser');?>
                        <a href="http://blog.shortpixel.com/how-webp-images-can-speed-up-your-site/" target="_blank" class="shortpixel-help-link">
                            <span class="dashicons dashicons-editor-help"></span><?php _e('More info','shortpixel-image-optimiser');?>
                        </a>
                    </p>
                    <div class="deliverWebpSettings">
                        <input name="deliverWebp" type="checkbox" id="deliverWebp" value="1" <?php checked( ($view->data->deliverWebp > 0), true);?>>
                        <label for="deliverWebp">
                            <?php _e('Deliver the WebP versions of the images in the front-end:','shortpixel-image-optimiser');?>
                        </label>
                        <ul class="deliverWebpTypes">
                            <li>
                                <input type="radio" name="deliverWebpType" id="deliverWebpAltered" <?php checked( ($view->data->deliverWebp >= 1 && $view->data->deliverWebp <= 2), true); ?> <?php echo( $deliverWebpAlteredDisabled );?> value="deliverWebpAltered">
                                <label for="deliverWebpAltered">
                                    <?php _e('Using the &lt;PICTURE&gt; tag syntax','shortpixel-image-optimiser');?>
                                </label>
                                <?php if($deliverWebpAlteredDisabledNotice){ ?>
                                    <p class="sp-notice">
                                        <?php _e('After the option to work on .htaccess was selected, the .htaccess file has become unaccessible / readonly. Please make the .htaccess file writeable again to be able to further set up this option.','shortpixel-image-optimiser')?>
                                    </p>
                                <?php } ?>
                                <p class="settings-info">
                                    <?php _e('Each &lt;img&gt; will be replaced with a &lt;picture&gt; tag that will also provide the WebP image as a choice for browsers that support it. Also loads the picturefill.js for browsers that don\'t support the &lt;picture&gt; tag. You don\'t need to activate this if you\'re using the Cache Enabler plugin because your WebP images are already handled by this plugin. <strong>Please make a test before using this option</strong>, as if the styles that your theme is using rely on the position of your &lt;img&gt; tag, you might experience display problems.','shortpixel-image-optimiser'); ?>
                                    <strong><?php _e('You can revert anytime to the previous state by just deactivating the option.','shortpixel-image-optimiser'); ?></strong>
                                </p>
                                <ul class="deliverWebpAlteringTypes">
                                    <li>
                                        <input type="radio" name="deliverWebpAlteringType" id="deliverWebpAlteredWP" <?php checked(($view->data->deliverWebp == 2), true);?> value="deliverWebpAlteredWP">
                                        <label for="deliverWebpAlteredWP">
                                            <?php _e('Only via Wordpress hooks (like the_content, the_excerpt, etc)');?>
                                        </label>
                                    </li>
                                    <li>
                                        <input type="radio" name="deliverWebpAlteringType" id="deliverWebpAlteredGlobal" <?php checked(($view->data->deliverWebp == 1),true)?>  value="deliverWebpAlteredGlobal">
                                        <label for="deliverWebpAlteredGlobal">
                                            <?php _e('Global (processes the whole output buffer before sending the HTML to the browser)','shortpixel-image-optimiser');?>
                                        </label>
                                    </li>
                                </ul>
                            </li>
                            <li>
                                <input type="radio" name="deliverWebpType" id="deliverWebpUnaltered" <?php checked(($view->data->deliverWebp == 3), true);?> <?php echo( $deliverWebpUnalteredDisabled );?> value="deliverWebpUnaltered">
                                <label for="deliverWebpUnaltered">
                                    <?php _e('Without altering the page code (via .htaccess)','shortpixel-image-optimiser')?>
                                </label>
                                <?php if($deliverWebpUnalteredLabel){ ?>
                                    <p class="sp-notice">
                                        <?php echo( $deliverWebpUnalteredLabel );?>
                                    </p>
                                <?php } ?>
                            </li>
                        </ul>
                    </div>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Optimize Retina images','shortpixel-image-optimiser');?></th>
                <td>
                    <input name="optimizeRetina" type="checkbox" id="optimizeRetina" value="1" <?php checked( $view->data->optimizeRetina, "1"); ?>>
                    <label for="optimizeRetina"><?php _e('Also optimize the Retina images (@2x) if they exist.','shortpixel-image-optimiser');?></label>
                    <p class="settings-info">
                        <?php _e('If you have a Retina plugin that generates Retina-specific images (@2x), ShortPixel can optimize them too, alongside the regular Media Library images and thumbnails.','shortpixel-image-optimiser');?>
                        <a href="http://blog.shortpixel.com/how-to-use-optimized-retina-images-on-your-wordpress-site-for-best-user-experience-on-apple-devices/" target="_blank" class="shortpixel-help-link">
                            <span class="dashicons dashicons-editor-help"></span><?php _e('More info','shortpixel-image-optimiser');?>
                        </a>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Optimize other thumbs','shortpixel-image-optimiser');?></th>
                <td>
                    <input name="optimizeUnlisted" type="checkbox" id="optimizeUnlisted" value="1" <?php checked( $view->data->optimizeUnlisted, "1" );?>>
                    <label for="optimizeUnlisted"><?php _e('Also optimize the unlisted thumbs if found.','shortpixel-image-optimiser');?></label>
                    <p class="settings-info">
                        <?php _e('Some plugins create thumbnails which are not registered in the metadata but instead only create them alongside the other thumbnails. Let ShortPixel optimize them as well.','shortpixel-image-optimiser');?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Optimize PDFs','shortpixel-image-optimiser');?></th>
                <td>
                    <input name="optimizePdfs" type="checkbox" id="optimizePdfs" value="1" <?php checked( $view->data->optimizePdfs, "1" );?>>
                    <label for="optimizePdfs"><?php _e('Automatically optimize PDF documents.','shortpixel-image-optimiser');?></label>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="excludePatterns"><?php _e('Exclude patterns','shortpixel-image-optimiser');?></label></th>
                <td>
                    <input name="excludePatterns" type="text" id="excludePatterns" value="<?php echo( $excludePatterns );?>" class="regular-text" placeholder="<?php
                        _e('name:keepbig, path:/ignore_regex/i, size:1000x2000','shortpixel-image-optimiser');?>">
                    <?php _e('Exclude certain images from being optimized, based on patterns.','shortpixel-image-optimiser');?>
                    <p class="settings-info">
                        <?php _e('Add patterns separated by comma. A pattern consist of a <strong>type:value</strong> pair; the accepted types are
                                  <strong>"name"</strong>, <strong>"path"</strong> and <strong>"size"</strong>.
                                   A file will be excluded if it matches any of the patterns.
                                   <br>For a <strong>"name"</strong> pattern only the filename will be matched but for a <strong>"path"</strong>,
                                   all the path will be matched (useful for excluding certain subdirectories altoghether).
                                   For these you can also use regular expressions accepted by preg_match, but without "," or ":".
                                   A pattern will be considered a regex if it starts with a "/" and is valid.
                                   <br>For the <strong>"size"</strong> type,
                                   which applies only to Media Library images, <strong>the main images (not thumbnails)</strong> that have the size in the specified range will be excluded.
                                   The format for the "size" exclude is: <strong>minWidth</strong>-<strong>maxWidth</strong>x<strong>minHeight</strong>-<strong>maxHeight</strong>, for example <strong>size:1000-1100x2000-2200</strong>. You can also specify a precise size, as <strong>1000x2000</strong>.','shortpixel-image-optimiser');?>
                        <a href="http://blog.shortpixel.com/shortpixel-how-to-exclude-images-and-folders-from-optimization/" target="_blank" class="shortpixel-help-link">
                            <span class="dashicons dashicons-editor-help"></span><?php _e('More info','shortpixel-image-optimiser');?>
                        </a>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="authentication"><?php _e('HTTP AUTH credentials','shortpixel-image-optimiser');?></label></th>
                <td>
                    <input name="siteAuthUser" type="text" id="siteAuthUser" value="<?php echo( stripslashes(esc_html($view->data->siteAuthUser )));?>" class="regular-text" placeholder="<?php _e('User','shortpixel-image-optimiser');?>"><br>
                    <input name="siteAuthPass" type="text" id="siteAuthPass" value="<?php echo( stripslashes(esc_html($view->data->siteAuthPass )));?>" class="regular-text" placeholder="<?php _e('Password','shortpixel-image-optimiser');?>">
                    <p class="settings-info">
                        <?php _e('Only fill in these fields if your site (front-end) is not publicly accessible and visitors need a user/pass to connect to it. If you don\'t know what is this then just <strong>leave the fields empty</strong>.','shortpixel-image-optimiser');?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Optimize media on upload','shortpixel-image-optimiser');?></th>
                <td>
                    <input name="autoMediaLibrary" type="checkbox" id="autoMediaLibrary" value="1" <?php checked( $view->data->autoMediaLibrary, "1" );?>>
                    <label for="autoMediaLibrary"><?php _e('Automatically optimize Media Library items after they are uploaded (recommended).','shortpixel-image-optimiser');?></label>
                    <p class="settings-info">
                        <?php _e('By default, ShortPixel will automatically optimize all the freshly uploaded image and PDF files. If you uncheck this you\'ll need to either run Bulk ShortPixel or go to Media Library (in list view) and click on the right side "Optimize now" button(s).','shortpixel-image-optimiser');?>
                    </p>
                </td>
            </tr>
            <tr id="frontBootstrapRow">
                <th scope="row"><?php _e('Process in front-end','shortpixel-image-optimiser');?></th>
                <td>
                    <input name="frontBootstrap" type="checkbox" id="frontBootstrap" value="1" <?php checked( $view->data->frontBootstrap, '1' );?>>
                    <label for="frontBootstrap"><?php _e('Automatically optimize images added by users in front end.','shortpixel-image-optimiser');?></label>
                    <p class="settings-info">
                        <?php _e('Check this if you have users that add images or PDF documents from custom forms in the front-end. This could increase the load on your server if you have a lot of users simultaneously connected.','shortpixel-image-optimiser');?>
                    </p>
                </td>
                <script>
                    var spaiAML = document.getElementById('autoMediaLibrary');
                    document.getElementById('frontBootstrapRow').setAttribute('style', spaiAML.checked ? '' : 'display:none;');
                    spaiAML.addEventListener('change', function() {
                        if(this.checked) {
                            jQuery('#frontBootstrapRow').show(500);
                        } else {
                            jQuery('#frontBootstrapRow').hide(500);
                        }
                    });


                </script>
            </tr>
            <tr>
                <th scope="row"><label for="excludeSizes"><?php _e('Exclude thumbnail sizes','shortpixel-image-optimiser');?></label></th>
                <td>
                    <?php foreach($view->allThumbSizes as $sizeKey => $sizeVal) {?>
                        <span style="margin-right: 20px;white-space:nowrap">
                            <input name="excludeSizes[]" type="checkbox" id="excludeSizes_<?php echo($sizeKey);?>" <?php echo((in_array($sizeKey, $view->data->excludeSizes) ? 'checked' : ''));?>
                                   value="<?php echo($sizeKey);?>">&nbsp;<?php $w=$sizeVal['width']?$sizeVal['width'].'px':'*';$h=$sizeVal['height']?$sizeVal['height'].'px':'*';echo("$sizeKey ({$w} &times; {$h})");?>&nbsp;&nbsp;
                        </span><br>
                    <?php } ?>
                    <p class="settings-info">
                        <?php _e('Please check the thumbnail sizes you would like to <strong>exclude</strong> from optimization. There might be sizes created by themes or plugins which do not appear here, because they were not properly registered with WordPress. If you want to ignore them too, please uncheck the option <strong>Optimize other thumbs</strong> above.','shortpixel-image-optimiser');?>
                    </p>
                </td>
            </tr>
        </tbody>
    </table>
    <p class="submit">
        <input type="submit" name="save" id="saveAdv" class="button button-primary" title="<?php _e('Save Changes','shortpixel-image-optimiser');?>" value="<?php _e('Save Changes','shortpixel-image-optimiser');?>"> &nbsp;
        <input type="submit" name="save_bulk" id="bulkAdvGo" class="button button-primary" title="<?php _e('Save and go to the Bulk Processing page','shortpixel-image-optimiser');?>" value="<?php _e('Save and Go to Bulk Process','shortpixel-image-optimiser');?>"> &nbsp;
    </p>
    </div>
    <script>
        jQuery(document).ready(function () { ShortPixel.setupAdvancedTab();});
    </script>
</section>
