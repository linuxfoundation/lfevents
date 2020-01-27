<?php
namespace ShortPixel;
use ShortPixel\ShortpixelLogger\ShortPixelLogger as Log;

/* FileModel class.
*
*
* - Represents a -single- file.
* - Can handle any type
* - Usually controllers would use a collection of files
* - Meant for all low-level file operations and checks.
* - Every file can have a backup counterpart.
*
*/
class FileModel extends ShortPixelModel
{

  // File info
  protected $fullpath = null;
  protected $filename = null; // filename + extension
  protected $filebase = null; // filename without extension
  protected $directory = null;
  protected $extension = null;

  // File Status
  protected $exists = null;
  protected $is_writable = null;
  protected $is_readable = null;
  protected $is_file = null;

  protected $status;

  protected $backupDirectory;

  const FILE_OK = 1;
  const FILE_UNKNOWN_ERROR = 2;


  /** Creates a file model object. FileModel files don't need to exist on FileSystem */
  public function __construct($path)
  {
    $this->fullpath = trim($path);
  }

  /* Get a string representation of file, the fullpath
  *  Note - this might be risky, without processedpath, in cases.
  * @return String  Full path  processed or unprocessed.
  */
  public function __toString()
  {
    return (string) $this->fullpath;
  }

  protected function setFileInfo()
  {
      $processed_path = $this->processPath($this->fullpath);
      if ($processed_path !== false)
        $this->fullpath = $processed_path; // set processed path if that went alright


      $info = $this->mb_pathinfo($this->fullpath);

      // Todo, maybe replace this with splFileINfo.
      if ($this->is_file()) // only set fileinfo when it's an actual file.
      {
        $this->filename = isset($info['basename']) ? $info['basename'] : null; // filename + extension
        $this->filebase = isset($info['filename']) ? $info['filename'] : null; // only filename
        $this->extension = isset($info['extension']) ? $info['extension'] : null; // only (last) extension
      }

  }

  /** Call when file status changed, so writable / readable / exists are not reliable anymore */
  public function resetStatus()
  {
      $this->is_writable = null;
      $this->is_readable = null;
      $this->is_file = null;
      $this->exists = null;
  }

  public function exists()
  {
    if (is_null($this->exists))
      $this->exists = file_exists($this->fullpath);

    return $this->exists;
  }

  public function is_writable()
  {
    if (is_null($this->is_writable))
      $this->is_writable = is_writable($this->fullpath);

    return $this->is_writable;
  }

  public function is_readable()
  {
    if (is_null($this->is_readable))
      $this->is_readable = is_readable($this->fullpath);

    return $this->is_readable;
  }

  /* Function checks if path is actually a file. This can be used to check possible confusion if a directory path is given to filemodel */
  public function is_file()
  {
    if (is_null($this->is_file))
    {
      if ($this->exists())
        $this->is_file = is_file($this->fullpath);
      else // file can not exist, but still have a valid filepath format. In that case, if file should return true.
      {
         //$pathinfo = pathinfo($this->fullpath);
         //if (isset($pathinfo['extension'])) // extension means file.

         //  if file does not exist on disk, anything can become a file ( with/ without extension, etc). Meaning everything non-existing is a potential file ( or directory ) until created.
         $this->is_file = true;

      }
    }
    return $this->is_file;
  }

  public function getModified()
  {
    return filemtime($this->fullpath);
  }

  public function hasBackup()
  {
      $directory = $this->getBackupDirectory();
      if (! $directory)
        return false;

      $backupFile =  $directory . $this->getFileName();

      if (file_exists($backupFile) && ! is_dir($backupFile) )
        return true;
      else {
        return false;
      }
  }

  /** Tries to retrieve an *existing* BackupFile. Returns false if not present.
  * This file might not be writable.
  * To get writable directory reference to backup, use FileSystemController
  */
  public function getBackupFile()
  {
     if ($this->hasBackup())
        return new FileModel($this->getBackupDirectory() . $this->getFileName() );
     else
       return false;
  }

  /** Returns the Directory Model this file resides in
  *
  * @return DirectoryModel Directorymodel Object
  */
  public function getFileDir()
 {
      // create this only when needed.
      if (is_null($this->directory) && strlen($this->fullpath) > 0)
      {
        // Feed to full path to DirectoryModel since it checks if input is file, or dir. Using dirname here would cause errors when fullpath is already just a dirpath ( faulty input )
          $this->directory = new DirectoryModel($this->fullpath);
      }
      return $this->directory;
  }

