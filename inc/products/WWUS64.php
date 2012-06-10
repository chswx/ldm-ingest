<?php
/*
 * Local WFO Watch County Notification ingestor.
 */

class WWUS64 extends NWSProduct {
	function parse($product) {
		// TODO: Write the parser here.
		$product = "Parsed by WWUS64 class!\n\n" . $product;
		return $product;
	}
}
?>