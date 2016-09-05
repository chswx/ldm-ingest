<?php
/**
 * Get lat and lon from the weird coordinates that are in the end of NWS products.
 */

namespace UpdraftNetworks\Parser\Library\Geo;
use UpdraftNetworks\Utils as Utils;

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
        $this->coordinates = Utils::convert_coords_to_geojson($coords);
        
        return $this;
    }

    function to_array() {
        return array('coordinates'=>$this->coordinates,'type'=>$this->type);
    }

}
