<?php
namespace ShortPixel;

$quotaData = $this->quotaData;

?>

<section id="tab-stats" <?php echo ($this->display_part == 'stats') ? ' class="sel-tab" ' :''; ?>>
    <h2><a class='tab-link' href='javascript:void(0);' data-id="tab-stats"><?php _e('Statistics','shortpixel-image-optimiser');?></a></h2>

    <div class="wp-shortpixel-tab-content" style="visibility: hidden">
        <a id="facts"></a>
        <h3><?php _e('Your ShortPixel Stats','shortpixel-image-optimiser');?></h3>
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">
                        <?php _e('Average compression of your files:','shortpixel-image-optimiser');?>
                    </th>
                    <td>
                        <strong><?php echo($view->averageCompression);?>%</strong>
                        <div class="sp-bulk-summary">
                            <input type="text" value="<?php echo("" . round($view->averageCompression))?>" id="sp-total-optimization-dial" class="dial">
                        </div>
                        <div id="sp-bulk-stats" style="display:none">
                            <?php
                                $under5PercentCount = $view->data->under5Percent; //amount of under 5% optimized imgs.
                                $totalOptimized = isset($quotaData['totalProcessedFiles']) ? $quotaData['totalProcessedFiles'] : 0;
                                $mainOptimized = isset($quotaData['mainProcessedFiles']) ? $quotaData['mainProcessedFiles'] : 0;
                            ?>
                                <div class="bulk-progress bulk-stats">
                                    <div class="label"><?php _e('Processed Images and PDFs:','shortpixel-image-optimiser');?></div><div class="stat-value"><?php echo(number_format($mainOptimized));?></div><br>
                                    <div class="label"><?php _e('Processed Thumbnails:','shortpixel-image-optimiser');?></div><div class="stat-value"><?php echo(number_format($totalOptimized - $mainOptimized));?></div><br>
                                    <div class="label totals"><?php _e('Total files processed:','shortpixel-image-optimiser');?></div><div class="stat-value"><?php echo(number_format($totalOptimized));?></div><br>
                                    <div class="label totals"><?php _e('Minus files with <5% optimization (free):','shortpixel-image-optimiser');?></div><div class="stat-value"><?php echo(number_format($under5PercentCount));?></div><br><br>
                                    <div class="label totals"><?php _e('Used quota:','shortpixel-image-optimiser');?></div><div class="stat-value"><?php echo(number_format($totalOptimized - $under5PercentCount));?></div><br>
                                    <br>
                                    <div class="label"><?php _e('Average optimization:','shortpixel-image-optimiser');?></div><div class="stat-value"><?php echo($view->averageCompression);?>%</div><br>
                                    <div class="label"><?php _e('Saved space:','shortpixel-image-optimiser');?></div><div class="stat-value"><?php echo($view->data->savedSpace);?></div>
                                </div>
                        </div>
                        <script>
                            jQuery(function() {
                                jQuery("#sp-total-optimization-dial").val("<?php echo("" . round($view->averageCompression))?>");
                                ShortPixel.percentDial("#sp-total-optimization-dial", 160);

                                jQuery(".sp-bulk-summary").spTooltip({
                                    tooltipSource: "inline",
                                    tooltipSourceID: "#sp-bulk-stats"
                                });
                            });
                            !function(d,s,id){//Just optimized my site with ShortPixel image optimization plugin
                                var js,
                                    fjs=d.getElementsByTagName(s)[0],
                                    p=/^http:/.test(d.location)?'http':'https';
                                if(!d.getElementById(id)){js=d.createElement(s);
                                    js.id=id;js.src=p+'://platform.twitter.com/widgets.js';
                                    fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');
                        </script>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php _e('Disk space saved by ShortPixel:','shortpixel-image-optimiser');?>
                    </th>
                    <td><?php echo(\WpShortPixel::formatBytes($view->data->savedSpace));?></td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php _e('Bandwidth* saved by ShortPixel:','shortpixel-image-optimiser');?>
                    </th>
                    <td><?php echo($view->savedBandwidth);?></td>
                </tr>
            </tbody>
        </table>

        <h3><?php _e('Your ShortPixel Credits','shortpixel-image-optimiser');?></h3>
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row" bgcolor="#ffffff">
                        <?php _e('Your monthly plan','shortpixel-image-optimiser');?>:
                    </th>
                    <td bgcolor="#ffffff">
                        <?php
                            $DateNow = time();
                            $DateSubscription = strtotime($quotaData['APILastRenewalDate']);
                            $DaysToReset = 30 - ((($DateNow  - $DateSubscription) / 84600) % 30);
                            printf(__('%s/month, renews in %s  days, on %s ( <a href="https://shortpixel.com/login/%s" target="_blank">Need More? See the options available</a> )','shortpixel-image-optimiser'),
                                $quotaData['APICallsQuota'], $DaysToReset,
                                date('M d, Y', strtotime(date('M d, Y') . ' + ' . $DaysToReset . ' days')), ( $this->hide_api_key) ? '' : $view->data->apiKey ); ?><br/>
                        <?php printf(__('<a href="https://shortpixel.com/login/%s/tell-a-friend" target="_blank">Join our friend referral system</a> to win more credits. For each user that joins, you receive +100 images credits/month.','shortpixel-image-optimiser'),
                                ( $this->hide_api_key ? '' : $view->data->apiKey));?>
                        <br><br>
                        <?php _e('Consumed: ','shortpixel-image-optimiser'); ?>
                        <strong><?php echo( number_format( $view->totalCallsMade['plan'] ) ); ?></strong>
                        <?php _e('; Remaining: ','shortpixel-image-optimiser'); ?>
                        <strong><?php echo( number_format( $quotaData['APICallsQuotaNumeric'] - $view->totalCallsMade['plan'] ) ); ?></strong>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php _e('Your One Time credits:','shortpixel-image-optimiser');?>
                    </th>
                    <td>
                        <?php _e('Total: ','shortpixel-image-optimiser'); ?>
                        <strong><?php echo(  number_format($quotaData['APICallsQuotaOneTimeNumeric'])); ?></strong>
                        <br><br>
                        <?php _e('Consumed: ','shortpixel-image-optimiser'); ?>
                        <strong><?php echo( number_format($view->totalCallsMade['oneTime']) ); ?></strong>
                        <?php _e('; Remaining: ','shortpixel-image-optimiser'); ?>
                        <strong><?php echo( number_format( $quotaData['APICallsQuotaOneTimeNumeric'] - $view->totalCallsMade['oneTime'] ) ); ?></strong>**
                    </td>
                </tr>
                <tr>
                <?php

                  if ($this->hide_api_key)
                    $link = 'https://shortpixel.com/login';
                  else {
                    $link = 'https://' . SHORTPIXEL_API . '/v2/report.php?key=' . $view->data->apiKey;
                  }
                ?>
                    <th><a href="<?php echo $link ?>" target="_blank">
                            <?php _e('See report (last 40 days)','shortpixel-image-optimiser');?>
                        </a></th>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php _e('Credits consumed on','shortpixel-image-optimiser');?>
                        <?php echo(parse_url(get_site_url(),PHP_URL_HOST));?>:
                    </th>
                    <td><strong><?php echo($view->data->fileCount);?></strong></td>
                </tr>
                <?php
                // @todo This is always true, but must it be?
                if(true || $view->data->backupImages) { ?>
                <tr>
                    <th scope="row">
                        <?php _e('Original images are stored in a backup folder. Your backup folder\'s size is now:','shortpixel-image-optimiser');?>
                    </th>
                    <td>
                        <form action="" method="POST">
                          <?php $backupFolderSize = null; ?>
                            <?php if ($backupFolderSize === null) { ?>
                                <span id='backup-folder-size'>Calculating...</span>
                            <?php } else { echo($backupFolderSize); }?>
                            <input type="submit"  style="margin-left: 15px; vertical-align: middle;" class="button button-secondary shortpixel-confirm"
                                   name="emptyBackup" value="<?php _e('Empty backups','shortpixel-image-optimiser');?>"
                                   data-confirm="<?php  _e('Are you sure you want to delete all the backup images? You won\'t be able to restore from backup or to reoptimize with different settings if you delete the backups.','shortpixel-image-optimiser'); ?>"/>
                        </form>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
        <div style="display:none">

        </div>
        <p style="padding-top: 0px; color: #818181;" ><?php _e('* Saved bandwidth is calculated at 10,000 impressions/image','shortpixel-image-optimiser');?></p>
        <p style="padding-top: 0px; color: #818181;" >
            <?php printf(__('** Increase your image quota by <a href="https://shortpixel.com/login/%s" target="_blank">upgrading your ShortPixel plan.</a>','shortpixel-image-optimiser'),
                $this->hide_api_key ? '' : $view->data->apiKey );?>
        </p>
    </div>
</section>

<?php

if( $view->resources !== null && $quotaData['APICallsQuotaOneTimeNumeric']<10000 && $quotaData['APICallsQuotaNumeric']<5000 ) {?>
<section id="tab-resources" <?php echo ($this->display_part == 'resources') ? ' class="sel-tab" ' :''; ?>>
    <h2><a class='tab-link' href='javascript:void(0);' data-id="tab-resources"><?php _e('WP Resources','shortpixel-image-optimiser');?></a></h2>
    <div class="wp-shortpixel-tab-content" style="visibility: hidden">
        <?php echo((isset($view->resources['body']) ? $view->resources['body'] : __("Please reload",'shortpixel-image-optimiser')));?>
    </div>
</section>
<?php } ?>
