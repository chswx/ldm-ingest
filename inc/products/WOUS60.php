<?php
/*
 * SPC Watch County Notification ingestor.
 */

class WOUS60 extends NWSProduct {
	function parse() {
		// TODO: Write the parser here.
		$product = "Parsed by WOUS60 class!\n\n" . $this->raw_product;
		return $product;
	}
}
?>