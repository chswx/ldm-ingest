<?php
/**
 * Polygon definition
 * Very much like a Point, just with multiple points
 */

namespace UpdraftNetworks\Parser\Library\Geo;

class Polygon {
    /**
     * Polygon coordinates; an array of points
     * @var array
     */
    var $coordinates = array();

    /**
     * Type of item (for GeoJSON)
     * @var string
     */
    var $type;

    /**
     * Constructor.
     * Takes in an array of coordinates and sets up a GeoJSON-compatible object.
     * @param array $coords_arr Array of coordinates to pass into the constructor
     */
    function __construct($coords_arr) {
        $this->type = "Polygon";
        $this->coordinates = $coords_arr;
    }
}
