<?php
/*
 * Ingests a new Severe Thunderstorm Warning. (Updates to the product are in WWUS54, Severe Weather Statements.)
 */

class WUUS54 extends NWSProduct {
	function parse($product) {
		// TODO: Write the parser here.
		$product = "Parsed by WUUS54 class!\n\n" . $product;
		return $product;
	}
}