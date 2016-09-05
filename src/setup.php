#!/usr/bin/php
<?php
/**
 * Setup utility for the Updraft LDM ingestor and alerter combos.
 */

require_once('../vendor/autoload.php');

echo "Opening the connection to the local RethinkDB instance...\n";
$conn = r\connect('localhost');

echo "Checking for and creating (if needed) the Updraft database...\n";
$dblist = r\dbList()->run($conn);
if(!in_array('updraft',$dblist)) {
    echo "Updraft database not there, making it...";
    try {
        $result = r\dbCreate('updraft')->run($conn);
    }
    catch (ReqlRuntimeError $e) {
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
    'geo_counties'      // County outlines in GeoJSON
);

echo "Setting up database tables for the Updraft LDM tools...\n";
$tablelist = r\db('updraft')->tableList()->run($conn);
foreach($tables as $table) {
    if(in_array($table,$tablelist)) {
        echo "Skipping creation of $table, already exists\n";
    } else {
        echo "Creating table $table\n";
        r\tableCreate($table)->run($conn);
    }
}
echo "Tables created and we're out of here!\n";
exit(0);