  public function getFileSize()
  {
    if ($this->exists())
      return filesize($this->fullpath);
    else
      return 0;
  }

  /** Copy a file to somewhere
  *
  * @param $destination String Full Path to new file.
  */
  public function copy(FileModel $destination)
  {
      $sourcePath = $this->getFullPath();
      $destinationPath = $destination->getFullPath();
      Log::addDebug("Copy from $sourcePath to $destinationPath ");

      if (! strlen($sourcePath) > 0 || ! strlen($destinationPath) > 0)
      {
        Log::addWarn('Attempted Copy on Empty Path', array($sourcePath, $destinationPath));
        return false;
      }

      if (! $this->exists())
      {
        Log::addWarn('Tried to copy non-existing file - '  . $sourcePath);
        return false;
      }

      $is_new = ($destination->exists()) ? false : true;
      $status = copy($sourcePath, $destinationPath);

      if (! $status)
        Log::addWarn('Could not copy file ' . $sourcePath . ' to' . $destinationPath);
      else {
        $destination->resetStatus();
        $destination->setFileInfo(); // refresh info.
      }
      //
      do_action('shortpixel/filesystem/addfile', array($destinationPath, $destination, $this, $is_new));
      return $status;
  }

  /** Move a file to somewhere
  * This uses copy and delete functions and will fail if any of those fail.
  * @param $destination String Full Path to new file.
  */
  public function move(FileModel $destination)
  {
     $result = false;
     if ($this->copy($destination))
     {
       $result = $this->delete();
       $this->resetStatus();
       $destination->resetStatus();
     }
     return $result;
  }

  /** Deletes current file
  * This uses the WP function since it has a filter that might be useful
  */
  public function delete()
  {
     if ($this->exists())
      \wp_delete_file($this->fullpath);  // delete file hook via wp_delete_file

      if (! file_exists($this->fullpath))
      {
        $this->resetStatus();
        return true;
      }
      else {
        return false;
        Log::addWarn('File seems not removed - ' . $this->fullpath);
      }

  }

  public function getFullPath()
  {
    if (is_null($this->filename))
      $this->setFileInfo();

    return $this->fullpath;
  }

  public function getFileName()
  {
    if (is_null($this->filename))
      $this->setFileInfo();

    return $this->filename;
  }

  public function getFileBase()
  {
    if (is_null($this->filename))
      $this->setFileInfo();

    return $this->filebase;
  }

  public function getExtension()
  {
    if (is_null($this->filename))
      $this->setFileInfo();

    return $this->extension;
  }

  /* Util function to get location of backup Directory.
  * @return Boolean | DirectModel  Returns false if directory is not properly set, otherwhise with a new directoryModel
  */
  private function getBackupDirectory()
  {
    if (is_null($this->getFileDir()))
    {
        Log::addWarn('Could not establish FileDir ' . $this->fullpath);
        return false;
    }
    if (is_null($this->backupDirectory))
    {
      $backup_dir = str_replace(get_home_path(), "", $this->directory->getPath());
      $backupDirectory = SHORTPIXEL_BACKUP_FOLDER . '/' . $backup_dir;
      $directory = new DirectoryModel($backupDirectory);

      if (! $directory->exists()) // check if exists. FileModel should not attempt to create.
      {
        Log::addWarn('Backup Directory not existing ' . $backupDirectory, $directory);
        return false;
      }
      $this->backupDirectory = $directory;
    }

    return $this->backupDirectory;
  }

  /* Internal function to check if path is a real path
  *  - Test for URL's based on http / https
  *  - Test if given path is absolute, from the filesystem root.
  * @param $path String The file path
  * @param String The Fixed filepath.
  */
  protected function processPath($path)
  {
    $original_path = $path;

    if ($this->pathIsUrl($path))
    {
      $path = $this->UrlToPath($path);
    }

    if ($path === false)
      return false;

    $path = wp_normalize_path($path);

    // if path does not contain basepath.
    $uploadPath = wp_normalize_path($this->getUploadPath()); // mixed slashes and dashes can also be a config-error in WP.
    if (strpos($path, ABSPATH) === false && strpos($path, $uploadPath) === false)
      $path = $this->relativeToFullPath($path);

    $path = apply_filters('shortpixel/filesystem/processFilePath', $path, $original_path);
    /* This needs some check here on malformed path's, but can't be test for existing since that's not a requirement.
    if (file_exists($path) === false) // failed to process path to something workable.
    {
    //  Log::addInfo('Failed to process path', array($path));
      $path = false;
    } */

    return $path;
  }

