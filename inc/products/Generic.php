<?php
/* 
 * Generic product ingestor. Fallback for non-specific products.
 */

class GenericProduct extends NWSProduct {
	function parse() {
		// TODO: Write the parser here.
		
		// STEP 1: Pull in counties
		$this->parse_zones($this->get_product_text());

		// STEP 2: Parse out VTEC
		$this->parse_vtec();

		// STEP 3: Relay readiness
		// Relay hydrological and tropical products
		//echo $this->get_vtec_phenomena();
		if($this->get_vtec_phenomena() == 'WS' || $this->get_vtec_phenomena() == 'ZR' || $this->get_vtec_phenomena() == 'IS' || $this->get_vtec_phenomena() == 'CF' || ($this->get_vtec_phenomena() == 'FA' && $this->get_vtec_significance() == 'W') || $this->get_vtec_phenomena() == 'WI' || $this->get_vtec_phenomena() == 'WC' || $this->get_vtec_phenomena() == 'FF' || $this->get_vtec_phenomena() == 'FG' || $this->get_vtec_phenomena() == 'HF' || $this->get_vtec_phenomena() == 'HI' || $this->get_vtec_phenomena() == 'HU' || $this->get_vtec_phenomena() == 'TI' || $this->get_vtec_phenomena() == 'TR') {
			$this->properties['relay'] = true;
		}
		else
		{
			$this->properties['relay'] = false;
		}

		// FINAL: Return the properties array

		return $this->properties;
	}

	/**
     * Get the name of the product.
     * 
     * @return string Product name
     */
	function get_name() {
		return $this->get_name_from_vtec();	
	}

	/**
	 * Get expiration time from the product.
	 * 
	 * @return string Expiration time
	 */
	function get_expiry() {
		return $this->get_expiry_from_vtec();
	}
}
