<?php
/*
 * Parses SPC watch probabilities.
 * Alerts if the watch is a PDS.
 */

class WWUS40 extends NWSProduct {

	var $tweet_templates = array(
		// Brief explanation of concerns.
		'CONCERNS_GENERIC' => 'Any thunderstorms that form may produce {{phenomena}}.',
		// Isolated tornadoes possible
		'CONCERNS_ISOTOR' => 'Isolated tornadoes are the primary severe weather threat.',
		// If strong tornado probability is over 50%
		'CONCERNS_STRONGTOR' => 'Thunderstorms may produce tornadoes with little or no warning. A few tornadoes could be particularly strong.',
		// Enhance wording for PDS watch
		'CONCERNS_STRONGTOR_PDS' => 'The potential exists for long-tracked, violent tornadoes capable of extreme damage.',
		// If widespread wind damage is possible (destructive winds over 50%)
		'CONCERNS_DESWIND' => 'Destructive straight-line wind gusts are possible with any thunderstorms.',
		// Enhance wording for PDS watch
		'CONCERNS_DESWIND_PDS' => 'A widespread destructive straight-line wind event is likely. Straight-line winds can do as much damage as tornadoes.',
		// If large hail over 2" is expected
		'CONCERNS_LARGEHAIL' => 'Large hail to {{size}}-size can be expected. Take steps to protect sensitive property now in case a warning is issued.',
		// Fragment for additional hazards
		'CONCERNS_HAZARD_FRAGMENT' => ' Thunderstorms may also produce {{phenomena}}.',
		// PDS watch (separate tweet)
		'PDS' => 'THIS IS A PARTICULARLY DANGEROUS SITUATION. Stay weather-alert! Be prepared to act to save your life if a warning is issued.'
	);

	function parse() {
		global $active_zones;
		
		$output = $this->get_product_text();
		$output = str_replace("\r\n", "", $output);

		// Split product into lines.
		$product_line = explode("\r", $output);
		//print_r($product_line);
		
		// Line 9 -- get watch info
		$product_code = $product_line[8];
		$product_info = explode(' ', $product_code);

		if($product_info[0] == "WS")
		{
			$product_name = "Severe Thunderstorm Watch";
		}
		else
		{
			$product_name = "Tornado Watch";
		}

		$this->properties['product_name'] = $product_name . " " . ltrim($product_info[1],'0');
		$this->properties['watch_number'] = ltrim($product_info[1],'0');

		// Lines 11-17 -- probabilities
		// In this order:
		// -- 2 or more tornadoes
		// -- 1 or more strong (F2-F5) tornadoes
		// -- 10 or more severe wind events
		// -- 1 or more destructive wind event
		// -- 10 or more severe hail events
		// -- 1 or more hail events with hail > 2"
		// -- 6 or more combined severe hail/wind events
		for($i = 10; $i < 17; $i++) {
			$prob_line = explode(": ",$product_line[$i]);
			$watch_prob[] = rtrim(ltrim($prob_line[1],"< "),'%');
		}
		
		$this->properties['probabilities']['tor'] = $watch_prob[0];
		$this->properties['probabilities']['strongtor'] = $watch_prob[1];
		$this->properties['probabilities']['wind'] = $watch_prob[2];
		$this->properties['probabilities']['deswind'] = $watch_prob[3];
		$this->properties['probabilities']['hail'] = $watch_prob[4];
		$this->properties['probabilities']['largehail'] = $watch_prob[5];
		$this->properties['probabilities']['combined'] = $watch_prob[6];

		// Lines 20-25 -- attributes
		// In this order:
		// -- Max hail (inches)
		// -- Max gusts (kts)
		// -- Max cloud tops
		// -- Storm motion vector
		// -- PDS
		for($i = 20; $i < 25; $i++) {
			$att_line = explode(": ", $product_line[$i]);
			//print_r($prob_line);
			$watch_att[] = trim($att_line[1]);
		}

		$this->properties['attributes']['max_hail'] = $watch_att[0];
		$this->properties['attributes']['max_gusts'] = $watch_att[1];
		$this->properties['attributes']['max_tops'] = $watch_att[2];
		$this->properties['attributes']['smv'] = $watch_att[3];
		$this->properties['attributes']['pds'] = ($watch_att[4] == "YES") ? true : false;	

		if($this->is_tracked()) {
			$this->properties['relay'] = true;
			$this->untrack_watch_number();
		}
		return $this->properties;
	}

	/**
     * Get the name of the product.
     * 
     * @return string Product name
     */
	function get_name() {
		return $this->properties['product_name'];
	}

