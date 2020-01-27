<?php
namespace ShortPixel;
use ShortPixel\ShortPixelLogger\ShortPixelLogger as Log;

/* Prevent Flock Queue on FlyWheel Cloud Hosting due to performance issues.
*
* Detect Flywheel and set Constant
*/

if (isset($_SERVER['SERVER_SOFTWARE']))
{
    $server = strtolower($_SERVER['SERVER_SOFTWARE']);

    if (strpos($server, 'flywheel') !== false) // server detected
    {
        $pos = strpos($server, '/') + 1;

        $version = substr($server, $pos);

        if (version_compare($version, '5.0.0') >= 0)
        {
          Log::addInfo('Flywheel detected on ' . $server . ' . Starting NOFLOCK Queue.');
          if (! defined('SHORTPIXEL_NOFLOCK'))
            define('SHORTPIXEL_NOFLOCK', 1);
        }
    }
}
