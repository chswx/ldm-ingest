<?php

namespace UpdraftNetworks\Storage;
use r;

class ProductStorage {
    static function send($product) {
        //var_dump($product);
        $conn = r\connect('localhost');

        //r\db("test")->tableCreate("tacos")->run($conn);
        
        // Today in PHP Is Terrible: Encoding and then decoding the product to get an object->array conversion
        // Seems to be the only way this will work!
        $result = r\table("tacos")->insert(json_decode(json_encode($product)))->run($conn);
        var_dump($result);
    }
}
