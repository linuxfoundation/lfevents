<?php

/** Settings Model **/
class WPShortPixelSettings extends ShortPixel\ShortPixelModel {
    private $_apiKey = '';
    private $_compressionType = 1;
    private $_keepExif = 0;
    private $_processThumbnails = 1;
    private $_CMYKtoRGBconversion = 1;
    private $_backupImages = 1;
    private $_verifiedKey = false;

    private $_resizeImages = false;
    private $_resizeWidth = 0;
    private $_resizeHeight = 0;

    private static $_optionsMap = array(
        //This one is accessed also directly via get_option
        'frontBootstrap' => array('key' => 'wp-short-pixel-front-bootstrap', 'default' => null, 'group' => 'options'), //set to 1 when need the plugin active for logged in user in the front-end
        'lastBackAction' => array('key' => 'wp-short-pixel-last-back-action', 'default' => null, 'group' => 'state'), //when less than 10 min. passed from this timestamp, the front-bootstrap is ineffective.

        //optimization options
        'apiKey' => array('key' => 'wp-short-pixel-apiKey', 'default' => '', 'group' => 'options'),
        'verifiedKey' => array('key' => 'wp-short-pixel-verifiedKey', 'default' => false, 'group' => 'options'),
        'compressionType' => array('key' => 'wp-short-pixel-compression', 'default' => 1, 'group' => 'options'),
        'processThumbnails' => array('key' => 'wp-short-process_thumbnails', 'default' => null, 'group' => 'options'),
        'keepExif' => array('key' => 'wp-short-pixel-keep-exif', 'default' => 0, 'group' => 'options'),
        'CMYKtoRGBconversion' => array('key' => 'wp-short-pixel_cmyk2rgb', 'default' => 1, 'group' => 'options'),
        'createWebp' => array('key' => 'wp-short-create-webp', 'default' => null, 'group' => 'options'),
        'deliverWebp' => array('key' => 'wp-short-pixel-create-webp-markup', 'default' => 0, 'group' => 'options'),
        'optimizeRetina' => array('key' => 'wp-short-pixel-optimize-retina', 'default' => 1, 'group' => 'options'),
        'optimizeUnlisted' => array('key' => 'wp-short-pixel-optimize-unlisted', 'default' => 0, 'group' => 'options'),
        'backupImages' => array('key' => 'wp-short-backup_images', 'default' => 1, 'group' => 'options'),
        'resizeImages' => array('key' => 'wp-short-pixel-resize-images', 'default' => false, 'group' => 'options'),
        'resizeType' => array('key' => 'wp-short-pixel-resize-type', 'default' => null, 'group' => 'options'),
        'resizeWidth' => array('key' => 'wp-short-pixel-resize-width', 'default' => 0, 'group' => 'options'),
        'resizeHeight' => array('key' => 'wp-short-pixel-resize-height', 'default' => 0, 'group' => 'options'),
        'siteAuthUser' => array('key' => 'wp-short-pixel-site-auth-user', 'default' => null, 'group' => 'options'),
        'siteAuthPass' => array('key' => 'wp-short-pixel-site-auth-pass', 'default' => null, 'group' => 'options'),
        'autoMediaLibrary' => array('key' => 'wp-short-pixel-auto-media-library', 'default' => 1, 'group' => 'options'),
        'optimizePdfs' => array('key' => 'wp-short-pixel-optimize-pdfs', 'default' => 1, 'group' => 'options'),
        'excludePatterns' => array('key' => 'wp-short-pixel-exclude-patterns', 'default' => array(), 'group' => 'options'),
        'png2jpg' => array('key' => 'wp-short-pixel-png2jpg', 'default' => 0, 'group' => 'options'),
        'excludeSizes' => array('key' => 'wp-short-pixel-excludeSizes', 'default' => array(), 'group' => 'options'),

        //CloudFlare
        'cloudflareEmail'   => array( 'key' => 'wp-short-pixel-cloudflareAPIEmail', 'default' => '', 'group' => 'options'),
        'cloudflareAuthKey' => array( 'key' => 'wp-short-pixel-cloudflareAuthKey', 'default' => '', 'group' => 'options'),
        'cloudflareZoneID'  => array( 'key' => 'wp-short-pixel-cloudflareAPIZoneID', 'default' => '', 'group' => 'options'),

        //optimize other images than the ones in Media Library
        'includeNextGen' => array('key' => 'wp-short-pixel-include-next-gen', 'default' => null, 'group' => 'options'),
        'hasCustomFolders' => array('key' => 'wp-short-pixel-has-custom-folders', 'default' => false, 'group' => 'options'),
        'customBulkPaused' => array('key' => 'wp-short-pixel-custom-bulk-paused', 'default' => false, 'group' => 'options'),

        //uninstall
        'removeSettingsOnDeletePlugin' => array('key' => 'wp-short-pixel-remove-settings-on-delete-plugin', 'default' => false, 'group' => 'options'),

        //stats, notices, etc.
        'currentStats' => array('key' => 'wp-short-pixel-current-total-files', 'default' => null, 'group' => 'state'),
        'fileCount' => array('key' => 'wp-short-pixel-fileCount', 'default' => 0, 'group' => 'state'),
        'thumbsCount' => array('key' => 'wp-short-pixel-thumbnail-count', 'default' => 0, 'group' => 'state'),
        'under5Percent' => array('key' => 'wp-short-pixel-files-under-5-percent', 'default' => 0, 'group' => 'state'),
        'savedSpace' => array('key' => 'wp-short-pixel-savedSpace', 'default' => 0, 'group' => 'state'),
        //'averageCompression' => array('key' => 'wp-short-pixel-averageCompression', 'default' => null, 'group' => 'state'),
        'apiRetries' => array('key' => 'wp-short-pixel-api-retries', 'default' => 0, 'group' => 'state'),
        'totalOptimized' => array('key' => 'wp-short-pixel-total-optimized', 'default' => 0, 'group' => 'state'),
        'totalOriginal' => array('key' => 'wp-short-pixel-total-original', 'default' => 0, 'group' => 'state'),
        'quotaExceeded' => array('key' => 'wp-short-pixel-quota-exceeded', 'default' => 0, 'group' => 'state'),
        'httpProto' => array('key' => 'wp-short-pixel-protocol', 'default' => 'https', 'group' => 'state'),
        'downloadProto' => array('key' => 'wp-short-pixel-download-protocol', 'default' => null, 'group' => 'state'),
        //TODO downloadArchive initial sa fie 10% - hash pe numele de domeniu
        'downloadArchive' => array('key' => 'wp-short-pixel-download-archive', 'default' => -1, 'group' => 'state'),
        'mediaAlert' => array('key' => 'wp-short-pixel-media-alert', 'default' => null, 'group' => 'state'),
        'dismissedNotices' => array('key' => 'wp-short-pixel-dismissed-notices', 'default' => array(), 'group' => 'state'),
        'activationDate' => array('key' => 'wp-short-pixel-activation-date', 'default' => null, 'group' => 'state'),
        'activationNotice' => array('key' => 'wp-short-pixel-activation-notice', 'default' => null, 'group' => 'state'),
        'mediaLibraryViewMode' => array('key' => 'wp-short-pixel-view-mode', 'default' => null, 'group' => 'state'),
        'redirectedSettings' => array('key' => 'wp-short-pixel-redirected-settings', 'default' => null, 'group' => 'state'),
        'convertedPng2Jpg' => array('key' => 'wp-short-pixel-converted-png2jpg', 'default' => array(), 'group' => 'state'),

        //bulk state machine
        'bulkType' => array('key' => 'wp-short-pixel-bulk-type', 'default' => null, 'group' => 'bulk'),
        'bulkLastStatus' => array('key' => 'wp-short-pixel-bulk-last-status', 'default' => null, 'group' => 'bulk'),
        'startBulkId' => array('key' => 'wp-short-pixel-query-id-start', 'default' => 0, 'group' => 'bulk'),
        'stopBulkId' => array('key' => 'wp-short-pixel-query-id-stop', 'default' => 0, 'group' => 'bulk'),
        'bulkCount' => array('key' => 'wp-short-pixel-bulk-count', 'default' => 0, 'group' => 'bulk'),
        'bulkPreviousPercent' => array('key' => 'wp-short-pixel-bulk-previous-percent', 'default' => 0, 'group' => 'bulk'),
        'bulkCurrentlyProcessed' => array('key' => 'wp-short-pixel-bulk-processed-items', 'default' => 0, 'group' => 'bulk'),
        'bulkAlreadyDoneCount' => array('key' => 'wp-short-pixel-bulk-done-count', 'default' => 0, 'group' => 'bulk'),
        'lastBulkStartTime' => array('key' => 'wp-short-pixel-last-bulk-start-time', 'default' => 0, 'group' => 'bulk'),
        'lastBulkSuccessTime' => array('key' => 'wp-short-pixel-last-bulk-success-time', 'default' => 0, 'group' => 'bulk'),
        'bulkRunningTime' => array('key' => 'wp-short-pixel-bulk-running-time', 'default' => 0, 'group' => 'bulk'),
        'cancelPointer' => array('key' => 'wp-short-pixel-cancel-pointer', 'default' => 0, 'group' => 'bulk'),
        'skipToCustom' => array('key' => 'wp-short-pixel-skip-to-custom', 'default' => null, 'group' => 'bulk'),
        'bulkEverRan' => array('key' => 'wp-short-pixel-bulk-ever-ran', 'default' => false, 'group' => 'bulk'),
        'flagId' => array('key' => 'wp-short-pixel-flag-id', 'default' => 0, 'group' => 'bulk'),
        'failedImages' => array('key' => 'wp-short-pixel-failed-imgs', 'default' => 0, 'group' => 'bulk'),
        'bulkProcessingStatus' => array('key' => 'bulkProcessingStatus', 'default' => null, 'group' => 'bulk'),

        //'priorityQueue' => array('key' => 'wp-short-pixel-priorityQueue', 'default' => array()),
        'prioritySkip' => array('key' => 'wp-short-pixel-prioritySkip', 'default' => array(), 'group' => 'state'),

        //'' => array('key' => 'wp-short-pixel-', 'default' => null),
    );

