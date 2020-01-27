<?php

class ShortPixelMeta extends ShortPixelEntity{
    const META_VERSION = 1;

    protected $id;
    protected $folderId;
    protected $extMetaId;
    protected $path;
    protected $name;
    protected $webPath;
    protected $compressionType;
    protected $compressedSize;
    protected $png2Jpg;
    protected $thumbsOpt;
    protected $thumbsOptList;
    protected $excludeSizes;
    protected $thumbsMissing;
    protected $retinasOpt;
    protected $thumbsTodo;
    protected $keepExif;
    protected $cmyk2rgb;
    protected $resize;
    protected $resizeWidth;
    protected $resizeHeight;
    protected $actualWidth;
    protected $actualHeight;
    protected $backup;
    protected $status; //0 waiting, 1 pending, 2 success, -x errors
    protected $retries;
    protected $message;
    protected $tsAdded;
    protected $tsOptimized;
    protected $thumbs;

    const TABLE_SUFFIX = 'meta';
    const WEBP_THUMB_PREFIX = 'sp-webp-';
    const FOUND_THUMB_PREFIX = 'sp-found-';

    // [BS] Attempt to shed some light on Meta Status on File.
    // Anything lower than 0 is a processing error.
    const FILE_STATUS_UNPROCESSED = 0;
    const FILE_STATUS_PENDING = 1;
    const FILE_STATUS_SUCCESS = 2;
    const FILE_STATUS_RESTORED = 3;
    const FILE_STATUS_TORESTORE = 4; // Used for Bulk Restore

    public function __construct($data = array()) {
        parent::__construct($data);
    }

    /**
     * @return meta string to be embedded into the image
     */
    public function getCompressedMeta() {
        //To be implemented
        return base64_encode("Not now John.");
    }

    function getImprovementPercent() {
        if(is_numeric($this->message)) {
            return round($this->message,2);
        }
        return 0;
    }

    function getId() {
        return $this->id;
    }

    function getPath() {
        return $this->path;
    }
    function getWebPath() {
        return $this->webPath;
    }

    function setWebPath($webPath) {
        $this->webPath = $webPath;
    }

        function getPathMd5() {
        return md5($this->path);
    }

    function getFolderId() {
        return $this->folderId;
    }

    function setFolderId($folderId) {
        $this->folderId = $folderId;
    }

    function getExtMetaId() {
        return $this->extMetaId;
    }

    function setExtMetaId($extMetaId) {
        $this->extMetaId = $extMetaId;
    }

    function getCompressionType() {
        return $this->compressionType;
    }

    function getKeepExif() {
        return $this->keepExif;
    }

    function getBackup() {
        return $this->backup;
    }

    function getTsOptimized() {
        return $this->tsOptimized;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setPath($path) {
        $this->path = $path;
    }

    function getName() {
        return $this->name;
    }

    function getCompressedSize() {
        return $this->compressedSize;
    }

    function getPng2Jpg() {
        return $this->png2Jpg;
    }

    function setPng2Jpg($png2jpg) {
        $this->png2Jpg = $png2jpg;
    }

    function setName($name) {
        $this->name = $name;
    }

    function setCompressedSize($compressedSize) {
        $this->compressedSize = $compressedSize;
    }

    function setCompressionType($compressionType) {
        $this->compressionType = $compressionType;
    }

    function getThumbsOpt() {
        return $this->thumbsOpt;
    }

    function setThumbsOpt($thumbsOpt) {
        $this->thumbsOpt = $thumbsOpt;
    }

    function getThumbsOptList() {
        return $this->thumbsOptList;
    }

    /** @todo There is only one function using this in Shortpixel API, but getting the values from the same class */
    function setThumbsOptList($thumbsOptList) {
        $this->thumbsOptList = $thumbsOptList;
    }

    /**
     * @return mixed
     */
    public function getExcludeSizes()
    {
        return $this->excludeSizes;
    }

    /**
     * @param mixed $excludeSizes
     */
    public function setExcludeSizes($excludeSizes)
    {
        $this->excludeSizes = $excludeSizes;
    }

    function getThumbsMissing() {
        return $this->thumbsMissing;
    }

    function setThumbsMissing($thumbsMissing) {
        $this->thumbsMissing = $thumbsMissing;
    }

    function getRetinasOpt() {
        return $this->retinasOpt;
    }

    function setRetinasOpt($retinasOpt) {
        $this->retinasOpt = $retinasOpt;
    }

        function getThumbsTodo() {
        return $this->thumbsTodo;
    }

    function setThumbsTodo($thumbsTodo) {
        $this->thumbsTodo = $thumbsTodo;
    }

    function setKeepExif($keepExif) {
        $this->keepExif = $keepExif;
    }

    function setBackup($backup) {
        $this->backup = $backup;
    }

    function setTsOptimized($tsOptimized) {
        $this->tsOptimized = $tsOptimized;
    }

    function getTsAdded() {
        return $this->tsAdded;
    }

    function setTsAdded($tsAdded) {
        $this->tsAdded = $tsAdded;
    }

    function getCmyk2rgb() {
        return $this->cmyk2rgb;
    }

    function getResize() {
        return $this->resize;
    }

    function getResizeWidth() {
        return $this->resizeWidth;
    }

    function getResizeHeight() {
        return $this->resizeHeight;
    }

    function getActualWidth() {
        return $this->actualWidth;
    }

    function getActualHeight() {
        return $this->actualHeight;
    }

    function setActualWidth($actualWidth) {
        $this->actualWidth = $actualWidth;
    }

    function setActualHeight($actualHeight) {
        $this->actualHeight = $actualHeight;
    }

    function setCmyk2rgb($cmyk2rgb) {
        $this->cmyk2rgb = $cmyk2rgb;
    }

    function setResize($resize) {
        $this->resize = $resize;
    }

    function setResizeWidth($resizeWidth) {
        $this->resizeWidth = $resizeWidth;
    }

    function setResizeHeight($resizeHeight) {
        $this->resizeHeight = $resizeHeight;
    }

    function getStatus() {
        return $this->status;
    }

    function getMessage() {
        return $this->message;
    }

    function getRetries() {
        return $this->retries;
    }

    function setRetries($retries) {
        $this->retries = $retries;
    }

    function setStatus($status) {
        $this->status = $status;
    }

    function setMessage($message) {
        $this->message = $message;
    }

    function getType() {
        return (isset($this->folderId) ? ShortPixelMetaFacade::CUSTOM_TYPE : ShortPixelMetaFacade::MEDIA_LIBRARY_TYPE);
    }

    function getThumbs() {
        return $this->thumbs;
    }

    function setThumbs($thumbs) {
        $this->thumbs = $thumbs;
    }

    function addThumbs($thumbs) {
        $this->thumbs = array_merge($this->thumbs, $thumbs);
    }
}
