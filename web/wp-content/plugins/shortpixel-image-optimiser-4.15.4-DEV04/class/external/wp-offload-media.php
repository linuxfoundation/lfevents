<?php
namespace ShortPixel;
use ShortPixel\ShortPixelLogger\ShortPixelLogger as Log;
use ShortPixel\FileSystemController as FileSystem;
use ShortPixel\Notices\NoticeController as Notice;

class wpOffload
{
    protected $as3cf;
    protected $active = false;
    private $itemClassName;

    protected $settings;

    protected $is_cname = false;
    protected $cname;

    public function __construct()
    {
       // This must be called before WordPress' init.
       add_action('as3cf_init', array($this, 'init'));
    }

    public function init($as3cf)
    {
      if (! class_exists('\DeliciousBrains\WP_Offload_Media\Items\Media_Library_Item'))
      {
        Notice::addWarning(__('Your S3-Offload plugin version doesn\'t seem to be compatible. Please upgrade the S3-Offload plugin', 'shortpixel-image-optimiser'), true);
      }
      else {
        $this->itemClassName = '\DeliciousBrains\WP_Offload_Media\Items\Media_Library_Item';
      }

      $this->as3cf = $as3cf;
      $this->active = true;

      // if setting to upload to bucket is off, don't hook or do anything really.
      if (! $this->as3cf->get_setting( 'copy-to-s3' ))
      {
        return;
      }

      if ('cloudfront' === $this->as3cf->get_setting( 'domain' ))
      {
        $this->is_cname = true;
        $this->cname = $this->as3cf->get_setting( 'cloudfront' );
      }

      add_action('shortpixel_image_optimised', array($this, 'image_upload'));
      add_action('shortpixel_after_restore_image', array($this, 'image_restore')); // hit this when restoring.
      add_action('shortpixel/image/convertpng2jpg_after', array($this, 'image_converted'));
      add_action('shortpixel_restore_after_pathget', array($this, 'remove_remote')); // not optimal -> has to do w/ doRestore and when URL/PATH is available when not on server .
      add_action('shortpixel/image/convertpng2jpg_before', array($this, 'remove_remote'));
      add_filter('as3cf_attachment_file_paths', array($this, 'add_webp_paths'));
      add_filter('as3cf_remove_attachment_paths', array($this, 'remove_webp_paths'));

      add_filter('shortpixel/restore/targetfile', array($this, 'returnOriginalFile'),10,2);

      add_filter('as3cf_pre_update_attachment_metadata', array($this, 'preventInitialUpload'), 10,4);

      add_filter('shortpixel_get_attached_file', array($this, 'get_raw_attached_file'),10, 2);
      add_filter('shortpixel_get_original_image_path', array($this, 'get_raw_original_path'), 10, 2);

      // for webp picture paths rendered via output
      add_filter('shortpixel_webp_image_base', array($this, 'checkWebpRemotePath'), 10, 2);
      add_filter('shortpixel/front/webp_notfound', array($this, 'fixWebpRemotePath'), 10, 4);
    }

    public function get_raw_attached_file($file, $id)
    {
      $scheme = parse_url($file, PHP_URL_SCHEME);
      if ($scheme !== false && strpos($scheme, 's3') !== false)
      {
        return get_attached_file($id, true);
      }
      return $file;
    }

    // partial copy of the wp_get_original_image_path function. It doesn't support raw filter on get_attached_file
    public function get_raw_original_path($file, $id)
    {

      $scheme = parse_url($file, PHP_URL_SCHEME);
      if ($scheme !== false && strpos($scheme, 's3') !== false)
      {
        $image_meta = wp_get_attachment_metadata( $id );
        $image_file = get_attached_file( $id, true );

        if ( empty( $image_meta['original_image'] ) ) {
            $original_image = $image_file;
        } else {
            $original_image = path_join( dirname( $image_file ), $image_meta['original_image'] );
        }
        $file = $original_image;
      }

      return $file;
    }

