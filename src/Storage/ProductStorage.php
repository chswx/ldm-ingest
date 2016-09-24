<?php

namespace UpdraftNetworks\Storage;
use r;

class ProductStorage {
    var $conn;

    function __construct() {
        $this->conn = r\connect('localhost');   // TODO: make configurable
        $this->conn->useDb('updraft');
    }

    /**
     * Inserts a product into the database.
     * @param $product mixed Array of product data to be inserted into the database
     * @param $table string Table to write to
     */ 
    function send($product, $table = 'products') {
        $product_class = get_class($product);
        // Today in PHP Is Terrible: Encoding and then decoding the product to get an object->array conversion
        // Seems to be the only way this will work!
        $encoded_product = json_decode(json_encode($product));
        $encoded_product = $this->prepare_location_data($encoded_product, $product_class);
        $result = r\table($table)->insert($encoded_product)->run($this->conn);
    }

    /**
     * Updates a record with additional information.
     * TODO: Implement
     * @param $product mixed Array of product data to attach to the record
     * @param $record 
     */
    function update($product, $record) {
        return; 
    }

    /**
     * Prepares location data for RethinkDB. 
     * RethinkDB as of 2.3.x does not support GeoJSON natively.
     * @param $product Product object of varying shapes
     * @return Prepared object for database insertion
     */
    function prepare_location_data($product, $product_class) {
        switch($product_class) {
        case 'UpdraftNetworks\Parser\VTEC':
            $prepped = $this->_prepare_vtec($product);
            break;
        }
    
        return $product;
    }

    private function _prepare_vtec($product) {
        $prepped_segments = array();
        foreach($product->segments as $segment) {
            if(isset($segment->smv->location)) {
                $segment->smv->location = r\geojson((array)$segment->smv->location);
            }
            if(isset($segment->polygon)) {
                $segment->polygon = r\geojson((array)$segment->polygon);
            }
            $prepped_segments[] = $segment;
        }

        $product->segments = $prepped_segments;

        return $product;
    }

}
