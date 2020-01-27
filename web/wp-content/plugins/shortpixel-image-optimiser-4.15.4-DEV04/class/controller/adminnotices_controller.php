<?php
namespace ShortPixel;
use ShortPixel\Notices\NoticeController as Notices;
use ShortPixel\ShortpixelLogger\ShortPixelLogger as Log;


/* Controller for automatic Notices about status of the plugin.
* This controller is bound for automatic fire. Regular procedural notices should just be queued using the Notices modules.
* Called in admin_notices.
*/

class adminNoticesController extends ShortPixelController
{
    protected static $instance;

    const MSG_COMPAT = 'Error100';  // Plugin Compatility, warn for the ones that disturb functions.
    const MSG_FILEPERMS = 'Error101'; // File Permission check, if Queue is file-based.
    const MSG_UNLISTED_FOUND = 'Error102'; // SPIO found unlisted images, but this setting is not on

    //const MSG_NO_
    const MSG_UPGRADE_MONTH = 'UpgradeNotice200';  // When processing more than the subscription allows on average..
    const MSG_UPGRADE_BULK = 'UpgradeNotice201'; // when there is no enough for a bulk run.

    const MSG_NO_APIKEY = 'ApiNotice300'; // API Key not found
    const MSG_NO_APIKEY_REPEAT = 'ApiNotice301';  // First Repeat.
    const MSG_NO_APIKEY_REPEAT_LONG = 'ApiNotice302'; // Last Repeat.

    public function __construct()
    {
        add_action('admin_notices', array($this, 'check_admin_notices'));
    }

    public static function getInstance()
    {
      if (is_null(self::$instance))
          self::$instance = new adminNoticesController();

      return self::$instance;
    }

    /** Triggered when plugin is activated */
    public static function resetCompatNotice()
    {
        Notices::removeNoticeByID(self::MSG_COMPAT);
    }

    public static function resetAPINotices()
    {
      Notices::removeNoticeByID(self::MSG_NO_APIKEY);
      Notices::removeNoticeByID(self::MSG_NO_APIKEY_REPEAT);
      Notices::removeNoticeByID(self::MSG_NO_APIKEY_REPEAT_LONG);
    }

    public static function resetQuotaNotices()
    {
      Notices::removeNoticeByID(self::MSG_UPGRADE_MONTH);
      Notices::removeNoticeByID(self::MSG_UPGRADE_BULK);
    }

    /* General function to check on Hook for admin notices if there is something to show globally */
    public function check_admin_notices()
    {
       $this->doFilePermNotice();
       $this->doAPINotices();
       $this->doCompatNotices();
       $this->doUnlistedNotices();
       $this->doQuotaNotices();
    }

