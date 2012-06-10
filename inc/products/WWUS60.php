<?php
/*
 * Local WFO Watch County Notification ingestor.
 */

class WWUS60 extends NWSProduct {
	function parse($in_product) {
		// TODO: Write the parser here.
		$product = "Parsed by WWUS64 class!\n" . $in_product;
		return $product;
	}
}
?>