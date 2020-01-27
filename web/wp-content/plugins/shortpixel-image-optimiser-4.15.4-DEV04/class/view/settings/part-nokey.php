<?php
namespace ShortPixel;
use ShortPixel\Notices\NoticeController as Notice;
use ShortPixel\ShortpixelLogger\ShortPixelLogger as Log;

$canValidate = false;
// Several conditions for showing API key.
if ($this->hide_api_key)
  $showApiKey = false;
elseif($this->is_multisite && $this->is_constant_key)
  $showApiKey = false;
else {
  $showApiKey = true;  // is_mainsite, multisite, no constant.
}

$editApiKey = (! $this->is_constant_key && $showApiKey) ? true : false;

// Notices for fringe cases
if (! $this->is_verifiedkey && $this->hide_api_key && ! $this->is_constant_key)
{
  Notice::addError(__('wp-config.php is hiding the API key, but no API key was found. Remove the constant, or define the SHORTPIXEL_API_KEY constant as well', 'shortpixel-image-optimiser'));
}
elseif ($this->is_constant_key && ! $this->is_verifiedkey)
{
  $dkey = ($this->hide_api_key) ? '' : '(' . SHORTPIXEL_API_KEY.  ')';
  Notice::addError(sprintf(__('Constant API Key is not verified. Please check if this is a valid API key %s'),$dkey));
}

$adminEmail = get_bloginfo('admin_email');
if($adminEmail == 'noreply@addendio.com') $adminEmail = false; //hack for the addendio sandbox e-mail

?>
<section id="tab-settings" <?php echo ($this->display_part == 'settings') ? ' class="sel-tab" ' :''; ?> >
    <h2><a class='tab-link' href='javascript:void(0);' data-id="tab-settings">
      <?php _e('Join ShortPixel','shortpixel-image-optimiser');?></a>
    </h2>
<div class="wp-shortpixel-options wp-shortpixel-tab-content">
<?php if($showApiKey): ?>
  <h3><?php _e('Request an API Key:','shortpixel-image-optimiser');?></h3>
<p style='font-size: 14px'><?php _e('If you don\'t have an API Key, you can request one for free. Just press the "Request Key" button after checking that the e-mail is correct.','shortpixel-image-optimiser');?></p>

<table class="form-table">
    <tbody>
        <tr>
            <th scope="row"><label for="key"><?php _e('E-mail address:','shortpixel-image-optimiser');?></label></th>
            <td>
                <input name="pluginemail" type="text" id="pluginemail" value="<?php echo( $adminEmail );?>"
                       onchange="ShortPixel.updateSignupEmail();" class="regular-text">
                <span class="spinner" id="pluginemail_spinner" style="float:none;"></span>
                <a type="button" id="request_key" class="button button-primary" title="<?php _e('Request a new API key','shortpixel-image-optimiser');?>"
                   href="https://shortpixel.com/free-sign-up?pluginemail=<?php echo( $adminEmail );?>"
                   onclick="ShortPixel.newApiKey(event);"
                   onmouseenter="ShortPixel.updateSignupEmail();">
                   <?php _e('Request Key','shortpixel-image-optimiser');?>
                </a>
                <p class="settings-info shortpixel-settings-error" style='display:none;' id='pluginemail-error'>
                    <b><?php _e('Please provide a valid e-mail address.', 'shortpixel-image-optimiser');?></b>
                </p>
                <p class="settings-info" id='pluginemail-info'>
                    <?php if($adminEmail) {
                        printf(__('<b>%s</b> is the e-mail address in your WordPress Settings. You can use it, or change it to any valid e-mail address that you own.','shortpixel-image-optimiser'), $adminEmail);
                    } else {
                        _e('Please input your e-mail address and press the Request Key button.','shortpixel-image-optimiser');
                    }
                    ?><br><span style="position:relative;">
                        <input name="tos" type="checkbox" id="tos">
                        <img id="tos-robo" alt="<?php _e('ShortPixel logo', 'shortpixel-image-optimiser'); ?>"
                             src="<?php echo(wpSPIO()->plugin_url('res/img/slider.png' ));?>" style="position: absolute;left: -95px;bottom: -26px;display:none;">
                        <img id="tos-hand" alt="<?php _e('Hand pointing', 'shortpixel-image-optimiser'); ?>"
                             src="<?php echo(wpSPIO()->plugin_url('res/img/point.png' ));?>" style="position: absolute;left: -39px;bottom: -9px;display:none;">
                    </span>
                    <?php _e('I have read and I agree to the <a href="https://shortpixel.com/tos" target="_blank">Terms of Service</a> and the <a href="https://shortpixel.com/privacy" target="_blank">Privacy Policy</a> (<a href="https://shortpixel.com/privacy#gdpr" target="_blank">GDPR compliant</a>).','shortpixel-image-optimiser');
                    ?>
                </p>
            </td>
        </tr>
    </tbody>
</table>
<?php endif; ?>
<h3>
    <?php _e('Already have an API Key:','shortpixel-image-optimiser');?>
</h3>
<p style='font-size: 14px'>
    <?php _e('If you already have an API Key please input it below and press Validate.','shortpixel-image-optimiser');?>
</p>

<form method="POST" action="<?php echo add_query_arg(array('noheader' => 'true', 'sp-action' => 'action_addkey')) ?>"
  id="shortpixel-form-nokey">
  <?php wp_nonce_field($this->form_action, 'sp-nonce'); ?>

<table class="form-table">
    <tbody>
        <tr>
            <th scope="row"><label for="key"><?php _e('API Key:','shortpixel-image-optimiser');?></label></th>
            <td>
              <?php
              if($showApiKey) {

                  if (! $this->is_constant_key)
                    $canValidate = true;

              ?>
                  <input name="key" type="text" id="key" value="<?php echo( $view->data->apiKey );?>"
                     class="regular-text" <?php echo($editApiKey ? "" : 'disabled') ?> >
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
                    <input type="hidden" name="validate" id="valid" value="validate"/>
                    <span class="spinner" id="pluginemail_spinner" style="float:none;"></span>
                    <button type="submit" id="validate" class="button button-primary" title="<?php _e('Validate the provided API key','shortpixel-image-optimiser');?>"
                        >
                        <?php _e('Validate','shortpixel-image-optimiser');?>
                    </button>

                <?php if($this->is_constant_key) { ?>
                    <p class="settings-info"><?php _e('Key defined in wp-config.php.','shortpixel-image-optimiser');?></p>
                <?php } ?>

            </td>
        </tr>
    </tbody>
</table>
</form>
</div> <!-- tab content -->
</section>
