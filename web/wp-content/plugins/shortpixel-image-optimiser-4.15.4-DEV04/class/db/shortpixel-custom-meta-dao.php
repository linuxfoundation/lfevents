<?php
use ShortPixel\ShortpixelLogger\ShortPixelLogger as Log;
use ShortPixel\Notices\NoticeController as Notice;

class ShortPixelCustomMetaDao {
    const META_VERSION = 1;
    private $db, $excludePatterns;

    private static $fields = array(
        ShortPixelMeta::TABLE_SUFFIX => array(
            "folder_id" => "d",
            "path" => "s",
            "name" => "s",
            "compression_type" => "d",
            "compressed_size" => "d",
            "keep_exif" => "d",
            "cmyk2rgb" => "d",
            "resize" => "d",
            "resize_width" => "d",
            "resize_height" => "d",
            "backup" => "d",
            "status" => "d",
            "retries" => "d",
            "message" => "s",
            "ts_added" => 's',
            "ts_optimized" => 's',
            "ext_meta_id" => "d" //this is nggPid for now
        ),
        ShortPixelFolder::TABLE_SUFFIX => array(
            "path" => "s",
            "file_count" => "d",
            "status" => "d",
            "ts_updated" => "s",
            "ts_created" => "s",
        )
    );

    public function __construct($db, $excludePatterns = false) {
        $this->db = $db;
        $this->excludePatterns = $excludePatterns;
    }

