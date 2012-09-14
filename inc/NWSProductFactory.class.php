<?php
/*
 * Factory class that routes products to the most specific available parser.
 * 
 */

class NWSProductFactory {
	
	/**
	 * Dispatches a sanitized product to its parser.
	 * It's up to the parser to generate and relay appropriate events.
	 * 
	 * @static
	 * @param string $product Sanitized product text.
	 */
	public static function parse_product($product_text) {
		// Get WMO header and issuing office 
		$prod_info = self::get_product_details($product_text);

		// Break the WMO header down into the first five to allow for regional differences
		$wmo_header_generic = substr($prod_info['wmo'],0,5) . '0';
		
		// Construct the path to the parser
		$parser_path = dirname(__FILE__) . "/products/$wmo_header_generic.php";
		if(file_exists($parser_path)) {
			include_once($parser_path);
			// Instantiate the class
			$parser = new $wmo_header_generic($prod_info, $product_text);
		}
		// It's not here...return a generic parsing library.
		else
		{
			include_once(dirname(__FILE__) . "/products/Generic.php");
			$parser = new GenericProduct($prod_info, $product_text);
		}

		$parser->generate_events();
	}

	/**
	 * Get WMO product ID and authority from the second line.
	 * 
	 * @static
	 * @param string $product_text Sanitized product text.
	 * @return array WMO header ID, issuing office, and AWIPS code
	 */
	private static function get_product_details($product_text) {
		$text_array = Utils::make_array($product_text);
		$wmo_and_office = explode(' ',$text_array[1]);
		$wmo = $wmo_and_office[0];
		$office = $wmo_and_office[1];
		$awips = $text_array[2];

		return array(
			'wmo' => $wmo,
			'office' => $office,
			'awips' => $awips
		);
	}
}