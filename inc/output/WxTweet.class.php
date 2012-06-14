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
	protected $tweet_text = array(
		// New VTEC product in effect immediately
		'NEW' => "{{product_name}} now in effect for {{location}} until {{exp_time}}.",
		// New VTEC product goes into effect at a specific time in the future
		'NEW_FUTURE' => "{{product_name}} for {{location}} will go into effect from {{start_time}} to {{exp_time}}.",
		// Product continues (especially convective watches and warnings)
		'CON' => "{{product_name}} for {{location}} continues until {{exp_time}}.",
		// VTEC continuation of product in the future. Treat as a reminder.
		'CON_FUTURE' => "Reminder: {{product_name}} for {{location}} will go into effect from {{start_time}} to {{exp_time}}.", 
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
		'UPG' => "{{old_product_name}} for {{location}} has been upgraded to a {{new_product_name}} until {{exp_time}}.", 
		// Not yet displaying corrections, but TODO enable this when warnings are published to the Web and tweeted.
		'COR' => "{{product_name}} now in effect for {{location}} until {{exp_time}}.",
		// Not sure when we would see this one, either.  Including for completeness but I don't expect to tweet it.
		'ROU' => "{{product_name}} issued routinely."
	);

	// Local storage for the active product
	protected $product;

	// Expecting a product object...expecting too much?
	// Return tweet text for the Twitter library
	function __construct($product) {
		// Save the parsed product data here
		$this->$product = $product;

		// Shoot right over to the tweet function, return it out.
		return $this->tweet();
	}

	protected function tweet() {
		$m = new Mustache;
		$template_suffix = '';

		if($this->is_future()) {
			$template_suffix = "_FUTURE";
			$tweet_vars['exp_time'] = "the future"; // TODO: Calculate this
		}

		$tweet_template = $this->$product->get_vtec_action() . $template_suffix;

		return $m->render($tweet_template,$tweet_vars);
	}

	// Is this product going into effect in the future?  Returns true or false based on VTEC.
	protected function is_future() {
		if($product->get_vtec_effective_timestamp() > time()) {
			// Product effective timestamp per VTEC is in the future.
			return true;
		}

		// VTEC timestamp has occurred or is zeroed out (happens with Severe Weather Statements)
		return false;
	}	
}

?>