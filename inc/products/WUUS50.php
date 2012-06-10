<?php
/*
 * Ingests a new Severe Thunderstorm Warning. (Updates to the product are in WWUS54, Severe Weather Statements.)
 */

class WUUS50 extends NWSProduct {
	function parse() {
		// TODO: Write the parser here.
		$product = "Parsed by WUUS50 class!\n\n" . $this->raw_product;
		return $product;
	}
}