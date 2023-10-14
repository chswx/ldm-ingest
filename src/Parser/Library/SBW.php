<?php

/**
 * Storm-based warnings
 * Respect the polygon!
 */

namespace chswx\LDMIngest\Parser\Library;

use chswx\LDMIngest\Utils;
use chswx\LDMIngest\Parser\Library\Geo;

class SBW
{
    public $polygon = array();

    public function __construct($text)
    {
        $this->polygon = $this->findPolygon($text);
    }

    /**
     * Takes segment text and finds a Storm-Based Warning polygon.
     *
     * @param string $text Segment text to check
     *
     * @return Polygon|array
     */
    public function findPolygon($text)
    {
        // Get the product on one line and remove extra indenting spaces for maximum parsability.
        $sanitized_text = Utils::deindent(Utils::stripNewlines($text));
        // Get a clean LAT...LON string devoid of any other point types
        // (primarily those found in TIME...MOT...LOC) to ensure quality polygons
        $clean_lat_lon_string = explode("TIME...MOT...LOC", $sanitized_text);
        // If we have matches, loop through and convert them to normal coordinates
        if (preg_match_all('/(\d{4}\s\d{4})/', $clean_lat_lon_string[0], $matches)) {
            $coords_arr = array();
            foreach ($matches[0] as $point) {
                $coords_arr[] = Utils::convertCoordsToGeojson($point);
            }

            // Return a GeoJSON polygon.
            return (new Geo\Polygon($coords_arr));
        }

        // Empty array if we don't have one
        // Fixes downstream crasher where we aren't expecting Null
        return [];
    }
}