    public static function getCreateFolderTableSQL($tablePrefix, $charsetCollate) {
       return "CREATE TABLE {$tablePrefix}shortpixel_folders (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            path varchar(512),
            name varchar(64),
            path_md5 char(32),
            file_count int,
            status SMALLINT NOT NULL DEFAULT 0,
            ts_updated timestamp,
            ts_created timestamp,
            UNIQUE KEY id (id)
          ) $charsetCollate;";
       // UNIQUE INDEX spf_path_md5 (path_md5)
    }

    public static function getCreateMetaTableSQL($tablePrefix, $charsetCollate) {
       return "CREATE TABLE {$tablePrefix}shortpixel_meta (
            id mediumint(10) NOT NULL AUTO_INCREMENT,
            folder_id mediumint(9) NOT NULL,
            ext_meta_id int(10),
            path varchar(512),
            name varchar(64),
            path_md5 char(32),
            compressed_size int(10) NOT NULL DEFAULT 0,
            compression_type tinyint,
            keep_exif tinyint,
            cmyk2rgb tinyint,
            resize tinyint,
            resize_width smallint,
            resize_height smallint,
            backup tinyint,
            status SMALLINT NOT NULL DEFAULT 0,
            retries tinyint NOT NULL DEFAULT 0,
            message varchar(255),
            ts_added timestamp,
            ts_optimized timestamp,
            UNIQUE KEY sp_id (id)
          ) $charsetCollate;";
          //UNIQUE INDEX sp_path_md5 (path_md5),
          //FOREIGN KEY fk_shortpixel_meta_folder(folder_id) REFERENCES {$tablePrefix}shortpixel_folders(id)
    }

    private function addIfMissing($type, $table, $key, $field, $fkTable = null, $fkField = null) {
        $hasIndexSql = "select count(*) hasIndex from information_schema.statistics where table_name = '%s' and index_name = '%s' and table_schema = database()";
        $createIndexSql = "ALTER TABLE %s ADD UNIQUE INDEX %s (%s)";
        $createFkSql = "ALTER TABLE %s ADD FOREIGN KEY %s(%s) REFERENCES %s(%s)";
        $hasIndex = $this->db->query(sprintf($hasIndexSql, $table, $key));
        if($hasIndex[0]->hasIndex == 0){
            if($type == "UNIQUE INDEX"){
                $this->db->query(sprintf($createIndexSql, $table, $key, $field));
            } else {
                $this->db->query(sprintf($createFkSql, $table, $key, $field, $fkTable, $fkField));
            }
            return true;
        }
        return false;
    }

    public function tablesExist() {
        $hasTablesSql = "SELECT COUNT(1) tableCount FROM information_schema.tables WHERE table_schema='".$this->db->getDbName()."' "
                     . "AND (table_name='".$this->db->getPrefix()."shortpixel_meta' OR table_name='".$this->db->getPrefix()."shortpixel_folders')";
        $hasTables = $this->db->query($hasTablesSql);
        if($hasTables[0]->tableCount == 2){
            return true;
        }
        return false;
    }

    public function dropTables() {
        if($this->tablesExist()) {
            $this->db->query("DROP TABLE ".$this->db->getPrefix()."shortpixel_meta");
            $this->db->query("DROP TABLE ".$this->db->getPrefix()."shortpixel_folders");
        }
    }

    public function createUpdateShortPixelTables() {
        $res = $this->db->createUpdateSchema(array(
                self::getCreateFolderTableSQL($this->db->getPrefix(), $this->db->getCharsetCollate()),
                self::getCreateMetaTableSQL($this->db->getPrefix(), $this->db->getCharsetCollate())
            ));
        // Set up indexes, not handled well by WP DBDelta
        $this->addIfMissing("UNIQUE INDEX", $this->db->getPrefix()."shortpixel_folders", "spf_path_md5", "path_md5");
        $this->addIfMissing("UNIQUE INDEX", $this->db->getPrefix()."shortpixel_meta", "sp_path_md5", "path_md5");
        $this->addIfMissing("FOREIGN KEY", $this->db->getPrefix()."shortpixel_meta", "fk_shortpixel_meta_folder", "folder_id",
                                           $this->db->getPrefix()."shortpixel_folders", "id");
    }

    public function getFolders($deleted = false) {
        $sql = "SELECT * FROM {$this->db->getPrefix()}shortpixel_folders" . ($deleted ? "" : " WHERE status <> -1");
        $rows = $this->db->query($sql);
        $folders = array();
        foreach($rows as $row) {
            $folders[] = new ShortPixelFolder($row, $this->excludePatterns);
        }
        return $folders;
    }

    public function getFolder($path, $deleted = false) {
        $sql = "SELECT * FROM {$this->db->getPrefix()}shortpixel_folders" . ($deleted ? "WHERE path = %s " : " WHERE path = %s AND status <> -1");
        $rows = $this->db->query($sql, array($path));
        $folders = array();
        foreach($rows as $row) {
            return new ShortPixelFolder($row, $this->excludePatterns);
        }
        return false;
    }

    public function hasFoldersTable() {
        global $wpdb;
        $foldersTable = $wpdb->get_results("SELECT COUNT(1) hasFoldersTable FROM information_schema.tables WHERE table_schema='{$wpdb->dbname}' AND table_name='{$wpdb->prefix}shortpixel_folders'");
        if(isset($foldersTable[0]->hasFoldersTable) && $foldersTable[0]->hasFoldersTable > 0) {
            return true;
        }
        return false;
    }

    /** Folder is ShortPixelFolder object */
    public function addFolder(ShortPixelFolder $folder, $fileCount = 0) {
        $path = $folder->getPath();
        $tsUpdated = date("Y-m-d H:i:s", $folder->getTsUpdated());


        return $this->db->insert($this->db->getPrefix().'shortpixel_folders',
                                 array("path" => $path, "path_md5" => md5($path), "file_count" => $fileCount, "ts_updated" => $tsUpdated, "ts_created" => date("Y-m-d H:i:s")),
                                 array("path" => "%s", "path_md5" => "%s", "file_count" => "%d", "ts_updated" => "%s"));
    }

    public function updateFolder($folder, $newPath, $status = 0, $fileCount = 0) {
        $sql = "UPDATE {$this->db->getPrefix()}shortpixel_folders SET path = %s, path_md5 = %s, file_count = %d, ts_updated = %s, status = %d WHERE path = %s";
        $this->db->query($sql, array($newPath, md5($newPath), $fileCount, date("Y-m-d H:i:s"), $status, $folder));
        $sql2 = "SELECT id FROM {$this->db->getPrefix()}shortpixel_folders WHERE path = %s";
        $res = $this->db->query($sql2, array($folder));
        if(is_array($res)) {
            return $res[0]->id;
        }
        else return -1;
    }

    public function removeFolder($folderPath) {
        $sql = "SELECT id FROM {$this->db->getPrefix()}shortpixel_folders WHERE path = %s";
        $row = $this->db->query($sql, array(stripslashes($folderPath)));

        if(!isset($row[0]->id)) return false;
        $id = $row[0]->id;
        $sql = "UPDATE {$this->db->getPrefix()}shortpixel_folders SET status = -1 WHERE id = %d";
        $this->db->query($sql, array($id));

        //$this->db->hideErrors();
        //  If images are optimized, not all are removed here.
        $sql = "DELETE FROM {$this->db->getPrefix()}shortpixel_meta WHERE folder_id = %d AND status <> %d AND status <> %d";
        $this->db->query($sql, array($id, ShortPixelMeta::FILE_STATUS_PENDING, ShortPixelMeta::FILE_STATUS_SUCCESS));

        $sql = "SELECT FROM {$this->db->getPrefix()}shortpixel_meta WHERE folder_id = %d ";
        $still_has_images = $this->db->query($sql, array($id));

        // if there are no images left, remove the folder. Otherwise keep it at -1.
        if (count($still_has_images) == 0)
        {
          $sql = "DELETE FROM {$this->db->getPrefix()}shortpixel_folders WHERE path = %s";
          $this->db->query($sql, array($folderPath));
        }

        //$this->db->restoreErrors();
    }

    public function newFolderFromPath($path, $uploadPath, $rootPath) {
        WpShortPixelDb::checkCustomTables(); // check if custom tables are created, if not, create them

        $fs = \wpSPIO()->filesystem();

        //$addedFolder = ShortPixelFolder::checkFolder($path, $uploadPath);
        $newfolder = $fs->getDirectory($path);
        $rootPath = $fs->getWPFileBase();

        if(! $newfolder->exists() ) {
            return __('Folder could not be found: ' . $uploadPath . $path ,'shortpixel-image-optimiser');
        }

        if (! $newfolder->isSubFolderOf($rootPath))
        {
                return( sprintf(__('The %s folder cannot be processed as it\'s not inside the root path of your website (%s).','shortpixel-image-optimiser'),$addedFolder, $rootPath));
        }

        if($this->getFolder($newfolder->getPath())) {
            return __('Folder already added.','shortpixel-image-optimiser');
        }

        $folder = new ShortPixelFolder(array("path" => $newfolder->getPath()), $this->excludePatterns);
      /*  try {
            $folder->setFileCount($folder->countFiles());
        } catch(ShortPixelFileRightsException $ex) {
            return $ex->getMessage();
        } */

        if(ShortPixelMetaFacade::isMediaSubfolder($folder->getPath())) {
            return __('This folder contains Media Library images. To optimize Media Library images please go to <a href="upload.php?mode=list">Media Library list view</a> or to <a href="upload.php?page=wp-short-pixel-bulk">SortPixel Bulk page</a>.','shortpixel-image-optimiser');
        }

        // Set this to 0 on new, not null since mysql will auto-complete that to current TS.
        $folder->setTSUpdated(0);
        $folder->setFileCount(0);

        $folderMsg = $this->saveFolder($folder);
        if(!$folder->getId()) {
            //try again creating the tables first.
            $this->createUpdateShortPixelTables();
            $folderMsg = $this->saveFolder($folder);
            //still no luck - complain... :)
            if(!$folder->getId()) {
                return __('The folder could not be saved to the database. Please check that the plugin can create its database tables.', 'shortpixel-image-optimiser') . $folderMsg;
            }
        }

        if(!$folderMsg) {
            //$fileList = $folder->getFileList();
            $this->refreshFolder($newfolder);
        }
        return $folderMsg;

    }

    /** Check files and add what's needed */
    public function refreshFolder(ShortPixel\DirectoryModel $folder)
    {

        $folderObj = $this->getFolder($folder->getPath());

        if ($folderObj === false)
        {
          Log::addWarn('FolderObj from database is not there, while folder seems ok ' . $folder->getPath() );
          return false;
        }
        
        Log::addDebug('Doing Refresh Folder for (DirectoryModel / ShortpixelFolder) ', array($folder->getPath(), $folderObj->getPath()) );

        $fs = \wpSPIO()->fileSystem();

        if (! $folder->exists())
        {
          Notice::addError( sprintf(__('Folder %s does not exist! ', 'shortpixel-image-optimiser'), $folder->getPath()) );
          return false;
        }
        if (! $folder->is_writable())
        {
          Notice::addWarning( sprintf(__('Folder %s is not writeable. Please check permissions and try again.','shortpixel-image-optimiser'),$folder->getPath()) );
        }

        $filter = array('date_newer' => strtotime($folderObj->getTsUpdated()));
        $files = $fs->getFilesRecursive($folder, $filter);

        $shortpixel = \wpSPIO()->getShortPixel();
        // check processable by invoking filter, for now processablepath takes only paths, not objects.
        $files = array_filter($files, function($file) use($shortpixel) { return $shortpixel->isProcessablePath($file->getFullPath());  });

        Log::addDebug('Found Files for custom media ' . count($files));
        $folderObj->setTsUpdated(date("Y-m-d H:i:s", $folderObj->getFolderContentsChangeDate()) );
        $folderObj->setFileCount($folderObj->countFiles() );
        $this->update($folderObj);

        $this->batchInsertImages($files, $folderObj->getId());
    }

    /**
     *
     * @param type $path
     * @return false if saved OK, error message otherwise.
     */
    public function saveFolder(&$folder) {
        $addedPath = $folder->getPath();
        if($addedPath) {
            //first check if it does contain the Backups Folder - we don't allow that
            if(ShortPixelFolder::checkFolderIsSubfolder(SHORTPIXEL_BACKUP_FOLDER, $addedPath)) {
                return __('This folder contains the ShortPixel Backups. Please select a different folder.','shortpixel-image-optimiser');
            }
            $customFolderPaths = array_map(array('ShortPixelFolder','path'), $this->getFolders());
            $allFolders = $this->getFolders(true);
            $customAllFolderPaths = array_map(array('ShortPixelFolder','path'), $allFolders);
            $parent = ShortPixelFolder::checkFolderIsSubfolder($addedPath, $customFolderPaths);
            if(!$parent){
                $sub = ShortPixelFolder::checkFolderIsParent($addedPath, $customAllFolderPaths);
                if($sub) {
                    $id = $this->updateFolder($sub, $addedPath, 0, $folder->getFileCount());
                } else {
                    $id = $this->addFolder($folder, $folder->getFileCount());
                }
                $folder->setId($id);
                return false;
            } else {
                foreach($allFolders as $fld) {
                    if($fld->getPath() == $parent) {
                        $folder->setPath($parent);
                        $folder->setId($fld->getId());
                    }
                }
                //var_dump($allFolders);
                return sprintf(__('Folder already included in %s.','shortpixel-image-optimiser'),$parent);
            }
        } else {
            return __('Folder does not exist.','shortpixel-image-optimiser');
        }
    }

    protected function metaToParams($meta) {
        $params = $types = array();
        foreach(self::$fields[ShortPixelMeta::TABLE_SUFFIX] as $key=>$type) {
            $getter = "get" . ShortPixelTools::snakeToCamel($key);
            if(!method_exists($meta, $getter)) {
                throw new Exception("Bad fields list in ShortPixelCustomMetaDao::metaToParams");
            }
            $val = $meta->$getter();
            if($val !== null) {
            $params[$key] = $val;
            $types[] = "%" . $type;
            }
        }
        return (object)array("params" => $params, "types" => $types);
    }
    public function addImage($meta) {
        $p = $this->metaToParams($meta);
        $id = $this->db->insert($this->db->getPrefix().'shortpixel_meta', $p->params, $p->types);
        return $id;
    }

    private function batchInsertImages($files, $folderId) {
        //facem un delete pe cele care nu au shortpixel_folder, pentru curatenie - am mai intalnit situatii in care stergerea s-a agatat (stop monitoring)
        global $wpdb;

        $sqlCleanup = "DELETE FROM {$this->db->getPrefix()}shortpixel_meta WHERE folder_id NOT IN (SELECT id FROM {$this->db->getPrefix()}shortpixel_folders)";
        $this->db->query($sqlCleanup);

        $values = array();
        $sql = "INSERT IGNORE INTO {$this->db->getPrefix()}shortpixel_meta(folder_id, path, name, path_md5, status) VALUES ";
        $format = '(%d,%s,%s,%s,%d)';
        $i = 0;
        $count = 0;
        $placeholders = array();
        foreach($files as $file) {
            $filepath = $file->getFullPath();
            $filename = $file->getFileName();

            array_push($values, $folderId, $filepath, $filename, md5($filepath), 0);
            $placeholders[] = $format;


            if($i % 500 == 499) {
                $query = $sql;
                $query .= implode(', ', $placeholders);
                $this->db->query( $this->db->prepare("$query ", $values));

                $values = array();
            }
            $i++;
        }
        if($values) {
          $query = $sql;
          $query .= implode(', ', $placeholders);
          $result = $wpdb->query( $wpdb->prepare("$query ", $values) );
          Log::addDebug('Q Result', array($result, $wpdb->last_error));
          //$this->db->query( $this->db->prepare("$query ", $values));
        }

    }

    public function resetFailed() {
        $sql = "UPDATE {$this->db->getPrefix()}shortpixel_meta SET status = 0, retries = 0 WHERE status < 0";
        $this->db->query($sql);
    }

    /** Reset Restored items
    * On Bulk Optimize, Reset the restored status so it will process these images again.
    *
    **/
    public function resetRestored() {
        $sql = "UPDATE {$this->db->getPrefix()}shortpixel_meta SET status = 0, retries = 0 WHERE status = 3";
        $this->db->query($sql);
    }

    public function getPaginatedMetas($hasNextGen, $filters, $count, $page, $orderby = false, $order = false) {
        // [BS] Remove exclusion for sm.status <> 3. Status 3 is 'restored, perform no action'
        $sql = "SELECT sm.id, sm.name, sm.path folder, "
                . ($hasNextGen ? "CASE WHEN ng.gid IS NOT NULL THEN 'NextGen' ELSE 'Custom' END media_type, " : "'Custom' media_type, ")
                . "sm.status, sm.compression_type, sm.keep_exif, sm.cmyk2rgb, sm.resize, sm.resize_width, sm.resize_height, sm.message, sm.ts_added, sm.ts_optimized "
                . "FROM {$this->db->getPrefix()}shortpixel_meta sm "
                . "INNER JOIN  {$this->db->getPrefix()}shortpixel_folders sf on sm.folder_id = sf.id "
                . ($hasNextGen ? "LEFT JOIN {$this->db->getPrefix()}ngg_gallery ng on sf.path = ng.path " : " ")
                . "WHERE sf.status <> -1"; //  AND sm.status <> 3
        foreach($filters as $field => $value) {
            $sql .= " AND sm.$field " . $value->operator . " ". $value->value . " ";
        }
        $sql  .= ($orderby ? " ORDER BY $orderby $order " : "")
                . " LIMIT $count OFFSET " . ($page - 1) * $count;
        return $this->db->query($sql);
    }

    public function getPendingMetas($count) {
        return $this->db->query("SELECT sm.id from {$this->db->getPrefix()}shortpixel_meta sm "
            . "INNER JOIN  {$this->db->getPrefix()}shortpixel_folders sf on sm.folder_id = sf.id "
            . "WHERE sf.status <> -1 AND sm.status <> 3 AND ( sm.status = 0 OR sm.status = 1 OR (sm.status < 0 AND sm.retries < 3)) "
            . "ORDER BY sm.id DESC LIMIT $count");
    }

    public function getFolderOptimizationStatus($folderId) {
        $res = $this->db->query("SELECT SUM(CASE WHEN sm.status = 2 THEN 1 ELSE 0 END) Optimized, SUM(CASE WHEN sm.status = 1 THEN 1 ELSE 0 END) Pending, "
            . "SUM(CASE WHEN sm.status = 0 THEN 1 ELSE 0 END) Waiting, SUM(CASE WHEN sm.status < 0 THEN 1 ELSE 0 END) Failed, count(*) Total "
            . "FROM  {$this->db->getPrefix()}shortpixel_meta sm "
            . "INNER JOIN  {$this->db->getPrefix()}shortpixel_folders sf on sm.folder_id = sf.id "
            . "WHERE sf.id = $folderId");
        return $res[0];
    }

    public function getPendingMetaCount() {
        $res = $this->db->query("SELECT COUNT(sm.id) recCount from  {$this->db->getPrefix()}shortpixel_meta sm "
            . "INNER JOIN  {$this->db->getPrefix()}shortpixel_folders sf on sm.folder_id = sf.id "
            . "WHERE sf.status <> -1 AND sm.status <> 3 AND ( sm.status = 0 OR sm.status = 1 OR (sm.status < 0 AND sm.retries < 3))");
        return isset($res[0]->recCount) ? $res[0]->recCount : null;
    }

    /** Get all Custom Meta when status is other than 'restored' **/
    public function getPendingBulkRestore($count)
    {
      return $this->db->query("SELECT sm.id from {$this->db->getPrefix()}shortpixel_meta sm "
          . "INNER JOIN  {$this->db->getPrefix()}shortpixel_folders sf on sm.folder_id = sf.id "
          . "WHERE sf.status <> -1 AND sm.status =  " . ShortPixelMeta::FILE_STATUS_TORESTORE
          . " ORDER BY sm.id DESC LIMIT $count");
    }

    /** Sets files from folders that are selected for bulk restore to the status 'To Restore';
    *
    */
    public function setBulkRestore($folder_id)
    {
        LOG::addDebug('Set Bulk Restore', array('folderid' => $folder_id));
      if (! is_numeric($folder_id) || $folder_id <= 0)
        return false;

        $table = $this->db->getPrefix() . 'shortpixel_meta';
        //$sql = "UPDATE status on "; ShortPixelMeta::FILE_STATUS_TORESTORE
        $this->db->update($table, array('status' => ShortPixelMeta::FILE_STATUS_TORESTORE), array('folder_id' => $folder_id), '%d', '%d' );
    }


    public function getCustomMetaCount($filters = array()) {
      // [BS] Remove exclusion for sm.status <> 3. Status 3 is 'restored, perform no action'
        $sql = "SELECT COUNT(sm.id) recCount FROM {$this->db->getPrefix()}shortpixel_meta sm "
            . "INNER JOIN  {$this->db->getPrefix()}shortpixel_folders sf on sm.folder_id = sf.id "
            . "WHERE sf.status <> -1"; //  AND sm.status <> 3
        foreach($filters as $field => $value) {
            $sql .= " AND sm.$field " . $value->operator . " ". $value->value . " ";
        }

        $res = $this->db->query($sql);
        return isset($res[0]->recCount) ? $res[0]->recCount : 0;
    }

    public function getMeta($id, $deleted = false) {
        $sql = "SELECT * FROM {$this->db->getPrefix()}shortpixel_meta WHERE id = %d " . ($deleted ? "" : " AND status <> -1");
        $rows = $this->db->query($sql, array($id));
        foreach($rows as $row) {
            $meta = new ShortPixelMeta($row);
            if($meta->getPath()) {
                $meta->setWebPath(ShortPixelMetaFacade::filenameToRootRelative($meta->getPath()));
            }
            if ($meta->getStatus() == $meta::FILE_STATUS_UNPROCESSED)
            {
              $meta = $this->updateMetaWithSettings($meta);
            }
            //die(var_dump($meta)."ZA META");
            return $meta;
        }
        return null;
    }

    /** If File is not yet processed, don't use empty default meta, but use the global settings
    *
    * @param $meta ShortPixelMeta object
    * @return ShortPixelMetaObject - Replaced meta object data
    * @author Bas Schuiling
    */
    protected function updateMetaWithSettings($meta)
    {
      $objSettings = new WPShortPixelSettings();

      $meta->setKeepExif( $objSettings->keepExif );
      $meta->setCmyk2rgb($objSettings->CMYKtoRGBconversion);
      $meta->setResize($objSettings->resizeImages);
      $meta->setResizeWidth($objSettings->resizeWidth);
      $meta->setResizeHeight($objSettings->resizeHeight);
      $meta->setCompressionType($objSettings->compressionType);
      $meta->setBackup($objSettings->backupImages);
      // update the record. If the image is pending the meta will be requested again.
      $this->update($meta);
      return $meta;
    }


    public function getMetaForPath($path, $deleted = false) {
        $sql = "SELECT * FROM {$this->db->getPrefix()}shortpixel_meta WHERE path = %s " . ($deleted ? "" : " AND status <> -1");
        $rows = $this->db->query($sql, array($path));
        foreach($rows as $row) {
            return new ShortPixelMeta($row);
        }
        return null;
    }

    public function update($meta) {
        $metaClass = get_class($meta);
        //$tableSuffix = "";
        $tableSuffix = $metaClass::TABLE_SUFFIX;
        $prefix = $this->db->getPrefix();

//        eval( '$tableSuffix = ' . $metaClass . '::TABLE_SUFFIX;'); // horror!
        $sql = "UPDATE " . $prefix . "shortpixel_" . $tableSuffix . " SET ";
        foreach(self::$fields[$tableSuffix] as $field => $type) {
            $getter = "get" . ShortPixelTools::snakeToCamel($field);
            $val = $meta->$getter();
            if($meta->$getter() !== null) {
                $sql .= " {$field} = %{$type},";
                $params[] = $val;
            }
        }

        if(substr($sql, -1) != ',') {
            return; //no fields added;
        }

        $sql = rtrim($sql, ",");
        $sql .= " WHERE id = %d";
        $params[] = $meta->getId();
        Log::addDebug('Update Custom Meta' . $sql, $params);
        $this->db->query($sql, $params);
    }

    public function delete($meta) {
        $metaClass = get_class($meta);
        $tableSuffix = $metaClass::TABLE_SUFFIX;
        //eval( '$tableSuffix = ' . $metaClass . '::TABLE_SUFFIX;');
        $sql = "DELETE FROM {$this->db->getPrefix()}shortpixel_" . $tableSuffix . " WHERE id = %d";
        $this->db->query($sql, array($meta->getId()));
    }

    public function countAllProcessableFiles() {
        $sql = "SELECT count(*) totalFiles, sum(CASE WHEN status = 2 THEN 1 ELSE 0 END) totalProcessedFiles,"
              ." sum(CASE WHEN status = 2 AND compression_type = 1 THEN 1 ELSE 0 END) totalProcLossyFiles,"
              ." sum(CASE WHEN status = 2 AND compression_type = 2 THEN 1 ELSE 0 END) totalProcGlossyFiles,"
              ." sum(CASE WHEN status = 2 AND compression_type = 0 THEN 1 ELSE 0 END) totalProcLosslessFiles"
              ." FROM {$this->db->getPrefix()}shortpixel_meta WHERE status <> -1";
        $rows = $this->db->query($sql);

        $filesWithErrors = array();
        $sql = "SELECT id, name, path, message FROM {$this->db->getPrefix()}shortpixel_meta WHERE status < -1 AND retries >= 3 LIMIT 30";
        $failRows = $this->db->query($sql);
        $filesWithErrors = array(); $moreFilesWithErrors = 0;
        foreach($failRows as $failLine) {
            if(count($filesWithErrors) < 50){
                $filesWithErrors['C-' . $failLine->id] = array('Id' => 'C-' . $failLine->id, 'Name' => $failLine->name, 'Message' => $failLine->message, 'Path' => $failLine->path);
            } else {
                $moreFilesWithErrors++;
            }
        }

        if(!isset($rows[0])) {
            $rows[0] = (object)array('totalFiles' => 0, 'totalProcessedFiles' => 0, 'totalProcLossyFiles' => 0, 'totalProcGlossyFiles' => 0, 'totalProcLosslessFiles' => 0);
        }

        return array("totalFiles" => $rows[0]->totalFiles, "mainFiles" => $rows[0]->totalFiles,
                     "totalProcessedFiles" => $rows[0]->totalProcessedFiles, "mainProcessedFiles" => $rows[0]->totalProcessedFiles,
                     "totalProcLossyFiles" => $rows[0]->totalProcLossyFiles, "mainProcLossyFiles" => $rows[0]->totalProcLossyFiles,
                     "totalProcGlossyFiles" => $rows[0]->totalProcGlossyFiles, "mainProcGlossyFiles" => $rows[0]->totalProcGlossyFiles,
                     "totalProcLosslessFiles" => $rows[0]->totalProcLosslessFiles, "mainProcLosslessFiles" => $rows[0]->totalProcLosslessFiles,
                     "totalCustomFiles" => $rows[0]->totalFiles, "mainCustomFiles" => $rows[0]->totalFiles,
                     "totalProcessedCustomFiles" => $rows[0]->totalProcessedFiles, "mainProcessedCustomFiles" => $rows[0]->totalProcessedFiles,
                     "totalProcLossyCustomFiles" => $rows[0]->totalProcLossyFiles, "mainProcLossyCustomFiles" => $rows[0]->totalProcLossyFiles,
                     "totalProcGlossyCustomFiles" => $rows[0]->totalProcGlossyFiles, "mainProcGlossyCustomFiles" => $rows[0]->totalProcGlossyFiles,
                     "totalProcLosslessCustomFiles" => $rows[0]->totalProcLosslessFiles, "mainProcLosslessCustomFiles" => $rows[0]->totalProcLosslessFiles,
                     "filesWithErrors" => $filesWithErrors,
                     "moreFilesWithErrors" => $moreFilesWithErrors
                    );

    }
}