  private function pathIsUrl($path)
  {
    $is_http = (substr($path, 0, 4) == 'http') ? true : false;
    $is_https = (substr($path, 0, 5) == 'https') ? true : false;
    $is_neutralscheme = (substr($path, 0, 1) == '//') ? true : false; // when URL is relative like //wp-content/etc
    $has_urldots = (strpos($path, '://') !== false) ? true : false;

    if ($is_http || $is_https || $is_neutralscheme || $has_urldots)
      return true;
    else {
      return false;
    }

  }

  private function UrlToPath($url)
  {
     //$uploadDir = wp_upload_dir();
     $site_url = str_replace('http:', '', get_site_url(null, '', 'http'));
     $url = str_replace(array('http:', 'https:'), '', $url);

     if (strpos($url, $site_url) !== false)
     {
       // try to replace URL for Path
       $path = str_replace($site_url, rtrim(ABSPATH,'/'), $url);
       if (! $this->pathIsUrl($path)) // test again.
       {
        return $path;
       }
     }

     return false; // seems URL from other server, can't file that.
  }

  /** Tries to find the full path for a perceived relative path.
  *
  * Relative path is detected on basis of WordPress ABSPATH. If this doesn't appear in the file path, it might be a relative path.
  * Function checks for expections on this rule ( tmp path ) and returns modified - or not - path.
  * @param $path The path for the file_exists
  * @returns String The updated path, if that was possible.
  */
  private function relativeToFullPath($path)
  {
      // A file with no path, can never be created to a fullpath.
      if (strlen($path) == 0)
        return $path;

      // if the file plainly exists, it's usable /**
      if (file_exists($path))
      {
        return $path;
      }

      // Test if our 'relative' path is not a path to /tmp directory.

      // This ini value might not exist.
      $tempdirini = ini_get('upload_tmp_dir');
      if ( (strlen($tempdirini) > 0) && strpos($path, $tempdirini) !== false)
        return $path;

      $tempdir = sys_get_temp_dir();
      if ( (strlen($tempdir) > 0) && strpos($path, $tempdir) !== false)
        return $path;

      // Path contains upload basedir. This happens when upload dir is outside of usual WP.
      if (strpos($path, $this->getUploadPath()) !== false)
      {
        return $path;
      }


      // this is probably a bit of a sharp corner to take.
      // if path starts with / remove it due to trailingslashing ABSPATH
      $path = ltrim($path, '/');
      $fullpath = trailingslashit(ABSPATH) . $path;
      // We can't test for file_exists here, since file_model allows non-existing files.
      return $fullpath;
  }

  private function getUploadPath()
  {
    $upload_dir = wp_upload_dir(null, false, false);
    $basedir = $upload_dir['basedir'];

    return $basedir;
  }

  /** Fix for multibyte pathnames and pathinfo which doesn't take into regard the locale.
  * This snippet taken from PHPMailer.
  */
  private function mb_pathinfo($path, $options = null)
  {
        $ret = ['dirname' => '', 'basename' => '', 'extension' => '', 'filename' => ''];
        $pathinfo = [];
        if (preg_match('#^(.*?)[\\\\/]*(([^/\\\\]*?)(\.([^.\\\\/]+?)|))[\\\\/.]*$#m', $path, $pathinfo)) {
            if (array_key_exists(1, $pathinfo)) {
                $ret['dirname'] = $pathinfo[1];
            }
            if (array_key_exists(2, $pathinfo)) {
                $ret['basename'] = $pathinfo[2];
            }
            if (array_key_exists(5, $pathinfo)) {
                $ret['extension'] = $pathinfo[5];
            }
            if (array_key_exists(3, $pathinfo)) {
                $ret['filename'] = $pathinfo[3];
            }
        }
        switch ($options) {
            case PATHINFO_DIRNAME:
            case 'dirname':
                return $ret['dirname'];
            case PATHINFO_BASENAME:
            case 'basename':
                return $ret['basename'];
            case PATHINFO_EXTENSION:
            case 'extension':
                return $ret['extension'];
            case PATHINFO_FILENAME:
            case 'filename':
                return $ret['filename'];
            default:
                return $ret;
        }
    }


} // FileModel Class

/*
// do this before putting the meta down, since maybeDump check for last timestamp
$URLsAndPATHs = $itemHandler->getURLsAndPATHs(false);
$this->maybeDumpFromProcessedOnServer($itemHandler, $URLsAndPATHs);

*/
