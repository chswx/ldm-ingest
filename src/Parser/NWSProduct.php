<?php
/*
 * NWSProduct class
 * Defines most of what is a National Weather Service text product and puts it out into easily reusable chunks.
 * Portions adapted from code by Andrew: http://phpstarter.net/2010/03/parse-zfp-zone-forecast-product-data-in-php-option-1/
 */

namespace UpdraftNetworks\Parser;

use UpdraftNetworks\Utils as Utils;

class NWSProduct
{
    /**
     * Raw product text (with some light cleanup).
     *
     * @var string Product text.
     */
    public $raw_product;

    /**
     * Issuing office.
     *
     * @var string WFO
     */
    public $office;

    /**
     * AFOS identifier.
     *
     * @var string AFOS ID
     */
    public $afos;

    /**
     * Unique stamp for this particular product.
     *
     * @var string stamp
     */
    public $stamp;

    /**
     * Holds the product's NWSProductSegments, if any. Generate events from these later if needed.
     *
     * @var mixed Array of segments
     */
    public $segments;

    /**
     * Issuance time as set in the WMO abbreviated header.
     *
     * @var int
     */
    public $timestamp;

    /**
     * Constructor.
     */
    public function __construct($prod_info, $product_text)
    {
        // Extract info from the $prod_info array...
        $this->office = $prod_info['office'];   // Issuing office
        $this->afos = $prod_info['afos'];     // AFOS code
        $this->timestamp = $prod_info['timestamp'];
        // Keep the raw product around for now
        $this->raw_product = $product_text;
        // Parse the product out into segments if not already done by a more specialized parser.
        if (empty($this->segments)) {
            $this->segments = $this->parse();
        }
        // Set up the product stamp.
        $this->stamp = Utils::generate_stamp($this->afos, $this->timestamp);
    }

    /**
     * Generic parsing ability.
     * STRONGLY recommend overriding in a WMO-specific file
     */

    public function parse()
    {
        return $this->split_product($this->raw_product);
    }

    /**
     * Return the unencumbered product text
     *
     * @return string Product text
     */
    public function get_product_text()
    {
        return $this->raw_product;
    }

    /**
     * Split the product by $$ if needed.
     *
     * @param $product string Raw product data to get shredded
     * @param $class   string Optional definition of which class defines what a segment is
     *
     * @return array of NWSProductSegments
     */
    public function split_product($product, $class = 'UpdraftNetworks\\Parser\\NWSProductSegment')
    {
        // Previously, we removed the header of the product.
        // Inadvertently, this would strip VTEC strings and zones from short-fuse warnings
        // Thus...just set the product variable to the raw product.
        // TODO: Determine storage strategy. For short-fused warnings we'd essentially be storing the product twice

        // Check if the product contains $$ identifiers for multiple products
        if (strpos($product, "$$")) {
            // Loop over the file for multiple products within one file identified by $$
            $raw_segments = explode('$$', trim($product), -1);
        } else {
            // No delimiters
            $raw_segments = array(trim($product));
        }

        foreach ($raw_segments as $segment) {
            $segments[] = new $class($segment, $this->afos, $this->office);
        }

        return $segments;
    }
}
