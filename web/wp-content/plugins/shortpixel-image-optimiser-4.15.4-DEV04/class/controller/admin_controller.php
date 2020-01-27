<?php
namespace ShortPixel;
use ShortPixel\ShortPixelLogger\ShortPixelLogger as Log;
use ShortPixel\Notices\NoticeController as Notices;

/* AdminController is meant for handling events, hooks, filters in WordPress where there is *NO* specific or more precise  Shortpixel Page active.
*
* This should be a delegation class connection global hooks and such to the best shortpixel handler.
*/
class adminController extends ShortPixelController
{
    protected static $instance;

    public function __construct()
    {

    }

    public static function getInstance()
    {
      if (is_null(self::$instance))
          self::$instance = new adminController();

      return self::$instance;
    }

    /** Handling upload actions
    * @hook wp_generate_attachment_metadata
    */
    public function handleImageUploadHook($meta, $ID = null)
    {
        return \wpSPIO()->getShortPixel()->handleMediaLibraryImageUpload($meta, $ID);
    }

    /** For conversion
    * @hook wp_handle_upload
    */
    public function handlePng2JpgHook($params)
    {
      return \wpSPIO()->getShortPixel()->convertPng2Jpg($params);
    }

    /** When replacing happens.
    * @hook wp_handle_replace
    */
    public function handleReplaceHook($params)
    {
      if(isset($params['post_id'])) { //integration with EnableMediaReplace - that's an upload for replacing an existing ID
          $itemHandler = \wpSPIO()->getShortPixel()->onDeleteImage( intval($params['post_id']) );
          $itemHandler->deleteAllSPMeta();
      }
    }


}
