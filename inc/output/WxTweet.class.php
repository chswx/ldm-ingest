<?php
/*
 * Generic Twitter module
 * Will work with any product, can be extended for specific situations with others
 * Ultimately encapsulates the process with which a tweet is issued
 */

class WxTweet
{
	// Base array of basic tweet text
	// Mustache variables
	// TODO: Make these completely configurable
	var $tweet_text = array(
		// New VTEC product in effect immediately
		'NEW' => "{{product_name}} now in effect for {{location}} until {{exp_time}}.",
		// New VTEC product goes into effect at a specific time in the future
		'NEW_FUTURE' => "{{product_name}} for {{location}} will go into effect at {{start_time}} until {{exp_time}}.",
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

	// Local storage for the active product
	var $product_obj;

	// Expecting a product object...expecting too much?
	// Return tweet text for the Twitter library
	function __construct($product) {
		// Save the parsed product data here
		$this->product_obj = $product;
	}

	/**
	 * Return the rendered tweet.
	 * 
	 * @return string Tweet to send via the API.
	 * @param string $tweet_template Optionally pass in its own tweet template.
	 */
	function render_tweet($tweet_template = null) {
		// Initialize Mustache

		$m = new Mustache;

		// Template suffix declaration...just in case
		$template_suffix = '';

		$location_string = '';

		// Get friendly names of zones in an array
		$zones = GeoLookup::get_zones($this->product_obj->get_location_zones());
		//print_r($zones);
		// Render these zones out
		// TODO May need a refactor
		//echo "Number of zones: " . count($zones) . "\n";
		$zone_count = count($zones);
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

		$tweet_vars['product_name'] = $this->product_obj->get_name();
		$tweet_vars['location'] = $location_string;
		if($this->product_obj->get_expiry() == '0') {
			$tweet_vars['exp_time'] = "further notice";
		}
		else if(!is_null($this->product_obj->get_expiry())) {
			$tweet_vars['exp_time'] = date('g:i A',$this->product_obj->get_expiry());
		}
		else {
			$template_suffix = "_NO_EXPIRE";
		}

		if(!is_null($this->product_obj->get_expiry())) {
			if($this->is_future()) {
				$template_suffix = "_FUTURE";
				$tweet_vars['exp_time'] = "the future"; // TODO: Calculate this
			}
		}

		if(is_null($tweet_template)) {
			if($this->product_obj->get_vtec()) {
				$tweet_template = $this->product_obj->get_vtec_action() . $template_suffix;
			}
			else {
				$tweet_template = $template_suffix;
			}
		}

		return $m->render($this->tweet_text[$tweet_template],$tweet_vars);
	}

	/**
	 * Fast function call to determine if we're in the future here.
	 * 
	 * @return boolean True if the product goes into effect in the future, false otherwise.
	 */
	
	protected function is_future() {
		if($this->product_obj->get_vtec_effective_timestamp() > time()) {
			// Product effective timestamp per VTEC is in the future.
			return true;
		}

		// VTEC timestamp has occurred or is zeroed out (happens with Severe Weather Statements)
		return false;
	}	
}

?>