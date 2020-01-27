<?php
namespace ShortPixel;
use ShortPixel\ShortpixelLogger\ShortPixelLogger as Log;

/* Main function of this controller is to load key on runtime
This should probably in future incorporate some apikey checking functions that shouldn't be in model.
*/
class ApiKeyController extends shortPixelController
{

    public function __construct()
    {
      $this->loadModel('apikey');
      $this->model = new apiKeyModel();
    }

    // glue method.
    public function setShortPixel($pixel)
    {
      parent::setShortPixel($pixel);
      $this->model->shortPixel = $pixel;
    }

    public function load()
    {
      $this->model->loadKey();
    }


}
