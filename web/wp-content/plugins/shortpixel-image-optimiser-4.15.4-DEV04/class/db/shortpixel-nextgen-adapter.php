<?php

class ShortPixelNextGenAdapter {
    public static function getGalleries () {
        global $wpdb;
        return array_map(array('ShortPixelNextGenAdapter','path'), $wpdb->get_results("SELECT path FROM {$wpdb->prefix}ngg_gallery"));  
    }
    
    public static function hasNextGen() {
        global $wpdb;
        $nggTable = $wpdb->get_results("SELECT COUNT(1) hasNggTable FROM information_schema.tables WHERE table_schema='{$wpdb->dbname}' AND table_name='{$wpdb->prefix}ngg_gallery'");
        if(isset($nggTable[0]->hasNggTable) && $nggTable[0]->hasNggTable > 0) {
            return true;
        }
        return false;        
    }
    
    public static function getImageAbspath($image) {
        $storage = C_Gallery_Storage::get_instance();
        return $storage->get_image_abspath($image);        
    }
    
    public static function updateImageSize($nggId, $path) {
        $mapper = C_Image_Mapper::get_instance();
        $image = $mapper->find($nggId);
        $dimensions = getimagesize(self::getImageAbspath($image));
        $size_meta = array('width' => $dimensions[0], 'height' => $dimensions[1]);
        $image->meta_data = array_merge($image->meta_data, $size_meta);
        $image->meta_data['full'] = $size_meta;
        $mapper->save($image);
    }
    
    public static function pathToAbsolute($item) {
        return str_replace('//', '/', get_home_path() . $item);
    }
    
    public static function path($item) {
        return $item->path;
    }
}
