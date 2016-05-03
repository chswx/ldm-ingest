<?php
/* 
 * Generic product ingestor. Fallback for non-specific products.
 */

class GenericProduct extends NWSProduct {
    function __construct($prod_info, $product_text) {
        parent::__construct($prod_info,$product_text);
    }
}
