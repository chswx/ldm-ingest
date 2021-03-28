#!/usr/bin/php
<?php

/*
 * LDM Product Ingestor
 * Command-line tool
 * Main entry point for LDM ingest. This hands off to a factory which generates a class for specific products.
 * Many thanks to @blairblends, @edarc, and the Updraft team for help and inspiration
 */

namespace chswx\LDMIngest;

use chswx\LDMIngest\Utils;
use chswx\LDMIngest\Ingestor;
use chswx\LDMIngest\Storage\ProductStorage;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Dotenv;

// Begin timing execution
$time_start = microtime(true);

// Default exit code
$exit_code = 0;

// Include composer autoload
include(dirname(dirname(__FILE__)) . '/vendor/autoload.php');

// Configuration
include(dirname(dirname(__FILE__)) . '/conf/chswx.conf.php');

// Include environment
$env = Dotenv\Dotenv::create(dirname(dirname(__FILE__)));
$env->load();

// Set up logging
$log = new Logger('ldm-ingest');
// Cast the log level to an int so that it can be properly read in from environment vars
$log->pushHandler(new StreamHandler($_ENV['LOG_OUTPUT'], (int) $_ENV['LOG_LEVEL']));

// Handle to DB
$db = new ProductStorage();
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
$product_obj = Ingestor\NWSProductFactory::getProduct(Utils::sanitize($m_text));

// If we're not null, victory! Encode and send on its merry way
if (!is_null($product_obj)) {
    // set a source for the product so we can sniff this out as needed.
    $product_obj->src = "ldm";
    $db->send($product_obj, $product_obj->table);

    // Have you heard the good word of our properly parsed product?
    Utils::log("Parsed product {$product_obj->pil} from {$product_obj->office} successfully");
    Utils::log("Channels: " . implode(', ', $product_obj->channels));
} else {
    // Something went wrong
    Utils::log("Error parsing.", 'error');
    $exit_code = 1;
}

Utils::exit($exit_code);
