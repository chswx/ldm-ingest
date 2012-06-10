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

// Bring in the class definition for NWSProduct.
include('inc/NWSProduct.class.php');

// And its factory
include('inc/NWSProductFactory.class.php');

// Bring in the Twitter OAuth lib.
//include('lib/twitter/twitteroauth/twitteroauth.php');

//
// Execution time
//

// Get the file path from the command line.
$file_path = $argv[1];

// Get the WMO ID as well. Easier to send to the parser...
$wmo_id = $argv[2];

// Bring in the file
$m_text = file_get_contents($file_path);

// Sanitize the file
$output = trim($m_text);
$output = trim($output, "\x00..\x1F");
$output = str_replace("\r\n", "", $output);

// Loop over the file for multiple products within one file identified by $$
$products = explode('$$',$output);

//
// Kick off the factory for each parsed product
//

foreach($products as $parsed_product)
{
	$parsed_product = NWSProductFactory::parse_product($wmo_id,$product);
}