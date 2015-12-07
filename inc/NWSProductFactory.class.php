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
	 * @param string $product_text Sanitized product text.
	 * @return Parsed Product object.
	 */
	public static function get_product($product_text) {
		// Get WMO header and issuing office 
		$prod_info = self::get_product_details($product_text);

		// Get AFOS for parser
		$afos = $prod_info['afos'];

		// Construct the path to the parser
		$parser_path = dirname(__FILE__) . "/products/$afos.php";

		if(file_exists($parser_path)) {
			include($parser_path);
			// Instantiate the class
			$product = new $afos($prod_info, $product_text);
		}
		// It's not here...return a generic parsing library.
		else
		{
			Utils::log("Generic product");
			include(dirname(__FILE__) . "/products/Generic.php");
			$product = new GenericProduct($prod_info, $product_text);
		}

		return $product;
	}

	/**
	 * Get WMO product ID and authority from the second line.
	 * 
	 * @param string $product_text Sanitized product text.
	 * @return array WMO header ID, issuing office, and AWIPS code
	 */
	private static function get_product_details($product_text) {
		$text_array = Utils::make_array($product_text);
		$wmo_and_office = explode(' ',$text_array[1]);
		$wmo = $wmo_and_office[0];
		$office = $wmo_and_office[1];
		$afos = $text_array[2];

		Utils::log("Product WMO: " . $wmo . '; Office: ' . $office . '; AFOS code: ' . $afos);

		return array(
			'wmo' => $wmo,
			'office' => $office,
			'afos' => $afos
		);
	}
}