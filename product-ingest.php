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

// HipChat driver
include('lib/Hippy/Hippy.php');

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

log_message("Product ingest running - WMO ID: " . $wmo_id . " File Path: " . $file_path);

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
	$product_parsed = NWSProductFactory::parse_product($wmo_id,$product);
	if(!is_null($product_parsed)) {
		//$product_data = $product_parsed->get_properties();
		if($product_parsed->can_relay()) {
			mail('jared.smith@updraftnetworks.com', $product_parsed->get_name(), $product_parsed->get_product_text(),'From: jared.smith+alerts@updraftnetworks.com');
		}
		// Authenticate with Twitter
		$twitter = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, ACCESS_TOKEN, ACCESS_TOKEN_SECRET);
		$tweets = $product_parsed->get_tweets();

		if(!empty($tweets)) {
			foreach($tweets as $tweet_text) {
				//echo "Length of tweet: " . strlen($tweet_text) . "\n";
				//echo $tweet_text;
				$response = $twitter->post('statuses/update',array('status' => $tweet_text));
				print_r($response);
				if(!$response) {
					log_message("product-ingest.php: Tweet of length " . strlen($tweet_text) . " failed: " . $tweet_text);
				}
				// Route to HipChat
				if(defined('HIPCHAT_TOKEN')) {
					Hippy::speak($tweet_text);
				}
			}
		}
		else
		{
			log_message("product-ingest.php: No tweet for $wmo_id from " . $product_parsed->get_vtec_wfo());
		}
	}
	else {
		log_message("product-ingest.php: Product parser for $wmo_id is null.");
	}
}

function log_message($message) {
	$log_format = "[" . date('m-d-Y g:i:s A') . "] " . $message . "\n";
	$log_location = '/home/ldm/chswx-error.log';
	$log_mode = 0; 	// defaults to syslog/stderr

	//echo $message;

	if(file_exists('/home/ldm/chswx-error.log')) {
		$log_mode = 3;
		error_log($log_format,$log_mode,$log_location);
	}
	else {
		error_log($log_format,$log_mode);
	}
}