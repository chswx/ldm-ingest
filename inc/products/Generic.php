<?php
/* 
 * Generic product ingestor. Fallback for non-specific products.
 * Is VTEC capable.
 */

class GenericProduct extends NWSProduct {
	function parse() {
		
		// STEP 1: Pull in counties
		$this->parse_zones($this->get_product_text());

		// STEP 2: Parse out VTEC
		$this->parse_vtec();
	}
}