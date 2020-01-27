<?php
namespace ShortPixel;
use ShortPixel\ShortpixelLogger\ShortPixelLogger as Log;

// Future contoller for the edit media metabox view.
class editMediaController extends ShortPixelController
{
      //$this->model = new
      protected $template = 'view-edit-media';
      protected $model = 'image';

      private $post_id;
      private $actions_allowed;

      private $legacyViewObj;

      public function __construct()
      {

        $this->loadModel($this->model);
      //  $this->loadModel('image');
        parent::__construct();
      }

      // This data should be rendered by Image Model in the future.
      public function setTempData($data)
      {
          $this->data = $data;
      }

      public function load($post_id)
      {
          $this->post_id = $post_id;

          $this->imageModel = new ImageModel();
          $this->imageModel->setByPostID($post_id);
          $this->imageModel->reAcquire(); // single display mode - reset things.

          $this->view->id = $post_id;
          $this->view->status_message = null;

          $this->actions_allowed = $this->checkUserPrivileges();

          $this->view->status_message = $this->getStatusMessage();
          $this->view->actions = $this->getActions();
          $this->view->stats = $this->getStatistics();
          $this->view->todo = $this->getTodo();
          $this->view->debugInfo = $this->getDebugInfo();

          $this->view->message = isset($this->data['message']) ? $this->data['message'] : '';
          //$this->view->r
          $this->loadView();

      }


      // The old view, we are trying to get rid of.
      public function setLegacyView($legacyView)
      {
          $this->legacyViewObj = $legacyView;
      }

      protected function getStatusMessage()
      {
          if (! isset($this->data['status']))
            return;

          switch($this->data['status'])
          {
            case 'n/a':
                return _e('Optimization N/A','shortpixel-image-optimiser');
            break;
            case 'notFound':
                return  _e('Image does not exist.','shortpixel-image-optimiser');
                break;
            case 'invalidKey':
                return _e('Invalid API Key. <a href="options-general.php?page=wp-shortpixel-settings">Check your Settings</a>','shortpixel-image-optimiser');
            break;
            case 'quotaExceeded':
                return __('Quota Exceeded','shortpixel-image-optimiser');
            break;
          }
      }

      protected function getActions()
      {
          $actions = array();
          if (! $this->actions_allowed)
            return $actions;

          switch($this->data['status'])
          {
            case 'optimizeNow':
              $actions[] =  '<a class="button button-smaller button-primary" href="javascript:manualOptimization(' . $this->post_id . ',false)">
                                          ' .  __('Optimize now','shortpixel-image-optimiser') . '
                            </a>';
            break;
            case 'retry':
            case 'waiting':
              if (isset($this->data['cleanup']))
              {
                  $actions[] = '<a class="button button-smaller button-primary" href="javascript:manualOptimization(' . $this->post_id . ', true)">' .
                       __('Cleanup&Retry','shortpixel-image-optimiser') . '</a>';
              }
              else {
                if($this->data['status'] == 'retry' && (isset($this->data['backup']) && $this->data['backup']) ) {
                  $actions[] =  '<a class="button button-smaller sp-action-restore"
                                href="admin.php?action=shortpixel_restore_backup&attachment_ID=' . $this->post_id . '" style="margin-left:5px;"
                        title="' . __('Restore Image from Backup', 'shortpixel-image-optimiser') . '">
                         ' . __('Cleanup','shortpixel-image-optimiser') . '</a>';

                    }
                  $actions[] = '<a class="button button-smaller button-primary" href="javascript:manualOptimization(' . $this->post_id . ', false)">' .
                         __('Retry','shortpixel-image-optimiser')  . '</a>';
              }
            break;
          }

          return $actions;
      }

      protected function getStatistics()
      {
        $data = $this->data;

        if ( $data['status'] != 'pdfOptimized' && $data['status'] != 'imgOptimized')
          return array();

        $stats = array();
        if ($data['percent'] && $data['percent'] > 0)
        {
          $stats[] = array(__('Reduced by','shortpixel-image-optimiser'), '<strong>' . $data['percent'] . '% </strong>');
        }

        $stats[] = array(__('Type: ', 'shortpixel-image-optimiser'), $data['type']);
        if ($data['bonus'])
          $stats[] = array(__('Bonus processing','shortpixel-image-optimiser'), '');

        if ($data['thumbsOpt'])
        {
           if ($data['thumbsTotal'] > $data['thumbsOpt'] )
              $stats[] = array(sprintf(__('+%s of %s thumbnails optimized','shortpixel-image-optimiser'),$data['thumbsOpt'],$data['thumbsTotal']), '');
           else
              $stats[] = array(sprintf(__('+ %s thumbnails optimized','shortpixel-image-optimiser'),$data['thumbsOpt']), '');
        }

        if ($data['retinasOpt'])
        {
            $stats[] = array(sprintf(__('+%s Retina images optimized','shortpixel-image-optimiser') , $data['retinasOpt']), '');
        }

        if ($data['webpCount'])
        {
          $stats[] = array(__(" WebP images", 'shortpixel-image-optimiser'), $data['webpCount']);
        }
        if ($data['exifKept'])
          $stats[] = array(__('EXIF kept', 'shortpixel-image-optimiser'), '');
        else {
          $stats[] = array(__('EXIF removed', 'shortpixel-image-optimiser'), '');
        }

        if ($data['png2jpg'])
        {
          $stats[] = array(  __('Converted from PNG','shortpixel-image-optimiser'), '');
        }

        $stats[] = array(__("Optimized on", 'shortpixel-image-optimiser') . ": " . $data['date'], '');


      /*  $successText .= ($data['webpCount'] ? "<br>+" . $data['webpCount'] . __(" WebP images", 'shortpixel-image-optimiser') : "")
                . "<br>EXIF: " . ($data['exifKept'] ? __('kept','shortpixel-image-optimiser') :  __('removed','shortpixel-image-optimiser'))
                . ($data['png2jpg'] ? '<br>' . __('Converted from PNG','shortpixel-image-optimiser'): '')
                . "<br>" . __("Optimized on", 'shortpixel-image-optimiser') . ": " . $data['date']
                . $todoSizes . $excludeSizes . $missingThumbs;
*/
        return $stats;
      }

