<?php

class ShortPixelFolder extends ShortPixelEntity{
    const META_VERSION = 1;

    protected $id;
    protected $path;
    protected $type;
    protected $status;
    protected $fileCount;
    protected $tsCreated;
    protected $tsUpdated;

    protected $excludePatterns;

    const TABLE_SUFFIX = 'folders';

    public function __construct($data, $excludePatterns = false) {
        parent::__construct($data);
        $this->excludePatterns = $excludePatterns;
    }

    public static function checkFolder($folder, $base) {
        if(strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN' && substr($folder, 0, 1) !== '/') {
            $folder = '/' . $folder;
        }
        if(is_dir($folder)) {
            return $folder;
        } elseif(is_dir($base . $folder)) {
            return $base . $folder;
        }
        return false;
    }

    /** This creates the general backup folder **/
    public static function createBackUpFolder($folder = SHORTPIXEL_BACKUP_FOLDER)
    {
      // create backup folder
      $fs = \wpSPIO()->filesystem();
      $dir = $fs->getDirectory($folder);
      $result = false;

      if (! $dir->exists() )
      {
        $dir->check();
        self::protectDirectoryListing($folder);
        $result = true;
      }

      return $result;
    }

    public static function protectDirectoryListing($dirname)
    {
      $rules = "Options -Indexes";
      /* Plugin init is before loading these admin scripts. So it can happen misc.php is not yet loaded */
      // This crashes at 5.3.
    /*  if (! function_exists('insert_with_markers'))
      {
        //require_once( ABSPATH . 'wp-admin/includes/misc.php' );
        return; // sadly then no.
      } */

  //    insert_with_markers( trailingslashit($dirname) . '.htaccess', 'ShortPixel', $rules);
      // note - this doesn't bring the same protection. Subdirs without files written will still be listable.
      file_put_contents(trailingslashit($dirname) . 'index.html', chr(0)); // extra - for non-apache

      if (\wpSPIO()->env()->is_nginx) // nginx has no htaccess support.
        return;

      file_put_contents(trailingslashit($dirname) . '.htaccess', $rules);

    }

    /** @todo This function is double with wp-short-pixel - deleteDir */
    public static function deleteFolder($dirname) {
        if (is_dir($dirname))
            $dir_handle = opendir($dirname);
        if (!$dir_handle)
            return false;
        while($file = @readdir($dir_handle)) {
            if ($file != "." && $file != "..") {
                if (!is_dir($dirname."/".$file))
                    @unlink($dirname."/".$file);
                else
                    self::deleteFolder($dirname.'/'.$file);
            }
        }
        closedir($dir_handle);
        @rmdir($dirname);
        return true;
    }

    /**
     * returns the first from parents that is a parent folder of $folder
     * @param string $folder
     * @param array $parents
     * @return parent if found, false otherwise
     */
    public static function checkFolderIsSubfolder($folder, $parents) {
        if(!is_array($parents)) {
            $parents = array($parents);
        }
        foreach($parents as $parent) {
            if(strpos($folder, $parent) === 0 && (strlen($parent) == strlen($folder) || substr($folder, strlen($parent), 1) == '/')) {
                return $parent;
            }
        }
        return false;
    }

    /**
     * finds the first from the subfolders that has the folder as parent
     * @param string $folder
     * @param array $subfolders
     * @return string subfolder if found, false otherwise
     */
    public static function checkFolderIsParent($folder, $subfolders) {
        if(!is_array($subfolders)) {
            $subfolders[] = $subfolders;
        }
        foreach($subfolders as $sub) {
            if(strpos($sub, $folder) === 0 && (strlen($folder) == strlen($sub) || substr($sub, strlen($folder) - 1, 1) == '/')) {
                return $sub;
            }
        }
        return false;
    }

    public function countFiles($path = null) {
        $size = 0;
        $path = $path ? $path : $this->getPath();
        if($path == null) {
            return 0;
        }
        $ignore = array('.','..');
        if(!is_writable($path)) {
            throw new ShortPixelFileRightsException(sprintf(__('Folder %s is not writeable. Please check permissions and try again.','shortpixel-image-optimiser'),$path));
        }
        $files = scandir($path);
        foreach($files as $t) {
            $tpath = rtrim($path, '/') . '/' . $t;
            if(in_array($t, $ignore)) continue;
            if (is_dir($tpath)) {
                $size += $this->countFiles($tpath);
            } elseif(WPShortPixel::_isProcessablePath($tpath, array(), $this->excludePatterns)) {
                $size++;
            }
        }
        return $size;
    }

