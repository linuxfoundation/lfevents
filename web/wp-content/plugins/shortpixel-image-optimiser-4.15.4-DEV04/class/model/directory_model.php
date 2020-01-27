<?php
namespace ShortPixel;
use ShortPixel\ShortpixelLogger\ShortPixelLogger as Log;

/* Model for Directories
*
* For all low-level operations on directories
* Private model of FileSystemController. Please get your directories via there.
*
*/

class DirectoryModel extends ShortPixelModel
{
  // Directory info
  protected $path;
  protected $name;

  // Directory status
  protected $exists = false;
  protected $is_writable = false;
  protected $is_readable = false;

  protected $new_directory_permission = 0755;

  /** Creates a directory model object. DirectoryModel directories don't need to exist on FileSystem
  *
  * When a filepath is given, it  will remove the file part.
  * @param $path String The Path
  */
  public function __construct($path)
  {


      $path = wp_normalize_path($path);
      if (! is_dir($path)) // path is wrong, *or* simply doesn't exist.
      {
        /* Test for file input.
        * If pathinfo is fed a fullpath, it rips of last entry without setting extension, don't further trust.
        * If it's a file extension is set, then trust.
        */
        $pathinfo = pathinfo($path);
        if (isset($pathinfo['extension']))
        {
          $path = $pathinfo['dirname'];
        }
        elseif (is_file($path))
          $path = dirname($path);
      }

      if (! is_dir($path))
      {
        /* Check if realpath improves things. We support non-existing paths, which realpath fails on, so only apply on result.
        Moved realpath to check after main pathinfo is set. Reason is that symlinked directories which don't include the WordPress upload dir will start to fail in file_model on processpath ( doesn't see it as a wp path, starts to try relative path). Not sure if realpath should be used anyhow in this model /BS
        */
        $testpath = realpath($path);
        if ($testpath)
          $path = $testpath;
      }

      $this->path = trailingslashit($path);
      $this->name = basename($this->path);

      if (file_exists($this->path))
      {
        $this->exists();
        $this->is_writable();
        $this->is_readable();
      }
  }

  public function __toString()
  {
    return (string) $this->path;
  }

  /** Returns path *with* trailing slash
  *
  * @return String Path with trailing slash
  */
  public function getPath()
  {
    return $this->path;
  }

  public function getModified()
  {
    return filemtime($this->path);
  }

  /** Get basename of the directory. Without path */
  public function getName()
  {
    return $this->name;
  }

  public function exists()
  {
    $this->exists = file_exists($this->path);
    return $this->exists;
  }

  public function is_writable()
  {
    $this->is_writable = is_writable($this->path);
    return $this->is_writable;
  }

  public function is_readable()
  {
     $this->is_readable = is_readable($this->path);
     return $this->is_readable;
  }
  /** Try to obtain the path, minus the installation directory.
  * @return Mixed False if this didn't work, Path as string without basedir if it did. With trailing slash, without starting slash.
  */
  public function getRelativePath()
  {
    //$_SERVER['DOCUMENT_ROOT'] <!-- another tool.
     $upload_dir = wp_upload_dir(null, false);

     $install_dir = get_home_path();
     if($install_dir == '/') {
         $install_dir = ABSPATH;
     }

     $install_dir = trailingslashit($install_dir);

     $path = $this->getPath();
     // try to build relativePath without first slash.
     $relativePath = str_replace($install_dir, '', $path);

     if (! is_dir( $install_dir . $relativePath))
     {
        $test_path = $this->reverseConstructPath($path, $install_dir);
        if ($test_path !== false)
        {
            $relativePath = $test_path;
        }
        else {
           if($test_path = $this->constructUsualDirectories($path))
           {
             $relativePath = $test_path;
           }

        }
     }

     // if relativePath has less amount of characters, changes are this worked.
     if (strlen($path) > strlen($relativePath))
     {
       return ltrim(trailingslashit($relativePath), '/');
     }
     return false;
  }

  private function reverseConstructPath($path, $install_path)
  {
    $pathar = array_values(array_filter(explode('/', $path))); // array value to reset index
    $parts = array(); //

    if (is_array($pathar))
    {
      // reverse loop the structure until solid ground is found.
      for ($i = (count($pathar)); $i > 0; $i--)
      {
        $parts[]  = $pathar[$i-1];
        $testpath = implode('/', array_reverse($parts));
        if (is_dir($install_path . $testpath)) // if the whole thing exists
        {
          return $testpath;
        }
      }
    }
    return false;
  }

