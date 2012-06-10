<?php
/*
 * Factory from which all NWS products will be served from.
 * If a product does not have a handler, it will be ignored.
 */

class NWSProductFactory {
	// Class name is the WMO header corresponding to the product
	public static function parse_product($wmo_header, $product) {
		// Construct the path to the parser
		$parser_path = "inc/$wmo_header.php";
		if(file_exists($parser_path)) {
			include_once('inc/' . $wmo_header . '.php');
			// Instantiate the class
			$parser = new $wmo_header($product);
		}
		// It's not here...return null to the ingestor.
		else
		{
			$parser = NULL;
		}

		return new $parser;
	}
}