<?php
/*
 * Ingests a new Tornado Warning. (Updates to a tornado warning come in via WWUS54, Severe Weather Statement)
 */

class WFUS50 extends NWSProduct {
	function parse() {
		// STEP 1: Pull in counties
		$this->parse_zones($this->get_product_text());

		// STEP 2: Parse out VTEC
		$this->parse_vtec();

		// STEP 3: Relay readiness
		$this->properties['relay'] = true;

		// FINAL: Return the properties array

		return $this->properties;
	}

	/**
     * Get the name of the product.
     * 
     * @return string Product name
     */
	function get_name() {
		// For initial tornado warnings, we want to highlight the fact that it is a tornado warning
		return strtoupper($this->get_name_from_vtec());	
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