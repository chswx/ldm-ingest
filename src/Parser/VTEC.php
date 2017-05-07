<?php
/**
 * VTEC parser.
 * Designed specifically for parsing VTEC-enabled products.
 */

namespace UpdraftNetworks\Parser;

use UpdraftNetworks\Parser\NWSProduct as NWSProduct;
use UpdraftNetworks\Parser\NWSProductSegment as NWSProductSegment;
use UpdraftNetworks\Parser\Library\VTECString as VTECString;
use UpdraftNetworks\Parser\Library\SMVString as SMVString;
use UpdraftNetworks\Parser\Library\IBW as IBW;
use UpdraftNetworks\Parser\Library\SBW as SBW;
use UpdraftNetworks\Utils;

class VTEC extends NWSProduct
{
    public function __construct($prod_info, $prod_text)
    {
        parent::__construct($prod_info, $prod_text);
    }

    public function parse()
    {
        return $this->split_product($this->raw_product, 'UpdraftNetworks\\Parser\\VTECSegment');
    }
}

/**
 * Extends the NWSProductSegment with attributes specific to VTEC-enabled products.
 */
class VTECSegment extends NWSProductSegment
{
    /**
     * Array of VTEC strings.
     *
     * @var array VTECString
     */
    public $vtec_strings;

    /**
     * Storm motion vector info.
     *
     * @var array SMVString
     */
    public $smv;

    /**
     * Impact-based warning info (if available.)
     */
    public $impacts;

    /**
     * Storm-based warning polygon geometry (if available)
     */
    public $polygon;

    public function __construct($segment_text, $afos, $office)
    {
        parent::__construct($segment_text, $afos, $office);
        $this->vtec_strings = $this->parseVTEC($segment_text);
        // Only attempt to parse out storm motion vector and impact-based information for:
        // - tornado warnings
        // - severe thunderstorm warnings
        // - severe weather followup statements
        // - special marine warnings
        // - marine weather statements (questionable)
        if (preg_match('/(TOR|SVR|SVS|MWW|MWS)/', $this->afos)) {
            $this->smv = new SMVString($segment_text);
            $this->impacts = new IBW($segment_text);
        }
        
        // Respect the polygon!
        $sbw = new SBW($segment_text);
        print_r($sbw);
        $this->polygon = $sbw->polygon;
    }

    //
    // Valid Time Extent Code (VTEC) support
    // Lots of useful information in one string about nature of product, start and end times, etc.
    // TODO: Implement H-VTEC for hydrological hazards
    //

    /**
     * Get VTEC strings if they exist...otherwise, return false
     *
     * @return array VTEC strings
     * @return boolean false if failure
     */
    public function getVTEC()
    {
        if (!empty($this->vtec_strings)) {
            foreach ($this->vtec_strings as $vtec_string) {
                $strings[] = $vtec_string;
            }

            // Return an array of VTEC strings
            return $strings;
        }

        // No VTEC string found
        return null;
    }

    /**
     * Quick check if this segment has VTEC
     *
     * @return  boolean
     */
    public function hasVTEC()
    {
        return !empty($this->vtec_strings);
    }

    /**
     * Checks if a segment has a VTEC message.
     *
     * @return boolean
     */
    public function parseVTEC($segment_text)
    {
        $data = $segment_text;
        $vtec_strings = array();

        // Fun regex to find VTEC strings
        $regex = "/\/([A-Z]{1})\.(NEW|CON|EXP|CAN|EXT|EXA|EXB|UPG|COR|ROU)\.([A-Z]{4})\.([A-Z]{2})\.([A-Z]{1})\.([0-9]{4})\.([0-9]{6})T([0-9]{4})Z-([0-9]{6})T([0-9]{4})Z\//";

        if (preg_match_all($regex, $data, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $key => $match) {
                $vtec_strings[$key] = new VTECString($match);
            }
        }

        return $vtec_strings;
    }
}
