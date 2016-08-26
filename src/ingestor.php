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

// Begin timing execution
$time_start = microtime(true);

// Include composer autoload
include('../vendor/autoload.php');

// Configuration
include('../conf/chswx.conf.php');

// Handle to DB
$db = new ProductStorage;

// Get the file path from the command line.
$shortopts = "f:";
$options = getopt($shortopts);
if(!empty($options['f'])) {
    $file_path = $options['f'];
    Utils::log("Ingest has begun. Filename: " . $file_path);
    // Bring in the file
    if(file_exists($file_path)) {
        $m_text = file_get_contents($file_path);
    } else {
        Utils::exit_with_error("File $file_path not found. Terminating ingest.\n");
    }
} else {
    // Abort the mission.
    Utils::exit_with_error("-f not given. Aborting.\n");
}

// Send to the factory to parse the product.
$product_obj = NWSProductFactory::get_product(Utils::sanitize($m_text));

// If we're not null, victory! Encode and send on its merry way
if(!is_null($product_obj)) {
    $table = $product_obj->table;
    // Unset the table now to prevent storing the name of the table along with the product
    unset($product_obj->table);
    // Send to our product storage system
    $db->send($product_obj,$table);

    // Have you heard the good word of our properly parsed product?
    Utils::log("Parsed product {$product_obj->afos} from {$product_obj->office} successfully");
} else {
    // Something went wrong
    Utils::log("Error parsing.");
}

// Finish logging execution, log and get out
$time_end = microtime(true);
$time = $time_end - $time_start;
Utils::log("Ingest has run. Execution time: $time seconds");
exit(0);
