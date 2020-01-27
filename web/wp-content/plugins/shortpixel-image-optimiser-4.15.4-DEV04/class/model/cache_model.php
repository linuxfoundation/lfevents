<?php
namespace ShortPixel;
use ShortPixel\ShortpixelLogger\ShortPixelLogger as Log;


/* Model for storing cached data
*
* Use this in conjunction with cache controller, don't call it stand-alone.
*/
class CacheModel
{

  protected $name;
  protected $value;
  protected $expires = HOUR_IN_SECONDS;

  protected $exists = false;


  public function __construct($name)
  {
     $this->name = $name;
     $this->load($name);
  }

  /** Set the expiration of this item. In seconds
  * @param $time Expiration in Seconds
  */
  public function setExpires($time)
  {
    $this->expires = $time;
  }

  public function setValue($value)
  {
    $this->value = $value;
  }

  public function exists()
  {
    return $this->exists;
  }

  public function getValue()
  {
      return $this->value;
  }

  public function getName()
  {
      return $this->name;
  }

  public function save()
  {
     $this->exists = set_transient($this->name, $this->value, $this->expires);
  }

  public function delete()
  {
     delete_transient($this->name);
     $this->exists = false;
  }

  protected function load()
  {
    $item = get_transient($this->name);

    if ($item)
    {
      $this->value = $item;
      $this->exists = true;
    }
  }

}
