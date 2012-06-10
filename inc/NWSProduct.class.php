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
		$this->properties = $this->parse($this->raw_product);
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
		if(!empty($this->properties['vtec']['string'])) {
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
	 * Retrieve the phenomena number
	 */

	function get_vtec_event_number() {
		return $this->properties['vtec']['event_number'];
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

	/*
	 * Retrieve effective date
	 */

	function get_vtec_effective_date() {
		return $this->properties['vtec']['effective_date'];
	}

	/*
	 * Retrieve effective time 
	 */

	function get_vtec_effective_time() {
		return $this->properties['vtec']['effective_time'];
	}

	/*
	 * Retrieve expire date
	 */

	function get_vtec_expire_date() {
		return $this->properties['vtec']['expire_date'];
	}

	/*
	 * Retrieve expire time
	 */

	function get_vtec_expire_time() {
		return $this->properties['vtec']['expire_time'];
	}

	/*
	 * Retrieve raw VTEC string
	 */

	function get_vtec_string() {
		return $this->properties['vtec']['string'];
	}

	/*
	 * Check if it is an operational product
	 */

	function is_operational() {
		return $this->properties['vtec']['status'] == 'O';
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
		$data = $this->get_product_text();

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
		// return $total_zones;
		$this->properties['counties'] = $total_zones;
	}

	/**
	 * The NWS combines multiple zones into ranges...not good for our purpose
	 * All we want to do here is convert ranges like 014>016 to 014-015-016
	 * See: http://www.weather.gov/emwin/winugc.htm
	 */
	protected function expand_ranges($data)
	{
		//$data = $this->get_product_text();

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
	protected function parse_vtec() {

		$data = $this->get_product_text();

		//
		// Regex out VTEC from the product
		//

		// Match all alerts, but we will only use operational warnings
		$regex = "/\/([A-Z]{1})\.(NEW|CON|EXP|CAN|EXT|EXA|EXB|UPG|COR|ROU)\.([A-Z]{4})\.([A-Z]{2})\.([A-Z]{1})\.([0-9]{4})\.([0-9]{6})T([0-9]{4})Z-([0-9]{6})T([0-9]{4})Z\//";

		// Successful match, save it all
		if(preg_match_all($regex, $data, $match)) {
			// Save the VTEC string in its entirety
			$this->properties['vtec']['string'] = $match[0][0];
			// VTEC product status
			$this->properties['vtec']['status'] = $match[1][0];
			// VTEC action
			$this->properties['vtec']['action'] = $match[2][0];
			// VTEC issuing WFO
			$this->properties['vtec']['wfo'] = $match[3][0];
			// VTEC phenomena
			$this->properties['vtec']['phenomena'] = $match[4][0];
			// VTEC significance
			$this->properties['vtec']['significance'] = $match[5][0];
			// VTEC event number
			$this->properties['vtec']['event_number'] = $match[6][0];
			// VTEC start date
			$this->properties['vtec']['effective_date'] = $match[7][0];
			// VTEC start time (Z)
			$this->properties['vtec']['effective_time'] = $match[8][0];
			// VTEC expire date
			$this->properties['vtec']['expire_date'] = $match[9][0];
			// VTEC expire time (Z)
			$this->properties['vtec']['expire_time'] = $match[10][0];
		}
		

	}	

}