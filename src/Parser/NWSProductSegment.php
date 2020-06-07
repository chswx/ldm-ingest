<?php

namespace UpdraftNetworks\Parser;

use UpdraftNetworks\Utils;

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
     * AFOS code (from parent product)
     *
     * @var string $afos
     */
    public $afos;

    /**
     * Basic constructor for product segments. Will be called explicitly by subclasses.
     *
     * @param string $segment_text
     */
    public function __construct(string $segment_text, NWSProduct $parentProduct)
    {
        $this->afos = $parentProduct->afos;
        $this->office = $parentProduct->office;
        $this->text = $segment_text;
        $this->zones = Utils::parseZones($this->text);
        $parentProduct->appendChannels($this->generateZoneChannels());
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
            $channels[] = $this->afos . "." . $zone;
        }

        return $channels;
    }
}
