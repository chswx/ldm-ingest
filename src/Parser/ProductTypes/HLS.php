<?php

namespace chswx\LDMIngest\Parser\ProductTypes;

use chswx\LDMIngest\Parser\NWSProduct;

class HLS extends NWSProduct
{
    public function generateChannels(): void
    {
        parent::generateChannels();
        $this->appendChannels(['HLS']);
    }

    public function parse(): array
    {
        // Not really a segmented product.
        return [];
    }
}
