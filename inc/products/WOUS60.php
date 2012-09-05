<?php
/*
 * SPC Watch County Notification ingestor.
 */

class WOUS60 extends NWSProduct {

	var $tweet_templates = array(
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

	function parse() {
		global $active_zones;
		
		// STEP 1: Pull in counties
		$this->parse_zones($this->get_product_text());

		// STEP 2: Parse out VTEC
		$this->parse_vtec();

		// FINAL: Return the properties array, track the watch if in our zones
		if($this->get_vtec_action() == 'NEW') {
			$this->properties['relay'] = true;
			if($this->in_zone($active_zones)) {
				$this->track_watch_number();
			}
		}
		else
		{
			$this->properties['relay'] = false;
		}
		return $this->properties;
	}

	/**
     * Get the name of the product.
     * 
     * @return string Product name
     */
	function get_name() {
		return $this->get_name_from_vtec();	
	}

	/**
	 * Get expiration time from the product.
	 * 
	 * @return string Expiration time
	 */
	function get_expiry() {
		return $this->get_expiry_from_vtec();
	}

	/**
	 * Get tweet templates for Severe Thunderstorm Watches.
	 * 
	 * @return array Tweet templates
	 */
	function get_tweet_templates() {
		return $this->tweet_templates;
	}

	/**
	 * Override of parent. We want to customize the location string.
	 */
	
	function get_location_string() {
        $zones = GeoLookup::get_zones($this->get_location_zones());
        $zone_count = count($zones);
		if($zone_count == 3) {
			$location_string = "the entire #CHS Tri-County area";
		}
		else {
			foreach($zones as $zone) {
				$location_string .= $zone;
				if($zone_count > 2)
				{
					$location_string .= ", ";
				}
				else if($zone_count > 1) {
					$location_string .= " and ";
				}
				--$zone_count;
			}

			if(sizeof($zones) > 1) {
				$location_string .= " counties";
			}
			else {
				$location_string .= " County";
			}
		}
        return $location_string;
    }

    /**
     * Track the watch number to get probabilities when they are issued by SPC in WWUS40.
     * Writes a flat file to the filesystem.
     * 
     * @return void
     */
    private function track_watch_number()
    {
    	$file = "trackwatch";
		$fh = fopen($file, 'w');
		$event = $this->get_vtec_event_number();
		fwrite($fh, $event);
		fclose($fh);
    }
}
?>