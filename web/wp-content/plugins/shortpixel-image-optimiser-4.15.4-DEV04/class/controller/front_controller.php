<?php
namespace ShortPixel;
use ShortPixel\ShortPixelLogger\ShortPixelLogger as Log;
use ShortPixel\Notices\NoticeController as Notices;


/** Handle everything that SP is doing front-wise */
class frontController extends ShortPixelController
{
  // DeliverWebp option settings for front-end delivery of webp
  const WEBP_GLOBAL = 1;
  const WEBP_WP = 2;
  const WEBP_NOCHANGE = 3;

  public function __construct()
  {
    if (wpSPIO()->env()->is_front) // if is front.
    {
      $this->initWebpHooks();
      $this->hookFrontProcessing();
    }
  }

  public function initWebpHooks()
  {
    $webp_option = \wpSPIO()->settings()->deliverWebp;

    if ( $webp_option ) {
        if(\ShortPixelTools::shortPixelIsPluginActive('shortpixel-adaptive-images/short-pixel-ai.php')) {
            Notices::addWarning(__('Please deactivate the ShortPixel Image Optimizer\'s
                <a href="options-general.php?page=wp-shortpixel-settings&part=adv-settings">Deliver WebP using PICTURE tag</a>
                option when the ShortPixel Adaptive Images plugin is active.','shortpixel-image-optimiser'), true);
        }
        elseif( $webp_option == self::WEBP_GLOBAL ){
            add_action( 'wp_head', array($this, 'addPictureJs') ); // adds polyfill JS to the header
            add_action( 'init',  array($this, 'startOutputBuffer'), 1 ); // start output buffer to capture content
        } elseif ($webp_option == self::WEBP_WP){
            add_filter( 'the_content', array($this, 'convertImgToPictureAddWebp'), 10000 ); // priority big, so it will be executed last
            add_filter( 'the_excerpt', array($this, 'convertImgToPictureAddWebp'), 10000 );
            add_filter( 'post_thumbnail_html', array($this,'convertImgToPictureAddWebp') );
        }
    }
  }

  public function hookFrontProcessing()
  {
    if (!  \wpSPIO()->settings()->frontBootstrap)
      return;

    $prio = (! defined('SHORTPIXEL_NOFLOCK')) ? \ShortPixelQueue::get() : \ShortPixelQueueDB::get();

    if ($prio && is_array($prio) && count($prio) > 0)
    {
      //also need to have it in the front footer then
      add_action( 'wp_footer', array( \wpSPIO()->getShortPixel(), 'shortPixelJS') );
      add_filter('script_loader_tag', array($this, 'load_sp_async'), 10, 3);


      //need to add the nopriv action for when items exist in the queue and no user is logged in
      add_action( 'wp_ajax_nopriv_shortpixel_image_processing', array( \wpSPIO()->getShortPixel(), 'handleImageProcessing') );
      add_action( 'wp_ajax_shortpixel_image_processing', array( \wpSPIO()->getShortPixel(), 'handleImageProcessing') );

    }
  }

  /* When loading on front, asynd defer ourselves */
  public function load_sp_async($tag, $handle, $src)
  {
    $defer = array('shortpixel.js','shortpixel.min.js', 'jquery.knob.min.js', 'jquery.tooltip.min.js', 'punycode.min.js');

    if (in_array($handle, $defer))
    {
          return '<script src="' . $src . '" defer="defer" type="text/javascript"></script>' . "\n";
    }

    return $tag;
  }

  /* Picture generation, hooked on the_content filter
  * @param $content String The content to check and convert
  * @return String Converted content
  */
  public function convertImgToPictureAddWebp($content) {

      if(function_exists('is_amp_endpoint') && is_amp_endpoint()) {
          //for AMP pages the <picture> tag is not allowed
          return $content . (isset($_GET['SHORTPIXEL_DEBUG']) ? '<!-- SPDBG is AMP -->' : '');
      }
      require_once(\ShortPixelTools::getPluginPath() . 'class/front/img-to-picture-webp.php');

      $webpObj = new ShortPixelImgToPictureWebp();
      return $webpObj->convert($content);
    //  return \::convert($content);// . "<!-- PICTURE TAGS BY SHORTPIXEL -->";
  }

  public function addPictureJs() {
      // Don't do anything with the RSS feed.
      if ( is_feed() || is_admin() ) { return; }

      echo '<script>'
         . 'var spPicTest = document.createElement( "picture" );'
         . 'if(!window.HTMLPictureElement && document.addEventListener) {'
              . 'window.addEventListener("DOMContentLoaded", function() {'
                  . 'var scriptTag = document.createElement("script");'
                  . 'scriptTag.src = "' . plugins_url('/res/js/picturefill.min.js', __FILE__) . '";'
                  . 'document.body.appendChild(scriptTag);'
              . '});'
          . '}'
         . '</script>';
  }


  public function startOutputBuffer() {
      $env = wpSPIO()->env();
      if ($env->is_admin || $env->is_ajaxcall)
        return;

      $call = array($this, 'convertImgToPictureAddWebp');
      ob_start( $call );
  }



} // class
