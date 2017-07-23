<?php

namespace UpdraftNetworks\Parser;

use UpdraftNetworks\Parser\NWSProduct;
use UpdraftNetworks\Utils;

class Climate extends NWSProduct
{
    public function __construct($prod_info, $prod_text) 
    {
        parent::__construct($prod_info, $prod_text);
    }

    public function parse()
    {
        
    }
} 
