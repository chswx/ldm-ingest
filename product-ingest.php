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

// Geodata library
include('inc/geo/GeoLookup.class.php');

// Tweet generation library

include('inc/output/WxTweet.class.php');

// Initialize Mustache
$m = new Mustache;

// Bring in the Twitter OAuth lib.
//include('lib/twitter/twitteroauth/twitteroauth.php');

//
// Execution time
//

// Get the file path from the command line.
$file_path = $argv[1];

// Bring in the file
$m_text = file_get_contents($file_path);

// Sanitize the file
$output = trim($m_text, "\x00..\x1F");

// Get the WMO ID
$wmo_id = preg_match('/[A-Z]{4}[0-9]{2}/',$output);

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
		$product_data = $product_obj->get_properties();

		//
		// New tweet if the warning is in a particular zone
		// 
		if($product_obj->in_zone($active_zones)) {
			$tweet = new WxTweet($product_obj);
			echo $tweet->render_tweet();
			echo "\n";
		}
	}
	else {
		echo "Product parser for $wmo_id is null\n";
	}
}

if(empty($tweet)) {
	echo "No products matched required rules.\n";
}