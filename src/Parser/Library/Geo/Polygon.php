<?php
/**
 * Polygon definition
 * Very much like a Point, just with multiple points
 */

namespace UpdraftNetworks\Parser\Library\Geo;

class Polygon {
    /**
     * Polygon coordinates; an array of points
     *
     * @var array
     */
    var $coordinates = array();

    /**
     * Type of item (for GeoJSON)
     *
     * @var string
     */
    var $type;

    /**
     * Constructor.
     * Takes in an array of coordinates and sets up a GeoJSON-compatible object.
     *
     * @param array $coords_arr Array of coordinates to pass into the constructor
     */
    function __construct($coords_arr) {
        $this->type = "Polygon";
        // Make sure the first coordinate is also the last
        array_push($coords_arr, $coords_arr[0]);
        $this->coordinates = array($coords_arr);
    }

    function to_array() {
        return array('type' => $this->type, 'coordinates' => $this->coordinates);
    }
}
