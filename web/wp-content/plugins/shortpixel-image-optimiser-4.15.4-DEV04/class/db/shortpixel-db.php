<?php

interface ShortPixelDb {
    
    public static function createUpdateSchema($tableDefinitions);
    public function getPrefix();
    public function query($sql);
    public function getCharsetCollate();    
}
