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
	function __construct($vtec_string_array) {
		$this->parse($vtec_string_array);
	}

	///
	/// Private functions /////////////////////////////////////////////////
	///

	/**
	 * Parse out the VTEC string into its properties
	 */
	private function parse($vtec_string_array)
	{
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

    /**
     * Checks if an operational VTEC string.
     * 
     * @return boolean
     */
    function is_operational()
    {
    	return $this->product_class === 'O';
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

/**
 * Global VTEC properties
 */

//
// VTEC phenomena codes.
// Based on code from Iowa Environmental Mesonet
//
$vtec_phenomena_codes = array(
	'AF' => 'Ashfall',
	'AS' => 'Air Stagnation',
	'BS' => 'Blowing Snow',
	'BW' => 'Brisk Wind',
	'BZ' => 'Blizzard',
	'CF' => 'Coastal Flood',
	'DS' => 'Dust Storm',
	'DU' => 'Blowing Dust',
	'EC' => 'Extreme Cold',
	'EH' => 'Excessive Heat',
	'EW' => 'Extreme Wind',
	'FA' => 'Areal Flood',
	'FF' => 'Flash Flood',
	'FG' => 'Dense Fog',
	'FL' => 'Flood',
	'FR' => 'Frost',
	'FW' => 'Fire Weather',
	'FZ' => 'Freeze',
	'GL' => 'Gale',
	'HF' => 'Hurricane Force Wind',
	'HI' => 'Inland Hurricane',
	'HS' => 'Heavy Snow',
	'HT' => 'Heat', 
	'HU' => 'Hurricane',
	'HW' => 'High Wind',
	'HY' => 'Hydrologic',
	'HZ' => 'Hard Freeze',
	'IP' => 'Sleet',
	'IS' => 'Ice Storm',
	'LB' => 'Lake Effect Snow and Blowing Snow',
	'LE' => 'Lake Effect Snow',
	'LO' => 'Low Water',
	'LS' => 'Lakeshore Flood',
	'LW' => 'Lake Wind',
	'MA' => 'Marine',
	'RB' => 'Small Craft for Rough Bar',
	'RP' => 'Rip Currents', 	// NWS CHS addition
	'SB' => 'Snow and Blowing Snow',
	'SC' => 'Small Craft',
	'SE' => 'Hazardous Seas',
	'SI' => 'Small Craft for Winds',
	'SM' => 'Dense Smoke',
	'SN' => 'Snow',
	'SR' => 'Storm',
	'SU' => 'High Surf',
	'SV' => 'Severe Thunderstorm',
	'SW' => 'Small Craft for Hazardous Seas',
	'TI' => 'Inland Tropical Storm',
	'TO' => 'Tornado',
	'TR' => 'Tropical Storm',
	'TS' => 'Tsunami',
	'TY' => 'Typhoon',
	'UP' => 'Ice Accretion',
	'WC' => 'Wind Chill',
	'WI' => 'Wind',
	'WS' => 'Winter Storm',
	'WW' => 'Winter Weather',
	'ZF' => 'Freezing Fog',
	'ZR' => 'Freezing Rain'
);

//
// VTEC significance
//
$vtec_significance_codes = array(
	'W' => 'Warning',
	'A' => 'Watch',
	'Y' => 'Advisory',
	'S' => 'Statement',
	'F' => 'Forecast',
	'O' => 'Outlook',
	'N' => 'Synopsis'
);

?>