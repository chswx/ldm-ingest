#!/usr/bin/php
<?php
/*
 * LDM Product Ingestor
 * Command-line tool
 * Main entry point for LDM ingest. This hands off to a factory which generates a class for specific products.
 * Many thanks to @blairblends, @edarc, and the Updraft team for help and inspiration
 */

namespace UpdraftNetworks\Ingestor;
use UpdraftNetworks\Utils as Utils;
use UpdraftNetworks\Storage\ProductStorage as ProductStorage;

//
// Execution time
//
$time_start = microtime(true);

//
// Include composer autoload
//
include('../vendor/autoload.php');

//
// Configuration
//
include('../conf/chswx.conf.php');

// Get the file path from the command line.
// #11: Pipable stuff, arguments, etc.

// First, backward compatibility for file input
$shortopts = "f::";
$options = getopt($shortopts);
var_dump($options);
if(!empty($options['f'])) {
    $file_path = $options['f'];
    Utils::log("Ingest has begun. Filename: " . $file_path);
    // Bring in the file
    $m_text = file_get_contents($file_path);
}
// Next, if a file is not specified, require these options and pipe input
else {
    $shortopts = "w:o:t:a:c::";
    $longopts = array(
        'wmo:',
        'office:',
        'time:',
        'afos:',
        'corrections::'
    );

    $options = getopt($shortopts,$longopts);
    if($options) {
        Utils::log("Ingest has begun from STDIN ({$options['a']})");
        stream_set_blocking(STDIN,0);
        $m_text = stream_get_contents(STDIN);
    }
    else {
        die("Aborting parse");
    }
}

// Send to the factory to parse the product.
$product_obj = NWSProductFactory::get_product(Utils::sanitize($m_text));

// If we're not null, victory! Encode and send on its merry way
if(!is_null($product_obj)) {
    // Only here as a debugging measure.
    // TODO: Introduce debugging flag
    Utils::log(print_r($product_obj));

    // Send to our product storage system
    ProductStorage::send($product_obj);

    // Have you heard the good word of our properly parsed product?
    Utils::log("Parsed product {$product_obj->afos} from {$product_obj->office} successfully");
} else {
    // Something went wrong
    Utils::log("Error parsing.");
}
$time_end = microtime(true);
$time = $time_end - $time_start;
Utils::log("Ingest has run. Execution time: $time seconds");