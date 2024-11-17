<?php

namespace chswx\LDMIngest\Parser;

use chswx\LDMIngest\Utils;

class NWSProductSegment
{
    /**
     * Segment text
     *
     * @var string
     */
    public $text;

    /**
     * Zones for this segment.
     *
     * @var array zones
     */
    public $zones;

    /**
     * Issuing WFO (from parent product)
     *
     * @var string $office
     */
    public $office;

    /**
     * Product identifier line (from parent product)
     *
     * @var string $pil
     */
    public $pil;

    /**
     * Segment-specific channels
     *
     * @var array channels
     */
    public $channels;

    /**
     * Segment metadata
     *
     * @var array metadata
     */
    public $metadata;

    /**
     * Basic constructor for product segments. Will be called explicitly by subclasses.
     *
     * @param string $segment_text
     */
    public function __construct(string $segment_text, NWSProduct $parentProduct)
    {
        $this->pil = $parentProduct->pil;
        $this->office = $parentProduct->office;
        $this->text = $segment_text;
        $this->zones = Utils::parseZones($this->text);
        $this->metadata = [];
        //        $this->channels = [];
        // Get channels for this segment.
        //        $channels = $this->generateZoneChannels();
        // Append segment-specific channels.
        //       $this->appendChannels($channels);
        // Propagate the channels upward to the product.
        //        $parentProduct->appendChannels($channels);
    }

    /**
     * Get this segment's text.
     *
     * @return string Raw text of the segment
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Return the zones for this product segment.
     *
     * @return array of zones
     */
    public function getZones()
    {
        return $this->zones;
    }

    /**
     * Was this product issued for a particular zone(s)?
     * Note: Unclear we are going to need this going forward.
     *
     * @param array $zones Array of zone codes to check against
     *
     * @return boolean Array search result - true if found, false if not
     */
    public function inZone($zones)
    {
        foreach ($zones as $zone) {
            if (in_array($zone, $this->zones)) {
                return true;
            } else {
                $array_search_result = false;
            }
        }

        return $array_search_result;
    }

    public function generateZoneChannels()
    {
        $channels = array();
        // Pair AWIPS PIL with zones.
        foreach ($this->zones as $zone) {
            $channels[] = $zone;
            $channels[] = $this->pil . "." . $zone;
        }

        return $channels;
    }

    /**
     * Helper function to allow segments to add channels per-segment.
     *
     * @param array $newChannels
     * @return void
     */
    public function appendChannels(array $newChannels): void
    {
        $new_channel_list = array_merge($this->channels, $newChannels);
        // Sort the channel list in alphabetical order
        sort($new_channel_list);
        $this->channels = $new_channel_list;
    }

    /**
     * Helper function to add metadata keys and values.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */

    public function addMetadata(string $key, mixed $value): void
    {
        $this->metadata[$key] = $value;
    }
}
