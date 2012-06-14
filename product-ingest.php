#!/usr/bin/php
<?php
/* 
 * CHSWX Product Ingestor
 * Command-line tool
 * Main entry point for LDM ingest. This hands off to a factory which generates a class for specific products.
 * Many thanks to @blairblends, @edarc, and the Updraft team for help and inspiration
 */

//
// Support Files
//

// Bring in configuration.
include('conf/chswx.conf.php');

// Bring in the abstract class definition for NWSProduct.
include('inc/NWSProduct.class.php');

// And its factory
include('inc/NWSProductFactory.class.php');

// Mustache library
include('lib/mustache/Mustache.php');

// Initialize Mustache
$m = new Mustache;

// Bring in the Twitter OAuth lib.
//include('lib/twitter/twitteroauth/twitteroauth.php');

//
// Execution time
//

// Get the file path from the command line.
$file_path = $argv[1];

// Get the WMO ID -- TODO: Make this a bit more automatic
$wmo_id = $argv[2];

// Bring in the file
$m_text = file_get_contents($file_path);

// Sanitize the file
$output = trim($m_text, "\x00..\x1F");

//
// TODO: Move this check back later in the sequence
//

// Check if the product contains $$ identifiers for multiple products
if(strpos($output, "$$")) {
	// Loop over the file for multiple products within one file identified by $$
	$products = explode('$$',trim($output), -1);
}
else {
	// No delimiters
	$products = array(trim($output));
}

//
// Kick off the factory for each parsed product
//

foreach($products as $product)
{
	$product_obj = NWSProductFactory::parse_product($wmo_id,$product);
	if(!is_null($product_obj)) {
		//var_dump($product_parser->parse());
		//$product_data = $product_obj->get_properties();
		if(!$product_obj->is_operational()) {
			echo "Non-operational warning!\n";
		}
		//var_dump($product_data);
		
		//
		// Let's try a "tweet" via Mustache
		// TODO: Something more permanent
		//

		// First, set up the Mustache variables in an array
		//$product_variables = array(
		//	'product_name' => $vtec_phenomena_codes[$product_data['vtec']['phenomena']] . " " . $vtec_significance_codes[$product_data['vtec']['significance']],
			// TODO: County lookup
		//	'location' => 'Taco County',
			// Expiration time (TODO: convert to current timezone)
		//	'exp_time' => $product_data['vtec']['expire_time'] . "Z"
		//);

		echo $m->render($chswx_tweet_text[$product_data['vtec']['action']] . " " . HASHTAG, $product_variables);
	}
	else {
		echo "Product parser for $wmo_id is null\n";
	}
}
