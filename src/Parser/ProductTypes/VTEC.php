<?php

/**
 * VTEC parser.
 * Designed specifically for parsing VTEC-enabled products.
 */

namespace chswx\LDMIngest\Parser\ProductTypes;

use chswx\LDMIngest\Parser\NWSProduct;

class VTEC extends NWSProduct
{
    public function parse(): array
    {
        $this->type = 'vtec';
        $segments = $this->splitProduct($this->raw_product, 'chswx\\LDMIngest\\Parser\\SegmentTypes\\VTECSegment');
        return $segments;
    }
}
