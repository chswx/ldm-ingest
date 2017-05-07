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
include(dirname(dirname(__FILE__)) . '/vendor/autoload.php');

// Configuration
include(dirname(dirname(__FILE__)) . '/conf/chswx.conf.php');

// Handle to DB
$db = new ProductStorage;
if (empty($db->conn)) {
    Utils::exitWithError("Aborting due to database initialization failure.");
}

// #10: Pipe in products from the LDM vs. reading in written files.
// This gives us a level of concurrence that we wouldn't otherwise have...
// ...and sets us up to do longer-running piped processes down the road (#26)
Utils::log("Ingest has begun from STDIN.");
// Pipe in text from STDIN
$m_text = stream_get_contents(STDIN);

// If the text is empty, abort with a non-zero error code
if (empty($m_text)) {
    Utils::exitWithError("Aborting ingest due to empty input");
}

// Send to the factory to parse the product.
$product_obj = NWSProductFactory::getProduct(Utils::sanitize($m_text));

// If we're not null, victory! Encode and send on its merry way
if (!is_null($product_obj)) {
    $table = $product_obj->table;
    // Unset the table now to prevent storing the name of the table along with the product
    unset($product_obj->table);
    unset($product_obj->raw_product);
    // Send to our product storage system
    $db->send($product_obj, $table);

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