    /** Load the various messages about the lack of API-keys in the plugin */
    protected function doAPINotices()
    {
        if (\wpSPIO()->settings()->verifiedKey)
        {
            return; // all fine.
        }

        $activationDate = \wpSPIO()->settings()->activationDate;
        $noticeController = Notices::getInstance();
        $now = time();

        if (! $activationDate)
        {
           $activationDate = $now;
           \wpSPIO()->settings()->activationDate = $activationDate;
        }

        $notice = $noticeController->getNoticeByID(self::MSG_NO_APIKEY);
        $notice_repeat = $noticeController->getNoticeByID(self::MSG_NO_APIKEY_REPEAT);
        $notice_long = $noticeController->getNoticeByID(self::MSG_NO_APIKEY_REPEAT_LONG);

        $notice_dismissed = ($notice && $notice->isDismissed()) ? true : false;
        $notice_dismissed_repeat = ($notice_repeat && $notice_repeat->isDismissed()) ? true : false;
        $notice_dismissed_long = ($notice_long && $notice_long->isDismissed()) ? true : false;

        if (! $notice)
        {
          // If no key is activated, load the general one.
          $message = $this->getActivationNotice();
          $notice = Notices::addNormal($message);
          Notices::makePersistent($notice, self::MSG_NO_APIKEY, YEAR_IN_SECONDS);
        }

        // The trick is that after X amount of time, the first message is replaced by one of those.
        if ($notice_dismissed && ! $notice_dismissed_repeat && $now > $activationDate + (6 * HOUR_IN_SECONDS)) // after 6 hours.
        {
          //$notice->messageType = Notices::NOTICE_WARNING;
        //  $notice->
           //Notices::removeNoticeByID(self::MSG_NO_APIKEY); // remove the previous one.
           $message = __("Action needed. Please <a href='https://shortpixel.com/wp-apikey' target='_blank'>get your API key</a> to activate your ShortPixel plugin.",'shortpixel-image-optimiser');

           $notice = Notices::addWarning($message);
           Notices::makePersistent($notice, self::MSG_NO_APIKEY_REPEAT, YEAR_IN_SECONDS);
        }
        elseif ($notice_dismissed_repeat && $notice_dismissed && ! $notice_dismissed_long && $now > $activationDate + (3 * DAY_IN_SECONDS) ) // after 3 days
        {
        //  Notices::removeNoticeByID(self::MSG_NO_APIKEY); // remove the previous one.
          $message = __("Your image gallery is not optimized. It takes 2 minutes to <a href='https://shortpixel.com/wp-apikey' target='_blank'>get your API key</a> and activate your ShortPixel plugin.",'shortpixel-image-optimiser') . "<BR><BR>";

          $notice = Notices::addWarning($message);
          Notices::makePersistent($notice, self::MSG_NO_APIKEY_REPEAT_LONG, YEAR_IN_SECONDS);

        }

    }

    protected function doFilePermNotice()
    {
      $testQ = (! defined('SHORTPIXEL_NOFLOCK')) ? \ShortPixelQueue::testQ() : \ShortPixelQueueDB::testQ();

      if( $testQ) {
        return; // all fine.
      }

      // Keep this thing out of others screens.
      if (! \wpSPIO()->env()->is_our_screen)
        return;

       $message = sprintf(__("ShortPixel is not able to write to the uploads folder so it cannot optimize images, please check permissions (tried to create the file %s/.shortpixel-q-1).",'shortpixel-image-optimiser'),
                               SHORTPIXEL_UPLOADS_BASE);
       Notices::addError($message, true);
    }

    protected function doCompatNotices()
    {
      $noticeController = Notices::getInstance();

      $notice = $noticeController->getNoticeByID(self::MSG_COMPAT);
      $conflictPlugins = \ShortPixelTools::getConflictingPlugins();

      if ($notice)
      {
        if (count($conflictPlugins) == 0)
          Notices::removeNoticeByID(self::MSG_COMPAT); // remove when not actual anymore.
        if ($notice->isDismissed() )
          return;  // notice not wanted, don't bother.
      }

      // If this notice is not already out there, and there are conflicting plugins, go for display.
      if (! $notice && count($conflictPlugins) > 0)
      {
          $notice = Notices::addWarning($this->getConflictMessage($conflictPlugins));
          Notices::makePersistent($notice, self::MSG_COMPAT, YEAR_IN_SECONDS);
      }
    }

    protected function doUnlistedNotices()
    {
      $settings = \wpSPIO()->settings();
      if ($settings->optimizeUnlisted)
        return;

      if(isset($settings->currentStats['foundUnlistedThumbs']) && is_array($settings->currentStats['foundUnlistedThumbs'])) {
          $notice = Notices::addNormal($this->getUnlistedMessage($settings->currentStats['foundUnlistedThumbs']));
          Notices::makePersistent($notice, self::MSG_UNLISTED_FOUND, YEAR_IN_SECONDS);
      }
    }

