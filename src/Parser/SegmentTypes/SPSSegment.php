<?php

namespace chswx\LDMIngest\Parser\SegmentTypes;

use chswx\LDMIngest\Parser\NWSProductSegment;
use chswx\LDMIngest\Parser\Library\SMVString;
use chswx\LDMIngest\Parser\Library\IBW;
use chswx\LDMIngest\Parser\Library\SBW;

/**
 * Extends the NWSProductSegment with attributes specific to SPS products.
 */
class SPSSegment extends NWSProductSegment
{
    /**
     * Impact-based warning info (if available.)
     */
    public $ibw;

    /**
     * Storm motion vector info.
     */
    public $smv;

    /**
     * Storm-based warning polygon geometry (if available)
     */
    public $polygon;

    public function __construct($segment_text, \chswx\LDMIngest\Parser\NWSProduct $parentProduct)
    {
        parent::__construct($segment_text, $parentProduct);

        // Parse impact-based tags for SPS products.
        $this->ibw = new IBW($segment_text);

        // Parse storm motion vector.
        $smv = new SMVString($segment_text);
        if (!is_null($smv->time)) {
            $this->smv = $smv;
        }

        // Extract the polygon from the product and save.
        $sbw = new SBW($segment_text);
        if (!empty($sbw->polygon)) {
            $this->polygon = $sbw->polygon;
        } else {
            $this->polygon = [];
        }
    }
}