    // This  array --  field_name -> (s)anitize mask
    protected $model = array(
        'apiKey' => array('s' => 'string'), // string
    //    'verifiedKey' => array('s' => 'string'), // string
        'compressionType' => array('s' => 'int'), // int
        'resizeWidth' => array('s' => 'int'), // int
        'resizeHeight' => array('s' => 'int'), // int
        'processThumbnails' => array('s' => 'boolean'), // checkbox
        'backupImages' => array('s' => 'boolean'), // checkbox
        'keepExif' => array('s' => 'int'), // checkbox
        'resizeImages' => array('s' => 'boolean'),
        'resizeType' => array('s' => 'string'),
        'includeNextGen' => array('s' => 'boolean'), // checkbox
        'png2jpg' => array('s' => 'int'), // checkbox
        'CMYKtoRGBconversion' => array('s' => 'boolean'), //checkbox
        'createWebp' => array('s' => 'boolean'), // checkbox
        'deliverWebp' => array('s' => 'int'), // checkbox
        'optimizeRetina' => array('s' => 'boolean'), // checkbox
        'optimizeUnlisted' => array('s' => 'boolean'), // $checkbox
        'optimizePdfs' => array('s' => 'boolean'), //checkbox
        'excludePatterns' => array('s' => 'exception'), //  - processed, multi-layer, so skip
        'siteAuthUser' => array('s' => 'string'), // string
        'siteAuthPass' => array('s' => 'string'), // string
        'frontBootstrap' => array('s' =>'boolean'), // checkbox
        'autoMediaLibrary' => array('s' => 'boolean'), // checkbox
        'excludeSizes' => array('s' => 'array'), // Array
        'cloudflareEmail' => array('s' => 'string'), // string
        'cloudflareAuthKey' => array('s' => 'string'), // string
        'cloudflareZoneID' => array('s' => 'string'), // string
        'savedSpace' => array('s' => 'skip'),
        'fileCount' => array('s' => 'skip'), // int
        'under5Percent' => array('s' => 'skip'), // int
    );

