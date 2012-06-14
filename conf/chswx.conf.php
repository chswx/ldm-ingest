<?php
/*
 * Configuration: holds various interpretations of NWS product text, sets up Tweet text, etc...
 */

//
// Set timezone
//

date_default_timezone_set('America/New_York');

//
// Tweet text based on VTEC action identifiers
// Non-VTEC products will be rare on this bot.
// TODO: Make these configurable via Mustache or some other language.
//

$product_name = "";
$location = "";
$exp_time = "";

//
// VTEC phenomena codes.
//
$vtec_phenomena_codes = array(
	'AF' => 'Ashfall',
	'AS' => 'Air Stagnation',
	'BS' => 'Blowing Snow',
	'BW' => 'Brisk Wind',
	'BZ' => 'Blizzard',
	'CF' => 'Coastal Flood',
	'DS' => 'Dust Storm',
	'DU' => 'Blowing Dust',
	'EC' => 'Extreme Cold',
	'EH' => 'Excessive Heat',
	'EW' => 'Extreme Wind',
	'FA' => 'Areal Flood',
	'FF' => 'Flash Flood',
	'FG' => 'Dense Fog',
	'FL' => 'Flood',
	'FR' => 'Frost',
	'FW' => 'Fire Weather',
	'FZ' => 'Freeze',
	'GL' => 'Gale',
	'HF' => 'Hurricane Force Wind',
	'HI' => 'Inland Hurricane',
	'HS' => 'Heavy Snow',
	'HT' => 'Heat', 
	'HU' => 'Hurricane',
	'HW' => 'High Wind',
	'HY' => 'Hydrologic',
	'HZ' => 'Hard Freeze',
	'IP' => 'Sleet',
	'IS' => 'Ice Storm',
	'LB' => 'Lake Effect Snow and Blowing Snow',
	'LE' => 'Lake Effect Snow',
	'LO' => 'Low Water',
	'LS' => 'Lakeshore Flood',
	'LW' => 'Lake Wind',
	'MA' => 'Marine',
	'RB' => 'Small Craft for Rough Bar',
	'RP' => 'Rip Currents', 	// NWS CHS addition
	'SB' => 'Snow and Blowing Snow',
	'SC' => 'Small Craft',
	'SE' => 'Hazardous Seas',
	'SI' => 'Small Craft for Winds',
	'SM' => 'Dense Smoke',
	'SN' => 'Snow',
	'SR' => 'Storm',
	'SU' => 'High Surf',
	'SV' => 'Severe Thunderstorm',
	'SW' => 'Small Craft for Hazardous Seas',
	'TI' => 'Inland Tropical Storm',
	'TO' => 'Tornado',
	'TR' => 'Tropical Storm',
	'TS' => 'Tsunami',
	'TY' => 'Typhoon',
	'UP' => 'Ice Accretion',
	'WC' => 'Wind Chill',
	'WI' => 'Wind',
	'WS' => 'Winter Storm',
	'WW' => 'Winter Weather',
	'ZF' => 'Freezing Fog',
	'ZR' => 'Freezing Rain'
);

//
// VTEC significance
//

$vtec_significance_codes = array(
	'W' => 'Warning',
	'A' => 'Watch',
	'Y' => 'Advisory',
	'S' => 'Statement',
	'F' => 'Forecast',
	'O' => 'Outlook',
	'N' => 'Synopsis'
);

//
// Tweet hashtag
//

define('HASHTAG','#chswx');