    public function addURLforDownload($bool, $url, $host)
    {
      $provider = $this->as3cf->get_provider();
      $provider->get_url_domain();

      //as3cf_aws_s3_client_args filter?
      return $url;
    }

    public function returnOriginalFile($file, $attach_id)
    {
      $file = get_attached_file($attach_id, true);
      return $file;
    }

    public function image_restore($id)
    {
      $this->remove_remote($id);
      $this->image_upload($id);
    }

    public function remove_remote($id)
    {
      $mediaItem = $this->getItemById($id);
      if ($mediaItem === false)
      {
        Log::addDebug('S3-Offload MediaItem not remote - ' . $id);
        return false;
      }
    //  $provider_object = $this->as3cf->get_attachment_provider_info($id);
      $this->as3cf->remove_attachment_files_from_provider($id, $mediaItem);
    }

    /** @return Returns S3Ofload MediaItem, or false when this does not exist */
    protected function getItemById($id)
    {
        $clazz = $this->itemClassName;
        $mediaItem = $clazz::get_by_source_id($id);
        return $mediaItem;
    }

    protected function getByURL($url)
    {
      $class = $this->itemClassName;
      $source_id = $class::get_source_id_by_remote_url($url);

      if ($source_id !== false)
        return $this->getItemById($source_id);
      else
        return false;
    }

    public function image_converted($id)
    {
        $fs = new \ShortPixel\FileSystemController();

        // Don't offload when setting is off.
        // delete the old file.
      //  $provider_object = $this->as3cf->get_attachment_provider_info($id);

  //      $this->as3cf->remove_attachment_files_from_provider($id, $provider_object);
        // get some new ones.

        // delete the old file
        $mediaItem = $this->getItemById($id);
        if ($mediaItem === false) // mediaItem seems not present. Probably not a remote file
          return;

        $this->as3cf->remove_attachment_files_from_provider($id, $mediaItem);
        $providerSourcePath = $mediaItem->source_path();

        //$providerFile = $fs->getFile($provider_object['key']);
        $providerFile = $fs->getFile($providerSourcePath);
        $newFile = $fs->getFile($this->returnOriginalFile(null, $id));

        // convert
        //$newfilemeta = $provider_object['key'];
        if ($providerFile->getExtension() !== $newFile->getExtension())
        {
          //  $newfilemeta = str_replace($providerFile->getFileName(), $newFile->getFileName(), $newfilemeta);
          $data = $mediaItem->key_values(true);
          $record_id = $data['id'];
/*          $data['path']
          $data['original_path']
          $data['original_source_path']
          $data['source_path'] */

          $data['path'] = str_replace($providerFile->getFileName(), $newFile->getFileName(), $data['path']);
          /*$data['original_path'] = str_replace($providerFile->getFileName(), $newFile->getFileName(), $data['original_path']);
          $data['source_path'] = str_replace($providerFile->getFileName(), $newFile->getFileName(), $data['source_path']);
          $data['original_source_path'] = str_replace($providerFile->getFileName(), $newFile->getFileName(), $data['original_source_path']);
*/


//$provider, $region, $bucket, $path, $is_private, $source_id, $source_path, $original_filename = null, $private_sizes = array(), $id = null
          $newItem = new $this->itemClassName($data['provider'], $data['region'], $data['bucket'], $data['path'], $data['is_private'], $data['source_id'], $data['source_path'], $newFile->getFileName(), $data['extra_info'], $record_id );

          $newItem->save();

            Log::addDebug('S3Offload - Uploading converted file ');
        }

        // upload
        $this->image_upload($id); // delete and reupload
    }

    public function image_upload($id)
    {
        $item = $this->getItemById($id);

        if ( $item === false && ! $this->as3cf->get_setting( 'copy-to-s3' ) ) {
          // abort if not already uploaded to provider and the copy setting is off
          Log::addDebug('As3cf image upload is off and object not previously uploaded');
          return false;
        }

        Log::addDebug('Uploading New Attachment');
        $this->as3cf->upload_attachment($id);
    }

