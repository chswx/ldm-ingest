<?php
/*
 * Generic product ingestor. Fallback for non-specific products.
 */

namespace UpdraftNetworks\Parser\ProductTypes;

use UpdraftNetworks\Parser\NWSProduct;

class GenericProduct extends NWSProduct
{
    public function __construct($prod_info, $product_text)
    {
        parent::__construct($prod_info, $product_text);
    }
}
