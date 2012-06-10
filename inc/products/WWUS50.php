<?php
/*
 * Ingests a Severe Weather Statement that updates a Severe Thunderstorm or Tornado Warning.
 */

class WWUS50 extends NWSProduct {
	function parse() {
		// TODO: Write the parser here.
		$product = "Parsed by WWUS54 class!\n" . $this->raw_product;
		return $product;
	}
}