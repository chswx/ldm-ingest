<?php
/*
 * NWSProduct class
 * Defines most of what is a National Weather Service text product and puts it out into easily reusable chunks.
 * Portions adapted from code by Andrew: http://phpstarter.net/2010/03/parse-zfp-zone-forecast-product-data-in-php-option-1/
*/

abstract class NWSProduct {
    /**
     * Raw product text (with some light cleanup).
     *
     * @var string Product text.
     */
    var $raw_product;

    /**
     * Issuing office.
     * 
     * @var string WFO
     */
    
    var $wfo;

    /**
     * Holds the product's segments, if any. Generate events from these later if needed.
     * 
     * @var mixed Segments
     */
    
    var $segments;

    /**
     * This product's list of effective zones.
     * Returned as zone codes for decoding later by other scripts.
     *
     * @var array Zones
     */
    var $zones;

    /**
     * Constructor.
     */
    function __construct( $prod_info, $product_text ) {
        // Extract info from the $prod_info array...
        $this->office = $prod_info['office'];   // Issuing office
        $this->awips = $prod_info['awips'];     // AWIPS code
        // Keep the raw product around for now
        $this->raw_product = $product_text;

        // Parse the product out into segments.
        
        $this->segments = $this->parse();
    }

    /**
     * Generic parsing ability.
     * STRONGLY recommend overriding in a WMO-specific file
     */
    
    function parse() {
        return split_product();
    }

    /**
     * Generate events from this product, if applicable.
     */
    function generate_events() {
        foreach($this->segments as $segment) {

        }
    }

    /**
     * Return the zones for this product.
     *
     * @return array of zones
     */
    function get_zones() {
        //print_r($this->properties['zones']);
        return $this->zones;
    }

    /**
     * Return the unencumbered product text
     *
     * @return string Product text
     */
    function get_product_text() {
        return $this->raw_product;
    }

    //
    // Valid Time Extent Code (VTEC) support
    // Lots of useful information in one string about nature of product, start and end times, etc.
    // TODO: Implement H-VTEC for hydrological hazards
    //

    /*
     * Get VTEC strings if they exist...otherwise, return false
     */
    function get_vtec() {
        if(!empty($this->vtec_strings))
        {
            foreach($vtec_strings as $vtec_string) {
                $strings[] = $vtec_string;
            }
            // Return an array of VTEC strings
            return $strings;
        }
        // No VTEC string found
        return false;
    }

    /**
     * Checks if a segment has a VTEC message.
     * 
     * @return boolean
     */
    function has_vtec($segment) {
        // Match all alerts, but we will only use operational warnings
        $regex = "/\/([A-Z]{1})\.(NEW|CON|EXP|CAN|EXT|EXA|EXB|UPG|COR|ROU)\.([A-Z]{4})\.([A-Z]{2})\.([A-Z]{1})\.([0-9]{4})\.([0-9]{6})T([0-9]{4})Z-([0-9]{6})T([0-9]{4})Z\//";

        if ( preg_match_all( $regex, $data, $matches, PREG_SET_ORDER ) ) {
            // If the VTEC library is not loaded, go ahead and get it
            if(!defined(VTEC_LIB)) {
                include('VTECString.class.php');
            }
            foreach ( $matches as $match => $key ) {
                $this->vtec_strings[$key] = new VTECString( $match );
            }
        }
    }

    /**
     * Indicates if this product has multiple VTEC strings.
     * If so, handle accordingly upstream
     *
     * @return boolean
     */
    function has_multiple_vtec() {
        return count( $this->vtec_strings ) > 1;
    }

    /**
     * Was this product issued for a particular zone?
     *
     * @param array   $zones Array of zone codes to check against
     * @return boolean Array search result - true if found, false if not
     */
    function in_zone( $zones ) {
        foreach ( $zones as $zone ) {
            //echo "Checking zone $zone\n";
            if ( in_array( $zone, $this->properties['zones'] ) ) {
                return true;
            }
            else {
                $array_search_result = false;
            }
        }

        return $array_search_result;
    }

    /**
     * Retrieve product templates.
     * Overridden in more specific classes. Return null here.
     * @todo revisit this in a pub/sub world
     *
     * @return null
     */
    function get_product_templates() {
        return null;
    }

    //
    // Private functions
    //

    /**
     * The NWS combines does not repeat the state code for multiple zones...not good for our purpose
     * All we want to do here is convert ranges like INZ021-028 to INZ021-INZ028
     * We will also call the function to expand the ranges here.
     * See: http://www.weather.gov/emwin/winugc.htm
     */
    protected function parse_zones( $data ) {
        $data = $this->get_product_text();

        $output = str_replace( array( "\r\n", "\r" ), "\n", $data );
        $lines = explode( "\n", $output );
        $new_lines = array();

        foreach ( $lines as $i => $line ) {
            if ( !empty( $line ) )
            $new_lines[] = trim( $line );
        }
        $data = implode( $new_lines );

        /* split up individual states - multiple states may be in the same forecast */
        $regex = '/(([A-Z]{2})(C|Z){1}([0-9]{3})((>|-)[0-9]{3})*)-/';

        $count = preg_match_all( $regex, $data, $matches );
        $total_zones = '';

        foreach ( $matches[0] as $field => $value ) {
            /* since the NWS thought it was efficient to not repeat state codes, we have to reverse that */
            $state = substr( $value, 0, 3 );
            $zones = substr( $value, 3 );

            /* convert ranges like 014>016 to 014-015-016 */
            $zones = $this->expand_ranges( $zones );

            /* hack off the last dash */
            $zones = substr( $zones, 0, strlen( $zones ) - 1 );
            $zones = $state . str_replace( '-', '-'.$state, $zones );

            $total_zones .= $zones;
        }


        $total_zones = explode( '-', $total_zones );
        // return $total_zones;
        $this->properties['zones'] = $total_zones;
    }

    /**
     * The NWS combines multiple zones into ranges...not good for our purpose
     * All we want to do here is convert ranges like 014>016 to 014-015-016
     * See: http://www.weather.gov/emwin/winugc.htm
     */
    protected function expand_ranges( $data ) {
        //$data = $this->get_product_text();

        $regex = '/(([0-9]{3})(>[0-9]{3}))/';

        $count = preg_match_all( $regex, $data, $matches );

        foreach ( $matches[0] as $field => $value ) {
            list( $start, $end ) = explode( '>', $value );

            $new_value = array();
            for ( $i = $start; $i <= $end; $i++ ) {
                $new_value[] = str_pad( $i, 3, '0', STR_PAD_LEFT );
            }

            $data = str_replace( $value, implode( '-', $new_value ), $data );
        }

        return $data;
    }

    /**
     * Split the product by $$ if needed.
     */
    function split_product() {   
        // Check if the product contains $$ identifiers for multiple products
        if(strpos($this->raw_product, "$$")) {
            // Loop over the file for multiple products within one file identified by $$
            $products = explode('$$',trim($this->raw_product), -1);
        }
        else {
            // No delimiters
            $products = array(trim($this->raw_product));
        }

        return $segments;
    }
}