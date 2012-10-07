<?php
/**
 * @static class Format
 * Utilities to assist in formatting output
 */

class Format
{
	/**
	 * Generate a plain-English list of counties for use in a Mustache template.
	 */
	
	static function generate_county_list_string($zone_codes) {
		$zones = GeoLookup::get_zones( $zone_codes );
        $zone_count = count( $zones );
        foreach ( $zones as $zone ) {
            $location_string .= $zone;
            if ( $zone_count > 2 ) {
                $location_string .= ", ";
            }
            else if ( $zone_count > 1 ) {
                $location_string .= " and ";
            }
            --$zone_count;
        }

        /**
         *
         *
         * @todo Need to flesh out some more -- in LA, these are Parishes; in VA, independent cities are named
         */
        if ( sizeof( $zones ) > 1 ) {
            $location_string .= " counties";
        }
        else {
            $location_string .= " County";
        }

        return $location_string;
	}
}