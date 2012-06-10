<?php
/*
 * Factory from which all NWS products will be served from.
 * If a product does not have a handler, it will be ignored.
 */

class NWSProductFactory {
	// Class name is the WMO header corresponding to the product
	public static function parse_product($wmo_header, $product) {
		// Break the WMO header down into the first five to allow for regional differences
		$wmo_header_generic = substr($wmo_header,0,5) . '0';
		echo "Generic WMO header is $wmo_header_generic\n";
		// Construct the path to the parser
		$parser_path = dirname(__FILE__) . "/products/$wmo_header_generic.php";
		echo "Parser path is $parser_path\n";
		if(file_exists($parser_path)) {
			include_once($parser_path);
			// Instantiate the class
			$parser = new $wmo_header_generic($product);
		}
		// It's not here...return null to the ingestor.
		else
		{
			$parser = null;
		}

		return $parser;
	}
}