    // @todo Eventually, this should not happen onLoad, but on demand.
      public function __construct() {
        $this->populateOptions();
    }

    public function populateOptions() {

        $this->_apiKey = self::getOpt('wp-short-pixel-apiKey', '');
        $this->_verifiedKey = self::getOpt('wp-short-pixel-verifiedKey', $this->_verifiedKey);
        $this->_compressionType = self::getOpt('wp-short-pixel-compression', $this->_compressionType);
        $this->_processThumbnails = self::getOpt('wp-short-process_thumbnails', $this->_processThumbnails);
        $this->_CMYKtoRGBconversion = self::getOpt('wp-short-pixel_cmyk2rgb', $this->_CMYKtoRGBconversion);
        $this->_backupImages = self::getOpt('wp-short-backup_images', $this->_backupImages);
        $this->_resizeImages =  self::getOpt( 'wp-short-pixel-resize-images', 0);
        $this->_resizeWidth = self::getOpt( 'wp-short-pixel-resize-width', 0);
        $this->_resizeHeight = self::getOpt( 'wp-short-pixel-resize-height', 0);

        // the following lines practically set defaults for options if they're not set
        foreach(self::$_optionsMap as $opt) {
            self::getOpt($opt['key'], $opt['default']);
        }

        if(self::getOpt("downloadArchive") == -1) {
            self::setOpt(self::$_optionsMap["downloadArchive"]['key'], crc32(get_site_url())%10);
        }
    }

