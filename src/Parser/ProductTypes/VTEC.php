<?php

/**
 * VTEC parser.
 * Designed specifically for parsing VTEC-enabled products.
 */

namespace chswx\LDMIngest\Parser\ProductTypes;

use chswx\LDMIngest\Parser\NWSProduct;

class VTEC extends NWSProduct
{
    public function __construct($prod_info, $prod_text)
    {
        parent::__construct($prod_info, $prod_text);
    }

    public function parse()
    {
        return $this->splitProduct($this->raw_product, 'chswx\\LDMIngest\\Parser\\SegmentTypes\\VTECSegment');
    }
}
