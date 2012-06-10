<?php
/*
 * Ingests a new Tornado Warning. (Updates to a tornado warning come in via WWUS54, Severe Weather Statement)
 */

class WFUS50 extends NWSProduct {
	function parse() {
		// TODO: Write the parser here.
		//$product = "Parsed by WFUS50 class!\n" . $this->raw_product;

		// STEP 1: Pull in counties
		$this->parse_zones($this->get_product_text());
		//var_dump($this->properties['counties']);

		// STEP 2: Parse out VTEC
		$this->parse_vtec();

		/*echo "VTEC string: ";
		var_dump($this->get_vtec_string());*/

		// FINAL: Return the properties array

		return $this->properties;
	}

}