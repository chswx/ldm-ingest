<?php
/**
 * TCM product
 * The first product to be issued when advisories are initiated or updated for a tropical cyclone.
 * Use this product to get the vitals of a tropical cyclone into the system.
 * Subsequent products (such as the public advisory, discussion, etc.) expound on this
 */

namespace UpdraftNetworks\Parser\Library\NHC;
use UpdraftNetworks\Utils as Utils;

class TCM {
    /**
     * @var string Tropical Depression, Tropical Storm, or Hurricane
     */
    var $classification;

    /**
     * @var string Name of the system.
     */
    var $name;

    /**
     * @var int Advisory number.
     */
    var $advisory_num;
    /**
     * @var string Intermediate update letter (A, B, C)
     */
    var $intermediate;

    /**
     * @var int Storm number
     */
    var $storm_num;

    /**
     * @var int Storm year
     */
    var $storm_year;

    /**
     * @var array Array of coordinates signifying the tropical cyclone's current position
     */
    var $position;

    /**
     * @var array Vector of movement
     */
    var $movement;

    /**
     * @var int Intensity in knots
     */
    var $intensity;

    /**
     * @var int Minimum central pressure in millibars
     */
    var $pressure;

    /**
     * @var array Series of points containing the forecast track
     */
    var $forecast_track;

    /**
     * @var int Timestamp for the next advisory
     */
    var $next_advisory;

    function __construct($product_text) {

    }

    function parse($text) {

    }
}