    public function getFileList($onlyNewerThan = 0) {
        $fileListPath = tempnam(SHORTPIXEL_UPLOADS_BASE . '/', 'sp_');
        $fileHandle = fopen($fileListPath, 'w+');
        self::getFileListRecursive($this->getPath(), $fileHandle, $onlyNewerThan);
        fclose($fileHandle);
        return $fileListPath;
    }

    protected static function getFileListRecursive($path, $fileHandle, $onlyNewerThan) {
        $ignore = array('.','..');
        $files = scandir($path);
        $add = (filemtime($path) > $onlyNewerThan);
        /*
        if($add && $onlyNewerThan) {
            echo("<br> FOLDER UPDATED: " . $path);
        }
        */
        foreach($files as $t) {
            if(in_array($t, $ignore)) continue;
            $tpath = trailingslashit($path) . $t;
            if (is_dir($tpath)) {
                self::getFileListRecursive($tpath, $fileHandle, $onlyNewerThan);
            } elseif($add && WPShortPixel::_isProcessablePath($tpath, array(), WPShortPixelSettings::getOpt('excludePatterns'))) {
                fwrite($fileHandle, $tpath . "\n");
            }
        }
    }

    public function checkFolderContents($callback) {
        $changed = array();
        self::checkFolderContentsRecursive($this->getPath(), $changed, $callback);
        return $changed;
    }

    protected static function checkFolderContentsRecursive($path, &$changed, $callback) {
        $ignore = array('.','..');
        $files = scandir($path);
        $reference = call_user_func_array($callback, array($path));
        foreach($files as $t) {
            if(in_array($t, $ignore)) continue;
            $tpath = trailingslashit($path) . $t;
            if (is_dir($tpath)) {
                self::checkFolderContentsRecursive($tpath, $changed, $callback);
            } elseif(   WPShortPixel::_isProcessablePath($tpath, array(), WPShortPixelSettings::getOpt('excludePatterns'))
                     && !(in_array($tpath, $reference) && $reference[$tpath]->compressedSize == filesize($tpath))) {
                $changed[] = $tpath;
            }
        }
    }

    public function getFolderContentsChangeDate() {
        return self::getFolderContentsChangeDateRecursive($this->getPath(), 0, strtotime($this->getTsUpdated()));
    }

    protected static function getFolderContentsChangeDateRecursive($path, $mtime, $refMtime) {
        $ignore = array('.','..');
        if(!is_writable($path)) {
            throw new ShortPixelFileRightsException(sprintf(__('Folder %s is not writeable. Please check permissions and try again.','shortpixel-image-optimiser'),$path));
        }
        $files = scandir($path);
        $mtime = max($mtime, filemtime($path));
        foreach($files as $t) {
            if(in_array($t, $ignore)) continue;
            $tpath = rtrim($path, '/') . '/' . $t;
            if (is_dir($tpath)) {
                $mtime = max($mtime, filemtime($tpath));
                self::getFolderContentsChangeDateRecursive($tpath, $mtime, $refMtime);
            }
        }
        return $mtime;
    }

    function getId() {
        return $this->id;
    }

    function getPath() {
        return $this->path;
    }

    function getTsCreated() {
        return $this->tsCreated;
    }

    function getTsUpdated() {
        return $this->tsUpdated;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setPath($path) {
        $this->path = $path;
    }

    function getType() {
        return $this->type;
    }

    function setType($type) {
        $this->type = $type;
    }

    function getStatus() {
        return $this->status;
    }

    function setStatus($status) {
        $this->status = $status;
    }

    function getFileCount() {
        return $this->fileCount;
    }

    function setFileCount($fileCount) {
        $this->fileCount = $fileCount;
    }

    function setTsCreated($tsCreated) {
        $this->tsCreated = $tsCreated;
    }

    function setTsUpdated($tsUpdated) {
        $this->tsUpdated = $tsUpdated;
    }

    /**
     * needed as callback
     * @param ShortPixelFolder $item
     */
    public static function path($item) {
        return $item->getPath();
    }

}
