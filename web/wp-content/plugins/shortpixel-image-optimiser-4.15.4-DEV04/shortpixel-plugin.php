<?php
namespace ShortPixel;
use ShortPixel\ShortpixelLogger\ShortPixelLogger as Log;
use ShortPixel\Notices\NoticeController as Notices;


/** Plugin class
* This class is meant for: WP Hooks, init of runtime and Controller Routing.

*/
class ShortPixelPlugin
{
  private static $instance;
  protected static $modelsLoaded = array(); // don't require twice, limit amount of require looksups..

  private $paths = array('class', 'class/controller', 'class/external'); // classes that are autoloaded

  protected $is_noheaders = false;

  protected $plugin_path;
  protected $plugin_url;

  protected $shortPixel; // shortpixel megaclass
  protected $settings; // settings object.

  protected $admin_pages = array();  // admin page hooks.

  public function __construct()
  {
      $this->plugin_path = plugin_dir_path(SHORTPIXEL_PLUGIN_FILE);
      $this->plugin_url = plugin_dir_url(SHORTPIXEL_PLUGIN_FILE);

      $this->initRuntime(); // require controllers, and other needed classes
      $this->initHooks();

      add_action('plugins_loaded', array($this, 'init'), 5); // early as possible init.
  }

  /*
  * Init the plugin after plugins_loaded hook. All of WP is there, all plugins.
  * This can't be loaded on construct time, because of model Loaders etc, with would result in loop.
  *
  */
  public function init()
  {
      if(isset($_REQUEST['noheader'])) {
          $this->is_noheaders = true;
      }


      // @todo Transitionary init for the time being, since plugin init functionality is still split between.
      global $shortPixelPluginInstance;
      $shortPixelPluginInstance = new \wpShortPixel();
      $this->shortPixel = $shortPixelPluginInstance;

      $front = new frontController();
      $admin = adminController::getInstance();
      $adminNotices = adminNoticesController::getInstance(); // Hook in the admin notices.
      $notices = Notices::getInstance(); // This hooks the ajax listener

      if ($this->settings()->autoMediaLibrary)
      {
          // compat filter to shortcircuit this in cases.  (see external - visualcomposer)
          if (apply_filters('shortpixel/init/automedialibrary', true))
          {
          //  $autoPng2Jpg = get_option('wp-short-pixel-png2jpg');
            //$autoMediaLibrary = get_option('wp-short-pixel-auto-media-library');

            if($this->settings()->autoMediaLibrary && $this->settings()->png2jpg) {
                add_action( 'wp_handle_upload', array($admin,'handlePng2JpgHook'));
                // @todo Document what plugin does mpp
                add_action( 'mpp_handle_upload', array($admin,'handlePng2JpgHook'));
            }
            add_action('wp_handle_replace', array($admin,'handleReplaceHook'));

            if($this->settings()->autoMediaLibrary) {

                add_filter( 'wp_generate_attachment_metadata', array($admin,'handleImageUploadHook'), 10, 2 );
                // @todo Document what plugin does mpp
                add_filter( 'mpp_generate_metadata', array($admin,'handleImageUploadHook'), 10, 2 );
            }
          }
      }
      elseif($this->settings()->frontBootstrap && $this->env()->is_front)
      {
        // if automedialibrary is off, but we do want to auto-optimize on the front, still load the hook.
        add_filter( 'wp_generate_attachment_metadata', array($admin,'handleImageUploadHook'), 10, 2 );
      }
  }

  /** Function to get plugin settings
  *
  * @return SettingsModel The settings model object.
  */
  public function settings()
  {
    if (is_null($this->settings))
      $this->settings = new \WPShortPixelSettings();

    return $this->settings;
  }

  /** Function to get all enviromental variables
  *
  * @return EnvironmentModel
  */
  public function env()
  {
    $this->loadModel('environment');
    return EnvironmentModel::getInstance();
  }

  public function fileSystem()
  {
    return new \ShortPixel\FileSystemController();
  }

  /** Create instance. This should not be needed to call anywhere else than main plugin file
  * This should not be called *after* plugins_loaded action
  **/
  public static function getInstance()
  {
    if (is_null(self::$instance))
    {
      self::$instance = new ShortPixelPlugin();
    }
    return self::$instance;

  }

  /** Init Runtime. Loads all classes. */
  protected function initRuntime()
  {
      $plugin_path = plugin_dir_path(SHORTPIXEL_PLUGIN_FILE);
      foreach($this->paths as $short_path)
      {
        $directory_path = realpath($plugin_path . $short_path);

        if ($directory_path !== false)
        {
          $it = new \DirectoryIterator($directory_path);
          foreach($it as $file)
          {
            $file_path = $file->getRealPath();
            if ($file->isFile() && pathinfo($file_path, PATHINFO_EXTENSION) == 'php')
            {
              require_once($file_path);
            }
          }
        }
      }

      // Loads all subclassed controllers. This is used for slug-based discovery of which controller to run
      $controllerClass = \ShortPixelTools::namespaceit('ShortPixelController');
      $controllerClass::init();
  }

