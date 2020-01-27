<?php
if ( !function_exists( 'download_url' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/file.php' );
}

use ShortPixel\ShortpixelLogger\ShortPixelLogger as Log;

class ShortPixelAPI {

    const STATUS_SUCCESS = 1;
    const STATUS_UNCHANGED = 0;
    const STATUS_ERROR = -1;
    const STATUS_FAIL = -2;
    const STATUS_QUOTA_EXCEEDED = -3;
    const STATUS_SKIP = -4;
    const STATUS_NOT_FOUND = -5;
    const STATUS_NO_KEY = -6;
    const STATUS_RETRY = -7;
    const STATUS_SEARCHING = -8; // when the Queue is looping over images, but in batch none were found.
    const STATUS_QUEUE_FULL = -404;
    const STATUS_MAINTENANCE = -500;

    const ERR_FILE_NOT_FOUND = -2;
    const ERR_TIMEOUT = -3;
    const ERR_SAVE = -4;
    const ERR_SAVE_BKP = -5;
    const ERR_INCORRECT_FILE_SIZE = -6;
    const ERR_DOWNLOAD = -7;
    const ERR_PNG2JPG_MEMORY = -8;
    const ERR_POSTMETA_CORRUPT = -9;
    const ERR_UNKNOWN = -999;

    private $_settings;
    private $_apiEndPoint;
    private $_apiDumpEndPoint;


    public function __construct($settings) {
        $this->_settings = $settings;
        $this->_apiEndPoint = $this->_settings->httpProto . '://' . SHORTPIXEL_API . '/v2/reducer.php';
        $this->_apiDumpEndPoint = $this->_settings->httpProto . '://' . SHORTPIXEL_API . '/v2/cleanup.php';
    }

    protected function prepareRequest($requestParameters, $Blocking = false) {
        $arguments = array(
            'method' => 'POST',
            'timeout' => 15,
            'redirection' => 3,
            'sslverify' => false,
            'httpversion' => '1.0',
            'blocking' => $Blocking,
            'headers' => array(),
            'body' => json_encode($requestParameters),
            'cookies' => array()
        );
        //die(var_dump($requestParameters));
        //add this explicitely only for https, otherwise (for http) it slows down the request
        if($this->_settings->httpProto !== 'https') {
            unset($arguments['sslverify']);
        }

        return $arguments;
    }

    public function doDumpRequests($URLs) {
        if(!count($URLs)) {
            return false;
        }
       $ret = wp_remote_post($this->_apiDumpEndPoint, $this->prepareRequest(array(
                'plugin_version' => SHORTPIXEL_IMAGE_OPTIMISER_VERSION,
                'key' => $this->_settings->apiKey,
                'urllist' => $URLs
            ) ) );
        return $ret;
    }

    /**
     * sends a compression request to the API
     * @param array $URLs - list of urls to send to API
     * @param Boolean $Blocking - true means it will wait for an answer
     * @param ShortPixelMetaFacade $itemHandler - the Facade that manages different types of image metadatas: MediaLibrary (postmeta table), ShortPixel custom (shortpixel_meta table)
     * @param bool|int $compressionType 1 - lossy, 2 - glossy, 0 - lossless
     * @param bool $refresh
     * @return Array response from wp_remote_post or error
     * @throws Exception
     */
    public function doRequests($URLs, $Blocking, $itemHandler, $compressionType = false, $refresh = false) {

        if(!count($URLs)) {
            $meta = $itemHandler->getMeta();
            $thumbsMissing = $meta->getThumbsMissing();
            if(is_array($thumbsMissing) && count($thumbsMissing)) {
                $added = array();
                $files = " (";
                foreach ($meta->getThumbsMissing() as $miss) {
                    if(isset($added[$miss])) continue;
                    $files .= $miss . ", ";
                    $added[$miss] = true;
                }
                if(strrpos($files, ', ')) {
                    $files = substr_replace($files , ')', strrpos($files , ', '));
                }
                throw new Exception(__('Image files are missing.', 'shortpixel-image-optimiser') . (strlen($files) > 1 ? $files : ''));
            }
            else throw new Exception(__('Image files are missing.', 'shortpixel-image-optimiser'));
        }

        $apiKey = $this->_settings->apiKey;
        if(strlen($apiKey) < 20) { //found in the logs many cases when the API Key is '', probably deleted from the DB but the verifiedKey setting is not changed
            $this->_settings->verifiedKey = false;
            Log::addWarn('Invalid API Key');
            throw new Exception(__('Invalid API Key', 'shortpixel-image-optimiser'));
        }

      //  WpShortPixel::log("DO REQUESTS for META: " . json_encode($itemHandler->getRawMeta()) . " STACK: " . json_encode(debug_backtrace()));
        $URLs = apply_filters('shortpixel_image_urls', $URLs, $itemHandler->getId()) ;

        $requestParameters = array(
            'plugin_version' => SHORTPIXEL_IMAGE_OPTIMISER_VERSION,
            'key' => $apiKey,
            'lossy' => $compressionType === false ? $this->_settings->compressionType : $compressionType,
            'cmyk2rgb' => $this->_settings->CMYKtoRGBconversion,
            'keep_exif' => ($this->_settings->keepExif ? "1" : "0"),
            'convertto' => ($this->_settings->createWebp ? urlencode("+webp") : ""),
            'resize' => $this->_settings->resizeImages ? 1 + 2 * ($this->_settings->resizeType == 'inner' ? 1 : 0) : 0,
            'resize_width' => $this->_settings->resizeWidth,
            'resize_height' => $this->_settings->resizeHeight,
            'urllist' => $URLs
        );
        if(/*false &&*/ $this->_settings->downloadArchive == 7 && class_exists('PharData')) {
            $requestParameters['group'] = $itemHandler->getId();
        }
        if($refresh) {
            $requestParameters['refresh'] = 1;
        }

        //WpShortPixel::log("ShortPixel API Request Settings: " . json_encode($requestParameters));
        $response = wp_remote_post($this->_apiEndPoint, $this->prepareRequest($requestParameters, $Blocking) );
        Log::addDebug('ShortPixel API Request sent', $requestParameters);

        //WpShortPixel::log('RESPONSE: ' . json_encode($response));

        //only if $Blocking is true analyze the response
        if ( $Blocking )
        {
            //WpShortPixel::log("API response : " . json_encode($response));

            //die(var_dump(array('URL: ' => $this->_apiEndPoint, '<br><br>REQUEST:' => $requestParameters, '<br><br>RESPONSE: ' => $response, '<br><br>BODY: ' => isset($response['body']) ? $response['body'] : '' )));
            //there was an error, save this error inside file's SP optimization field
            if ( is_object($response) && get_class($response) == 'WP_Error' )
            {
                $errorMessage = $response->errors['http_request_failed'][0];
                $errorCode = 503;
            }
            elseif ( isset($response['response']['code']) && $response['response']['code'] <> 200 )
            {
                $errorMessage = $response['response']['code'] . " - " . $response['response']['message'];
                $errorCode = $response['response']['code'];
            }

            if ( isset($errorMessage) )
            {//set details inside file so user can know what happened
                $itemHandler->incrementRetries(1, $errorCode, $errorMessage);
                return array("response" => array("code" => $errorCode, "message" => $errorMessage ));
            }

            return $response;//this can be an error or a good response
        }

        return $response;
    }

    /**
     * parse the JSON response
     * @param $response
     * @return array parsed
     */
    public function parseResponse($response) {
        $data = $response['body'];
        $data = json_decode($data);
        return (array)$data;
    }

    /**
     * handles the processing of the image using the ShortPixel API
     * @param array $URLs - list of urls to send to API
     * @param array $PATHs - list of local paths for the images
     * @param ShortPixelMetaFacade $itemHandler - the Facade that manages different types of image metadatas: MediaLibrary (postmeta table), ShortPixel custom (shortpixel_meta table)
     * @return array status/message array
     */
    public function processImage($URLs, $PATHs, $itemHandler = null) {
        return $this->processImageRecursive($URLs, $PATHs, $itemHandler, 0);
    }

    /**
     * handles the processing of the image using the ShortPixel API - cals itself recursively until success
     * @param array $URLs - list of urls to send to API
     * @param array $PATHs - list of local paths for the images
     * @param ShortPixelMetaFacade $itemHandler - the Facade that manages different types of image metadatas: MediaLibrary (postmeta table), ShortPixel custom (shortpixel_meta table)
     * @param int $startTime - time of the first call
     * @return array status/message array
     */
    private function processImageRecursive($URLs, $PATHs, $itemHandler = null, $startTime = 0)
    {
        //WPShortPixel::log("processImageRecursive ID: " . $itemHandler->getId() . " PATHs: " . json_encode($PATHs));

        $PATHs = self::CheckAndFixImagePaths($PATHs);//check for images to make sure they exist on disk
        if ( $PATHs === false  || isset($PATHs['error'])) {
            $missingFiles = '';
            if(isset($PATHs['error'])) {
                foreach($PATHs['error'] as $errPath) {
                    $missingFiles .= (strlen($missingFiles) ? ', ':'') . basename(stripslashes($errPath));
                }
            }
            $msg = __('The file(s) do not exist on disk: ','shortpixel-image-optimiser') . $missingFiles;
            $itemHandler->setError(self::ERR_FILE_NOT_FOUND, $msg );
            return array("Status" => self::STATUS_SKIP, "Message" => $msg, "Silent" => $itemHandler->getType() == ShortPixelMetaFacade::CUSTOM_TYPE ? 1 : 0);
        }

        //tries multiple times (till timeout almost reached) to fetch images.
        if($startTime == 0) {
            $startTime = time();
        }
        $apiRetries = $this->_settings->apiRetries;

        if( time() - $startTime > SHORTPIXEL_MAX_EXECUTION_TIME2)
        {//keeps track of time
            if ( $apiRetries > SHORTPIXEL_MAX_API_RETRIES )//we tried to process this time too many times, giving up...
            {
                $itemHandler->incrementRetries(1, self::ERR_TIMEOUT, __('Timed out while processing.','shortpixel-image-optimiser'));
                $this->_settings->apiRetries = 0; //fai added to solve a bug?
                return array("Status" => self::STATUS_SKIP,
                             "Message" => ($itemHandler->getType() == ShortPixelMetaFacade::CUSTOM_TYPE ? __('Image ID','shortpixel-image-optimiser') : __('Media ID','shortpixel-image-optimiser'))
                                         . ": " . $itemHandler->getId() .' ' . __('Skip this image, try the next one.','shortpixel-image-optimiser'));
            }
            else
            {//we'll try again next time user visits a page on admin panel
                $apiRetries++;
                $this->_settings->apiRetries = $apiRetries;
                return array("Status" => self::STATUS_RETRY, "Message" => __('Timed out while processing.','shortpixel-image-optimiser') . ' (pass '.$apiRetries.')',
                             "Count" => $apiRetries);
            }
        }

        //#$compressionType = isset($meta['ShortPixel']['type']) ? ($meta['ShortPixel']['type'] == 'lossy' ? 1 : 0) : $this->_settings->compressionType;
        $meta = $itemHandler->getMeta();
        $compressionType = $meta->getCompressionType() !== null ? $meta->getCompressionType() : $this->_settings->compressionType;

        try {
          $response = $this->doRequests($URLs, true, $itemHandler, $compressionType);//send requests to API
        }
        catch(Exception $e) {
          Log::addError('Api DoRequest Thrown ' . $e->getMessage());
        }

        //die($response['body']);

        if($response['response']['code'] != 200) {//response <> 200 -> there was an error apparently?
            return array("Status" => self::STATUS_FAIL, "Message" => __('There was an error and your request was not processed.', 'shortpixel-image-optimiser')
                . (isset($response['response']['message']) ? ' (' . $response['response']['message'] . ')' : ''), "Code" => $response['response']['code']);
        }

        $APIresponse = $this->parseResponse($response);//get the actual response from API, its an array

        if ( isset($APIresponse[0]) ) //API returned image details
        {
            foreach ( $APIresponse as $imageObject ) {//this part makes sure that all the sizes were processed and ready to be downloaded
                if ( isset($imageObject->Status) && ( $imageObject->Status->Code == 0 || $imageObject->Status->Code == 1 ) ) {
                    sleep(1);
                    return $this->processImageRecursive($URLs, $PATHs, $itemHandler, $startTime);
                }
            }

            $firstImage = $APIresponse[0];//extract as object first image
            switch($firstImage->Status->Code)
            {
            case 2:
                //handle image has been processed
                if(!isset($firstImage->Status->QuotaExceeded)) {
                    $this->_settings->quotaExceeded = 0;//reset the quota exceeded flag
                }
                return $this->handleSuccess($APIresponse, $PATHs, $itemHandler, $compressionType);
            default:
                //handle error
                $incR = 1;
                if ( !file_exists($PATHs[0]) ) {
                    $err = array("Status" => self::STATUS_NOT_FOUND, "Message" => "File not found on disk. "
                                 . ($itemHandler->getType() == ShortPixelMetaFacade::CUSTOM_TYPE ? "Image" : "Media")
                                 . " ID: " . $itemHandler->getId(), "Code" => self::ERR_FILE_NOT_FOUND);
                    $incR = 3;
                }
                elseif ( isset($APIresponse[0]->Status->Message) ) {
                    //return array("Status" => self::STATUS_FAIL, "Message" => "There was an error and your request was not processed (" . $APIresponse[0]->Status->Message . "). REQ: " . json_encode($URLs));
                    $err = array("Status" => self::STATUS_FAIL, "Code" => (isset($APIresponse[0]->Status->Code) ? $APIresponse[0]->Status->Code : self::ERR_UNKNOWN),
                                 "Message" => __('There was an error and your request was not processed.','shortpixel-image-optimiser')
                                              . " (" . wp_basename($APIresponse[0]->OriginalURL) . ": " . $APIresponse[0]->Status->Message . ")");
                } else {
                    $err = array("Status" => self::STATUS_FAIL, "Message" => __('There was an error and your request was not processed.','shortpixel-image-optimiser'),
                                 "Code" => (isset($APIresponse[0]->Status->Code) ? $APIresponse[0]->Status->Code : self::ERR_UNKNOWN));
                }

                $itemHandler->incrementRetries($incR, $err["Code"], $err["Message"]);
                $meta = $itemHandler->getMeta();
                if($meta->getRetries() >= SHORTPIXEL_MAX_FAIL_RETRIES) {
                    $meta->setStatus($APIresponse[0]->Status->Code);
                    $meta->setMessage($APIresponse[0]->Status->Message);
                    $itemHandler->updateMeta($meta);
                }
                return $err;
            }
        }

        if(!isset($APIresponse['Status'])) {
            WpShortPixel::log("API Response Status unfound : " . json_encode($APIresponse));
            return array("Status" => self::STATUS_FAIL, "Message" => __('Unrecognized API response. Please contact support.','shortpixel-image-optimiser'),
                         "Code" => self::ERR_UNKNOWN, "Debug" => ' (SERVER RESPONSE: ' . json_encode($response) . ')');
        } else {
            switch($APIresponse['Status']->Code)
            {
                case -403:
                    @delete_option('bulkProcessingStatus');
                    $this->_settings->quotaExceeded = 1;
                    return array("Status" => self::STATUS_QUOTA_EXCEEDED, "Message" => __('Quota exceeded.','shortpixel-image-optimiser'));
                    break;
                case -404:
                    return array("Status" => self::STATUS_QUEUE_FULL, "Message" => $APIresponse['Status']->Message);
                case -500:
                    return array("Status" => self::STATUS_MAINTENANCE, "Message" => $APIresponse['Status']->Message);
            }

            //sometimes the response array can be different
            if (is_numeric($APIresponse['Status']->Code)) {
                return array("Status" => self::STATUS_FAIL, "Message" => $APIresponse['Status']->Message);
            } else {
                return array("Status" => self::STATUS_FAIL, "Message" => $APIresponse[0]->Status->Message);
            }
        }
    }

    /**
     * sets the preferred protocol of URL using the globally set preferred protocol.
     * If  global protocol not set, sets it by testing the download of a http test image from ShortPixel site.
     * If http works then it's http, otherwise sets https
     * @param string $url
     * @param bool $reset - forces recheck even if preferred protocol is already set
     * @return string url with the preferred protocol
     */
    public function setPreferredProtocol($url, $reset = false) {
        //switch protocol based on the formerly detected working protocol
        if($this->_settings->downloadProto == '' || $reset) {
            //make a test to see if the http is working
            $testURL = 'http://' . SHORTPIXEL_API . '/img/connection-test-image.png';
            $result = download_url($testURL, 10);
            $this->_settings->downloadProto = is_wp_error( $result ) ? 'https' : 'http';
        }
        return $this->_settings->downloadProto == 'http' ?
                str_replace('https://', 'http://', $url) :
                str_replace('http://', 'https://', $url);


    }

    function fromArchive($path, $optimizedUrl, $optimizedSize, $originalSize, $webpUrl) {
        $webpTempFile = "NA";
        if($webpUrl !== "NA") {
            $webpTempFile = $path . '/' . wp_basename($webpUrl);
            $webpTempFile = file_exists($webpTempFile) ? $webpTempFile : 'NA';
        }

        //if there is no improvement in size then we do not download this file
        if ( $originalSize == $optimizedSize )
            return array("Status" => self::STATUS_UNCHANGED, "Message" => "File wasn't optimized so we do not download it.", "WebP" => $webpTempFile);

        $correctFileSize = $optimizedSize;
        $tempFile = $path . '/' . wp_basename($optimizedUrl);

        if(file_exists($tempFile)) {
            //on success we return this
           if( filesize($tempFile) != $correctFileSize) {
               $size = filesize($tempFile);
               @unlink($tempFile);
               @unlink($webpTempFile);
               $returnMessage = array(
                   "Status" => self::STATUS_ERROR,
                   "Code" => self::ERR_INCORRECT_FILE_SIZE,
                   "Message" => sprintf(__('Error in archive - incorrect file size (downloaded: %s, correct: %s )','shortpixel-image-optimiser'),$size, $correctFileSize));
            } else {
               $returnMessage = array("Status" => self::STATUS_SUCCESS, "Message" => $tempFile, "WebP" => $webpTempFile);
           }
        } else {
            $returnMessage = array("Status" => self::STATUS_ERROR,
                "Code" => self::ERR_FILE_NOT_FOUND,
                "Message" => __('Unable to locate downloaded file','shortpixel-image-optimiser') . " " . $tempFile);
        }

        return $returnMessage;
    }

    /**
     * handles the download of an optimized image from ShortPixel API
     * @param string $optimizedUrl
     * @param int $optimizedSize
     * @param int $originalSize
     * @param string $webpUrl
     * @return array status /message array
     */
    private function handleDownload($optimizedUrl, $optimizedSize, $originalSize, $webpUrl){

        $downloadTimeout = max(ini_get('max_execution_time') - 10, 15);

        $webpTempFile = "NA";
        if($webpUrl !== "NA") {
            $webpURL = $this->setPreferredProtocol(urldecode($webpUrl));
            $webpTempFile = download_url($webpURL, $downloadTimeout);
            $webpTempFile = is_wp_error( $webpTempFile ) ? "NA" : $webpTempFile;
        }

        //if there is no improvement in size then we do not download this file
        if ( $originalSize == $optimizedSize )
            return array("Status" => self::STATUS_UNCHANGED, "Message" => "File wasn't optimized so we do not download it.", "WebP" => $webpTempFile);

        $correctFileSize = $optimizedSize;
        $fileURL = $this->setPreferredProtocol(urldecode($optimizedUrl));

        $tempFile = download_url($fileURL, $downloadTimeout);
        Log::addInfo('Downloading file: '.json_encode($tempFile));
        if(is_wp_error( $tempFile ))
        { //try to switch the default protocol
            $fileURL = $this->setPreferredProtocol(urldecode($optimizedUrl), true); //force recheck of the protocol
            $tempFile = download_url($fileURL, $downloadTimeout);
        }

        //on success we return this
        $returnMessage = array("Status" => self::STATUS_SUCCESS, "Message" => $tempFile, "WebP" => $webpTempFile);

        if ( is_wp_error( $tempFile ) ) {
            @unlink($tempFile);
            @unlink($webpTempFile);
            $returnMessage = array(
                "Status" => self::STATUS_ERROR,
                "Code" => self::ERR_DOWNLOAD,
                "Message" => __('Error downloading file','shortpixel-image-optimiser') . " ({$optimizedUrl}) " . $tempFile->get_error_message());
        }
        //check response so that download is OK
        elseif (!file_exists($tempFile)) {
            $returnMessage = array("Status" => self::STATUS_ERROR,
                "Code" => self::ERR_FILE_NOT_FOUND,
                "Message" => __('Unable to locate downloaded file','shortpixel-image-optimiser') . " " . $tempFile);
        }
        elseif( filesize($tempFile) != $correctFileSize) {
            $size = filesize($tempFile);
            @unlink($tempFile);
            @unlink($webpTempFile);
            $returnMessage = array(
                "Status" => self::STATUS_ERROR,
                "Code" => self::ERR_INCORRECT_FILE_SIZE,
                "Message" => sprintf(__('Error downloading file - incorrect file size (downloaded: %s, correct: %s )','shortpixel-image-optimiser'),$size, $correctFileSize));
        }
        return $returnMessage;
    }

    /** Tries to create backup
    *
    * @param $mainPath The path of the main image?
    * @param $PATHs MUST be included. If just one image is for backup, add array($mainPath)
    * @return Array Array with Status and optional Message  */
    public static function backupImage($mainPath, $PATHs) {
        /**
         * Passing a truthy value to the filter will effectively short-circuit this function.
         * So third party plugins can handle Backup by there own.
         */
        if(apply_filters('shortpixel_skip_backup', false, $mainPath, $PATHs)){
            return array("Status" => self::STATUS_SUCCESS);
        }

//Log::addDebug('Backing The Up', array($mainPath, $PATHs));

        //$fullSubDir = str_replace(wp_normalize_path(get_home_path()), "", wp_normalize_path(dirname($itemHandler->getMeta()->getPath()))) . '/';
        //$SubDir = ShortPixelMetaFacade::returnSubDir($itemHandler->getMeta()->getPath(), $itemHandler->getType());
        $fullSubDir = ShortPixelMetaFacade::returnSubDir($mainPath);
        $source = $PATHs; //array with final paths for these files
        $fs = \wpSPIO()->filesystem();

        if( !file_exists(SHORTPIXEL_BACKUP_FOLDER) && ! ShortPixelFolder::createBackUpFolder() ) {//creates backup folder if it doesn't exist
            Log::addWarn('Backup folder does not exist and it cannot be created');
            return array("Status" => self::STATUS_FAIL, "Message" => __('Backup folder does not exist and it cannot be created','shortpixel-image-optimiser'));
        }
        //create subdir in backup folder if needed
        ShortPixelFolder::createBackUpFolder(SHORTPIXEL_BACKUP_FOLDER . '/' . $fullSubDir);

        foreach ( $source as $fileID => $filePATH )//create destination files array
        {
            $destination[$fileID] = SHORTPIXEL_BACKUP_FOLDER . '/' . $fullSubDir . self::MB_basename($source[$fileID]);
        }

        //now that we have original files and where we should back them up we attempt to do just that
        if(is_writable(SHORTPIXEL_BACKUP_FOLDER))
        {
            foreach ( $destination as $fileID => $filePATH )
            {
                $destination_file = $fs->getFile($filePATH);

                if ( ! $destination_file->exists() )
                {
                    $source_file = $fs->getFile($source[$fileID]);
                    $result = $source_file->copy($destination_file);
                    if (  ! $result )
                    {//file couldn't be saved in backup folder
                        $msg = sprintf(__('Cannot save file <i>%s</i> in backup directory','shortpixel-image-optimiser'),self::MB_basename($source[$fileID]));
                        return array("Status" => self::STATUS_FAIL, "Message" => $msg);
                    }

                }
            }
            return array("Status" => self::STATUS_SUCCESS);
        }
        else {//cannot write to the backup dir, return with an error
            $msg = __('Cannot save file in backup directory','shortpixel-image-optimiser');
            Log::addWarn('Backup directory not writable');
            return array("Status" => self::STATUS_FAIL, "Message" => $msg);
        }
    }

    private function createArchiveTempFolder($archiveBasename) {
        $archiveTempDir = get_temp_dir() . '/' . $archiveBasename;
        $fs = \wpSPIO()->filesystem();
        $tempDir = $fs->getDirectory($archiveTempDir);

        if( $tempDir->exists() && (time() - filemtime($archiveTempDir) < max(30, SHORTPIXEL_MAX_EXECUTION_TIME) + 10)) {
            Log::addWarn("CONFLICT. Folder already exists and is modified in the last minute. Current IP:" . $_SERVER['REMOTE_ADDR']);
            return array("Status" => self::STATUS_RETRY, "Code" => 1, "Message" => "Pending");
        }

        // try to create temporary folder
        $tempDir->check();

        if( ! $tempDir->exists() ) {
            return array("Status" => self::STATUS_ERROR, "Code" => self::ERR_SAVE, "Message" => "Could not create temporary folder.");
        }
        return array("Status" => self::STATUS_SUCCESS, "Dir" => $tempDir);
    }

    private function downloadArchive($archive, $compressionType, $first = true) {
        if($archive->ArchiveStatus->Code == 1 || $archive->ArchiveStatus->Code == 0) {
            return array("Status" => self::STATUS_RETRY, "Code" => 1, "Message" => "Pending");
        } elseif($archive->ArchiveStatus->Code == 2) {

            $suffix = ($compressionType == 0 ? "-lossless" : "");
            $archiveURL = "Archive" . ($compressionType == 0 ? "Lossless" : "") . "URL";
            $archiveSize = "Archive" . ($compressionType == 0 ? "Lossless" : "") . "Size";

            $archiveTemp = $this->createArchiveTempFolder(wp_basename($archive->$archiveURL, '.tar'));
            if($archiveTemp["Status"] == self::STATUS_SUCCESS) { $archiveTempDir = $archiveTemp["Dir"]; }
            else { return $archiveTemp; }

            $downloadResult = $this->handleDownload($archive->$archiveURL, $archive->$archiveSize, 0, 'NA');

            if ( $downloadResult['Status'] == self::STATUS_SUCCESS ) {
                $archiveFile = $downloadResult['Message'];
                if(filesize($archiveFile) !== $archive->$archiveSize) {
                    @unlink($archiveFile);
                    ShortpixelFolder::deleteFolder($archiveTempDir);
                    return array("Status" => self::STATUS_RETRY, "Code" => 1, "Message" => "Pending");
                }
                $pharData = new PharData($archiveFile);
                try {
                    if (SHORTPIXEL_DEBUG === true) {
                        $info = "Current IP:" . $_SERVER['REMOTE_ADDR'] . "ARCHIVE CONTENTS: COUNT " . $pharData->count() . ", ";
                        foreach($pharData as $file) {
                            $info .= $file . ", ";
                        }
                        WPShortPixel::log($info);
                    }
                    $pharData->extractTo($archiveTempDir, null, true);
                    WPShortPixel::log("ARCHIVE EXTRACTED " . json_encode(scandir($archiveTempDir)));
                    @unlink($archiveFile);
                } catch (Exception $ex) {
                    @unlink($archiveFile);
                    ShortpixelFolder::deleteFolder($archiveTempDir);
                    return array("Status" => self::STATUS_ERROR, "Code" => $ex->getCode(), "Message" => $ex->getMessage());
                }
                return array("Status" => self::STATUS_SUCCESS, "Code" => 2, "Message" => "Success", "Path" => $archiveTempDir);

            } else {
                WPShortPixel::log("ARCHIVE ERROR (" . $archive->$archiveURL . "): " . json_encode($downloadResult));
                if($first && $downloadResult['Code'] == self::ERR_INCORRECT_FILE_SIZE) {
                    WPShortPixel::log("RETRYING AFTER ARCHIVE ERROR");
                    return $this->downloadArchive($archive, $compressionType, false); // try again, maybe the archive was flushing...
                }
                @rmdir($archiveTempDir); //in the case it was just created and it's empty...
                return array("Status" => $downloadResult['Status'], "Code" => $downloadResult['Code'], "Message" => $downloadResult['Message']);
            }
        }
        return false;
    }

    /**
     * handles a successful optimization, setting metadata and handling download for each file in the set
     * @param array $APIresponse - the response from the API - contains the optimized images URLs to download
     * @param array $PATHs - list of local paths for the files
     * @param ShortPixelMetaFacade $itemHandler - the Facade that manages different types of image metadatas: MediaLibrary (postmeta table), ShortPixel custom (shortpixel_meta table)
     * @param int $compressionType - 1 - lossy, 2 - glossy, 0 - lossless
     * @return array status/message
     */
    private function handleSuccess($APIresponse, $PATHs, $itemHandler, $compressionType) {
        Log::addDebug('Shortpixel API : Handling Success!');

        $counter = $savedSpace =  $originalSpace =  $optimizedSpace /* = $averageCompression */ = 0;
        $NoBackup = true;

        if($compressionType) {
            $fileType = "LossyURL";
            $fileSize = "LossySize";
        } else {
            $fileType = "LosslessURL";
            $fileSize = "LoselessSize";
        }
        $webpType = "WebP" . $fileType;

        $archive = /*false &&*/
            ($this->_settings->downloadArchive == 7 && class_exists('PharData') && isset($APIresponse[count($APIresponse) - 1]->ArchiveStatus))
            ? $this->downloadArchive($APIresponse[count($APIresponse) - 1], $compressionType) : false;
        if($archive !== false && $archive['Status'] !== self::STATUS_SUCCESS) {
            return $archive;
        }

        //download each file from array and process it
        foreach ( $APIresponse as $fileData )
        {
            if(!isset($fileData->Status)) continue; //if optimized images archive is activated, last entry of APIResponse if the Archive data.

            if ( $fileData->Status->Code == 2 ) //file was processed OK
            {
                if ( $counter == 0 ) { //save percent improvement for main file
                    $percentImprovement = $fileData->PercentImprovement;
                } else { //count thumbnails only
                    $this->_settings->thumbsCount = $this->_settings->thumbsCount + 1;
                }
                //TODO la sfarsit sa faca fallback la handleDownload
                if($archive) {
                    $downloadResult = $this->fromArchive($archive['Path'], $fileData->$fileType, $fileData->$fileSize, $fileData->OriginalSize, isset($fileData->$webpType) ? $fileData->$webpType : 'NA');
                } else {
                    $downloadResult = $this->handleDownload($fileData->$fileType, $fileData->$fileSize, $fileData->OriginalSize, isset($fileData->$webpType) ? $fileData->$webpType : 'NA');
                }

                $tempFiles[$counter] = $downloadResult;
                if ( $downloadResult['Status'] == self::STATUS_SUCCESS ) {
                //nothing to do
                }
                //when the status is STATUS_UNCHANGED we just skip the array line for that one
                elseif( $downloadResult['Status'] == self::STATUS_UNCHANGED ) {
                    //this image is unchanged so won't be copied below, only the optimization stats need to be computed
                    $originalSpace += $fileData->OriginalSize;
                    $optimizedSpace += $fileData->$fileSize;
                }
                else {
                    self::cleanupTemporaryFiles($archive, $tempFiles);
                    return array("Status" => $downloadResult['Status'], "Code" => $downloadResult['Code'], "Message" => $downloadResult['Message']);
                }

            }
            else { //there was an error while trying to download a file
                $tempFiles[$counter] = "";
            }
            $counter++;
        }

        //figure out in what SubDir files should land
        $mainPath = $itemHandler->getMeta()->getPath();

        //if backup is enabled - we try to save the images
        if( $this->_settings->backupImages )
        {
            $backupStatus = self::backupImage($mainPath, $PATHs);
            Log::addDebug('Status', $backupStatus);
            if($backupStatus == self::STATUS_FAIL) {
                $itemHandler->incrementRetries(1, self::ERR_SAVE_BKP, $backupStatus["Message"]);
                self::cleanupTemporaryFiles($archive, empty($tempFiles) ? array() : $tempFiles);
                Log::addError('Failed to create image backup!', array('status' => $backupStatus));
                return array("Status" => self::STATUS_FAIL, "Code" =>"backup-fail", "Message" => "Failed to back the image up.");
            }
            $NoBackup = false;
        }//end backup section

        $writeFailed = 0;
        $width = $height = null;
        $resize = $this->_settings->resizeImages;
        $retinas = 0;
        $thumbsOpt = 0;
        $thumbsOptList = array();

        $fs = new \ShortPixel\FileSystemController();

        //Log::addDebug($tempFiles);
        // Check and Run all tempfiles. Move it to appropiate places.
        if ( !empty($tempFiles) )
        {
            //overwrite the original files with the optimized ones
            foreach ( $tempFiles as $tempFileID => $tempFile )
            {
                if(!is_array($tempFile)) continue;

                $targetFile = $fs->getFile($PATHs[$tempFileID]);
                $isRetina = ShortPixelMetaFacade::isRetina($targetFile->getFullPath());

                if(   ($tempFile['Status'] == self::STATUS_UNCHANGED || $tempFile['Status'] == self::STATUS_SUCCESS) && !$isRetina
                   && $targetFile->getFullPath() !== $mainPath) {
                    $thumbsOpt++;
                    $thumbsOptList[] = self::MB_basename($targetFile->getFullPath());
                }

                if($tempFile['Status'] == self::STATUS_SUCCESS) { //if it's unchanged it will still be in the array but only for WebP (handled below)
                    $tempFilePATH = $fs->getFile($tempFile["Message"]);

                    //@todo Move file logic to use FS controller / fileModel.
                    if ( $tempFilePATH->exists() && (! $targetFile->exists() || $targetFile->is_writable()) ) {
                      //  copy($tempFilePATH, $targetFile);
                        $tempFilePATH->move($targetFile);

                        if(ShortPixelMetaFacade::isRetina($targetFile->getFullPath())) {
                            $retinas ++;
                        }
                        if($resize && $itemHandler->getMeta()->getPath() == $targetFile->getFullPath() ) { //this is the main image
                            $size = getimagesize($PATHs[$tempFileID]);
                            $width = $size[0];
                            $height = $size[1];
                        }
                        //Calculate the saved space
                        $fileData = $APIresponse[$tempFileID];
                        $savedSpace += $fileData->OriginalSize - $fileData->$fileSize;
                        $originalSpace += $fileData->OriginalSize;
                        $optimizedSpace += $fileData->$fileSize;
                        //$averageCompression += $fileData->PercentImprovement;
                        Log::addInfo("HANDLE SUCCESS: Image " . $PATHs[$tempFileID] . " original size: ".$fileData->OriginalSize . " optimized: " . $fileData->$fileSize);

                        //add the number of files with < 5% optimization
                        if ( ( ( 1 - $APIresponse[$tempFileID]->$fileSize/$APIresponse[$tempFileID]->OriginalSize ) * 100 ) < 5 ) {
                            $this->_settings->under5Percent++;
                        }
                    }
                    else {
                        if($archive &&  SHORTPIXEL_DEBUG === true) {
                            if(! $tempFilePATH->exists()) {
                                Log::addWarn("MISSING FROM ARCHIVE. tempFilePath: " . $tempFilePATH->getFullPath() . " with ID: $tempFileID");
                            } elseif(! $targetFile->is_writable() ){
                                Log::addWarn("TARGET NOT WRITABLE: " . $targetFile->getFullPath() );
                            }
                        }
                        $writeFailed++;
                    }
                    //@unlink($tempFilePATH); // @todo Unlink is risky due to lack of checks.
                  //  $tempFilePath->delete();
                }

                $tempWebpFilePATH = $fs->getFile($tempFile["WebP"]);
                if( $tempWebpFilePATH->exists() ) {
                    $targetWebPFileCompat = $fs->getFile($targetFile->getFileDir() . $targetFile->getFileName() . '.webp');
                    /*$targetWebPFileCompat = dirname($targetFile) . '/'. self::MB_basename($targetFile, '.' . pathinfo($targetFile, PATHINFO_EXTENSION)) . ".webp"; */

                    $targetWebPFile = $fs->getFile($targetFile->getFileDir() . $targetFile->getFileBase() . '.webp');
                    //if the Targetfile already exists, it means that there is another file with the same basename but different extension which has its .webP counterpart save it with double extension
                    if(SHORTPIXEL_USE_DOUBLE_WEBP_EXTENSION || $targetWebPFile->exists()) {
                        $tempWebpFilePATH->move($targetWebPFileCompat);
                    } else {
                        $tempWebpFilePATH->move($targetWebPFile);
                    }
                }
            } // / For each tempFile
            self::cleanupTemporaryFiles($archive, $tempFiles);

            if ( $writeFailed > 0 )//there was an error
            {

              /*  Log::addDebug("ARCHIVE HAS MISSING FILES. EXPECTED: " . json_encode($PATHs)
                                . " AND: " . json_encode($APIresponse)
                                . " GOT ARCHIVE: " . $APIresponse[count($APIresponse) - 1]->ArchiveURL . " LOSSLESS: " . $APIresponse[count($APIresponse) - 1]->ArchiveLosslessURL
                                . " CONTAINING: " . json_encode(scandir($archive['Path']))); */
                Log::addDebug('Archive files missing (expected paths, response)', array($PATHs, $APIresponse));

                $msg = sprintf(__('Optimized version of %s file(s) couldn\'t be updated.','shortpixel-image-optimiser'),$writeFailed);
                $itemHandler->incrementRetries(1, self::ERR_SAVE, $msg);
                $this->_settings->bulkProcessingStatus = "error";
                return array("Status" => self::STATUS_FAIL, "Code" =>"write-fail", "Message" => $msg);
            }
        } elseif( 0 + $fileData->PercentImprovement < 5) {
            $this->_settings->under5Percent++;
        }
        //old average counting
        $this->_settings->savedSpace += $savedSpace;
        //$averageCompression = $this->_settings->averageCompression * $this->_settings->fileCount /  ($this->_settings->fileCount + count($APIresponse));
        //$this->_settings->averageCompression = $averageCompression;
        $this->_settings->fileCount += count($APIresponse);
        //new average counting
        $this->_settings->totalOriginal += $originalSpace;
        $this->_settings->totalOptimized += $optimizedSpace;

        //update metadata for this file
        $meta = $itemHandler->getMeta();
//        die(var_dump($percentImprovement));
        if($meta->getThumbsTodo()) {
            $percentImprovement = $meta->getImprovementPercent();
        }
        $png2jpg = $meta->getPng2Jpg();
        $png2jpg = is_array($png2jpg) ? $png2jpg['optimizationPercent'] : 0;
        $meta->setMessage($originalSpace
                ? number_format(100.0 * (1.0 - $optimizedSpace / $originalSpace), 2)
                : "Couldn't compute thumbs optimization percent. Main image: " . $percentImprovement);
        WPShortPixel::log("HANDLE SUCCESS: Image optimization: ".$meta->getMessage());
        $meta->setCompressionType($compressionType);
        $meta->setCompressedSize(@filesize($meta->getPath()));
        $meta->setKeepExif($this->_settings->keepExif);
        $meta->setTsOptimized(date("Y-m-d H:i:s"));
        $meta->setThumbsOptList(is_array($meta->getThumbsOptList()) ? array_unique(array_merge($meta->getThumbsOptList(), $thumbsOptList)) : $thumbsOptList);
        $meta->setThumbsOpt(($meta->getThumbsTodo() ||  $this->_settings->processThumbnails) ? count($meta->getThumbsOptList()) : 0);
        $meta->setRetinasOpt($retinas);
        if(null !== $this->_settings->excludeSizes) {
            $meta->setExcludeSizes($this->_settings->excludeSizes);
        }
        $meta->setThumbsTodo(false);
        //* Not yet as it doesn't seem to work... */$meta->addThumbs($webpSizes);
        if($width && $height) {
            $meta->setActualWidth($width);
            $meta->setActualHeight($height);
        }
        $meta->setRetries($meta->getRetries() + 1);
        $meta->setBackup(!$NoBackup);
        $meta->setStatus(2);

        $itemHandler->updateMeta($meta);
        $itemHandler->optimizationSucceeded();
        Log::addDebug("HANDLE SUCCESS: Metadata saved.");

        if(!$originalSpace) { //das kann nicht sein, alles klar?!
            throw new Exception("OriginalSpace = 0. APIResponse" . json_encode($APIresponse));
        }

        //we reset the retry counter in case of success
        $this->_settings->apiRetries = 0;

        return array("Status" => self::STATUS_SUCCESS, "Message" => 'Success: No pixels remained unsqueezed :-)',
            "PercentImprovement" => $originalSpace
            ? number_format(100.0 * (1.0 - (1.0 - $png2jpg / 100.0) * $optimizedSpace / $originalSpace), 2)
            : "Couldn't compute thumbs optimization percent. Main image: " . $percentImprovement);
    }//end handleSuccess

    /**
     * @param $archive
     * @param $tempFiles
     * @todo Move to FS-controller
     */
    protected static function cleanupTemporaryFiles($archive, $tempFiles)
    {
        if ($archive) {
            ShortpixelFolder::deleteFolder($archive['Path']);
        } else {
            if (!empty($tempFiles) && is_array($tempFiles)) {

                foreach ($tempFiles as $tmpFile) {
                    $filepath = isset($tmpFile['Message']) ? $tmpFile['Message'] : false;
                    if ($filepath)
                    {
                      $file = \wpSPIO()->filesystem()->getFile($filepath);
                      if ($file->exists())
                        $file->delete();
                    //@unlink($tmpFile["Message"]);
                    }
                }
            }
        }
    }

    /**
     * a basename alternative that deals OK with multibyte charsets (e.g. Arabic)
     * @param string $Path
     * @return string
     */
    static public function MB_basename($Path, $suffix = false){
        $Separator = " qq ";
        $qqPath = preg_replace("/[^ ]/u", $Separator."\$0".$Separator, $Path);
        if(!$qqPath) { //this is not an UTF8 string!! Don't rely on basename either, since if filename starts with a non-ASCII character it strips it off

            // This line is separated because of 'passed by reference' errors otherwise.
            $pathAr = explode(DIRECTORY_SEPARATOR, $Path);
            $fileName = end($pathAr);
            $pos = strpos($fileName, $suffix);
            if($pos !== false) {
                return substr($fileName, 0, $pos);
            }
            return $fileName;
        }
        $suffix = preg_replace("/[^ ]/u", $Separator."\$0".$Separator, $suffix);
        $Base = basename($qqPath, $suffix);
        $Base = str_replace($Separator, "", $Base);
        return $Base;
    }

    /**
     * sometimes, the paths to the files as defined in metadata are wrong, we try to automatically correct them
     * @param array $PATHs
     * @return boolean|string
     */
    static public function CheckAndFixImagePaths($PATHs){

        $ErrorCount = 0;
        $Tmp = explode("/", SHORTPIXEL_UPLOADS_BASE);
        $TmpCount = count($Tmp);
        $StichString = $Tmp[$TmpCount-2] . "/" . $Tmp[$TmpCount-1];
        //files exist on disk?
        $missingFiles = array();
        foreach ( $PATHs as $Id => $File )
        {
            //we try again with a different path
            if ( !apply_filters( 'shortpixel_image_exists', file_exists($File), $File, null ) ){
                //$NewFile = $uploadDir['basedir'] . "/" . substr($File,strpos($File, $StichString));//+strlen($StichString));
                $NewFile = SHORTPIXEL_UPLOADS_BASE . substr($File,strpos($File, $StichString)+strlen($StichString));
                if (file_exists($NewFile)) {
                    $PATHs[$Id] = $NewFile;
                } else {
	            $NewFile = SHORTPIXEL_UPLOADS_BASE . "/" . $File;
                    if (file_exists($NewFile)) {
                        $PATHs[$Id] = $NewFile;
                    } else {
                        $missingFiles[] = $File;
                        $ErrorCount++;
                    }
                }
            }
        }

        if ( $ErrorCount > 0 ) {
            return array("error" => $missingFiles);//false;
        } else {
            return $PATHs;
        }
    }

    static public function getCompressionTypeName($compressionType) {
        if(is_array($compressionType)) {
            return array_map(array('ShortPixelAPI', 'getCompressionTypeName'), $compressionType);
        }
        return 0 + $compressionType == 2 ? 'glossy' : (0 + $compressionType == 1 ? 'lossy' : 'lossless');
    }

    static public function getCompressionTypeCode($compressionName) {
        return $compressionName == 'glossy' ? 2 : ($compressionName == 'lossy' ? 1 : 0);
    }
}