  /* Last Resort function to just reduce path to various known WorPress paths. */
  private function constructUsualDirectories($path)
  {
    $pathar = array_values(array_filter(explode('/', $path))); // array value to reset index
    $testpath = false;
    if ( ($key = array_search('wp-content', $pathar)) !== false)
    {
        $testpath = implode('/', array_slice($pathar, $key));
    }
    elseif ( ($key = array_search('uploads', $pathar)) !== false)
    {
        $testpath = implode('/', array_slice($pathar, $key));
    }

    return $testpath;
  }

  /** Checks the directory into working order
  * Tries to create directory if it doesn't exist
  * Tries to fix file permission if writable is needed
  * @param $check_writable Boolean Directory should be writable
  */
  public function check($check_writable = false)
  {
     if (! $this->exists())
     {
        Log::addInfo('Directory does not exists. Try to create recursive ' . $this->path . ' with '  . $this->new_directory_permission);
        $result = @mkdir($this->path, $this->new_directory_permission , true);
        if (! $result)
        {
          $error = error_get_last();
          echo $error['message'];
          Log::addWarn('MkDir failed: ' . $error['message'], array($error));
        }

     }
     if ($this->exists() && $check_writable && ! $this->is_writable())
     {
       chmod($this->path, $this->new_directory_permission);
     }

     if (! $this->exists())
     {
       Log::addInfo('Directory does not exist :' . $this->path);
       return false;
     }
    if ($check_writable && !$this->is_writable())
    {
        Log::addInfo('Directory not writable :' . $this->path);
        return false;
    }
    return true;
  }

  /* Get files from directory
  * @todo In future this should accept some basic filters via args.
  * @returns Array|boolean Returns false if something wrong w/ directory, otherwise a files array of FileModel Object.
  */
  public function getFiles($args = array())
  {

    $defaults = array(
        'date_newer' => null,
    );
    $args = wp_parse_args($args, $defaults);

    // if all filters are set to null, so point in checking those.
    $has_filters = (count(array_filter($args)) > 0) ? true : false;

    if ( ! $this->exists() || ! $this->is_readable() )
      return false;

    $fileArray = array();

    if ($handle = opendir($this->path)) {
        while (false !== ($entry = readdir($handle))) {
            if ( ($entry != "." && $entry != "..") && ! is_dir($this->path . $entry) ) {
                $fileArray[] = new FileModel($this->path . $entry);
            }
        }
        closedir($handle);
    }

    if ($has_filters)
    {
      $fileArray = array_filter($fileArray, function ($file) use ($args) {
           return $this->fileFilter($file, $args);
       } );
    }
    return $fileArray;
  }

  // @return boolean true if it should be kept in array, false if not.
  private function fileFilter(FileModel $file, $args)
  {
     $filter = true;

     if (! is_null($args['date_newer']))
     {
       $modified = $file->getModified();
       if ($modified < $args['date_newer'] )
          $filter = false;
     }

     return $filter;
  }

  /** Get subdirectories from directory
  * * @returns Array|boolean Returns false if something wrong w/ directory, otherwise a files array of DirectoryModel Object.
  */
  public function getSubDirectories()
  {
    $fs = \wpSPIO()->fileSystem();

    if (! $this->exists() || ! $this->is_readable() )
      return false;

    $dirIt = new \DirectoryIterator($this->path);
    $dirArray = array();
    foreach($dirIt as $fileInfo)
    {
       if ($fileInfo->isDir() && $fileInfo->isReadable() && ! $fileInfo->isDot() )
       {
         $dir = new DirectoryModel($fileInfo->getRealPath());
         if ($dir->exists())
            $dirArray[] = $dir;
       }

    }
    return $dirArray;
  }

  /** Check if this dir is a subfolder
  * @param DirectoryModel The directoryObject that is tested as the parent */
  public function isSubFolderOf(DirectoryModel $dir)
  {
    // the same path, is not a subdir of.
    if ($this->getPath() === $dir->getPath())
      return false;

    // the main path must be followed from the beginning to be a subfolder.
    if (strpos($this->getPath(), $dir->getPath() ) === 0)
    {
      return true;
    }
    return false;
  }

}
