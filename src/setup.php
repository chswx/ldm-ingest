#!/usr/bin/php
<?php
/**
 * Setup utility for the @chswx LDM ingestor and alerter combos.
 */

ini_set('memory_limit', '512M');

require_once('../vendor/autoload.php');

define('DATABASE_NAME', 'chswx');
define('DATABASE_SERVER', 'localhost');
// define('IMPORT_GEOSPATIAL', true);

echo "Opening the connection to the local RethinkDB instance...\n";
$conn = r\connect(DATABASE_SERVER);

echo "Checking for and creating (if needed) the " . DATABASE_NAME . " database...\n";
$dblist = r\dbList()->run($conn);
if (!in_array(DATABASE_NAME, $dblist)) {
    echo DATABASE_NAME . " database not there, making it...";
    try {
        $result = r\dbCreate(DATABASE_NAME)->run($conn);
    } catch (ReqlRuntimeError $e) {
        print_r($e);
        die("Couldn't create the " . DATABASE_NAME . " database. Can't continue.");
    }
} else {
    echo DATABASE_NAME . " database exists...moving on.\n";
}

// Use the database
$conn->useDb(DATABASE_NAME);

$tables = array(
    'geo_cities',       // Badly designed websites
    'geo_counties',     // County outlines in GeoJSON
    'geo_custom_locs',  // Custom locations
    'products'          // Bucket for incoming products

);

echo "Setting up database tables for the @chswx LDM bridge...\n";
$tablelist = r\db(DATABASE_NAME)->tableList()->run($conn);
$count = 0;
foreach ($tables as $table) {
    if (in_array($table, $tablelist)) {
        echo "Skipping creation of $table, already exists\n";
    } else {
        echo "Creating table $table\n";
        r\tableCreate($table)->run($conn);
    }
    $count++;
}
echo "{$count} tables created\n";

// Set up geospatial index
if (defined('IMPORT_GEOSPATIAL')) {
    echo "Importing geospatial data...\n";
    $file = '../data/awips_cities_geojson.geojson';
    $json = file_get_contents($file);
    $decoded = json_decode($json);
    $complete = 0;
    $total = count($decoded);
    echo "Importing $total items...\n";
    // 200 inserts at a time
    foreach ($decoded as $item) {
        $item->geometry = r\geojson((array)$item->geometry);
        $result = r\table('geo_cities')->insert($item)->run($conn);
        if ($result) {
            $complete++;
            echo "$complete record of $total complete\n";
        }
    }
}

echo "Setting up indexes...\n";
try {
    r\table('geo_cities')->indexCreateGeo('geometry')->run($conn);
} catch (r\Exceptions\RQLServerError $e) {
    echo "Index may already exist.\n";
}
echo "Setup complete\n";
exit(0);
