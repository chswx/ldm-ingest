<?php
/*
 * NWSProduct class
 * Defines most of what is a National Weather Service text product and puts it out into easily reusable chunks.
 * Portions adapted from code by Andrew: http://phpstarter.net/2010/03/parse-zfp-zone-forecast-product-data-in-php-option-1/
 */

namespace chswx\LDMIngest\Parser;

use chswx\LDMIngest\Utils;

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
     * Channels to send this product to for dissemination.
     *
     * @var \array
     */
    public $channels;

    /**
     * Constructor.
     */
    public function __construct($prod_info, $product_text)
    {
        // Extract info from the $prod_info array...
        $this->office = $prod_info['office'];   // Issuing office
        $this->afos = $prod_info['afos'];       // AWIPS/AFOS PIL
        $this->timestamp = $prod_info['timestamp'];
        // Keep the raw product around for now
        $this->raw_product = $product_text;
        // Generate initial channels for this product
        $this->generateChannels();
        // Parse the product out into segments if not already done by a more specialized parser.
        if (empty($this->segments)) {
            $this->segments = $this->parse();
        }
        // Set up the product stamp.
        $this->stamp = Utils::generateStamp($this->afos, $this->timestamp);
    }

    /**
     * Generic parsing ability.
     * STRONGLY recommend overriding in a WMO-specific file
     */

    public function parse()
    {
        return $this->splitProduct($this->raw_product);
    }

    /**
     * Return the unencumbered product text
     *
     * @return string Product text
     */
    public function getProductText()
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
    public function splitProduct($product, $class = 'chswx\LDMIngest\\Parser\\NWSProductSegment')
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
            $segments[] = new $class($segment, $this);
        }

        return $segments;
    }

    /**
     * Generates channels for dissemination. These can be used upstream for targeting of specific messages to different media (tweets, email, text, etc.)
     * Specialized parsers should call this and then populate with their own additional channels as appropriate.
     *
     * @return void
     */
    public function generateChannels()
    {
        // Initialize if needed.
        if (empty($this->channels)) {
            $this->channels = array();
        }
        // Adds the PIL and issuing office to the channels list by default
        $this->appendChannels(array(substr($this->office, 1), $this->afos));
    }

    /**
     * Helper function to allow segments to add to the channels list for this product.
     *
     * @param array $newChannels
     * @return void
     */
    public function appendChannels(array $newChannels)
    {
        $this->channels = array_merge($this->channels, $newChannels);
    }
}
