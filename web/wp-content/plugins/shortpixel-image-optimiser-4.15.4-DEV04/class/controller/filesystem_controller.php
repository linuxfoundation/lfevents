<?php
namespace ShortPixel;
use ShortPixel\ShortpixelLogger\ShortPixelLogger as Log;


/** Controller for FileSystem operations
*
* This controller is used for -compound- ( complex ) FS operations, using the provided models File en Directory.
*/
Class FileSystemController extends ShortPixelController
{
    protected $env;

    public function __construct()
    {
      $this->loadModel('file');
      $this->loadModel('directory');
    //  $this->loadModel('environment');

      $this->env = wpSPIO()->env();

    }

    /** Get FileModel for a certain path. This can exist or not
    *
    * @param String Path Full Path to the file
    * @return FileModel FileModel Object. If file does not exist, not all values are set.
    */
    public function getFile($path)
    {
      return new FileModel($path);
    }

    /** Get FileModel for a mediaLibrary post_id .
    *
    * This function exists to put get_attached_file to plugin control
    * Externals / Interals maybe filter it.
    *
    * @param $id Attachement ID for the media library item
    * @return FileModel returns a FileModel file.
    * @todo This function will be more at home in a medialibrary_model
    */
    public function getAttachedFile($id)
    {
        $filepath = get_attached_file($id);
        // same signature as wordpress' filter. Only for this plugin.
        $filepath = apply_filters('shortpixel_get_attached_file', $filepath, $id);

        return new FileModel($filepath);

    }

    /* wp_get_original_image_path with specific ShortPixel filter */
    public function getOriginalPath($id)
    {
      $filepath = \wp_get_original_image_path($id);
      $filepath = apply_filters('shortpixel_get_original_image_path', $filepath, $id);
      return new FileModel($filepath);
    }

    /** Get DirectoryModel for a certain path. This can exist or not
    *
    * @param String $path Full Path to the Directory.
    * @return DirectoryModel Object with status set on current directory.
    */
    public function getDirectory($path)
    {
      return new DirectoryModel($path);
    }

    /** Get the BackupLocation for a FileModel. FileModel should be not a backup itself or it will recurse
    *
    *  For now this function should only be used for *new* backup files. Retrieving backup files via this method
    *  doesn't take into account legacy ways of storage.
    *
    * @param FileModel $file FileModel with file that needs a backup.
    * @return DirectoryModel | Boolean DirectoryModel pointing to the backup directory. Returns false if the directory could not be created, or initialized.
    */
    public function getBackupDirectory(FileModel $file)
    {
      $wp_home = get_home_path();
      $filepath = $file->getFullPath();

      // Implement this bastard better here.
      $backup_subdir = \ShortPixelMetaFacade::returnSubDir($filepath);

      $backup_fulldir = SHORTPIXEL_BACKUP_FOLDER . '/' . $backup_subdir;
      Log::addDebug('Get File BackupDirectory' . $backup_fulldir);

      $directory = $this->getDirectory($backup_fulldir);

      if ($directory->check())
        return $directory;
      else {
        return false;
      }
    }

    /** Get the base folder from where custom paths are possible (from WP-base / sitebase)

    */
    public function getWPFileBase()
    {
      if(\wpSPIO()->env()->is_mainsite) {
          $path = get_home_path();

      } else {
          $up = wp_upload_dir();
          $path = realpath($up['basedir']);
      }
      $dir = $this->getDirectory($path);
      if (! $dir->exists())
        Log::addWarn('getWPFileBase - Base path doesnt exist');

      return $dir;

    }

    /** Not in use yet, do not use. Future replacement. */
    public function createBackUpFolder($folder = SHORTPIXEL_BACKUP_FOLDER)
    {

    }

    /** Utility function that tries to convert a file-path to a webURL.
    *
    * If possible, rely on other better methods to find URL ( e.g. via WP functions ).
    */
    public function pathToUrl(FileModel $file)
    {
      $filepath = $file->getFullPath();
      $directory = $file->getFileDir();

      // stolen from wp_get_attachment_url
      if ( ( $uploads = wp_get_upload_dir() ) && false === $uploads['error'] ) {
            // Check that the upload base exists in the file location.
            if ( 0 === strpos( $filepath, $uploads['basedir'] ) ) {
                // Replace file location with url location.
                $url = str_replace( $uploads['basedir'], $uploads['baseurl'], $filepath );
            } elseif ( false !== strpos( $filepath, 'wp-content/uploads' ) ) {
                // Get the directory name relative to the basedir (back compat for pre-2.7 uploads)
                $url = trailingslashit( $uploads['baseurl'] . '/' . _wp_get_attachment_relative_path( $filepath ) ) . wp_basename( $filepath );
            } else {
                // It's a newly-uploaded file, therefore $file is relative to the basedir.
                $url = $uploads['baseurl'] . "/$filepath";
            }
        }

        $wp_home_path = trailingslashit(get_home_path());
        // If the whole WP homepath is still in URL, assume the replace when wrong ( not replaced w/ URL)
        // This happens when file is outside of wp_uploads_dir
        if (strpos($url, $wp_home_path) !== false)
        {
          $home_url = trailingslashit(get_home_url());
          $url = str_replace($wp_home_path, $home_url, $filepath);
        }

        if (parse_url($url) !== false)
          return $url;
        else {
          return false;
        }
    }

    /** Sort files / directories in a certain way.
    * Future dev to include options via arg.
    */
    public function sortFiles($array, $args = array() )
    {
        if (count($array) == 0)
          return $array;

        // what are we sorting.
        $class = get_class($array[0]);
        $is_files = ($class == 'ShortPixel\FileModel') ? true : false; // if not files, then dirs.

        usort($array, function ($a, $b) use ($is_files)
            {
              if ($is_files)
                return strcmp($a->getFileName(), $b->getFileName());
              else {
                return strcmp($a->getName(), $b->getName());
              }
            }
        );

        return $array;

    }

    /** Get all files from a directory tree, starting at given dir.
    * @param DirectoryModel $dir to recursive into
    * @param Array $filters Collection of optional filters as accepted by FileFilter in directoryModel
    * @return Array Array of FileModel Objects
     **/
    public function getFilesRecursive(DirectoryModel $dir, $filters = array() )
    {
        $fileArray = array();

        if (! $dir->exists())
          return $fileArray;

        $files = $dir->getFiles($filters);
        $fileArray = array_merge($fileArray, $files);

        $subdirs = $dir->getSubDirectories();

        foreach($subdirs as $subdir)
        {
             $fileArray = array_merge($fileArray, $this->getFilesRecursive($subdir, $filters));
        }

        return $fileArray;
    }


}