  /** Hooks for all WordPress related hooks
  */
  public function initHooks()
  {
      add_action('admin_menu', array($this,'admin_pages'));
      add_action('admin_enqueue_scripts', array($this, 'admin_scripts')); // admin scripts
      add_action('admin_notices', array($this, 'admin_notices')); // notices occured before page load
      add_action('admin_footer', array($this, 'admin_notices'));  // called in views.
  }

  /** Hook in our admin pages */
  public function admin_pages()
  {
      $admin_pages = array();
      // settings page
      $admin_pages[] = add_options_page( __('ShortPixel Settings','shortpixel-image-optimiser'), 'ShortPixel', 'manage_options', 'wp-shortpixel-settings', array($this, 'route'));

      if($this->shortPixel->getSpMetaDao()->hasFoldersTable() && count($this->shortPixel->getSpMetaDao()->getFolders())) {
          /*translators: title and menu name for the Other media page*/
        $admin_pages[] = add_media_page( __('Other Media Optimized by ShortPixel','shortpixel-image-optimiser'), __('Other Media','shortpixel-image-optimiser'), 'edit_others_posts', 'wp-short-pixel-custom', array( $this->shortPixel, 'listCustomMedia' ) );
      }
      /*translators: title and menu name for the Bulk Processing page*/
      $admin_pages[] = add_media_page( __('ShortPixel Bulk Process','shortpixel-image-optimiser'), __('Bulk ShortPixel','shortpixel-image-optimiser'), 'edit_others_posts', 'wp-short-pixel-bulk', array( $this->shortPixel, 'bulkProcess' ) );

      $this->admin_pages = $admin_pages;
  }

  /** PluginRunTime. Items that should be initialized *only* when doing our pages and territory. */
  protected function initPluginRunTime()
  {

  }

  /** All scripts should be registed, not enqueued here (unless global wp-admin is needed )
  *
  * Not all those registered must be enqueued however.
  */
  public function admin_scripts()
  {
    // FileTree in Settings
    wp_register_style('sp-file-tree', plugins_url('/res/css/sp-file-tree.min.css',SHORTPIXEL_PLUGIN_FILE),array(), SHORTPIXEL_IMAGE_OPTIMISER_VERSION );
    wp_register_script('sp-file-tree', plugins_url('/res/js/sp-file-tree.min.js',SHORTPIXEL_PLUGIN_FILE) );

    wp_register_style('shortpixel-admin', plugins_url('/res/css/shortpixel-admin.css', SHORTPIXEL_PLUGIN_FILE),array(), SHORTPIXEL_IMAGE_OPTIMISER_VERSION );

    wp_register_style('shortpixel', plugins_url('/res/css/short-pixel.min.css',SHORTPIXEL_PLUGIN_FILE), array(), SHORTPIXEL_IMAGE_OPTIMISER_VERSION);

    //modal - used in settings for selecting folder
    wp_register_style('shortpixel-modal', plugins_url('/res/css/short-pixel-modal.min.css',SHORTPIXEL_PLUGIN_FILE), array(), SHORTPIXEL_IMAGE_OPTIMISER_VERSION);

    // notices. additional styles for SPIO.
    wp_register_style('shortpixel-notices', plugins_url('/res/css/shortpixel-notices.css',SHORTPIXEL_PLUGIN_FILE), array(), SHORTPIXEL_IMAGE_OPTIMISER_VERSION);


    wp_register_script('shortpixel-debug', plugins_url('/res/js/debug.js',SHORTPIXEL_PLUGIN_FILE), array('jquery', 'jquery-ui-draggable'), SHORTPIXEL_IMAGE_OPTIMISER_VERSION);

  }

  public function admin_notices()
  {
      $noticeControl = Notices::getInstance();
      $noticeControl->loadIcons(array(
          'normal' => '<img class="short-pixel-notice-icon" src="' . plugins_url('res/img/slider.png', SHORTPIXEL_PLUGIN_FILE) . '">',
          'success' => '<img class="short-pixel-notice-icon" src="' . plugins_url('res/img/robo-cool.png', SHORTPIXEL_PLUGIN_FILE) . '">',
          'warning' => '<img class="short-pixel-notice-icon" src="' . plugins_url('res/img/robo-scared.png', SHORTPIXEL_PLUGIN_FILE) . '">',
          'error' => '<img class="short-pixel-notice-icon" src="' . plugins_url('res/img/robo-scared.png', SHORTPIXEL_PLUGIN_FILE) . '">',
      ));

      if ($noticeControl->countNotices() > 0)
      {
        $notices = $noticeControl->getNoticesForDisplay();

        if (count($notices) > 0)
        {
          wp_enqueue_style('shortpixel-notices');

          foreach($notices as $notice)
          {
            echo $notice->getForDisplay();
          }
        }
      }
      $noticeControl->update(); // puts views, and updates
  }

  /** Load Style via Route, on demand */
  public function load_style($name)
  {
    if ($this->is_noheaders)  // fail silently, if this is a no-headers request.
      return;

    if (wp_style_is($name, 'registered'))
    {
      wp_enqueue_style($name);
    }
    else {
      Log::addWarn("Style $name was asked for, but not registered");
    }
  }

