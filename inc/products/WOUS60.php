<?php
/*
 * SPC Watch County Notification ingestor.
 */

class WOUS60 extends NWSProduct {
	function parse() {
		// TODO: Write the parser here.
		
		// STEP 1: Pull in counties
		$this->parse_zones($this->get_product_text());

		// STEP 2: Parse out VTEC
		$this->parse_vtec();

		// FINAL: Return the properties array

		return $this->properties;
	}
}
?>