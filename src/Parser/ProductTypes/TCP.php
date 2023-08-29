<?php

// SUMMARY OF (.*)\.\.\.(\d+)+ UTC\.\.\.INFORMATION
// LOCATION...(.*) (.*)
// ABOUT (.*) MI\.\.\.(.*) KM (.*)
// MAXIMUM SUSTAINED WINDS\.\.\.(.*) MPH\.\.\.(.*) KM\/H
// PRESENT MOVEMENT\.\.\.(.*)
// MINIMUM CENTRAL PRESSURE\.\.\.(.*) MB\.\.\.(.*) INCHES

namespace chswx\LDMIngest\Parser\ProductTypes;

use chswx\LDMIngest\Parser\Library\NHC\TCInfo;
use chswx\LDMIngest\Parser\NWSProduct;

class TCP extends NWSProduct
{
    public function parse(): array
    {
        $this->type = 'tcp';

        $tc_info = new TCInfo;

        $data = $tc_info->parse($this->raw_product);

        return $data;
    }

    public final function generateChannels(): void
    {
        parent::generateChannels();

        $storm_id = $this->segments['storm_id'];

        $additional_channels = ["KNHC", $storm_id];
    }
}
