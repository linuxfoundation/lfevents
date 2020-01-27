<?php
namespace ShortPixel;
use ShortPixel\ShortPixelLogger\ShortPixelLogger as Log;

if ( class_exists( 'WPE_API', false ) ) {

  /* WPE has a limit on Query size (16K). After that it won't execute the query. */
  add_filter('shortpixel/db/chunk_size', function ($size)
  {
      // 16K chars = 2500 * size of ID 6 chars (until post_id 100.000);
      if ($size > 2500)
        return 2500;
  });
}
