<?php
namespace ShortPixel\Notices;

class NoticeModel //extends ShortPixelModel
{
  public $message; // The message we want to convey.
  public $code;

  private $id = null; // used for persistent messages.
  protected $viewed = false; // was this notice viewed?
  protected $is_persistent = false;  // This is a fatal issue, display until something was fixed.
  protected $is_dismissed = false; // for persistent notices,
  protected $suppress_until = null;
  protected $suppress_period = -1;
  public $is_removable = true; // if removable, display a notice dialog with red X or so.
  public $messageType = self::NOTICE_NORMAL;

  public $notice_action; // empty unless for display. Ajax action to talk back to controller.

  public static $icons = array();

  const NOTICE_NORMAL = 1;
  const NOTICE_ERROR = 2;
  const NOTICE_SUCCESS = 3;
  const NOTICE_WARNING = 4;

  /** Use this model in conjunction with NoticeController, do not call directly */
  public function __construct($message, $messageType = self::NOTICE_NORMAL)
  {
      $this->message = $message;
      $this->messageType = $messageType;

  }

  public function isDone()
  {
    // check suppressed
    if ($this->is_dismissed && ! is_null($this->suppress_until))
    {
        if (time() >= $this->suppress_until)
        {
            //Log::addDebug('')
            $this->is_persistent = false; // unpersist, so it will be cleaned and dropped.

        }
    }

    if ($this->viewed && ! $this->is_persistent)
      return true;
    else
      return false;
  }

  public function getID()
  {
     return $this->id;
  }

  public function isPersistent()
  {
     return $this->is_persistent;
  }

  public function isDismissed()
  {
    return $this->is_dismissed;
  }

  public function dismiss()
  {
     $this->is_dismissed = true;
     $this->suppress_until = time() + $this->suppress_period;
  }

  /** Set a notice persistent. Meaning it shows every page load until dismissed.
  * @param $key Unique Key of this message. Required
  * @param $suppress When dismissed do not show this message again for X amount of time. When -1 it will just be dropped from the Notices and not suppressed
  */
  public function setPersistent($key, $suppress = -1)
  {
      $this->id = $key;
      $this->is_persistent = true;
      $this->suppress_period = $suppress;
  }

  public static function setIcon($notice_type, $icon)
  {
    switch($notice_type)
    {
      case 'error':
        $type = self::NOTICE_ERROR;
      break;
      case 'success':
        $type = self::NOTICE_SUCCESS;
      break;
      case 'warning':
        $type = self::NOTICE_WARNING;
      break;
      case 'normal':
      default:
        $type = self::NOTICE_NORMAL;
      break;
    }
    self::$icons[$type] = $icon;
  }

  public function getForDisplay()
  {
    $this->viewed = true;
    $class = 'shortpixel notice ';

    $icon = '';

    switch($this->messageType)
    {
      case self::NOTICE_ERROR:
        $class .= 'notice-error ';
        $icon = isset(self::$icons[self::NOTICE_ERROR]) ? self::$icons[self::NOTICE_ERROR] : '';
        //$icon = 'scared';
      break;
      case self::NOTICE_SUCCESS:
        $class .= 'notice-success ';
        $icon = isset(self::$icons[self::NOTICE_SUCCESS]) ? self::$icons[self::NOTICE_SUCCESS] : '';
      break;
      case self::NOTICE_WARNING:
        $class .= 'notice-warning ';
        $icon = isset(self::$icons[self::NOTICE_WARNING]) ? self::$icons[self::NOTICE_WARNING] : '';
      break;
      case self::NOTICE_NORMAL:
         $class .= 'notice-info ';
         $icon = isset(self::$icons[self::NOTICE_NORMAL]) ? self::$icons[self::NOTICE_NORMAL] : '';
      break;
      default:
        $class .= 'notice-info ';
        $icon = '';
      break;
    }


    if ($this->is_removable)
    {
      $class .= 'is-dismissible ';
    }

    if ($this->is_persistent)
    {
      $class .= 'is-persistent ';
    }

      $id = ! is_null($this->id) ? 'id="' . $this->id . '"' : '';

    $output = "<div $id class='$class'><span class='icon'> " . $icon . "</span> <span class='content'>" . $this->message . "</span></div>";
    if ($this->is_persistent && $this->is_removable)
    {
        $output .= "<script type='text/javascript'>\n" . $this->getDismissJS() . "\n</script>";
    }
    return $output;

  }

  private function getDismissJS()
  {
     $url = wp_json_encode(admin_url('admin-ajax.php'));
    // $action = 'dismiss';
    $nonce = wp_create_nonce('dismiss');

    $data = wp_json_encode(array('action' => $this->notice_action, 'plugin_action' => 'dismiss', 'nonce' => $nonce, 'id' => $this->id, 'time' => $this->suppress_period));

  //  $data_string = "{action:'$this->notice_action'}";

      $js = "jQuery(document).on('click','#$this->id button',
         function() {
           var data = $data;
           var url = $url;
           jQuery.post(url, data); }
      );";
      return "\n jQuery(document).ready(function(){ \n" . $js . "\n});";
  }

}
