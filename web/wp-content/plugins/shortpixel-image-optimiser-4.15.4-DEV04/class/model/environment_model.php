<?php
namespace ShortPixel;
use ShortPixel\ShortpixelLogger\ShortPixelLogger as Log;

/** Loads a few environment variables handy to have nearby
*
* Notice - This is meant to be loaded via the plugin class. Easy access with wpSPIO()->getEnv().
*/
class EnvironmentModel extends ShortPixelModel
{
    // Server and PHP
    public $is_nginx;
    public $is_apache;
    public $is_gd_installed;
    public $is_curl_installed;

    // MultiSite
    public $is_multisite;
    public $is_mainsite;

    // Integrations
    public $has_nextgen;

    // WordPress
    public $is_front = false;
    public $is_admin = false;
    public $is_ajaxcall = false;

    private $screen_is_set = false;
    public $is_screen_to_use = false; // where shortpixel optimizer loads
    public $is_our_screen = false; // where shortpixel hooks in more complicated functions.
    public $is_bulk_page = false; // Shortpixel bulk screen.

    // Debug flag
    public $is_debug = false;

    protected static $instance;


  public function __construct()
  {
     $this->setServer();
     $this->setWordPress();
     $this->setIntegrations();
     $this->setScreen();  // This might not be set on construct time!
  }

  public static function getInstance()
  {
    if (is_null(self::$instance))
        self::$instance = new EnvironmentModel();

    if (! self::$instance->screen_is_set)
      self::$instance->setScreen();

    return self::$instance;
  }

  private function setServer()
  {
    $this->is_nginx = strpos(strtolower($_SERVER["SERVER_SOFTWARE"]), 'nginx') !== false ? true : false;
    $this->is_apache = strpos(strtolower($_SERVER["SERVER_SOFTWARE"]), 'apache') !== false ? true : false;
    $this->is_gd_installed = function_exists('imagecreatefrompng');
    $this->is_curl_installed = function_exists('curl_init');

  }

  private function setWordPress()
  {
    $this->is_multisite = (function_exists("is_multisite") && is_multisite()) ? true : false;
    $this->is_mainsite = is_main_site();

    $this->determineFrontBack();


    if (defined('DOING_AJAX') && DOING_AJAX)
    {
      $this->is_ajaxcall = true;
    }

    $this->is_debug = Log::debugIsActive();

  }

  // check if this request is front or back.
  protected function determineFrontBack()
  {
    if ( is_admin() )
      $this->is_admin = true;
    else
      $this->is_front = true;

  }

  public function setScreen()
  {
    if (! function_exists('get_current_screen')) // way too early.
      return false;

    $screen = get_current_screen();

    if (is_null($screen)) // too early
      return false;

    // WordPress pages where we'll be active on.
    // https://codex.wordpress.org/Plugin_API/Admin_Screen_Reference
    $use_screens = array(
        'edit-post_tag', // edit tags
        'upload', // media library
        'attachment', // edit media
        'post', // post screen
        'edit-post', // edit post
        'new-post',  // new post
        'edit-page', // all pages
    );
    $use_screens = apply_filters('shortpixel/init/optimize_on_screens', $use_screens);

    if(in_array($screen->id, $use_screens)) {
          $this->is_screen_to_use = true;
    }

    // Our pages.
    $pages = \wpSPIO()->get_admin_pages();
    // the main WP pages where SPIO hooks a lot of functions into, our operating area.
    $wp_pages = array('upload', 'attachment');
    $pages = array_merge($pages, $wp_pages);

    /* pages can be null in certain cases i.e. plugin activation.
    * treat those cases as improper screen set.
    */
    if (is_null($pages))
    {
        return false;
    }

    if ( in_array($screen->id, $pages))
    {
       $this->is_screen_to_use = true;
       $this->is_our_screen = true;

       if ($screen->id == 'media_page_wp-short-pixel-bulk')
        $this->is_bulk_page = true;
    }



    $this->screen_is_set = true;
  }

  private function setIntegrations()
  {
    $this->has_nextgen = \ShortPixelNextGenAdapter::hasNextGen();

  }
}
