<?php
/*
 * Configuration: holds various interpretations of NWS product text, sets up Tweet text, etc...
 */

//
// Tweet text based on VTEC action identifiers
// Non-VTEC products will be rare on this bot.
// TODO: Make these configurable via Mustache or some other language.
//

$chswx_tweet_text = array(
	// New VTEC product in effect
	'NEW' => $product_name . ' now in effect for ' . $location . ' until ' . $exp_time . '.',
	// Product continues (especially convective watches and warnings)
	'CON' => $product_name . ' for ' . $location . ' continues until ' . $exp_time . '.',
	// Product will be allowed to expire at scheduled time
	'EXP' => $product_name . ' for ' . $location . ' will be allowed to expire on time at ' . $exp_time . '.',
	// Product has been cancelled ahead of schedule (typically convective watches and warnings)
	'CAN' => $product_name . ' for ' . $location . ' has been cancelled.',
	// Product extended in time (rare, typically for convective watches)
	'EXT' => $product_name . ' for ' . $location . ' has been extended until ' . $exp_time . '.',
	// Product extended in area (typically flood watches, heat advisories) -- we'll treat this as a new issuance
	'EXA' => $product_name . ' now in effect for ' . $location . ' until ' . $exp_time . '.',
	// Product extended both in area and time. Again, treat like a new issuance, with language superseding previous issuance
	'EXB' => $product_name . ' now in effect for ' . $location . ' until ' . $exp_time . '.',
	// TODO: Indicate what product was upgraded from. Don't see this in the wild often.
	'UPG' => $product_name . ' now in effect for ' . $location . ' until ' . $exp_time . '.', 
	// Not yet displaying corrections, but TODO enable this when warnings are published to the Web and tweeted.
	'COR' => $product_name . ' for ' . $location . ' has been corrected.',
	// Not sure when we would see this one, either.  Including for completeness but I don't expect to tweet it.
	'ROU' => $product_name . ' issued routinely.'
);

//
// Tweet hashtag
//

define('HASHTAG','#chswx');