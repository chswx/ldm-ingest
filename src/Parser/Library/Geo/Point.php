<?php
/**
 * Get lat and lon from the weird coordinates that are in the end of NWS products.
 */

namespace UpdraftNetworks\Parser\Library\Geo;

class Point {
    /**
     * Array of coordinates to output to GeoJSON.
     * @var array
     */
    var $coordinates = array();

    /**
     * GeoJSON type.
     * In this case, will always be a point.
     */ 

    var $type;
    
    /**
     * Constructor.
     * Takes a string of coordinates and returns a GeoCoords object that is compatible with GeoJSON.
     */
    function __construct($coords) {
        $this->type = "Point";
        $this->coordinates = $this->_convert_coords_to_geojson($coords);
        
        return $this;
    }

    protected function _convert_coords_to_geojson($coords) {
        // Take the format LLLL OOOO
        // Explode into array
        $coords_arr = explode(" ", $coords);
        
        // Expand lat/long into regular coordinates
        // Easiest way is to coerce these into ints and then divide by 100
        // Note: In GeoJSON, it's lon then lat; not lat lon
        $coords_prepped = array(((int)$coords_arr[1] / -100),((int)$coords_arr[0] / 100));

        return $coords_prepped;
    }
}