      protected function getTodo()
      {
        $data = $this->data;
        if ( $data['status'] != 'pdfOptimized' && $data['status'] != 'imgOptimized')
          return array();

              $excluded = (isset($data['excludeSizes']) ? count($data['excludeSizes']) : 0);
              $todoSizes = $missingThumbs = $excludeSizes = '';

                if(isset($data['thumbsToOptimizeList']) && count($data['thumbsToOptimizeList'])) {
                    $todoSizes .= "<br><span style='word-break: break-all;'> <span style='font-weight: bold;'>" . __("To optimize:", 'shortpixel-image-optimiser') . "</span>";
                    foreach($data['thumbsToOptimizeList'] as $todoItem) {
                        $todoSizes .= "<br> &#8226;&nbsp;" . $todoItem;
                    }
                    $todoSizes .= '</span>';
                }
                if(isset($data['excludeSizes']) && count($data['excludeSizes']) > 0 ) {
                    $excludeSizes .= "<br><span style='word-break: break-all;'> <span style='font-weight: bold;'>" . __("Excluded thumbnails:", 'shortpixel-image-optimiser') . "</span>";
                    foreach($data['excludeSizes'] as $excludedItem) {
                        $excludeSizes .= "<br> &#8226;&nbsp;" . $excludedItem;
                    }
                    $excludeSizes .= '</span>';
                }
                if(count($data['thumbsMissing'])) {
                    $missingThumbs .= "<br><span style='word-break: break-all;'> <span style='font-weight: bold;'>" . __("Missing thumbnails:", 'shortpixel-image-optimiser') . "</span>";
                    foreach($data['thumbsMissing'] as $miss) {
                        $missingThumbs .= "<br> &#8226&nbsp;" . $miss;
                    }
                    $missingThumbs .= '</span>';
                }

              return array($todoSizes, $excludeSizes, $missingThumbs);
      }

      protected function getDebugInfo()
      {
          if(! \wpSPIO()->env()->is_debug )
          {
            return array();
          }

          $meta = \wp_get_attachment_metadata($this->post_id);

          $imageObj = new ImageModel();
          $imageObj->setByPostID($this->post_id);
          $imageFile = $imageObj->getFile();

          $sizes = isset($this->data['sizes']) ? $this->data['sizes'] : array();

          $debugInfo = array();
          $debugInfo[] = array(__('URL', 'shortpixel_image_optiser'), wp_get_attachment_url($this->post_id));
          $debugInfo[] = array(__('File'), get_attached_file($this->post_id));
          $debugInfo[] = array(__('Status'), $this->imageModel->getMeta()->getStatus() );

          $debugInfo[] = array(__('WPML Duplicates'), json_encode(\ShortPixelMetaFacade::getWPMLDuplicates($this->post_id)) );
          $debugInfo['shortpixeldata'] = array(__('Data'), $this->data);
          $debugInfo['wpmetadata'] = array(__('Meta'), $meta );
          if ($imageFile->hasBackup())
          {
            $backupFile = $imageFile->getBackupFile();
            $debugInfo[] = array(__('Backup Folder'), $this->shortPixel->getBackupFolderAny($this->imageModel->getFile()->getFullPath(), $sizes));
            $debugInfo[] = array(__('Backup File'), (string) $backupFile . '(' . \ShortPixelTools::formatBytes($backupFile->getFileSize()) . ')' );
          }
          else {
            $debugInfo[] =  array(__("No Backup Available"), '');
          }
          if ($or = $imageObj->has_original())
          {
             $debugInfo[] = array(__('Original File'), $or->getFullPath()  . '(' . \ShortPixelTools::formatBytes($or->getFileSize()) . ')');
             $orbackup = $or->getBackupFile();
             if ($orbackup)
              $debugInfo[] = array(__('Backup'), $orbackup->getFullPath() . '(' . \ShortPixelTools::formatBytes($orbackup->getFileSize()) . ')');
          }



          if (! isset($meta['sizes']) )
          {
             $debugInfo[] = array('',  __('Thumbnails were not generated', 'shortpixel-image-optimiser'));
          }
          else
          {   
            foreach($meta['sizes'] as $size => $data)
            {
              $display_size = ucfirst(str_replace("_", " ", $size));
              $img = wp_get_attachment_image_src($this->post_id, $size);
              //var_dump($img);
              $debugInfo[] = array('', "<div class='$size previewwrapper'><img src='" . $img[0] . "'><span class='label'>$img[0] ( $display_size )</span></div>");
            }
          }
          return $debugInfo;
      }

      protected function renderLegacyCell()
      {

        $data = $this->data;

        if ( $data['status'] != 'pdfOptimized' && $data['status'] != 'imgOptimized')
          return null;

        $this->legacyViewObj->renderListCell($this->post_id, $data['status'], $data['showActions'], $data['thumbsToOptimize'],
                $data['backup'], $data['type'], $data['invType'], '');
      }

      private function checkUserPrivileges()
      {
        if ((current_user_can( 'manage_options' ) || current_user_can( 'upload_files' ) || current_user_can( 'edit_posts' )))
          return true;

        return false;
      }

} // controller .
