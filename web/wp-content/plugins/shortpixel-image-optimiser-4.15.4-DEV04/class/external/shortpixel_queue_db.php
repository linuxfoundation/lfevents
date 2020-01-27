<?php
use ShortPixel\ShortPixelLogger\ShortPixelLogger as Log;

class ShortPixelQueueDB extends ShortPixelQueue{

    protected $ctrl;
    protected $settings;

    const THE_OPTION = 'shortpixel_prioq';
    const THE_TRANSIENT = 'shortpixel_prioq_lock';

    const BULK_TYPE_OPTIMIZE = 0;
    const BULK_TYPE_RESTORE = 1;
    const BULK_TYPE_CLEANUP = 2;
    const BULK_TYPE_CLEANUP_PENDING = 3;

    const BULK_NEVER = 0; //bulk never ran
    const BULK_RUNNING = 1; //bulk is running
    const BULK_PAUSED = 2; //bulk is paused
    const BULK_FINISHED = 3; //bulk finished

    public function __construct($controller, $settings) {
        $this->ctrl = $controller;
        $this->settings = $settings;
        //parent::__construct($controller, $settings);
    }

    //handling older
/*    public function ShortPixelQueue($controller) {
        $this->__construct($controller);
    }
*/

// @todo Replace
    public static function get() {
        $queue = self::openQ(LOCK_SH);
        if($queue === false) return array();

        Log::addDebug('DBQ - Get ' . $queue);
        $itemsRaw = $queue;
        $items = strlen($itemsRaw) ? self::parseQ($itemsRaw) : array();
        $fp = null;
        self::closeQ($fp);

        return $items;
    }

// @todo Replace
    public static function set($items) {

        $queue = self::openQ();
        if($queue === false) return false;

/*        fseek($fp, 0);
        ftruncate($fp, 0);      // truncate file
        fwrite($fp, implode(',', $items));
        fflush($fp);            // flush output before releasing the lock */
        Log::addDebug('DBQ - Set ' . implode(',', $items));
        update_option(self::THE_OPTION, implode(',', $items), false);

        $fp =null;
        self::closeQ($fp);
        return true;
    }

// @todo Replace
    public function apply($callable, $extra = false) {
        $queue = self::openQ();
        if($queue === false) return false;

        $itemsRaw = $queue;
        Log::addDebug('Apply' . $itemsRaw);
        $items = strlen($itemsRaw) ? self::parseQ($itemsRaw) : array();
        if($extra) {
            $items = call_user_func($callable, $items, $extra);
        } else {
            $items = call_user_func($callable, $items);
        }

        update_option(self::THE_OPTION, implode(',',$items), false);
        /*fseek($fp, 0);
        ftruncate($fp, 0);      // truncate file
        fwrite($fp, implode(',', $items));
        fflush($fp);            // flush output before releasing the lock */
        $fp = null;
        self::closeQ($fp);
        return $items;
    }

    public static function testQ() {
        $fp = self::openQ();
        if($fp === false) return false;
        self::closeQ($fp);
        return true;
    }


// @todo Replace - main thing here.
    protected static function openQ($lock = LOCK_EX) {

        //$queueName = SHORTPIXEL_UPLOADS_BASE . "/.shortpixel-q-" . get_current_blog_id();
        $trans = get_transient(self::THE_TRANSIENT);
        Log::addDebug('OpenQ', array($trans));
        if (! $trans === false) // if lock, then no beans.
          return false;

        Log::addDebug('OpenQ -- opened');

        wp_cache_delete( self::THE_OPTION, 'options' ); // ensure uncached goodness here.
        $queue = get_option(self::THE_OPTION, '');

        set_transient(self::THE_TRANSIENT, 'true', 60);
        return $queue;

/*         $fp = @fopen($queueName, "r+");
        if(!$fp) {
            $fp = @fopen($queueName, "w");
            if(!$fp) return false;
        }

        flock($fp, $lock);
        return $fp; */
    }

// @todo replace
    protected static function closeQ($fp) {
        Log::addDebug('CloseQ');
        delete_transient(self::THE_TRANSIENT);

      //  flock($fp, LOCK_UN);    // release the lock
      //  fclose($fp);
    }

    public static function resetPrio() {
        //delete_option( "wp-short-pixel-priorityQueue");
        self::set(array());
    }