  /** Load Style via Route, on demand */
  public function load_script($name)
  {
    if ($this->is_noheaders)  // fail silently, if this is a no-headers request.
      return;

    if (wp_script_is($name, 'registered'))
    {
      wp_enqueue_script($name);
    }
    else {
      Log::addWarn("Script $name was asked for, but not registered");
    }
  }

  /** Route, based on the page slug
  *
  * Principially all page controller should be routed from here.
  */
  public function route()
  {
      global $plugin_page;
      $this->initPluginRunTime();

      $default_action = 'load'; // generic action on controller.
      $action = isset($_REQUEST['sp-action']) ? sanitize_text_field($_REQUEST['sp-action']) : $default_action;
      Log::addDebug('Request', $_REQUEST);
      $controller = false;

      if ($this->env()->is_debug)
      {
         $this->load_script('shortpixel-debug');
      }

      switch($plugin_page)
      {
          case 'wp-shortpixel-settings':
            $this->load_style('shortpixel-admin');
            $this->load_style('shortpixel');
            $this->load_style('shortpixel-modal');
            $this->load_style('sp-file-tree');
            $this->load_script('sp-file-tree');
            $controller = \shortPixelTools::namespaceit("SettingsController");
            $url = menu_page_url($plugin_page, false);
          break;
      }

      if ($controller !== false)
      {
        $c = new $controller();
        $c->setShortPixel($this->shortPixel);
        $c->setControllerURL($url);
        if (method_exists($c, $action))
          $c->$action();
        else {
          Log::addWarn("Attempted Action $action on $controller does not exist!");
          $c->$default_action();
        }

      }
  }

  /** Loads the Model Data Structure upon request
  *
  * @param string $name Name of the model
  */
  public function loadModel($name){
     $path = \ShortPixelTools::getPluginPath() . 'class/model/' . $name . '_model.php';

     if (! in_array($name, self::$modelsLoaded))
     {
       self::$modelsLoaded[] = $name;
       if(file_exists($path)){
            require_once($path);
       }
       else {
         Log::addError("Model $name  could not be found");
       }
     }
  }

  // Get the plugin URL, based on real URL.
  public function plugin_url($urlpath = '')
  {
    $url = trailingslashit($this->plugin_url);
    if (strlen($urlpath) > 0)
      $url .= $urlpath;
    return $url;
  }

  // Get the plugin path.
  public function plugin_path($path = '')
  {
    $plugin_path = trailingslashit($this->plugin_path);
    if (strlen($path) > 0)
      $plugin_path .= $path;

    return $plugin_path;
  }

  // Get the ShortPixel Object.
  public function getShortPixel()
  {
    return $this->shortPixel;
  }

  /** Returns defined admin page hooks. Internal use - check states via environmentmodel
  * @returns Array
  */
  public function get_admin_pages()
  {
    return $this->admin_pages;
  }

  public static function activatePlugin()
  {
      self::deactivatePlugin();
      if(SHORTPIXEL_RESET_ON_ACTIVATE === true && WP_DEBUG === true) { //force reset plugin counters, only on specific occasions and on test environments
          \WPShortPixelSettings::debugResetOptions();
          $settings = new \WPShortPixelSettings();
          $spMetaDao = new \ShortPixelCustomMetaDao(new \WpShortPixelDb(), $settings->excludePatterns);
          $spMetaDao->dropTables();
      }

      $env = wpSPIO()->env();

      if(\WPShortPixelSettings::getOpt('deliverWebp') == 3 && ! $env->is_nginx) {
          \WpShortPixel::alterHtaccess(); //add the htaccess lines
      }

      adminNoticesController::resetCompatNotice();
      adminNoticesController::resetAPINotices();
      adminNoticesController::resetQuotaNotices();

      \WPShortPixelSettings::onActivate();

  }

  public static function deactivatePlugin()
  {
    \ShortPixelQueue::resetBulk();
    (! defined('SHORTPIXEL_NOFLOCK')) ? \ShortPixelQueue::resetPrio() : \ShortPixelQueueDB::resetPrio();
    \WPShortPixelSettings::onDeactivate();

    //$settingsControl = new \ShortPixel\SettingsController();
    $env = wpSPIO()->env();

    if (! $env->is_nginx)
      \WpShortPixel::alterHtaccess(true);

    // save remove.
    $fs = new FileSystemController();
    $log = $fs->getFile(SHORTPIXEL_BACKUP_FOLDER . "/shortpixel_log");
    if ($log->exists())
      $log->delete();
  //  @unlink(SHORTPIXEL_BACKUP_FOLDER . "/shortpixel_log");
  }

  public static function uninstallPlugin()
  {
    $settings = new \WPShortPixelSettings();
    $env = \wpSPIO()->env();

    if($settings->removeSettingsOnDeletePlugin == 1) {
        \WPShortPixelSettings::debugResetOptions();
        if (! $env->is_nginx)
          insert_with_markers( get_home_path() . '.htaccess', 'ShortPixelWebp', '');

        $spMetaDao = new \ShortPixelCustomMetaDao(new \WpShortPixelDb());
        $spMetaDao->dropTables();
    }
  }

} // class plugin