    protected function doQuotaNotices()
    {
      $settings = \wpSPIO()->settings();
      $currentStats = $settings->currentStats;
      $shortpixel = \wpSPIO()->getShortPixel();

      if (! \wpSPIO()->settings()->verifiedKey)
      {
        return; // no key, no quota.
      }

      if(!is_array($currentStats) || isset($_GET['checkquota']) || isset($currentStats["quotaData"])) {
          $shortpixel->getQuotaInformation();
      }

      /**  Comment for historical reasons, this seems strange in the original, excluding.
      * isset($this->_settings->currentStats['optimizePdfs'])
      * && $this->_settings->currentStats['optimizePdfs'] == $this->_settings->optimizePdfs )
      */
      if(!$settings->quotaExceeded)
      {
      //    $screen = get_current_screen();
          $env = \wpSPIO()->env();
          $stats = $shortpixel->countAllIfNeeded($settings->currentStats, 86400);
          $quotaData = $stats;
          $noticeController = Notices::getInstance();

          $bulk_notice = $noticeController->getNoticeByID(self::MSG_UPGRADE_BULK);
          $bulk_is_dismissed = ($bulk_notice && $bulk_notice->isDismissed() ) ? true : false;

          $month_notice = $noticeController->getNoticeByID(self::MSG_UPGRADE_MONTH);

          //this is for bulk page - alert on the total credits for total images
          if( ! $bulk_is_dismissed && $env->is_bulk_page && $this->bulkUpgradeNeeded($stats)) {
              //looks like the user hasn't got enough credits to bulk process all media library
              $message = $this->getBulkUpgradeMessage(array('filesTodo' => $stats['totalFiles'] - $stats['totalProcessedFiles'],
                                                      'quotaAvailable' => max(0, $quotaData['APICallsQuotaNumeric'] + $quotaData['APICallsQuotaOneTimeNumeric'] - $quotaData['APICallsMadeNumeric'] - $quotaData['APICallsMadeOneTimeNumeric'])));
              $notice = Notices::addNormal($message);
              Notices::makePersistent($notice, self::MSG_UPGRADE_BULK, YEAR_IN_SECONDS);
              //ShortPixelView::displayActivationNotice('upgbulk', );
          }
          //consider the monthly plus 1/6 of the available one-time credits.
          elseif( $this->monthlyUpgradeNeeded($stats)) {
              //looks like the user hasn't got enough credits to process the monthly images, display a notice telling this
              $message = $this->getMonthlyUpgradeMessage(array('monthAvg' => $this->getMonthAvg($stats), 'monthlyQuota' => $quotaData['APICallsQuotaNumeric']));
              //ShortPixelView::displayActivationNotice('upgmonth', );
              $notice = Notices::addNormal($message);
              Notices::makePersistent($notice, self::MSG_UPGRADE_MONTH, YEAR_IN_SECONDS);
          }
      }

    }

    protected function getActivationNotice()
    {
      $message = "<p>" . __('In order to start the optimization process, you need to validate your API Key in the '
              . '<a href="options-general.php?page=wp-shortpixel-settings">ShortPixel Settings</a> page in your WordPress Admin.','shortpixel-image-optimiser') . "
      </p>
      <p>" .  __('If you don’t have an API Key, you can get one delivered to your inbox, for free.','shortpixel-image-optimiser') . "</p>
      <p>" .  __('Please <a href="https://shortpixel.com/wp-apikey" target="_blank">sign up to get your API key.</a>','shortpixel-image-optimiser') . "</p>";

      return $message;
    }

    protected function getConflictMessage($conflicts)
    {
      $message = __("The following plugins are not compatible with ShortPixel and may lead to unexpected results: ",'shortpixel-image-optimiser');
      $message .= '<ul class="sp-conflict-plugins">';
      foreach($conflicts as $plugin) {
          //ShortPixelVDD($plugin);
          $action = $plugin['action'];
          $link = ( $action == 'Deactivate' )
              ? wp_nonce_url( admin_url( 'admin-post.php?action=shortpixel_deactivate_plugin&plugin=' . urlencode( $plugin['path'] ) ), 'sp_deactivate_plugin_nonce' )
              : $plugin['href'];
          $message .= '<li class="sp-conflict-plugins-list"><strong>' . $plugin['name'] . '</strong>';
          $message .= '<a href="' . $link . '" class="button button-primary">' . __( $action, 'shortpixel_image_optimiser' ) . '</a>';

          if($plugin['details']) $message .= '<br>';
          if($plugin['details']) $message .= '<span>' . $plugin['details'] . '</span>';
      }
      $message .= "</ul>";

      return $message;
    }

