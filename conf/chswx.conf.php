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

$chswx_tweet_text = array(
	// New VTEC product in effect
	'NEW' => "{{product_name}} now in effect for {{location}} until {{exp_time}}.",
	// Product continues (especially convective watches and warnings)
	'CON' => "{{product_name}} for {{location}} continues until {{exp_time}}.",
	// Product will be allowed to expire at scheduled time
	'EXP' => "{{product_name}} for {{location}} will be allowed to expire on time at {{exp_time}}.",
	// Product has been cancelled ahead of schedule (typically convective watches and warnings)
	'CAN' => "{{product_name}} for {{location}} has been cancelled.",
	// Product extended in time (rare, typically for convective watches)
	'EXT' => "{{product_name}} for {{location}} has been extended until {{exp_time}}.",
	// Product extended in area (typically flood watches, heat advisories) -- we'll treat this as a new issuance
	'EXA' => "{{product_name}} now in effect for {{location}} until {{exp_time}}.",
	// Product extended both in area and time. Again, treat like a new issuance, with language superseding previous issuance
	'EXB' => "{{product_name}} now in effect for {{location}} until {{exp_time}}.",
	// TODO: Indicate what product was upgraded from. Don't see this in the wild often.
	'UPG' => "{{product_name}} now in effect for {{location}} until {{exp_time}}.", 
	// Not yet displaying corrections, but TODO enable this when warnings are published to the Web and tweeted.
	'COR' => "{{product_name}} now in effect for {{location}} until {{exp_time}}.",
	// Not sure when we would see this one, either.  Including for completeness but I don't expect to tweet it.
	'ROU' => "{{product_name}} issued routinely."
);

//
// Tweet hashtag
//

define('HASHTAG','#chswx');