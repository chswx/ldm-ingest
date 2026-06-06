<?php
/* 
 * Generic product ingestor. Fallback for non-specific products.
 */

namespace chswx\LDMIngest\Parser;
use chswx\LDMIngest\Parser\NWSProduct as NWSProduct;

class GenericProduct extends NWSProduct {
    function __construct($prod_info, $product_text) {
        parent::__construct($prod_info,$product_text);
    }
}
