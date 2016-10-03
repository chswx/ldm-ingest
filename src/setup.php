#!/usr/bin/php
<?php
/**
 * Setup utility for the Updraft LDM ingestor and alerter combos.
 */

ini_set('memory_limit', '512M');

require_once('../vendor/autoload.php');

echo "Opening the connection to the local RethinkDB instance...\n";
$conn = r\connect('localhost');

echo "Checking for and creating (if needed) the Updraft database...\n";
$dblist = r\dbList()->run($conn);
if (!in_array('updraft', $dblist)) {
    echo "Updraft database not there, making it...";
    try {
        $result = r\dbCreate('updraft')->run($conn);
    } catch (ReqlRuntimeError $e) {
        print_r($e);
        die("Couldn't create the updraft database. Can't continue.");
    }
} else {
    echo "Updraft database exists...moving on.\n";
}

// Use the database
$conn->useDb('updraft');

$tables = array(
    'spc_watch',        // SPC watches (aviation, WOU, probabilities)
    'wwa',              // Watches/warnings/advisories (local WFOs)
    'sps',              // Special weather statements
    'mesodisc',         // Mesoscale discussions (both convective and precip)
    'spc_outlook',      // SPC outlooks
    'lsr',              // Local storm reports
    'nhc',              // Hurricane Center stuff (active tropical systems)
    'rvr_flood',        // River flood warnings
    'fcst',             // Forecast data
    'misc',             // Miscellaneous products (AFDs, HWOs, etc.)
    'climate',          // Climate data
    'wpc_outlook',      // WPC Excessive Rainfall Outlooks
    'wpc_mpd',          // WPC Mesoscale Precipitation Discussions
    'geo_cities',       // Badly designed websites
    'geo_counties',     // County outlines in GeoJSON
    'products'          // Bucket for incoming products
);

echo "Setting up database tables for the Updraft LDM tools...\n";
$tablelist = r\db('updraft')->tableList()->run($conn);
foreach ($tables as $table) {
    if (in_array($table, $tablelist)) {
        echo "Skipping creation of $table, already exists\n";
    } else {
        echo "Creating table $table\n";
        r\tableCreate($table)->run($conn);
    }
}
echo "Tables created\n";

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
} catch (RQLServerError $e) {
    echo "Index may already exist.\n";
}
echo "Setup complete\n";
exit(0);
