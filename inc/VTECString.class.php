<?php

define('VTEC_LIB',true);

/**
 * Class to assist with VTEC string operations.
 * VTECString object properties are externally accessible
 */
class VTECString
{
    /**
     * Raw VTEC string.
     * @var string
     */
    var $vtec_string;

    /**
     * @var string Product class
     */
    var $product_class;

    /**
     * @var string Action
     */
    var $action;

    /**
     * @var string Issuing office ID
     */
    var $office;

    /**
     * @var string Phenomena
     */
    var $phenomena;

    /**
     * @var string Significance
     */
    var $significance;

    /**
     * @var string Event Tracking Number
     */
    var $event_number;

    /**
     * @var int Event effective time as a UNIX timestamp
     */
    var $effective_timestamp;

    /**
     * @var int Event expiration time as a UNIX timestamp
     */
    var $expire_timestamp;

    /**
     * Constructor.
     * Take product text and parse out VTEC string(s).
     *
     * @param string $product Product text.
     */
    function __construct($vtec) {

        if(is_array($vtec)) {
            $this->_create_obj($vtec);
        }
        else {
            $this->_parse($vtec);
        }
    }

    ///
    /// Private functions /////////////////////////////////////////////////
    ///

    /**
     * Parse out the VTEC string into its properties
     */
    private function _create_obj($vtec_string_array) {
        // Save the VTEC string in its entirety
        $this->vtec_string = $vtec_string_array[0];

        // VTEC product class
        $this->product_class = $vtec_string_array[1];

        // VTEC action
        $this->action = $vtec_string_array[2];

        // VTEC issuing WFO
        $this->office = $vtec_string_array[3];

        // VTEC phenomena
        $this->phenomena = $vtec_string_array[4];

        // VTEC significance
        $this->significance = $vtec_string_array[5];

        // VTEC event number
        $this->event_number = $vtec_string_array[6];

        // Effective time (as UNIX timestamp)
        $this->effective_timestamp = $this->vtec_to_timestamp($vtec_string_array[7],$vtec_string_array[8]);

        // Expire time (as UNIX timestamp)
        $this->expire_timestamp = $this->vtec_to_timestamp($vtec_string_array[9],$vtec_string_array[10]);
    }

    private function _parse($vtec_string) {
        $regex = "/\/([A-Z]{1})\.(NEW|CON|EXP|CAN|EXT|EXA|EXB|UPG|COR|ROU)\.([A-Z]{4})\.([A-Z]{2})\.([A-Z]{1})\.([0-9]{4})\.([0-9]{6})T([0-9]{4})Z-([0-9]{6})T([0-9]{4})Z\//";

        if ( preg_match( $regex, $vtec_string, $matches ) ) {
            $this->_create_obj($matches);
        }
    }

    /**
     * Checks if an operational VTEC string.
     * @return boolean
     */
    function is_operational() {
        return $this->product_class === 'O';
    }

    /**
     * Checks if a test VTEC product.
     * @return boolean
     */
    function is_test() {
        return $this->product_class === 'T';
    }

    /**
     * Returns the action type from the VTEC dictionary.
     *
     * @return string action
     */
    function get_action() {
        return $this->action;
    }

    /**
     * Return the phenomena name from the global dictionary.
     * @return string Phenomena name
     */
    function get_phenomena_name()
    {
        if(isset($this->vtec_phenomena_codes[$this->phenomena]))
            return $this->vtec_phenomena_codes[$this->phenomena];
        else
            return null;
    }

    /**
     * Return the significance name from the global dictionary.
     * @return string Significance name
     */
    function get_significance_name() {
        if(isset($this->vtec_significance_codes[$this->significance]))
            return $this->vtec_significance_codes[$this->significance];
        else
            return null;
    }

    function get_product_name() {
        if(!empty($this->get_significance_name()) && !empty($this->get_phenomena_name())) {
            return $this->get_phenomena_name() . " " . $this->get_phenomena_name();
        }

        return null;
    }

    /**
     * Converts a VTEC timestamp to a UNIX timestamp (normalized to Z time)
     *
     * @return int UNIX timestamp
     */
    private function vtec_to_timestamp($vtec_date,$vtec_time)
    {
        // Don't bother with blank dates
        if($vtec_date == "OOOOOO")
        {
            $stamp = 0;
        }
        else
        {
            // Break out the VTEC datestamp into chunks to reassemble shortly
            $year = substr($vtec_date,0,2);
            $month = substr($vtec_date,2,2);
            $day = substr($vtec_date,4,2);

            // Y2.1K problem (read: not mine unless I live to be 130)
            $stamp = strtotime('20' . $year . '-' . $month . '-' . $day . ' ' . $vtec_time . 'Z');
        }

        return $stamp;
    }
}
