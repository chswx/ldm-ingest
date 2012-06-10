<?php
/* 
 * CHSWX: NWSProduct class 
 * Defines most of what is a National Weather Service text product and puts it out into easily reusable chunks.
 * Portions adapted from code by Andrew: http://phpstarter.net/2010/03/parse-zfp-zone-forecast-product-data-in-php-option-1/
*/

abstract class NWSProduct {
	
	var $raw_product;
	var $properties;

	function __construct($product) {
		// Keep the raw product around for now
		$this->raw_product = $product;
		
	}

	/*
	 * Abstract function for product-specific parsing.
	 */

	abstract function parse();

	/*
	 * Returns the properties of the product.
	 */
	function get_properties()
	{
		return $this->properties;
	}

	/*
	 * Return the raw product text
	 */

	function get_product_text() 
	{
		return $this->raw_product;
	}

	//
	// Valid Time Extent Code (VTEC) support
	// Lots of useful information in one string about nature of product, start and end times, etc.
	// TODO: Implement H-VTEC for hydrological hazards
	//

	/*
	 * Get VTEC string if it exists, otherwise return false
	 */

	function get_vtec() {
		if(!empty($this->properties['vtec']['action'])) {
			return $this->properties['vtec'];
		}

		// No VTEC string found
		return false;
	}

	/*
	 * Helper function for retrieving VTEC product class
	 */

	function get_vtec_product_class() {
		return $this->properties['vtec']['product_class'];
	}

	/*
	 * Helper function for retrieving VTEC action (issued, continued, extended, etc.)
	 */

	function get_vtec_action() {
		return $this->properties['vtec']['action'];
	}

	/*
	 * Helper function for retrieving issuing WFO from VTEC
	 */

	function get_vtec_wfo() {
		return $this->properties['vtec']['wfo'];
	}

	/*
	 * Retrieve VTEC phenomena code.
	 */

	function get_vtec_phenomena() {
		return $this->properties['vtec']['phenomena'];
	}

	/*
	 * Retrieve VTEC significance
	 */

	function get_vtec_significance() {
		return $this->properties['vtec']['significance'];
	}

	//
	// Private functions
	//

	/**
	 * The NWS combines does not repeat the state code for multiple zones...not good for our purpose
	 * All we want to do here is convert ranges like INZ021-028 to INZ021-INZ028
	 * We will also call the function to expand the ranges here.
	 * See: http://www.weather.gov/emwin/winugc.htm
	 */
	protected function parse_zones($data)
	{
		/* first, get rid of newlines */
		$data = str_replace("\n", '', $data);
		
		/* split up individual states - multiple states may be in the same forecast */
		$regex = '/(([A-Z]{2})(C|Z){1}([0-9]{3})((>|-)[0-9]{3})*)-/';
		
		$count = preg_match_all($regex, $data, $matches);
		$total_zones = '';
		
		foreach ($matches[0] as $field => $value)
		{
			/* since the NWS thought it was efficient to not repeat state codes, we have to reverse that */
			$state = substr($value, 0, 3);
			$zones = substr($value, 3);
			
			/* convert ranges like 014>016 to 014-015-016 */
			$zones = $this->expand_ranges($zones);
			
			/* hack off the last dash */
			$zones = substr($zones, 0, strlen($zones) - 1);
			$zones = $state . str_replace('-', '-'.$state, $zones);
			
			$total_zones .= $zones;
		}
		
		
		$total_zones = explode('-', $total_zones);
		return $total_zones;
	}

	/**
	 * The NWS combines multiple zones into ranges...not good for our purpose
	 * All we want to do here is convert ranges like 014>016 to 014-015-016
	 * See: http://www.weather.gov/emwin/winugc.htm
	 */
	protected function expand_ranges($data)
	{
		$regex = '/(([0-9]{3})(>[0-9]{3}))/';
		
		$count = preg_match_all($regex, $data, $matches);
		
		foreach ($matches[0] as $field => $value)
		{
			list($start, $end) = explode('>', $value);
			
			$new_value = array();
			for ($i = $start; $i <= $end; $i++)
			{
				$new_value[] = str_pad($i, 3, '0', STR_PAD_LEFT);
			}
			
			$data = str_replace($value, implode('-', $new_value), $data);
		}
		
		return $data;
	}


	/* 
	 * For VTEC-capable products, decode the VTEC string
	 */
	protected function parse_vtec($data) {
		
		//
		// VTEC phenomena codes.
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


		
	}	

}