<?php
/*
 * Local WFO Watch County Notification ingestor.
 */

class WWUS64 extends NWSProduct {
	function parse() {
		// TODO: Write the parser here.
		$product = "Parsed by WWUS64 class!\n\n" . $this->raw_product;
		return $product;
	}
}
?>