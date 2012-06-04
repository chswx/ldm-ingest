#!/usr/bin/php
<?php
/* CHSWX Ingestor
 * Command-line tool
 * Main entry point for LDM ingest
 * Many thanks to @blairblends, @edarc, and the Updraft team for help and inspiration
 */

//
// Initiate logging to syslog
//

// openlog('chswx',LOG_ODELAY,LOG_LOCAL0);

//
// Support Files
//

// Bring in configuration.
include('conf/chswx.conf.php');

// Bring in the class definition for NWSProduct.
include('inc/NWSProduct.class.php');

// Bring in the Twitter OAuth lib.
include('lib/twitter/twitteroauth/twitteroauth.php');

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

// For now, var_dump the products

$myFile = "/home/ldm/data/logging/chswx" . time() . ".txt";
$fh = fopen($myFile, 'w') or die("can't open file");
fwrite($fh,var_dump($products));
fclose($fh);

//syslog(LOG_DEBUG,var_dump($products));