
    <div class="wrap short-pixel-bulk-page bulk-restore-all">
        <form action='<?php echo remove_query_arg('part'); ?>' method='POST' >
        <h1><?php _e('Bulk Image Optimization by ShortPixel','shortpixel-image-optimiser');?></h1>

        <div class="sp-notice sp-notice-info sp-floating-block sp-full-width">

        <h3><?php _e( "Are you sure you want to restore from backup all the images optimized with ShortPixel?", 'shortpixel-image-optimiser' ); ?></h3>

        <p><?php _e('Please read carefully. This function will: ', 'shortpixel-image-optimiser'); ?> </p>
        <ol>
          <li><?php _e('Remove all optimized images from media library', 'shortpixel-image-optimiser'); ?></li>
          <li><?php _e( sprintf('Remove all optimized images from %s selected %s other media', '<strong>', '</strong>'), 'shortpixel-image-optimiser'); ?></li>
        </ol>

        <section class='select_folders'>
          <h4><?php _e('Select which Custom Media Folders to restore', 'shortpixel-image-optimiser'); ?></h4>

            <?php $folders = $controller->getCustomFolders();
            foreach($folders as $folder):
              $path = $folder->getPath();
              $fileCount = $folder->getFileCount();
              $folder_id = $folder->getId();
            //  $status = $folder->getStatus();
              ?>


          <label class='input'><input type='checkbox' name='selected_folders[]' value='<?php echo $folder_id ?>' checked />  <?php echo $path ?> <span class='filecount'> (<?php printf ('%s File(s)', $fileCount) ?>) </span></label>
          <?php endforeach; ?>
      </section>

        <section class='random_check'>
          <div><?php _e('To continue and agree with the warning, please check the correct value below', 'shortpixel-image-optimiser') ?>
            <div class='random_answer'><?php echo $controller->randomAnswer(); ?></div>
          </div>

          <div class='inputs'><?php echo $controller->randomCheck();  ?></div>
        </section>

        <div class='form-controls'>
          <a class='button' href="<?php echo remove_query_arg('part') ?>"><?php _e('Back', 'shortpixel-image-optimiser'); ?></a>
          <button disabled aria-disabled="true" type='submit' class='button bulk restore disabled' name='bulkRestore' id='bulkRestore'><?php _e('Bulk Restore', 'shortpixel-image-optimiser'); ?></button>
        </div>

        <div class='error'><p><?php _e('It is strongly recommended to backup your uploads', 'shortpixel-image-optimiser'); ?></p>
        </div>

      </div> <!-- sp-notice -->
  </form>
</div> <!-- wrap -->