    public static function debugResetOptions() {
        foreach(self::$_optionsMap as $key => $val) {
            delete_option($val['key']);
        }
        delete_option("wp-short-pixel-bulk-previous-percent");
    }

    public static function onActivate() {
        if(!self::getOpt('wp-short-pixel-verifiedKey', false)) {
            update_option('wp-short-pixel-activation-notice', true, 'no');
        }
        update_option( 'wp-short-pixel-activation-date', time(), 'no');
        delete_option( 'wp-short-pixel-bulk-last-status');
        delete_option( 'wp-short-pixel-current-total-files');
        delete_option(self::$_optionsMap['removeSettingsOnDeletePlugin']['key']);
        // Dismissed now via Notices Controller.
      /*  $dismissed = get_option('wp-short-pixel-dismissed-notices', array());
        if(isset($dismissed['compat'])) {
            unset($dismissed['compat']);
            update_option('wp-short-pixel-dismissed-notices', $dismissed, 'no');
        } */

        $formerPrio = get_option('wp-short-pixel-priorityQueue');
        $qGet = (! defined('SHORTPIXEL_NOFLOCK')) ?  ShortPixelQueue::get() : ShortPixelQueueDB::get();
        if(is_array($formerPrio) && !count($qGet)) {

          (! defined('SHORTPIXEL_NOFLOCK')) ? ShortPixelQueue::set($formerPrio) : ShortPixelQueueDB::set($formerPrio);
            delete_option('wp-short-pixel-priorityQueue');
        }
    }

    public static function onDeactivate() {
        delete_option('wp-short-pixel-activation-notice');
    }


    public function __get($name)
    {
        if (array_key_exists($name, self::$_optionsMap)) {
            return $this->getOpt(self::$_optionsMap[$name]['key']);
        }
        $trace = debug_backtrace();
        trigger_error(
            'Undefined property via __get(): ' . $name .
            ' in ' . $trace[0]['file'] .
            ' on line ' . $trace[0]['line'],
            E_USER_NOTICE);
        return null;
    }

    public function __set($name, $value) {
        if (array_key_exists($name, self::$_optionsMap)) {
            if($value !== null) {
                $this->setOpt(self::$_optionsMap[$name]['key'], $value);
            } else {
                delete_option(self::$_optionsMap[$name]['key']);
            }
        }
    }

    public static function getOpt($key, $default = null) {
        if(isset(self::$_optionsMap[$key]['key'])) { //first try our name
            $key = self::$_optionsMap[$key]['key'];
        }
        if(get_option($key) === false) {
            add_option( $key, $default, '', 'no' );
        }
        return get_option($key);
    }

    public function setOpt($key, $val) {
        $autoload = true;
        /*if (isset(self::$_optionsMap[$key]))
        {
            if (self::$_optionsMap[$key]['group'] == 'options')
               $autoload = true;  // add most used to autoload, because performance.

        } */

        $ret = update_option($key, $val, $autoload);

        //hack for the situation when the option would just not update....
        if($ret === false && !is_array($val) && $val != get_option($key)) {
            delete_option($key);
            $alloptions = wp_load_alloptions();
            if ( isset( $alloptions[$key] ) ) {
                wp_cache_delete( 'alloptions', 'options' );
            } else {
                wp_cache_delete( $key, 'options' );
            }
            delete_option($key);
            add_option($key, $val, '', $autoload);

            // still not? try the DB way...
            if($ret === false && $val != get_option($key)) {
                global $wpdb;
                $sql = "SELECT * FROM {$wpdb->prefix}options WHERE option_name = '" . $key . "'";
                $rows = $wpdb->get_results($sql);
                if(count($rows) === 0) {
                    $wpdb->insert($wpdb->prefix.'options',
                                 array("option_name" => $key, "option_value" => (is_array($val) ? serialize($val) : $val), "autoload" => $autoload),
                                 array("option_name" => "%s", "option_value" => (is_numeric($val) ? "%d" : "%s")));
                } else { //update
                    $sql = "update {$wpdb->prefix}options SET option_value=" .
                           (is_array($val)
                               ? "'" . serialize($val) . "'"
                               : (is_numeric($val) ? $val : "'" . $val . "'")) . " WHERE option_name = '" . $key . "'";
                    $rows = $wpdb->get_results($sql);
                }

                if($val != get_option($key)) {
                    //tough luck, gonna use the bomb...
                    wp_cache_flush();
                    delete_option($key);
                    add_option($key, $val, '', $autoload);
                }
            }
        }
    }
}