	/**
	 * Get expiration time from the product.
	 * 
	 * @return string Expiration time
	 */
	function get_expiry() {
		return null;
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
	 * Advertise if a watch is a PDS watch
	 * 
	 * @return boolean
	 */
	function is_PDS() {
		return $this->properties['attributes']['pds'];
	}

    /**
     * Override tweets -- we will provide our own
     */
    function get_tweets() {
    	$tweet_text = array();
    	if($this->can_relay()) {
	    	$m = new Mustache;

	    	// Set the tweet template to null for the moment.
	    	$tweet_template = null;

	    	// Flag to turn on additional hazards if prevalent.
	    	$use_addendum = false;

	    	/**
	    	 * Decide what to feature in the first tweet.
	    	 */
	    	
	    	// Strong tornadoes take precedence, especially if probability is over 30%
	    	if($this->properties['probabilities']['strongtor'] >= 30) {
	    		$tweet_template = 'CONCERNS_STRONGTOR';
	    		// Enhance the wording for PDS watches
	    		if($this->is_PDS()) {
	    			$tweet_template = 'CONCERNS_STRONGTOR_PDS';
	    		}
	    	}
	    	// Isolated tornadoes possible
	    	else if($this->properties['probabilities']['tor'] >= 30)
	    	{
	    		$tweet_template = "CONCERNS_ISOTOR";
	    	}
	    	// Destructive winds possible
	    	else if($this->properties['probabilities']['deswind'] >= 30) {
	    		$tweet_template = "CONCERNS_DESWIND";
	    		// Enhance wording for PDS watches
	    		if($this->is_PDS()) {
	    			$tweet_template = "CONCERNS_DESWIND_PDS";
	    		}
	    	}
	    	// Very large hail possible (2+")
	    	else if($this->properties['probabilities']['largehail'] >= 30) {
	    		$tweet_template = "CONCERNS_LARGEHAIL";
	    		// No PDS watches for hail that I've ever seen...
	    	}
	    	else {
	    		$tweet_template = 'CONCERNS_GENERIC';
	    	}

	    	// Do we have a defined tweet template? If so, check the addendum
			if(!is_null($tweet_template) && $tweet_template != 'CONCERNS_GENERIC' && !$this->is_PDS()) {
				// Check if we need the addendum
				if($this->properties['probabilities']['wind'] >= 30 || $this->properties['probabilities']['hail'] >= 30) {
					// We will need it
					$use_addendum = true;
				}
			}

	    	// Check appropriate phenomena (moderate percentage or higher, per SPC)
			if($this->properties['probabilities']['wind'] >= 30) {
				$tweet_vars['phenomena'] = "damaging winds";
			}

			if($this->properties['probabilities']['hail'] >= 30) {
				if(!empty($tweet_vars['phenomena'])) {
					$tweet_vars['phenomena'] .= " and large hail";
				}
				else
				{
					$tweet_vars['phenomena'] = "large hail";
				}
			}

			// Fall-back position
			if(empty($tweet_vars['phenomena'])) {
				$tweet_vars['phenomena'] = "damaging winds and large hail";
			}

	    	// Generate a tweet
	    	if(empty($tweet_vars['phenomena']) && !$use_addendum) {
	    		$tweet_text[] = $m->render($this->tweet_templates[$tweet_template]);
	    	}
	    	else if($use_addendum) {
	    		$tweet_text[] = $m->render($this->tweet_templates[$tweet_template] . $this->tweet_templates['CONCERNS_HAZARD_FRAGMENT'],$tweet_vars);
	    	}
	    	else {
	    		$tweet_text[] = $m->render($this->tweet_templates[$tweet_template],$tweet_vars);
	    	}
	    	
	    	/**
	    	 * If this is a PDS watch, follow up with an additional tweet.
	    	 */
	    	
	    	if($this->is_PDS()) {
	    		$tweet_text[] = $m->render($this->tweet_templates['PDS']);
	    	}
	    }
    	return $tweet_text;
    }

    /**
     * Compute a plain-English hail size given a value in inches.
     * Based on chart by WFO ALY: http://www.erh.noaa.gov/aly/Severe/HailSize_Chart.htm
     * 
     * @param $size Size of hailstone in inches.
     * @return string Plain text description of size.
     */
    function get_hail_size($size) {
    	switch($size) {
    		case '.25':
    			$obj = 'Pea';
    			break;
    		case '.50':
    			$obj = 'Dime';
    			break;
    		case '.75':
    			$obj = 'Penny';
    			break;
    		case '.90':
    			$obj = 'Nickel';
    			break;
    		// Severe criteria threshold
    		case '1.0':
    			$obj = 'Quarter';
    			break;
    		case '1.25':
    			$obj = "Half dollar";
    			break;
    		case '1.5':
    			$obj = "Ping-pong ball";
    			break;
    		case '1.75':
    			$obj = "Golf ball";
    			break;
    		case '2':
    			$obj = "Hen egg (2\")";
    			break;
    		case '2.25':
    			$obj = "2.25\" diameter";
    			break;
    		case '2.5':
    			$obj = "Tennis ball";
    			break;
    		case '2.75':
    			$obj = "Baseball";
    			break;
    		case '3':
    			$obj = "Teacup";
    			break;
    		case '4':
    			$obj = "Grapefruit";
    			break;
    		case '4.5':
    			$obj = "Softball";
    			break;
    	}
    	return $obj;
    }

    /**
     * Check if this is a tracked watch.
     */
    
    private function is_tracked() {
    	$file = "trackwatch";
		if(file_exists($file)) {
			$fh = fopen($file, 'r');
			$watch_number = fgets($fh);
			fclose($fh);
			$result = ltrim($watch_number,0) == $this->properties['watch_number'];
		}
		else
		{
			$result = false;
		}
		return $result;
    }

    /**
     * Remove the trackwatch file.
     * 
     * @return void
     */
    private function untrack_watch_number()
    {
    	$file = "trackwatch";
    	unlink($file);
    }
}