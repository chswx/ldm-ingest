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

// Bring in the Twitter OAuth lib and local config.
include('lib/twitter/twitteroauth/twitteroauth/twitteroauth.php');
include('oauth.config.php');

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
preg_match('/[A-Z][A-Z][A-Z][A-Z][0-9][0-9]/',$output,$matches);
$wmo_id = $matches[0];
//echo "WMO ID is $wmo_id";

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
		if($product_obj->in_zone($active_zones) && $product_obj->can_relay()) {
			$tweet = new WxTweet($product_obj);
			$tweet_text = $tweet->render_tweet();
			// Authenticate with Twitter
			$twitter = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, ACCESS_TOKEN, ACCESS_TOKEN_SECRET);
			if(!$twitter->post('statuses/update',array('status' => $tweet_text))) {
				log_message("product-ingest.php: Error sending a tweet.")
			}
		}
	}
	else {
		log_message("product-ingest.php: Product parser for $wmo_id is null.");
	}
}

if(empty($tweet)) {
	log_message("product-ingest.php: No products matched required rules.");
}

function log_message($message) {
	error_log("[" . date('m-d-Y g:i:s A') . "] " . $message . "\n",3,'/home/ldm/chswx-error.log');
}