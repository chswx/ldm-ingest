<?php

namespace UpdraftNetworks\Storage;
use r;

class ProductStorage {
    var $conn;

    function __construct() {
        $this->conn = r\connect('localhost');   // TODO: make configurable
    }

    /**
     * Inserts a product into the database.
     * @param $product mixed Array of product data to be inserted into the database
     * @param $table string Table to write to
     */ 
    function send($product, $table) {
        // Today in PHP Is Terrible: Encoding and then decoding the product to get an object->array conversion
        // Seems to be the only way this will work!
        $result = r\table($table)->insert(json_decode(json_encode($product)))->run($this->conn);
    }

    /**
     * Updates a record with additional information.
     * @param $product mixed Array of product data to attach to the record
     * @param $record 
     */
    function update($product, $record) {
        return; 
    }

}
