<?php
/* 
 * Generic product ingestor. Fallback for non-specific products.
 */

namespace UpdraftNetworks\Parser;
use UpdraftNetworks\Parser\NWSProduct as NWSProduct;

class GenericProduct extends NWSProduct {
    function __construct($prod_info, $product_text) {
        parent::__construct($prod_info,$product_text);
    }
}
