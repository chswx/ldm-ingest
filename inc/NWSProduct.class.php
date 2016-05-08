<?php
/*
 * NWSProduct class
 * Defines most of what is a National Weather Service text product and puts it out into easily reusable chunks.
 * Portions adapted from code by Andrew: http://phpstarter.net/2010/03/parse-zfp-zone-forecast-product-data-in-php-option-1/
 */

class NWSProduct {
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
    var $office;

    /**
     * AFOS identifier.
     *
     * @var string AFOS ID
     */

    var $afos;

    /**
     * Unique stamp for this particular product.
     *
     * @var string stamp
     */

    var $stamp;

    /**
     * Holds the product's NWSProductSegments, if any. Generate events from these later if needed.
     *
     * @var mixed Array of segments
     */

    var $segments;

    /**
     * Constructor.
     */
    function __construct( $prod_info, $product_text ) {
        // Extract info from the $prod_info array...
        $this->office = $prod_info['office'];   // Issuing office
        $this->afos = $prod_info['afos'];     // AFOS code
        // Keep the raw product around for now
        $this->raw_product = $product_text;
        // Parse the product out into segments.
        $this->segments = $this->parse();
        // Set up the product stamp.
        $this->stamp = Utils::generate_stamp($this->afos, time());
    }

    /**
     * Generic parsing ability.
     * STRONGLY recommend overriding in a WMO-specific file
     */

    function parse() {
        return $this->split_product();
    }

    /**
     * Return the unencumbered product text
     *
     * @return string Product text
     */
    function get_product_text() {
        return $this->raw_product;
    }

    /**
     * Split the product by $$ if needed.
     * TODO: Refactor this so it is more unit testable
     *
     * @return array of NWSProductSegments
     */
    function split_product() {
        // Previously, we removed the header of the product.
        // Inadvertently, this would strip VTEC strings and zones from short-fuse warnings
        // Thus...just set the product variable to the raw product.
        // TODO: Determine storage strategy. For short-fused warnings we'd essentially be storing the product twice
        $product = $this->raw_product;

        // Check if the product contains $$ identifiers for multiple products
        if(strpos($product, "$$")) {
            // Loop over the file for multiple products within one file identified by $$
            $raw_segments = explode('$$',trim($product), -1);
        }
        else {
            // No delimiters
            $raw_segments = array(trim($product));
        }

        foreach($raw_segments as $segment) {
            $segments[] = new NWSProductSegment($segment,$this->afos,$this->office);
        }

        return $segments;
    }

}

class NWSProductSegment
{
    /**
     * Segment text
     *
     * @var string
     */

    var $text;

    /**
     * Array of VTEC strings.
     *
     * @var array VTECString
     */
    var $vtec_strings;

    /**
     * Zones for this segment.
     *
     * @var array zones
     */
    var $zones;

    /**
     * Issuing time.
     *
     * @var int Timestamp
     */
    var $issued_time;

    /**
     * Issuing WFO (from parent product)
     *
     * @var string $office
     */
    var $office;

    /**
     * AFOS code (from parent product)
     * @var string $afos
     */
    var $afos;

    /**
     * Constructor.
     *
     * @param string $segment_text
     */
    function __construct($segment_text, $afos, $office)
    {
        $this->afos = $afos;
        $this->office = $office;
        $this->text = $segment_text;
        $this->vtec_strings = $this->parse_vtec();
        $this->zones = $this->parse_zones();
    }

    /**
     * Get this segment's text.
     *
     * @return string Raw text of the segment
     */
    function get_text()
    {
        return $this->text;
    }

    /**
     * Return the zones for this product.
     *
     * @return array of zones
     */
    function get_zones() {
        return $this->zones;
    }

    /**
     * Was this product issued for a particular zone(s)?
     *
     * @param array   $zones Array of zone codes to check against
     * @return boolean Array search result - true if found, false if not
     */
    function in_zone( $zones ) {
        foreach ( $zones as $zone ) {
            if ( in_array( $zone, $this->zones ) ) {
                return true;
            }
            else {
                $array_search_result = false;
            }
        }

        return $array_search_result;
    }

    //
    // Valid Time Extent Code (VTEC) support
    // Lots of useful information in one string about nature of product, start and end times, etc.
    // TODO: Implement H-VTEC for hydrological hazards
    //

    /**
     * Get VTEC strings if they exist...otherwise, return false
     *
     * @return array VTEC strings
     * @return boolean false if failure
     */
    function get_vtec() {
        if(!empty($this->vtec_strings))
        {
            foreach($this->vtec_strings as $vtec_string) {
                $strings[] = $vtec_string;
            }
            // Return an array of VTEC strings
            return $strings;
        }
        // No VTEC string found
        return false;
    }

    /**
     * Quick check if this segment has VTEC
     * @return  boolean
     */
    function has_vtec() {
        return !empty($this->vtec_strings);
    }

    /**
     * Checks if a segment has a VTEC message.
     *
     * @return boolean
     */
    function parse_vtec() {
        $data = $this->text;
        $vtec_strings = array();

        // Match all alerts, but we will only use operational warnings
        $regex = "/\/([A-Z]{1})\.(NEW|CON|EXP|CAN|EXT|EXA|EXB|UPG|COR|ROU)\.([A-Z]{4})\.([A-Z]{2})\.([A-Z]{1})\.([0-9]{4})\.([0-9]{6})T([0-9]{4})Z-([0-9]{6})T([0-9]{4})Z\//";

        if ( preg_match_all( $regex, $data, $matches, PREG_SET_ORDER ) ) {
            // If the VTEC library is not loaded, go ahead and get it
            if(!defined('VTEC_LIB')) {
                include('VTECString.class.php');
            }
            foreach ( $matches as $key => $match ) {
                //print_r($match);
                $vtec_strings[$key] = new VTECString( $match );
            }
        }

        return $vtec_strings;
    }

    /*
     * Zone generation functions
     */

    /**
     * The NWS combines does not repeat the state code for multiple zones...not good for our purpose
     * All we want to do here is convert ranges like INZ021-028 to INZ021-INZ028
     * We will also call the function to expand the ranges here.
     * See: http://www.weather.gov/emwin/winugc.htm
     */
    protected function parse_zones() {
        $data = $this->text;

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

        foreach ($matches[0] as $field => $value)
        {
            /* since the NWS thought it was efficient to not repeat state codes, we have to reverse that */
            $state = substr( $value, 0, 3 );
            $zones = substr( $value, 3 );

            /* convert ranges like 014>016 to 014-015-016 */
            $zones = $this->expand_ranges($zones);

            /* hack off the last dash */
            $zones = substr($zones, 0, strlen($zones) - 1);

            $zones = $state . str_replace('-', '-'.$state, $zones);

            $total_zones .= $zones;

            // Need one last dash to temporarily buffer between state changes
            $total_zones .= '-';
        }

        /* One last cleanup */
        $total_zones = substr( $total_zones, 0, strlen( $total_zones ) - 1 );
        $total_zones = explode( '-', $total_zones );
        return $total_zones;
    }

    /**
     * The NWS combines multiple zones into ranges...not good for our purpose
     * All we want to do here is convert ranges like 014>016 to 014-015-016
     * See: http://www.weather.gov/emwin/winugc.htm
     */
    protected function expand_ranges( $data ) {
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
}
