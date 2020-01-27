<?php
         require_once  (dirname(__FILE__)  . "/PackageLoader.php");
         $loader = new ShortPixel\Build\PackageLoader();
         $loader->load(__DIR__);
         