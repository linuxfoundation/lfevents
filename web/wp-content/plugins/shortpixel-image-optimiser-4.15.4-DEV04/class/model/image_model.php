<?php
namespace ShortPixel;
use ShortPixel\ShortpixelLogger\ShortPixelLogger as Log;

/* ImageModel class.
*
*
* - Represents a -single- image entity *not file*.
* - Can be either MediaLibrary, or Custom .
* - Not a replacement of Meta, but might be.
* - Goal: Structural ONE method calls of image related information, and combining information. Same task is now done on many places.
* -- Shortpixel Class should be able to blindly call model for information, correct metadata and such.
*/
class ImageModel extends ShortPixelModel
{

    private $file;  // the file representation
    private $meta; // metadata of the image.
    private $facade; // ShortPixelMetaFacade

    protected $thumbsnails = array(); // thumbnails of this
    protected $original_file; // the original instead of the possibly _scaled one created by WP 5.3 >

    private $post_id; // attachment id

    private $is_scaled = false; // if this is WP 5.3 scaled
    private $is_optimized = false; // if this is optimized
    private $is_png2jpg = false; // todo implement.

    public function __construct()
    {

    }

    public function setByPostID($post_id)
    {
      // Set Meta
      $fs = \wpSPIO()->filesystem();
      $this->post_id = $post_id;
      $this->facade = new \ShortPixelMetaFacade($post_id);
      $this->meta = $this->facade->getMeta();

      $this->setImageStatus();
      $this->file = $fs->getAttachedFile($post_id);

      // WP 5.3 and higher. Check for original file.
      if (function_exists('wp_get_original_image_path'))
      {
        $this->setOriginalFile();
      }
    }

    /** This function sets various status attributes for imageModel.
    * Goal is to make the status of images more consistent and don't have to rely constantly on getting and ready the whole meta
    * with it's various marks. */
    protected function setImageStatus()
    {
      $status = $this->meta->getStatus();
      if ($status == \ShortPixelMeta::FILE_STATUS_SUCCESS)
        $this->is_optimized = true;

      $png2jpg = $this->meta->getPng2Jpg();
      if(is_array($png2jpg))
      {
        $this->is_png2jpg = true;
      }
    }

    protected function setOriginalFile()
    {
      $fs = \wpSPIO()->filesystem();

      if (is_null($this->post_id))
        return false;

      $originalFile = $fs->getOriginalPath($this->post_id);

      if ($originalFile->exists() && $originalFile->getFullPath() !== $this->file->getfullPath() )
      {
        $this->original_file = $originalFile;
        $this->is_scaled = true;
      }

    }

    // Not sure if it will work like this.
    public function is_scaled()
    {
       return $this->is_scaled;
    }

    public function has_original()
    {
        if (is_null($this->original_file))
          return false;

        return $this->original_file;
    }

    public function getMeta()
    {
      return $this->meta;
    }

    public function getFile()
    {
      return $this->file;
    }

    /** Get the facade object.
    * @todo Ideally, the facade will be an internal thing, separating the custom and media library functions.
    */
    public function getFacade()
    {
       return $this->facade;
    }

  /*  public function getOriginalFile()
    {
       return $this->origin_file;
    } */

    /* Sanity check in process. Should only be called upon special request, or with single image displays. Should check and recheck stats, thumbs, unlistedthumbs and all assumptions of data that might corrupt or change outside of this plugin */
    public function reAcquire()
    {
        $this->addUnlistedThumbs();
        $this->reCheckThumbnails();

        // $this->recount();
    }

    /** Removed the current attachment, with hopefully removing everything we set.
    * @return ShortPixelFacade  Legacy return, to do something with replacing
    */
    public function delete()
    {
      $itemHandler = $this->facade;
      //$itemHandler = new ShortPixelMetaFacade($post_id);
      $urlsPaths = $itemHandler->getURLsAndPATHs(true, false, true, array(), true, true);

      // @todo move this to some better permanent structure w/ png2jpg class.
      if ($this->is_png2jpg)
      {
        $png2jpg = $this->meta->getPng2Jpg();
        if (isset($png2jpg['originalFile']))
        {
          $urlsPaths['PATHs'][] = $png2jpg['originalFile'];
        }
        if (isset($png2jpg['originalSizes']))
        {
              foreach($png2jpg['originalSizes'] as $size => $data)
              {
                if (isset($data['file']))
                {
                  $filedir = (string) $this->file->getFileDir();
                  $urlsPaths['PATHs'][] = $filedir . $data['file'];
                }
              }
        }
      }
      if(count($urlsPaths['PATHs'])) {
          Log::addDebug('Removing Backups and Webps', $urlsPaths);
          \wpSPIO()->getShortPixel()->maybeDumpFromProcessedOnServer($itemHandler, $urlsPaths);
          \wpSPIO()->getShortPixel()->deleteBackupsAndWebPs($urlsPaths['PATHs']);
      }

      $itemHandler->deleteItemCache();
      return $itemHandler; //return it because we call it also on replace and on replace we need to follow this by deleting SP metadata, on delete it
    }

