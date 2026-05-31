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

        // Extract headline from the first segment.
        if (!empty($segments)) {
            $first_segment_text = $segments[0]->text;

            // Match headline: ...TEXT... (single line)
            if (preg_match('/^\s*\.\.\.(.+?)\.\.\.\s*$/m', $first_segment_text, $matches)) {
                $this->headline = trim($matches[1]);
            } else {
                // Match multi-line headline: ...TEXT... spanning multiple lines
                if (preg_match('/^\s*\.\.\.(.+?)\n(.*?)\.\.\.\s*$/ms', $first_segment_text, $matches)) {
                    $this->headline = trim(preg_replace('/\s+/', ' ', $matches[1] . ' ' . $matches[2]));
                }
            }

            // Extract expiration from zone string.
            $this->expiration_time = Utils::parseZoneExpiration($first_segment_text, $this->timestamp);
        }

        return $segments;
    }
}
