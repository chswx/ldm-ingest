#!/usr/bin/env php
<?php
/**
 * Setup utility for the @chswx LDM ingestor and alerter combos.
 */

ini_set("memory_limit", "512M");

require_once "../vendor/autoload.php";

define("DATABASE_NAME", "chswx");
define("DATABASE_SERVER", "chswx-rethink-dev.orb.local");
define("IMPORT_GEOSPATIAL", true);
define("IMPORT_ZONES", true);

echo "Opening the connection to the local RethinkDB instance...\n";
$conn = r\connect(DATABASE_SERVER);

echo "Checking for and creating (if needed) the " .
    DATABASE_NAME .
    " database...\n";
$dblist = r\dbList()->run($conn);
if (!in_array(DATABASE_NAME, $dblist)) {
    echo DATABASE_NAME . " database not there, making it...";
    try {
        $result = r\dbCreate(DATABASE_NAME)->run($conn);
    } catch (ReqlRuntimeError $e) {
        print_r($e);
        die(
            "Couldn't create the " .
                DATABASE_NAME .
                " database. Can't continue."
        );
    }
} else {
    echo DATABASE_NAME . " database exists...moving on.\n";
}

// Use the database
$conn->useDb(DATABASE_NAME);

$tables = [
    "geo_cities", // Badly designed websites
    "geo_counties", // County outlines in GeoJSON
    "geo_zones", // NWS forecast zones, correlated by county
    "geo_custom_locs", // Custom locations
    "products", // All incoming products
    "events", // Holds ongoing events (VTEC, water level, etc.)
];

echo "Setting up database tables for the @chswx LDM bridge...\n";
$tablelist = r\db(DATABASE_NAME)
    ->tableList()
    ->run($conn);
$count = 0;
$skipped = 0;
foreach ($tables as $table) {
    if (in_array($table, $tablelist)) {
        $skipped++;
        echo "Skipping creation of $table, already exists\n";
    } else {
        $count++;
        echo "Creating table $table\n";
        r\tableCreate($table)->run($conn);
    }
}
echo "{$count} tables created, {$skipped} tables skipped\n";

// Set up geospatial index
if (defined("IMPORT_GEOSPATIAL") && IMPORT_GEOSPATIAL) {
    echo "Importing geospatial data...\n";
    echo "Step 1: Cities\n";
    $file = "../data/awips_cities_geojson.geojson";
    $json = file_get_contents($file);
    $decoded = json_decode($json);
    $complete = 0;
    $total = count($decoded);
    echo "Importing $total items...\n";
    // 200 inserts at a time
    foreach ($decoded as $item) {
        if (empty($item->properties->ID)) {
            continue;
        }
        $item->geometry = r\geojson((array) $item->geometry);
        $item->id = "{$item->properties->ID}";
        $result = r\table("geo_cities")->insert($item)->run($conn);
        if ($result) {
            $complete++;
            echo "$complete records of $total complete\r";
        }
    }

    // Free memory
    unset($json);
    unset($decoded);

    // Set up geospatial indexes
    echo "\nSetting up geospatial indexes...\n";
    $geo_indexes = [
        "geo_cities" => "geometry",
    ];
    foreach ($geo_indexes as $table => $index) {
        try {
            r\table($table)->indexCreateGeo($index)->run($conn);
        } catch (r\Exceptions\RQLServerError $e) {
            echo "Index may already exist.\n";
        }
    }
}

if (defined("IMPORT_ZONES") && IMPORT_ZONES) {
    echo "Importing: Zones\n";
    /*
     * Format:
     *  STATE	    Two character state abbreviation
        ZONE	    Three character zone number
        CWA	        Three character CWA ID (of the zone, starting with 01 May 2018 file)
        NAME	    Zone name
        STATE_ZONE	5 character state + three character zone number
        COUNTY	    County name
        FIPS	    5 character state-county FIPS code
        TIME_ZONE	Time zone of polygon (See comments on county page)
        FE_AREA	    Feature Area (location in STATE - See comments on county page)
        LAT	        Latitude of centroid of the zone
        LON	        Longitude of centroid of the zone
     */
    $count = 0;
    $county_count = 0; // lol
    $counties = []; // set up the counties array

    // Read the file.
    foreach (file("../data/zone_correlation.dbx") as $line) {
        $raw_zone = explode("|", $line);

        // First, create a zone
        $zone = new stdClass();
        $zone->id = $raw_zone[0] . "Z" . $raw_zone[1];
        $zone->county_id = $raw_zone[0] . "C" . substr($raw_zone["6"], 2);
        $zone->state = $raw_zone[0];
        $zone->zone = $raw_zone[1];
        $zone->cwa = $raw_zone[2];
        $zone->name = $raw_zone[3];
        $zone->state_zone = $raw_zone[4];
        $zone->county_name = $raw_zone[5];
        $zone->fips = $raw_zone[6];
        $zone->time_zone = $raw_zone[7];
        $zone->feature_area = $raw_zone[8];
        $zone->lat = $raw_zone[9];
        $zone->lon = $raw_zone[10];
        $zone->type = "zone";

        // Next, skim counties from zones
        if (!isset($counties[$zone->county_id])) {
            $county = new stdClass();
            $county->id = $zone->county_id;
            $county->name = $zone->county_name;
            $county->state = $zone->state;
            $county->cwa = $zone->cwa;
            $county->fips = $zone->fips;
            $county->type = "county";

            // Do not duplicate inserts
            $counties[$zone->county_id] = $county;

            $result = r\table("geo_zones")->insert($county)->run($conn);
            if ($result) {
                $county_count++;
            }
        }

        $result = r\table("geo_zones")->insert($zone)->run($conn);
        if ($result) {
            $count++;
            echo "$count zone records inserted; $county_count county records inserted\r";
        }
    }

    // Set up indexes
    echo "\nSetting up indexes...\n";
    $tables = [
        "geo_zones" => ["county_name", "county_id"],
    ];

    foreach ($tables as $table => $indexes) {
        try {
            foreach ($indexes as $index) {
                r\table($table)->indexCreate($index)->run($conn);
            }
        } catch (r\Exceptions\RqlServerError $e) {
            echo "Index may already exist.\n";
        }
    }
}

echo "Setup complete\n";
exit(0);

