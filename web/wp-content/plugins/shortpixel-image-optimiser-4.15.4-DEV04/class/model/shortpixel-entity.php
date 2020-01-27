<?php

class ShortPixelEntity{
    public function __construct($data) {
        if(is_object($data)) {
            $dataArr = get_object_vars($data);
        } elseif(is_array($data)) {
            $dataArr = $data;
        } else {
            return;
        }
        foreach($dataArr as $key => $val) {
            $setter = 'set' . ShortPixelTools::snakeToCamel($key);

            if(method_exists($this, $setter)) {
                $this->$setter($val);
            }
        }
    }
}
