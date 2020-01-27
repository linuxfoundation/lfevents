<?php
namespace ShortPixel;
use ShortPixel\Notices\NoticeController as Notices;

$path = '/var/www/shortpixel/wp-content/uploads/2019/09/';


?>

<section id="tab-debug" <?php echo ($this->display_part == 'debug') ? ' class="sel-tab" ' :''; ?>>
  <h2><a class='tab-link' href='javascript:void(0);' data-id="tab-debug">
    <?php _e('Debug','shortpixel-image-optimiser');?></a>
  </h2>

<div class="wp-shortpixel-options wp-shortpixel-tab-content" style="visibility: hidden">
  <div class='env'>
    <h3><?php _e('Environment', 'shortpixel'); ?></h3>
    <div class='flex'>
      <span>Nginx</span><span><?php var_export($this->is_nginx); ?></span>
      <span>KeyVerified</span><span><?php var_export($this->is_verifiedkey); ?></span>
      <span>HtAccess writable</span><span><?php var_export($this->is_htaccess_writable); ?></span>
      <span>Multisite</span><span><?php var_export($this->is_multisite); ?></span>
      <span>Main site</span><span><?php var_export($this->is_mainsite); ?></span>
      <span>Constant key</span><span><?php var_export($this->is_constant_key); ?></span>
      <span>Hide Key</span><span><?php var_export($this->hide_api_key); ?></span>
      <span>Has Nextgen</span><span><?php var_export($this->has_nextgen); ?></span>

    </div>
  </div>

  <div class='settings'>
    <h3><?php _e('Settings', 'shortpixel'); ?></h3>
    <?php $local = $this->view->data;
      $local->apiKey = strlen($local->apiKey) . ' chars'; ?>
    <pre><?php var_export($local); ?></pre>
  </div>

  <div class='quotadata'>
    <h3><?php _e('Quota Data', 'shortpixel'); ?></h3>
    <pre><?php var_export($this->quotaData); ?></pre>
  </div>

  <h3>Tools</h3>
  <div class='debug-images'>
    <form method="POST" action="<?php echo add_query_arg(array('sp-action' => 'action_debug_medialibrary')) ?>"
      id="shortpixel-form-debug-medialib">
      <button class='button' type='submit'>Reacquire Thumbnails on Media Library</button>
      </form>
  </div>

</div> <!-- tab-content -->
</section>
