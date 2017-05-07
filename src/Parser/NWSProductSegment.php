<?php

namespace UpdraftNetworks\Parser;

use UpdraftNetworks\Utils as Utils;

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
     * Constructor.
     *
     * @param string $segment_text
     */
    public function __construct($segment_text, $afos, $office)
    {
        $this->afos = $afos;
        $this->office = $office;
        $this->text = $segment_text;
        $this->zones = Utils::parseZones($this->text);
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
     * Return the zones for this product.
     *
     * @return array of zones
     */
    public function getZones()
    {
        return $this->zones;
    }

    /**
     * Was this product issued for a particular zone(s)?
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
}
