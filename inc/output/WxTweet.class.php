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

	// Current time (for time comparisons)
	var $curr_timestamp;

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

		// If we have a null tweet template coming in, set to the object's
		if(is_null($tweet_template)) {
			$tweet_text = $this->tweet_text;
		}
		else {
			$tweet_text = $tweet_template;
		}

		// Get the current timestamp
		$this->curr_timestamp = time();
		$curr_time = $this->curr_timestamp;
		// Template suffix declaration...just in case
		$template_suffix = '';

		// Generate the location string
		$location_string = $this->product_obj->get_location_string();

		$tweet_vars['product_name'] = $this->product_obj->get_name();
		$tweet_vars['location'] = $location_string;

		//
		// Determine format of expiration time.
		//
		if($this->product_obj->get_expiry() == '0') {
			// Warning is indefinite (some flood warnings, tropical cyclone watches/warnings)
			$tweet_vars['exp_time'] = "further notice";
		}
		else if(!is_null($this->product_obj->get_expiry())) {
			$expire_stamp = $this->product_obj->get_expiry();

			// For alerts starting more than 24 hours out, add effective date
			if($expire_stamp - $curr_time >= 86400) {
				$date_format = 'M j g:i A';
			}
			else { 
				$date_format = 'g:i A';
			}
			$tweet_vars['exp_time'] = date($date_format,$expire_stamp);
		}
		else {
			$template_suffix = "_NO_EXPIRE";
		}

		if(!is_null($this->product_obj->get_vtec_effective_timestamp())) {
			if($this->is_future()) {
				echo "We are in the future!\n";

				$effective_stamp = $this->product_obj->get_vtec_effective_timestamp();
				// echo "Effective timestamp: " . $effective_stamp;
				$template_suffix = "_FUTURE";
				
				// For alerts starting more than 24 hours out, add effective date
				if($effective_stamp - $curr_time >= 86400) {
					$date_format = 'M j g:i A';
				}
				else {
					$date_format = 'g:i A';
				}
				
				$tweet_vars['start_time'] = date($date_format,$effective_stamp);
			} 
		}

		if($this->product_obj->get_vtec()) {
			$template_select = $this->product_obj->get_vtec_action() . $template_suffix;
		}
		else {
			$template_select = $template_suffix;
		}
		
		return $m->render($tweet_text[$template_select],$tweet_vars);
	}

	/**
	 * Fast function call to determine if we're in the future here.
	 * 
	 * @return boolean True if the product goes into effect in the future, false otherwise.
	 */
	
	protected function is_future() {
		if($this->product_obj->get_vtec_effective_timestamp() > $this->curr_timestamp) {
			// Product effective timestamp per VTEC is in the future.
			return true;
		}

		// VTEC timestamp has occurred or is zeroed out (happens with Severe Weather Statements)
		return false;
	}	
}

?>
