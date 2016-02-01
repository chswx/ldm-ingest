<?php
/**
 * @static class Format
 * Utilities to assist in formatting output
 */

class Format
{
    /**
     * Generate a plain-English list of counties for use in a Mustache template.
     *
     * @static
     * @return string Locations in the list
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

    /**
     * Determine if given timestamp falls within tomorrow.
     *
     * @return  boolean True if it is tomorrow, false otherwise
     * @static
     */
    static function is_tomorrow($timestamp) {
        $date = date('Y-m-d', $timestamp);
        $today = date('Y-m-d');
        $tomorrow = date('Y-m-d', strtotime('tomorrow'));

        return ($date == $tomorrow);
    }

    /**
     * Fast function call to determine if we're in the future here.
     *
     * @return boolean True if the product goes into effect in the future, false otherwise.
     * @static
     */

    static function is_future($timestamp, $curr_timestamp) {
        if($timestamp > $curr_timestamp) {
            // Product effective timestamp per VTEC is in the future.
            return true;
        }

        // VTEC timestamp has occurred or is zeroed out (happens with Severe Weather Statements)
        return false;
    }
}