    protected function getUnlistedMessage($unlisted)
    {
      $message = __("<p>ShortPixel found thumbnails which are not registered in the metadata but present alongside the other thumbnails. These thumbnails could be created and needed by some plugin or by the theme. Let ShortPixel optimize them as well?</p>", 'shortpixel-image-optimiser');
      $message .= '<p>' . __("For example, the image", 'shortpixel-image-optimiser') . '
          <a href="post.php?post=' . $unlisted->id . '&action=edit" target="_blank">
              ' . $unlisted->name . '
          </a> has also these thumbs not listed in metadata: '  . (implode(', ', $unlisted->unlisted)) . '
          </p>';

        return $message;
    }

    protected function getBulkUpgradeMessage($extra)
    {
      $message = '<p>' . sprintf(__("You currently have <strong>%d images and thumbnails to optimize</strong> but you only have <strong>%d images</strong> available in your current plan."
            . " You might need to upgrade your plan in order to have all your images optimized.", 'shortpixel-image-optimiser'), $extra['filesTodo'], $extra['quotaAvailable']) . '</p>';
      $message .= $this->proposeUpgradePopup();
      //self::includeProposeUpgradePopup();
      return $message;
    }

    protected function getMonthlyUpgradeMessage($extra)
    {
      $message = '<p>' . sprintf(__("You are adding an average of <strong>%d images and thumbnails every month</strong> to your Media Library and you have <strong>a plan of %d images/month</strong>."
            . " You might need to upgrade your plan in order to have all your images optimized.", 'shortpixel-image-optimiser'), $extra['monthAvg'], $extra['monthlyQuota']) . '</p>';
      $message .= $this->proposeUpgradePopup();
      return $message;
    }

    protected function proposeUpgradePopup() {
        wp_enqueue_style('short-pixel-modal.min.css', plugins_url('/res/css/short-pixel-modal.min.css',SHORTPIXEL_PLUGIN_FILE), array(), SHORTPIXEL_IMAGE_OPTIMISER_VERSION);

        $message = '<div id="shortPixelProposeUpgradeShade" class="sp-modal-shade" style="display:none;">
            <div id="shortPixelProposeUpgrade" class="shortpixel-modal shortpixel-hide" style="min-width:610px;margin-left:-305px;">
                <div class="sp-modal-title">
                    <button type="button" class="sp-close-upgrade-button" onclick="ShortPixel.closeProposeUpgrade()">&times;</button>' .
                     __('Upgrade your ShortPixel account', 'shortpixel-image-optimiser') . '
                </div>
                <div class="sp-modal-body sptw-modal-spinner" style="height:auto;min-height:400px;padding:0;">
                </div>
            </div>
        </div>';
        return $message;
    }

    protected function monthlyUpgradeNeeded($quotaData) {
        return isset($quotaData['APICallsQuotaNumeric']) && $this->getMonthAvg($quotaData) > $quotaData['APICallsQuotaNumeric'] + ($quotaData['APICallsQuotaOneTimeNumeric'] - $quotaData['APICallsMadeOneTimeNumeric'])/6 + 20;
    }

    protected function bulkUpgradeNeeded($stats) {
        $quotaData = $stats;
        return $stats['totalFiles'] - $stats['totalProcessedFiles'] > $quotaData['APICallsQuotaNumeric'] + $quotaData['APICallsQuotaOneTimeNumeric'] - $quotaData['APICallsMadeNumeric'] - $quotaData['APICallsMadeOneTimeNumeric'];
    }

    protected function getMonthAvg($stats) {
        for($i = 4, $count = 0; $i>=1; $i--) {
            if($count == 0 && $stats['totalM' . $i] == 0) continue;
            $count++;
        }
        return ($stats['totalM1'] + $stats['totalM2'] + $stats['totalM3'] + $stats['totalM4']) / max(1,$count);
    }




} // class
