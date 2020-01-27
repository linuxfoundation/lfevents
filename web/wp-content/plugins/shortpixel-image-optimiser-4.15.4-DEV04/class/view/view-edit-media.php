<?php
namespace ShortPixel;
use ShortPixel\ShortpixelLogger\ShortPixelLogger as Log;

?>

<div id='sp-msg-<?php echo($view->id);?>' class='column-wp-shortPixel view-edit-media'>
<?php // Debug Data
if (! is_null($view->debugInfo) && is_array($view->debugInfo) && count($view->debugInfo) > 0 ):  ?>
      <div class='debugInfo' id='debugInfo'>
        <a class='debugModal' data-modal="debugInfo" ><?php _e('Debug Window', 'shortpixel-image-optimiser') ?></a>
        <div class='content wrapper'>
          <?php foreach($view->debugInfo as $index => $item): ?>
          <ul class="debug-<?php echo $index ?>">
            <li><strong><?php echo $item[0]; ?></strong>
              <?php
              if (is_array($item[1]) || is_object($item[1]))
                echo "<PRE>" . print_r($item[1], true) . "</PRE>";
              else
                echo $item[1];
              ?>
            </li>
          </ul>
          <?php endforeach; ?>
          <p>&nbsp;</p>
          <p>&nbsp;</p>
       </div>
    </div>
  <?php endif; ?>

  <?php if (! is_null($view->status_message)): ?>
    <h5><?php echo $view->status_message; ?></h5>
  <?php endif; ?>

  <p><?php echo $view->message; ?></p>

  <?php if (count($view->stats) > 0): ?>
  <div class='sp-column-stats'>
    <?php $this->renderLegacyCell(); ?>
    <ul class='edit-media-stats'>
    <?php foreach($view->stats as $index => $data)
    { ?>
       <li><span><?php echo $data[0] ?></span> <span><?php echo $data[1] ?></span></li>
    <?php } ?>
    </ul>
  </div>
<?php endif; ?>

  <?php foreach($view->todo as $item)
  echo $item ;
  ?>

  <div class='main-actions'>
    <?php foreach($view->actions as $action)
    echo $action;
    ?>
  </div>



</div>
