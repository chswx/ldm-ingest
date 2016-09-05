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

class VTEC extends NWSProduct {
    function __construct($prod_info,$prod_text) {    
        parent::__construct($prod_info,$prod_text);
    }

    function parse() {
        return $this->split_product($this->raw_product,'UpdraftNetworks\\Parser\\VTECSegment');
    }
}

/**
 * Extends the NWSProductSegment with attributes specific to VTEC-enabled products.
 */
class VTECSegment extends NWSProductSegment {
    /**
     * Array of VTEC strings.
     *
     * @var array VTECString
     */
    var $vtec_strings;

    /**
     * Storm motion vector info.
     * @var array SMVString
     */

    function __construct($segment_text, $afos, $office) {
        parent::__construct($segment_text, $afos, $office);
        $this->vtec_strings = $this->parse_vtec();
        $this->smv = new SMVString($segment_text);
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
    function get_vtec() {
        if(!empty($this->vtec_strings))
        {
            foreach($this->vtec_strings as $vtec_string) {
                $strings[] = $vtec_string;
            }
            // Return an array of VTEC strings
            return $strings;
        }
        // No VTEC string found
        return false;
    }

    /**
     * Quick check if this segment has VTEC
     * @return  boolean
     */
    function has_vtec() {
        return !empty($this->vtec_strings);
    }

    /**
     * Checks if a segment has a VTEC message.
     * @return boolean
     */
    function parse_vtec() {
        $data = $this->text;
        $vtec_strings = array();

        // Fun regex to find VTEC strings
        $regex = "/\/([A-Z]{1})\.(NEW|CON|EXP|CAN|EXT|EXA|EXB|UPG|COR|ROU)\.([A-Z]{4})\.([A-Z]{2})\.([A-Z]{1})\.([0-9]{4})\.([0-9]{6})T([0-9]{4})Z-([0-9]{6})T([0-9]{4})Z\//";

        if ( preg_match_all( $regex, $data, $matches, PREG_SET_ORDER ) ) {
            foreach ( $matches as $key => $match ) {
                $vtec_strings[$key] = new VTECString( $match );
            }
        }

        return $vtec_strings;
    }
}

