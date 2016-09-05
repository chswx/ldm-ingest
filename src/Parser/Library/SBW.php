<?php
/**
 * Storm-based warnings
 * Respect the polygon!
 */

namespace UpdraftNetworks\Parser\Library;
use UpdraftNetworks\Utils as Utils;
use UpdraftNetworks\Parser\Library\Geo as Geo;

class SBW {
    var $polygon = array();
    
    function __construct($text) {
        $this->polygon = $this->find_polygon($text);
    }

    /**
     * Takes segment text and finds a Storm-Based Warning polygon.
     * @param string $text Segment text to check
     * @return Polygon|null
     */ 
    function find_polygon($text) {
        // Get the product on one line and remove extra indenting spaces for maximum parsability.
        $sanitized_text = Utils::deindent(Utils::strip_newlines($text));
        // Get a clean LAT...LON string devoid of any other point types (primarily those found in TIME...MOT...LOC) to ensure quality polygons
        $clean_lat_lon_string = explode("TIME...MOT...LOC",$sanitized_text);
        // If we have matches, loop through and convert them to normal coordinates
        if(preg_match_all('/(\d{4}\s\d{4})/',$clean_lat_lon_string[0],$matches)) { 
            $coords_arr = array();
            foreach($matches[0] as $point) {
                $coords_arr[] = Utils::convert_coords_to_geojson($point);
            }
            
            // Return a GeoJSON polygon.

            return new Geo\Polygon($coords_arr);
        }

        // Null if we don't have one
        return null;
    }
}
