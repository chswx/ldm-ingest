<?php
/*
 * Special Weather Statement or Significant Weather Advisory depending on WFO.
 */

class WWUS80 extends NWSProduct {
	function parse() {		
		// STEP 1: Pull in counties
		$this->parse_zones($this->get_product_text());

		// Do not relay for now
		$this->properties['relay'] = false;

		return $this->properties;
	}

	/**
     * Get the name of the product.
     * 
     * @return string Product name
     * @todo Get the headline for the Special Weather Statement to display, otherwise show just Special Weather Statement
     */
	function get_name() {
		return "Special Weather Statement";
	}

	/**
	 * Get expiration time from the product.
	 * 
	 * @return string Expiration time
	 * @todo Not a VTEC product, expiration not easily extrapolated
	 */
	function get_expiry() {
		return null;
	}
}
?>