<?php
namespace ShortPixel;
use ShortPixel\Notices\NoticeController as Notice;

class NextGen
{
  protected $instance;

  public function __construct()
  {
    add_action('ngg_added_new_image', array($this,'new_image'));
    add_filter('shortpixel/init/optimize_on_screens', array($this, 'add_screen_loads'));
  }

  public function add_screen_loads($use_screens)
  {
    $use_screens[] = 'toplevel_page_nextgen-gallery'; // toplevel
    $use_screens[] = 'gallery_page_ngg_addgallery';  // add gallery
    $use_screens[] = 'nggallery-manage-gallery'; // manage gallery
    $use_screens[] = 'gallery_page_nggallery-manage-album'; // manage album

    return $use_screens;
  }

  public static function getInstance()
  {
    if (is_null(self::$instance))
      self::$instance = new nextGen();

     return self::$instance;
  }
  /** Enables nextGen, add galleries to custom folders
  * @param boolean $silent Throw a notice or not. This seems to be based if nextgen was already activated previously or not.
  */
  public function nextGenEnabled($silent)
  {
    \WpShortPixelDb::checkCustomTables(); // check if custom tables are created, if not, create them

    $this->addNextGenGalleriesToCustom($silent);

  }

  /** Adds nextGen galleries to custom table
  * Note - this function does *Not* check if nextgen is enabled, not if checks custom Tables. Use nextgenEnabled for this.
  * Enabled checks are not an external class issue, so must be done before calling.
  */
  public function addNextGenGalleriesToCustom($silent = true) {
      $shortPixel = \wpSPIO()->getShortPixel();

      $folderMsg = "";

      //add the NextGen galleries to custom folders
      $ngGalleries = \ShortPixelNextGenAdapter::getGalleries();
      $meta = $shortPixel->getSpMetaDao();
      foreach($ngGalleries as $gallery) {
          $msg = $meta->newFolderFromPath($gallery, get_home_path(), \WPShortPixel::getCustomFolderBase());
          if($msg) { //try again with ABSPATH as maybe WP is in a subdir
              $msg = $meta->newFolderFromPath($gallery, ABSPATH, \WPShortPixel::getCustomFolderBase());
          }
          $folderMsg .= $msg;
          //$this->_settings->hasCustomFolders = time();
      }

      if (count($ngGalleries) > 0)
      {
        // put timestamp to this setting.
        $settings = \wpSPIO()->settings();
        $settings->hasCustomFolders = time();

      }
      if (! $silent)
      {
          Notice::addNormal($folderMsg);
      }

  }

  /** @todo Move handling also to the integration */
  public function add_image($image)
  {
      wpSPIO()->getShortPixel()->handleNextGenImageUpload($image);
  }
} // class .



$ng = new nextGen();
