<?php
namespace ShortPixel;

// Integration class for HelpScout
class HelpScout
{
  public static function outputBeacon($apiKey)
  {
      global $shortPixelPluginInstance;
      $dismissed = $shortPixelPluginInstance->getSettings()->dismissedNotices ? $shortPixelPluginInstance->getSettings()->dismissedNotices : array();
      if(isset($dismissed['help'])) {
          return;
      }
    ?>
    <style>
           .shortpixel-hs-blind {
               position: fixed;
               bottom: 18px;
               right: 0;
               z-index: 20003;
               background-color: white;
               width: 87px;
               height: 174px;
               border-radius: 20px 0 0 20px;
               text-align: right;
               padding-right: 15px;
           }
           .shortpixel-hs-blind a {
               color: lightgray;
               text-decoration: none;
           }
           .shortpixel-hs-blind .dashicons-minus {
               border: 3px solid;
               border-radius: 12px;
               font-size: 12px;
               font-weight: bold;
               line-height: 15px;
               height: 13px;
               width: 13px;
               display:none;
           }
           .shortpixel-hs-blind .dashicons-dismiss {
               font-size: 23px;
               line-height: 19px;
               display: none;
           }
           .shortpixel-hs-blind:hover .dashicons-minus,
           .shortpixel-hs-blind:hover .dashicons-dismiss {
               display: inline-block;
           }
           .shortpixel-hs-button-blind {
               display:none;
               position: fixed;
               bottom: 115px;right: 0;
               z-index: 20003;
               background-color: white;
               width: 237px;
               height: 54px;
           }
           .shortpixel-hs-tools {
               position: fixed;
               bottom: 116px;
               right: 0px;
               z-index: 20003;
               background-color: #ecf9fc;
               padding: 8px 18px 3px 12px;
               border-radius: 26px 0 0 26px;
               -webkit-box-shadow: 1px 1px 5px 0px rgba(6,109,117,1);
               -moz-box-shadow: 1px 1px 5px 0px rgba(6,109,117,1);
               box-shadow: 1px 1px 10px 0px rgb(172, 173, 173);
           }
           @media (max-width: 767px) {
               .shortpixel-hs-blind {
                   bottom: 8px;
                   height: 194px;
               }
               .shortpixel-hs-button-blind {
                   bottom: 100px;
               }
           }
       </style>
      <div id="shortpixel-hs-blind" class="shortpixel-hs-blind">
          <a href="javascript:ShortPixel.closeHelpPane();">
              <i class="dashicons dashicons-minus" title="<?php _e('Dismiss for now', 'shortpixel-image-optimiser'); ?>   "></i>
          </a>
          <a href="javascript:ShortPixel.dismissHelpPane();">
              <i class="dashicons dashicons-dismiss" title="<?php _e('Never display again', 'shortpixel-image-optimiser'); ?>"></i>
          </a>
      </div>
       <div id="shortpixel-hs-button-blind" class="shortpixel-hs-button-blind"></div>
       <div id="shortpixel-hs-tools" class="shortpixel-hs-tools">
           <a href="javascript:shortpixelToggleHS();" class="shortpixel-hs-tools-docs" title="<?php _e('Search through our online documentation.', 'shortpixel-image-optimiser'); ?>">
               <img alt="<?php _e('ShortPixel document icon', 'shortpixel-image-optimiser'); ?>" src="<?php echo( wpSPIO()->plugin_url('res/img/notes-sp.png') );?>" style="margin-bottom: 2px;width: 36px;">
           </a>
       </div>
       <script>
           window.shortpixelHSOpen = -1;
           function shortpixelToggleHS() {
               if(window.shortpixelHSOpen == -1) {
                   HS.beacon.init();
               }
               if(window.shortpixelHSOpen == 1) {
                   HS.beacon.close();
                   jQuery("#shortpixel-hs-button-blind").css('display', 'none');
                   window.shortpixelHSOpen = 0;
               } else {
                   HS.beacon.open();
                   jQuery("#shortpixel-hs-button-blind").css('display', 'block');
                   window.shortpixelHSOpen = 1;
               }
           }
       </script>
       <script type="text/javascript" src="https://quriobot.com/qb/widget/KoPqxmzqzjbg5eNl/V895xbyndnmeqZYd" async defer></script>

    <script>
        <?php
        $screen = get_current_screen();
        if($screen) {
            switch($screen->id) {
                case 'media_page_wp-short-pixel-bulk':
                    echo("var shortpixel_suggestions =              [ '5a5de2782c7d3a19436843af', '5a5de6902c7d3a19436843e9', '5a5de5c42c7d3a19436843d0', '5a9945e42c7d3a75495145d0', '5a5de1c2042863193801047c', '5a5de66f2c7d3a19436843e0', '5a9946e62c7d3a75495145d8', '5a5de4f02c7d3a19436843c8', '5a5de65f042863193801049f', '5a5de2df0428631938010485' ]; ");
                    $suggestions = "shortpixel_suggestions";
                    break;
                case 'settings_page_wp-shortpixel-settings':
                    echo("var shortpixel_suggestions_settings =     [ '5a5de1de2c7d3a19436843a8', '5a6612032c7d3a39e6263a1d', '5a5de1c2042863193801047c', '5a5de2782c7d3a19436843af', '5a6610c62c7d3a39e6263a02', '5a9945e42c7d3a75495145d0', '5a5de66f2c7d3a19436843e0', '5a6597e80428632faf620487', '5a5de5c42c7d3a19436843d0', '5a5de5642c7d3a19436843cc' ]; ");
                    echo("var shortpixel_suggestions_adv_settings = [ '5a5de4f02c7d3a19436843c8', '5a8431f00428634376d01dc4', '5a5de58b0428631938010497', '5a5de65f042863193801049f', '5a9945e42c7d3a75495145d0', '5a9946e62c7d3a75495145d8', '5a5de57c0428631938010495', '5a5de2d22c7d3a19436843b1', '5a5de5c42c7d3a19436843d0', '5a5de5642c7d3a19436843cc' ]; ");
                    echo("var shortpixel_suggestions_cloudflare =   [ '5a5de1f62c7d3a19436843a9', '5a5de58b0428631938010497', '5a5de66f2c7d3a19436843e0', '5a5de5c42c7d3a19436843d0', '5a5de6902c7d3a19436843e9', '5a5de51a2c7d3a19436843c9', '5a9946e62c7d3a75495145d8', '5a5de46c2c7d3a19436843c1', '5a5de1de2c7d3a19436843a8', '5a6597e80428632faf620487' ]; ");
                    $suggestions = "shortpixel_suggestions_settings";
                    break;
                case 'media_page_wp-short-pixel-custom':
                    echo("var shortpixel_suggestions =              [ '5a9946e62c7d3a75495145d8', '5a5de1c2042863193801047c', '5a5de2782c7d3a19436843af', '5a5de6902c7d3a19436843e9', '5a5de4f02c7d3a19436843c8', '5a6610c62c7d3a39e6263a02', '5a9945e42c7d3a75495145d0', '5a5de46c2c7d3a19436843c1', '5a5de1de2c7d3a19436843a8', '5a5de25c2c7d3a19436843ad' ]; ");
                    $suggestions = "shortpixel_suggestions";
                    break;
            }
        }
        ?>
        !function(e,o,n){ window.HSCW=o,window.HS=n,n.beacon=n.beacon||{};var t=n.beacon;t.userConfig={
            color: "#1CBECB",
            icon: "question",
            instructions: "Send ShortPixel a message",
            topArticles: true,
            poweredBy: false,
            showContactFields: true,
            showName: false,
            showSubject: true,
            translation: {
                searchLabel: "What can ShortPixel help you with?",
                contactSuccessDescription: "Thanks for reaching out! Someone from our team will get back to you in 24h max."
            }

        },t.readyQueue=[],t.config=function(e){this.userConfig=e},t.ready=function(e){this.readyQueue.push(e)},o.config={docs:{enabled:!0,baseUrl:"//shortpixel.helpscoutdocs.com/"},contact:{enabled:!0,formId:"278a7825-fce0-11e7-b466-0ec85169275a"}};var r=e.getElementsByTagName("script")[0],c=e.createElement("script");
            c.type="text/javascript",c.async=!0,c.src="https://djtflbt20bdde.cloudfront.net/",r.parentNode.insertBefore(c,r);
        }(document,window.HSCW||{},window.HS||{});

        window.HS.beacon.ready(function(){
            HS.beacon.identify({
                email: "<?php $u = wp_get_current_user(); echo($u->user_email); ?>",
                apiKey: "<?php echo($apiKey);?>"
            });
            HS.beacon.suggest( <?php echo( $suggestions ) ?> );
        });
    </script>
    <?php
  }
}
