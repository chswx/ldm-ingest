<?php
/*
 * Ingests a Severe Weather Statement that updates a Severe Thunderstorm or Tornado Warning.
 */

class WWUS54 extends NWSProduct {
	function parse($product) {
		// TODO: Write the parser here.
		$product = "Parsed by WWUS54 class!\n\n" . $this->raw_product;
		return $product;
	}
}