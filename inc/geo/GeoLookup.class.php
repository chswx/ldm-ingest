<?php
/**
 * GeoLookup class
 * 
 * Makes it really easy to get counties (and eventually specific cities)
 * for text alerts
 * 
 * Methods are called statically -- we only need this once.
 */

class GeoLookup {
	
	static $zones = array(
			'SCZ050' => 'Charleston',
			'SCZ052' => 'Tidal Berkeley',
			'SCZ045' => 'Inland Berkeley',
			'SCZ044' => 'Dorchester',
			'SCC015' => 'Berkeley',
			'SCC019' => 'Charleston',
			'SCC035' => 'Dorchester'
		);

	/**
	 * Get NWS forecast zones or counties from supplied zone/county codes.
	 * 
	 * @param array $zone_codes Array of zone codes to translate.
	 * @return array Zone names if search is successful, empty array otherwise. Use empty() to check.
	 * @todo Switch this to use the Updraft GIS dataset.
	 * @static
	 */
	static function get_zones($zone_codes) {
		$zone_names = array();		

		foreach($zone_codes as $key => $zone) {
			//echo "Zone code: $zone\n";
			if(!empty(self::$zones[$zone])) {
				$zone_names[] = self::$zones[$zone];
			}
		}

		// Tidal Berkeley and Inland Berkeley are redundant -- it's all Berkeley if they're both there
		// Hack to remove those two counties and add the combined one instead
		if(in_array('Tidal Berkeley',$zone_names) && in_array('Inland Berkeley',$zone_names)) {
			$zone_names = array_filter($zone_names,function($var) { return !strpos($var,"Berkeley"); } );
			$zone_names[] = "Berkeley";
			sort($zone_names);
		}

		return $zone_names;
	}
}