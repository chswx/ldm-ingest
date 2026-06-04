<?php

namespace chswx\LDMIngest\Parser\ProductTypes;

use chswx\LDMIngest\Parser\NWSProduct;
use chswx\LDMIngest\Utils;

class SPS extends NWSProduct
{
    /**
     * Extracted headline from the segment text.
     */
    public $headline;

    /**
     * Extracted expiration time from zone string.
     */
    public $expiration_time;

    public function generateChannels(): void
    {
        parent::generateChannels();
        $this->appendChannels(['SPS']);
    }

    public function parse(): array
    {
        $this->type = 'sps';

        // Split product into segments using SPSSegment class.
        $segments = $this->splitProduct($this->raw_product, 'chswx\\LDMIngest\\Parser\\SegmentTypes\\SPSSegment');

        // Set product-level headline convenience property from first segment.
        if (!empty($segments)) {
            $this->headline = $segments[0]->headline ?? '';

            // Extract expiration from zone string.
            $this->expiration_time = Utils::parseZoneExpiration($segments[0]->text, $this->timestamp);
        }

        return $segments;
    }
}
