<?php
/*
 * Ingests a new Tornado Warning. (Updates to a tornado warning come in via WWUS54, Severe Weather Statement)
 */

class WFUS80 extends NWSProduct {
	function parse() {
		// TODO: Write the parser here.
		$product = "Parsed by WFUS84 class!\n\n" . $this->raw_product;
		return $product;
	}

}