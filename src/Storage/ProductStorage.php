<?php

namespace UpdraftNetworks\Storage;

use r;
use UpdraftNetworks\Utils as Utils;

class ProductStorage
{
    public $conn;

    public function __construct()
    {
        try {
            $this->conn = r\connect('localhost');   // TODO: make configurable
            $this->conn->useDb('chswx');
        } catch (Exception $e) {
            Utils::log("Error when trying to initialize the database: " . $e->getMessage());
        }
    }

    /**
     * Inserts a product into the database.
     *
     * @param $product mixed Array of product data to be inserted into the database
     * @param $table   string Table to write to (default is 'products')
     */
    public function send($product, $table = 'products')
    {
        $product_class = get_class($product);
        // Today in PHP Is Terrible: Encoding and then decoding the product to get an object->array conversion
        // Seems to be the only way this will work!
        $encoded_product = json_decode(json_encode($product));
        $encoded_product = $this->prepareLocationData($encoded_product, $product_class);
        $result = r\table($table)->insert($encoded_product)->run($this->conn);
    }

    /**
     * Updates a record with additional information.
     * TODO: Implement
     *
     * @param $product mixed Array of product data to attach to the record
     * @param $record
     */
    public function update($product, $record)
    {
        return;
    }

    /**
     * Prepares location data for RethinkDB.
     * RethinkDB as of 2.3.x does not support GeoJSON natively.
     *
     * @param $product Product object of varying shapes
     *
     * @return Prepared object for database insertion
     */
    public function prepareLocationData($product, $product_class)
    {
        switch ($product_class) {
            case 'UpdraftNetworks\Parser\VTEC':
                $product = $this->prepareVtec($product);
                break;
        }

        return $product;
    }

    private function prepareVtec($product)
    {
        $prepped_segments = array();
        foreach ($product->segments as $segment) {
            if (isset($segment->smv->location)) {
                $segment->smv->location = r\geojson((array)$segment->smv->location);
            }
            if (isset($segment->polygon)) {
                $segment->polygon = r\geojson((array)$segment->polygon);
            }
            $prepped_segments[] = $segment;
        }

        $product->segments = $prepped_segments;

        return $product;
    }
}
