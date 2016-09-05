<?php
/**
 * Storm-based warnings
 * Respect the polygon!
 */

namespace UpdraftNetworks\Parser\Library;
use UpdraftNetworks\Utils as Utils;

class SBW {
    function __construct($text) {
        $this->polygon = $this->find_polygon($text);
    }
    
    function find_polygon($text) {
        preg_match('/LAT\.\.\.LON\ ((\d|\s|\n)*)/',$text,$matches);
        Utils::log(print_r($matches));
        $sanitized_matches = trim(preg_replace('/\s+/',' ',$matches[1]));
        //$coords = explode(" ",$sanitized_matches);
        //Utils::log(print_r($coords));
        Utils::log(print_r($sanitized_matches));
        $coords = explode(" ",$sanitized_matches);
        Utils::log(print_r($coords));

    }
}
