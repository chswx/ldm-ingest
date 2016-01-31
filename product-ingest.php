#!/usr/bin/php
<?php
/* 
 * LDM Product Ingestor
 * Command-line tool
 * Main entry point for LDM ingest. This hands off to a factory which generates a class for specific products.
 * Many thanks to @blairblends, @edarc, and the Updraft team for help and inspiration
 */

//
// Execution time
//

$time_start = microtime(true);

//
// Configuration
// 

include('conf/base.conf.php');
include('conf/enabled-listeners.conf.php');

//
// Utilities and libraries
// 

// Base utilities
include('inc/Utils.class.php');
// Pub/Sub system -- backbone to the whole thing
include('inc/PubSub.php');

//
// Set up event dispatcher
// 
$relay = new Dispatcher();

// Bring in the abstract class definition for NWSProduct.
include('inc/NWSProduct.class.php');
// And its factory
include('inc/NWSProductFactory.class.php');
// Geodata library
include('inc/geo/GeoLookup.class.php');

//
// Listeners
// 

// Always include the log listener.
include('inc/endpoints/LogListener.class.php');

// Include other listeners as well.
if(isset($enabled_listeners)) {
	foreach($enabled_listeners as $listener) {
		$curr_path = "inc/endpoints/$listener.class.php";
		if(file_exists($curr_path)) {
			include($curr_path);
		}
		else {
			Utils::log("Listener $listener not found in inc/endpoints");
		}
	}
}

// Get the file path from the command line.
// TODO: Consider piping this in, may save a small bit of disk I/O
$file_path = $argv[1];
Utils::log("Ingest has begun. Filename: " . $file_path);

// Bring in the file
$m_text = file_get_contents($file_path);

// Send to the factory to parse the product.
$product_obj = NWSProductFactory::get_product(Utils::sanitize($m_text));

// Publish an event to signal the product is parsed.
$relay->publish(new Event('ldm',$product_obj->afos,$product_obj));


$time_end = microtime(true);
$time = $time_end - $time_start;
Utils::log("Ingest and relay complete. Execution time: $time seconds");