    public function  processing() {
        //WPShortPixel::log("QUEUE: processing(): get:" . json_encode($this->get()));
        return $this->bulkRunning() || count($this->get());
    }


/*    protected static function parseQ($items) {
        return explode(',', preg_replace("/[^0-9,C-]/", "", $items));
    }
*/

/*    public function skip($id) {
        if(is_array($this->settings->prioritySkip)) {
            $this->settings->prioritySkip = array_merge($this->settings->prioritySkip, array($id));
        } else {
            $this->settings->prioritySkip = array($id);
        }
    }
*/
/*    public function unskip($id) {
        $prioSkip = $this->settings->prioritySkip;
        $this->settings->prioritySkip = is_array($prioSkip) ? array_diff($prioSkip, array($id)) : array();
    }
*/
/*    public function allSkipped() {
        if( !is_array($this->settings->prioritySkip) ) return false;
        count(array_diff($this->get(), $this->settings->prioritySkip));
    }
*/
/*    public function skippedCount() {
        return is_array($this->settings->prioritySkip) ? count($this->settings->prioritySkip) : 0;
    }
*/
/*    public function isSkipped($id) {
        return is_array($this->settings->prioritySkip) && in_array($id, $this->settings->prioritySkip);
    }
*/
  /*  public function isPrio($id) {
        $prioItems = $this->get();
        return is_array($prioItems) && in_array($id, $prioItems);
    }
*/
/*    public function getSkipped() {
        return $this->settings->prioritySkip;
    }
*/
/*    public function reverse() {
        $this->apply('array_reverse');
        //$this->settings->priorityQueue = $_SESSION["wp-short-pixel-priorityQueue"] = array_reverse($_SESSION["wp-short-pixel-priorityQueue"]);

    }
*/
/*    protected function pushCallback($priorityQueue, $ID) {
        WPShortPixel::log("PUSH: Push ID $ID into queue " . json_encode($priorityQueue));
        array_push($priorityQueue, $ID);
        $prioQ = array_unique($priorityQueue);
        WPShortPixel::log("PUSH: Updated: " . json_encode($prioQ));//get_option("wp-short-pixel-priorityQueue")));
        return $prioQ;
    }
*/

/*    public function push($ID)//add an ID to priority queue
    {
        $this->apply(array(&$this, 'pushCallback'), $ID);
    }
*/
/*    protected function enqueueCallback($priorityQueue, $ID) {
        WPShortPixel::log("ENQUEUE: Enqueue ID $ID into queue " . json_encode($priorityQueue));
        array_unshift($priorityQueue, $ID);
        $prioQ = array_unique($priorityQueue);
        WPShortPixel::log("ENQUEUE: Updated: " . json_encode($prioQ));//get_option("wp-short-pixel-priorityQueue")));
        return $prioQ;
    }
*/
/*    public function enqueue($ID)//add an ID to priority queue as LAST
    {
        $this->apply(array(&$this, 'enqueueCallback'), $ID);
    }
*/
/*    public function getFirst($count = 1)//return the first values added to priority queue
    {
        $priorityQueue = $this->get();
        $count = min(count($priorityQueue), $count);
        return(array_slice($priorityQueue, count($priorityQueue) - $count, $count));
    }
*/
/*    public function getFromPrioAndCheck() {
        $idsPrio = $this->get();

        $ids = array();
        $removeIds = array();
        for($i = count($idsPrio) - 1, $cnt = 0; $i>=0 && $cnt < 3; $i--) {
            if(!isset($idsPrio[$i])) continue; //saw this situation but then couldn't reproduce it to see the cause, so at least treat the effects.
            $id = $idsPrio[$i];
            if(!$this->isSkipped($id) && $this->ctrl->isValidMetaId($id)) {
                $ids[] = $id; //valid ID
                $cnt++;
            } elseif(!$this->isSkipped($id)) {
                $removeIds[] = $id;//not skipped, url not found, means it's absent, to remove
            }
        }
        foreach($removeIds as $rId){
            WPShortPixel::log("HIP: Unfound ID $rId Remove from Priority Queue: ".json_encode($this->get()));
            $this->remove($rId);
        }
        return $ids;
    }
*/

// @todo Replace
    public function remove($ID)//remove an ID from priority queue
    {
        $queue = $this->openQ();
        if($queue === false) return false;
        $items = $queue;
        $items = self::parseQ($items);
        $items = is_array($items) ? $items : array();
        $newItems = array();
        $found = false;
        foreach($items as $item) { // this instead of array_values(array_diff(.. because we need to know if we actually removed it
            if($item != $ID) {
                $newItems[] = $item;
            } else {
                $found = true;
            }
        }
        if($found) {
          /*  fseek($fp, 0);
            ftruncate($fp, 0);
            fwrite($fp, implode(',', $newItems));
            fflush($fp);            // flush output before releasing the lock */
            update_option(self::THE_OPTION, implode(',',$newItems), false);
            Log::addDebug('DBQ - Found and Removing ' . $ID);
        }
        $fp = null;
        $this->closeQ($fp);
        return $found;
    }
/*
    public function removeFromFailed($ID) {
        $failed = explode(",", $this->settings->failedImages);
        $key = array_search($ID, $failed);
        if($key !== false) {
            unset($failed[$key]);
            $failed = array_values($failed);
            $this->settings->failedImages = implode(",", $failed) ;
        }
    }
*/
/*
    public function addToFailed($ID) {
        $failed = $this->settings->failedImages;
        if(!in_array($ID, explode(",", $failed))) {
            $this->settings->failedImages = (strlen($failed) ? $failed . "," : "") . $ID;
        }
    }
*/

/*
    public function getFailed() {
        $failed = $this->settings->failedImages;
        if(!strlen($failed)) return array();
        $ret = explode(",", $failed);
        $fails = array();
        foreach($ret as $fail) {
            if(ShortPixelMetaFacade::isCustomQueuedId($fail)) {
                $meta = $this->ctrl->getSpMetaDao()->getMeta(ShortPixelMetaFacade::stripQueuedIdType($fail));
                if($meta) {
                    $fails[] = (object)array("id" => ShortPixelMetaFacade::stripQueuedIdType($fail), "type" => ShortPixelMetaFacade::CUSTOM_TYPE, "meta" => $meta);
                }
            } else {
                $meta = wp_get_attachment_metadata($fail);
                if(!$meta || (isset($meta["ShortPixelImprovement"]) && is_numeric($meta["ShortPixelImprovement"]))){
                    $this->removeFromFailed($fail);
                } else {
                    $fails[] = (object)array("id" => $fail, "type" => ShortPixelMetaFacade::MEDIA_LIBRARY_TYPE, "meta" => $meta);
                }
            }
        }
        return $fails;
    }
*/
/*
    public function bulkRunning() {
        //$bulkProcessingStatus = get_option('bulkProcessingStatus');
        return $this->settings->startBulkId > $this->settings->stopBulkId;
    }
*/



}
