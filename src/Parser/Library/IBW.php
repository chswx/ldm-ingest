<?php
/*
 * Not Irritable Bowel Weather, Impact-Based Warnings.
 */

namespace UpdraftNetworks\Parser\Library;
use UpdraftNetworks\Utils as Utils;

class IBW {
    var $tornado;
    var $wind;
    var $hail;
    var $tornado_damage;
    var $waterspout;
    var $hazard;
    var $source;
    var $impact;

    /**
     * Constructor.
     * Brings in segment text and extracts metadata and other impact-based warning data.
     * @param string $segment_text Block of text coming in for examination.
     * @return IBW
     */
    function __construct($segment_text) {
        $this->tornado = $this->find_metadata($segment_text, 'tornado');
        $this->wind = $this->find_metadata($segment_text,'wind');
        $this->hail = $this->find_metadata($segment_text,'hail');
        $this->tornado_damage = $this->find_metadata($segment_text,'tornado damage threat');
        $this->waterspout = $this->find_metadata($segment_text,'waterspout');
        $this->hazard = $this->find_impacts_in_text($segment_text,"hazard");
        $this->source = $this->find_impacts_in_text($segment_text,"source");
        $this->impact = $this->find_impacts_in_text($segment_text,"impact");
    }

    function find_metadata($text, $type) {
        $type = strtoupper($type);
        if(preg_match("/$type\.\.\.(.*)/",$text,$matches)) {
            Utils::log(print_r($matches));
        } else {
            return null;
        }

        return $matches[1];
    }

    function find_impacts_in_text($text, $type) {
        $type = strtoupper($type);
        
    }

}
