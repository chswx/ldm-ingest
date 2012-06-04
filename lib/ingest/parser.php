<?php
/* CHSWX: Main parser 
 * Adapted from code by Andrew: http://phpstarter.net/2010/03/parse-zfp-zone-forecast-product-data-in-php-option-1/
*/

class ProductParser {
	
	/**
	 * The NWS combines does not repeat the state code for multiple zones...not good for our purpose
	 * All we want to do here is convert ranges like INZ021-028 to INZ021-INZ028
	 * We will also call the function to expand the ranges here.
	 * See: http://www.weather.gov/emwin/winugc.htm
	 */
	function parse_zones($data)
	{
		/* first, get rid of newlines */
		$data = str_replace("\n", '', $data);
		
		/* split up individual states - multiple states may be in the same forecast */
		$regex = '/(([A-Z]{2})(C|Z){1}([0-9]{3})((>|-)[0-9]{3})*)-/';
		
		$count = preg_match_all($regex, $data, $matches);
		$total_zones = '';
		
		foreach ($matches[0] as $field => $value)
		{
			/* since the NWS thought it was efficient to not repeat state codes, we have to reverse that */
			$state = substr($value, 0, 3);
			$zones = substr($value, 3);
			
			/* convert ranges like 014>016 to 014-015-016 */
			$zones = expand_ranges($zones);
			
			/* hack off the last dash */
			$zones = substr($zones, 0, strlen($zones) - 1);
			$zones = $state . str_replace('-', '-'.$state, $zones);
			
			$total_zones .= $zones;
		}
		
		
		$total_zones = explode('-', $total_zones);
		return $total_zones;
	}

	/**
	 * The NWS combines multiple zones into ranges...not good for our purpose
	 * All we want to do here is convert ranges like 014>016 to 014-015-016
	 * See: http://www.weather.gov/emwin/winugc.htm
	 */
	private function expand_ranges($data)
	{
		$regex = '/(([0-9]{3})(>[0-9]{3}))/';
		
		$count = preg_match_all($regex, $data, $matches);
		
		foreach ($matches[0] as $field => $value)
		{
			list($start, $end) = explode('>', $value);
			
			$new_value = array();
			for ($i = $start; $i <= $end; $i++)
			{
				$new_value[] = str_pad($i, 3, '0', STR_PAD_LEFT);
			}
			
			$data = str_replace($value, implode('-', $new_value), $data);
		}
		
		return $data;
	}

}