    /** This function will cut out the initial upload to S3Offload and rely solely on the image_upload function provided here, after shortpixel optimize.
    * Function will only work when plugin is set to auto-optimize new entries to the media library
    * Since S3-Offload 2.3 this will be called on every thumbnail ( changes in WP 5.3 )
    */
    public function preventInitialUpload($bool, $data, $post_id, $old_provider_object)
    {
        $settings = \wpSPIO()->settings();

        if ($settings->autoMediaLibrary)
        {
          // Don't prevent whaffever if shortpixel is already done. This can be caused by plugins doing a metadata update, we don't care then.
          if (! isset($data['ShortPixelImprovement']))
          {
            Log::addDebug('Preventing Initial Upload', $post_id);
            return true;
          }
        }
        return $bool;
    }

    private function getWebpPaths($paths, $check_exists = true)
    {
      $newPaths = array();
      $fs = new FileSystem();

      foreach($paths as $size => $path)
      {
         $file = $fs->getFile($path);
         $basepath = $file->getFileDir()->getPath();
         $newPaths[$size] = $path;

         $webpformat1 = $basepath . $file->getFileName() . '.webp';
         $webpformat2 = $basepath . $file->getFileBase() . '.webp';

         if ($check_exists)
         {
           if (file_exists($webpformat1))
            $newPaths[$size . '_webp'] =  $webpformat1;
         }
         else {
           $newPaths[$size . '_webp1'] =  $webpformat1;
         }

         if ($check_exists)
         {
           if(file_exists($webpformat2))
            $newPaths[$size . '_webp'] =  $webpformat2;
         }
         else {
           $newPaths[$size . '_webp2'] =  $webpformat2;
         }

      }

      return $newPaths;
    }

    /**  Get Webp Paths that might be generated and offload them as well.
    * Paths - size : path values
    */
    public function add_webp_paths($paths)
    {
      //  Log::addDebug('Received Paths', array($paths));
        $paths = $this->getWebpPaths($paths, true);
  //      Log::addDebug('Webp Path Founder (S3)', array($paths));
        return $paths;
    }

    public function remove_webp_paths($paths)
    {
      $paths = $this->getWebpPaths($paths, false);
    //  Log::addDebug('Remove S3 Paths', array($paths));
      return $paths;
    }

    public function checkWebpRemotePath($url, $original)
    {
      if ($url === false)
      {
        return $this->convertWebPRemotePath($url, $original);
        //  return $url;
      }
      elseif($this->is_cname) // check this. the webp to picture will convert subdomains with CNAME to some local path when offloaded.
      {
          Log::addDebug('S3 active, checking on CNAME for path' . $this->cname);
          if (strpos($original, $this->cname) !== false)
            return $this->convertWebPRemotePath($url, $original);
      }

      return $url;

    }

    private function convertWebPRemotePath($url, $original)
    {
      $mediaItem = $this->getByURL($original); // test if exists remote.
      Log::addDebug('ImageBaseName check for S3 - ', array($original, $mediaItem));

      if ($mediaItem === false)
      {
        $pattern = '/-\d+x\d*/i';
        $replaced_url = preg_replace($pattern, '', $original);
        $mediaItem = $this->getByURL($replaced_url);
      }

      if ($mediaItem === false)
      {
         return $url;
      }
      $parsed = parse_url($original);
      $url = str_replace($parsed['scheme'], '', $original);
      $url = str_replace(basename($url), '',  $url);
      Log::addDebug('New BasePath, External' . $url);

      return $url;
    }

    // GetbyURL can't find thumbnails, only the main image. We are going to assume, if imagebase is ok, the webp might be there.
    public function fixWebpRemotePath($bool, $file, $url, $imagebase)
    {
        if (strpos($url, $imagebase) !== false)
          return $file;
        else
          return $bool;

      /*  $mediaItem = $this->getByURL($url);
        if ($mediaItem !== false)
        {
          return $file;
        }
        else
        {
        //  Log::addDebug('Fixing Remote Path failed', array($file->getFullPath(), $url));
        }
        return false; */

    }

}

$wpOff = new wpOffload();