    // Rebuild the ThumbsOptList and others to fix old info, wrong builds.
    private function reCheckThumbnails()
    {
       // Redo only on non-processed images.
       if ($this->meta->getStatus() != \ShortPixelMeta::FILE_STATUS_SUCCESS)
       {
         return;
       }
       if (! $this->file->exists())
       {
         Log::addInfo('Checking thumbnails for non-existing file', array($this->file));
         return;
       }
       $data = $this->facade->getRawMeta();
       $oldList = array();
       if (isset($data['ShortPixel']['thumbsOptList']))
       {
        $oldList = $data['ShortPixel']['thumbsOptList'];
        unset($data['ShortPixel']['thumbsOptList']); // reset the thumbsOptList, so unset to get what the function thinks should be there.
       }
       list($includedSizes, $thumbsCount)  = \WpShortPixelMediaLbraryAdapter::getThumbsToOptimize($data, $this->file->getFullPath() );

       // When identical, save the check and the Dbase update.
       if ($oldList === $includedSizes)
       {
          return;
       }

       $newList = array();
       foreach($this->meta->getThumbsOptList() as $index => $item)
       {
         if ( in_array($item, $includedSizes))
         {
            $newList[] = $item;
         }
       }

       $this->meta->setThumbsOptList($newList);
       $this->facade->updateMeta($this->meta);

    }

    private function addUnlistedThumbs()
    {
      // @todo weak call. See how in future settings might come via central provider.
      $settings = new \WPShortPixelSettings();

      // must be media library, setting must be on.
      if($this->facade->getType() != \ShortPixelMetaFacade::MEDIA_LIBRARY_TYPE
         || ! $settings->optimizeUnlisted) {
        return 0;
      }

      $this->facade->removeSPFoundMeta(); // remove all found meta. If will be re-added here every time.
      $meta = $this->meta; //$itemHandler->getMeta();

      Log::addDebug('Finding Thumbs on path' . $meta->getPath());
      $thumbs = \WpShortPixelMediaLbraryAdapter::findThumbs($meta->getPath());

      $fs = \wpSPIO()->filesystem();
      $mainFile = $this->file;

      // Find Thumbs returns *full file path*
      $foundThumbs = \WpShortPixelMediaLbraryAdapter::findThumbs($mainFile->getFullPath());

        // no thumbs, then done.
      if (count($foundThumbs) == 0)
      {
        return 0;
      }
      //first identify which thumbs are not in the sizes
      $sizes = $meta->getThumbs();
      $mimeType = false;

      $allSizes = array();
      $basepath = $mainFile->getFileDir()->getPath();

      foreach($sizes as $size) {
        // Thumbs should have filename only. This is shortpixel-meta ! Not metadata!
        // Provided filename can be unexpected (URL, fullpath), so first do check, get filename, then check the full path
        $sizeFileCheck = $fs->getFile($size['file']);
        $sizeFilePath = $basepath . $sizeFileCheck->getFileName();
        $sizeFile = $fs->getFile($sizeFilePath);

        //get the mime-type from one of the thumbs metas
        if(isset($size['mime-type'])) { //situation from support case #9351 Ramesh Mehay
            $mimeType = $size['mime-type'];
        }
        $allSizes[] = $sizeFile;
      }

      foreach($foundThumbs as $id => $found) {
          $foundFile = $fs->getFile($found);

          foreach($allSizes as $sizeFile) {
              if ($sizeFile->getExtension() !== $foundFile->getExtension())
              {
                $foundThumbs[$id] = false;
              }
              elseif ($sizeFile->getFileName() === $foundFile->getFileName())
              {
                  $foundThumbs[$id] = false;
              }
          }
      }
          // add the unfound ones to the sizes array
          $ind = 1;
          $counter = 0;
          // Assumption:: there is no point in adding to this array since findThumbs should find *all* thumbs that are relevant to this image.
          /*while (isset($sizes[ShortPixelMeta::FOUND_THUMB_PREFIX . str_pad("".$start, 2, '0', STR_PAD_LEFT)]))
          {
            $start++;
          } */
      //    $start = $ind;

          foreach($foundThumbs as $found) {
              if($found !== false) {
                  Log::addDebug('Adding File to sizes -> ' . $found);
                  $size = getimagesize($found);
                  Log::addDebug('Add Unlisted, add size' . $found );

                  $sizes[\ShortPixelMeta::FOUND_THUMB_PREFIX . str_pad("".$ind, 2, '0', STR_PAD_LEFT)]= array( // it's a file that has no corresponding thumb so it's the WEBP for the main file
                      'file' => \ShortPixelAPI::MB_basename($found),
                      'width' => $size[0],
                      'height' => $size[1],
                      'mime-type' => $mimeType
                  );
                  $ind++;
                  $counter++;
              }
          }
          if($ind > 1) { // at least one thumbnail added, update
              $meta->setThumbs($sizes);
              $this->facade->updateMeta($meta);
          }

        return $counter;
    }

} // model
