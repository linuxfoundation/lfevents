<?php
namespace ShortPixel;
use ShortPixel\ShortPixelLogger\ShortPixelLogger as Log;

if (defined("SHORTPIXEL_EXPERIMENTAL_SECURICACHE"))
{
   $v = new securiVersion();
}

class securiVersion
{
  public function __construct()
  {
    add_filter('shortpixel_image_urls', array($this, 'timestamp_cache'), 10, 2 );
  }

  public function timestamp_cache($urls, $id)
  {

    //$urls = add_filter('shortpixel_image_urls', );
    // https://developer.wordpress.org/reference/functions/get_post_modified_time/
    $time = get_post_modified_time('U', false, $id );
    foreach($urls as $index => $url)
    {
      $urls[$index] = add_query_arg('ver', $time, $url);
    }

    Log::addDebug('SecuriVersion - URLS being versioned', $urls);
    return $urls;
  }


}
