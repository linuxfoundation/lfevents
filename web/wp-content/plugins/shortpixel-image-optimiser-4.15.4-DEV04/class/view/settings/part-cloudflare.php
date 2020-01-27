<?php
namespace ShortPixel;
?>

    <section id="tab-cloudflare" <?php echo ($this->display_part == 'cloudflare') ? ' class="sel-tab" ' :''; ?>>
        <h2><a class='tab-link' href='javascript:void(0);'
               data-id="tab-cloudflare"><?php _e('Cloudflare API', 'shortpixel-image-optimiser'); ?></a>
        </h2>

        <div class="wp-shortpixel-tab-content" style="visibility: hidden">
            <?php

            if(! $this->is_curl_installed) {
                echo('<p style="font-weight:bold;color:red">' . __("Please enable PHP cURL extension for the Cloudflare integration to work.", 'shortpixel-image-optimiser') . '</p>' );
            }
            ?>
            <p><?php _e("If you're using Cloudflare on your site then we advise you to fill in the details below. This will allow ShortPixel to work seamlessly with Cloudflare so that any image optimized/restored by ShortPixel will be automatically updated on Cloudflare as well.",'shortpixel-image-optimiser');?></p>

            <table class="form-table">
                <tbody>
                <tr>
                    <th scope="row">
                        <label for="cloudflare-email"><?php _e('Cloudflare E-mail:', 'shortpixel-image-optimiser'); ?></label>
                    </th>
                    <td>
                        <input name="cloudflareEmail" type="text" id="cloudflare-email" <?php echo(! $this->is_curl_installed ? 'disabled' : '');?>
                               value="<?php echo( stripslashes(esc_html($view->data->cloudflareEmail))); ?>" class="regular-text">
                        <p class="settings-info">
                            <?php _e('The e-mail address you use to login to CloudFlare.','shortpixel-image-optimiser');?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label
                                for="cloudflare-auth-key"><?php _e('Global API Key:', 'shortpixel-image-optimiser'); ?></label>
                    </th>
                    <td>
                        <input name="cloudflareAuthKey" type="text" id="cloudflare-auth-key" <?php echo(! $this->is_curl_installed ? 'disabled' : '');?>
                               value="<?php echo(stripslashes(esc_html($view->data->cloudflareAuthKey))); ?>" class="regular-text">
                        <p class="settings-info">
                            <?php _e("This can be found when you're logged into your account, on the My Profile page:",'shortpixel-image-optimiser');?> <a href='https://www.cloudflare.com/a/profile' target='_blank'>https://www.cloudflare.com/a/profile</a>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label
                                for="cloudflare-zone-id"><?php _e('Zone ID:', 'shortpixel-image-optimiser'); ?></label>
                    </th>
                    <td>
                        <input name="cloudflareZoneID" type="text" id="cloudflare-zone-id" <?php echo(! $this->is_curl_installed ? 'disabled' : '');?>
                               value="<?php echo(stripslashes(esc_html($view->data->cloudflareZoneID))); ?>" class="regular-text">
                        <p class="settings-info">
                            <?php _e('This can be found in your Cloudflare account in the "Overview" section for your domain.','shortpixel-image-optimiser');?>
                        </p>
                    </td>
                </tr>
                </tbody>
            </table>
            <p class="submit">
                <input type="submit" name="saveCloudflare" id="saveCloudflare" class="button button-primary"
                       title="<?php _e('Save Changes', 'shortpixel-image-optimiser'); ?>"
                       value="<?php _e('Save Changes', 'shortpixel-image-optimiser'); ?>"> &nbsp;
            </p>
        </div>

    </section>
