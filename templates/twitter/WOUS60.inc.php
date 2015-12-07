<?php
var $templates = array(
		// New VTEC product in effect immediately
		'NEW' => "A {{product_name}} is now in effect for {{location}} until {{exp_time}}.",
		// New VTEC product goes into effect at a specific time in the future
		'NEW_FUTURE' => "A {{product_name}} is now in effect for {{location}} until {{exp_time}}.",
		// Product continues (especially convective watches and warnings)
		'CON' => "{{product_name}} for {{location}} continues until {{exp_time}}.",
		// VTEC continuation of product in the future. Treat as a reminder.
		'CON_FUTURE' => "Reminder: {{product_name}} for {{location}} will go into effect at {{start_time}} until {{exp_time}}.", 
		// Product will be allowed to expire at scheduled time
		'EXP' => "{{product_name}} for {{location}} will expire at {{exp_time}}.",
		// Product has been cancelled ahead of schedule (typically convective watches and warnings)
		'CAN' => "{{product_name}} for {{location}} has been cancelled.",
		// Product extended in time (rare, typically for convective watches)
		'EXT' => "{{product_name}} for {{location}} has been extended until {{exp_time}}.",
		// Product extended in area (typically flood watches, heat advisories) -- we'll treat this as a new issuance
		'EXA' => "{{product_name}} now in effect for {{location}} until {{exp_time}}.",
		// Product extended both in area and time. Again, treat like a new issuance, with language superseding previous issuance
		'EXB' => "{{product_name}} now in effect for {{location}} until {{exp_time}}.",
		// TODO: Indicate what product was upgraded from. Don't see this in the wild often, don't tweet upgrades.
		// Use later: 'UPG' => "{{old_product_name}} for {{location}} has been upgraded to a {{new_product_name}} until {{exp_time}}.", 
		// For now...
		'UPG' => "{{product_name}} now in effect for {{location}} until {{exp_time}}.",
		// Not yet displaying corrections, but TODO enable this when warnings are published to the Web and tweeted.
		'COR' => "{{product_name}} now in effect for {{location}} until {{exp_time}}.",
		// Not sure when we would see this one, either.  Including for completeness but I don't expect to tweet it.
		'ROU' => "{{product_name}} issued routinely.",
		// Some random non-expiring product
		'_NO_EXPIRE' => "{{product_name}} issued for {{location}}."
	);
?>