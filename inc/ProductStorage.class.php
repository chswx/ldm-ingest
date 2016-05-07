<?php

class ProductStorage {
    static function send($product) {
        //var_dump($product);
        $conn = r\connect('localhost');

        //r\db("test")->tableCreate("tacos")->run($conn);
        
        $result = r\table("tacos")->insert(json_decode(json_encode($product)))->run($conn);
        var_dump($result);
    }
}
