<?php
/**
 * NHC parser.
 * For National Hurricane Center advisories, primarily.
 */

namespace UpdraftNetworks\Parser;

use UpdraftNetworks\Parser\NWSProduct as NWSProduct;
use UpdraftNetworks\Parser\NWSProductSegment as NWSProductSegment;

class NHC extends NWSProduct {
    function __construct($prod_info, $prod_text) {
        parent::__construct($prod_info, $prod_text);
    }

    function parse() {
        return $this->split_product($this->raw_product, 'UpdraftNetworks\\Parser\\NHCSegment');
    }
}

/**
 * Extends the NWSProductSegment with attributes specific to Hurricane Center products.
 */
class NHCSegment extends NWSProductSegment {
    function __construct($segment_text, $afos, $office) {
        parent::__construct($segment_text, $afos, $office);

    }
}

