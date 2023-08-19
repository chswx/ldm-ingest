<?php

namespace chswx\LDMIngest\Parser\ProductTypes;

use chswx\LDMIngest\Parser\NWSProduct;

class WatchProbs extends NWSProduct
{
    public function parse(): array
    {
        $this->type = 'watchprobs';

        $data = [];

        $output = $this->raw_product;

        // Split product into lines.
        $product_line = explode("\n", $output);

        // Line 9 -- get watch info
        $product_code = $product_line[8];
        $product_info = explode(' ', $product_code);

        if ($product_info[0] == "WS") {
            $product_name = "Severe Thunderstorm Watch";
        } else {
            $product_name = "Tornado Watch";
        }

        if (isset($product_info[2])) {
            $product_name = $product_info[2] . " $product_name";
        }

        $data['product_name'] = $product_name . " " . ltrim($product_info[1], '0');
        $data['watch_number'] = ltrim($product_info[1], '0');
        $data['raw_watch_number'] = $product_info[1];

        // Lines 11-17 -- probabilities
        // In this order:
        // -- 2 or more tornadoes
        // -- 1 or more strong (F2-F5) tornadoes
        // -- 10 or more severe wind events
        // -- 1 or more destructive wind event
        // -- 10 or more severe hail events
        // -- 1 or more hail events with hail > 2"
        // -- 6 or more combined severe hail/wind events
        for ($i = 10; $i < 17; $i++) {
            $prob_line = explode(": ", $product_line[$i]);
            $watch_prob[] = ltrim($prob_line[1]);
        }

        $data['probabilities']['tor'] = $watch_prob[0];
        $data['probabilities']['strongtor'] = $watch_prob[1];
        $data['probabilities']['wind'] = $watch_prob[2];
        $data['probabilities']['deswind'] = $watch_prob[3];
        $data['probabilities']['hail'] = $watch_prob[4];
        $data['probabilities']['largehail'] = $watch_prob[5];
        $data['probabilities']['combined'] = $watch_prob[6];

        // Lines 20-25 -- attributes
        // In this order:
        // -- Max hail (inches)
        // -- Max gusts (kts)
        // -- Max cloud tops
        // -- Storm motion vector
        // -- PDS
        for ($i = 20; $i < 25; $i++) {
            $att_line = explode(": ", $product_line[$i]);
            //print_r($prob_line);
            $watch_att[] = trim($att_line[1]);
        }

        $data['attributes']['max_hail'] = floatval($watch_att[0]);
        $data['attributes']['max_gusts'] = intval($watch_att[1]);
        $data['attributes']['max_tops'] = (intval($watch_att[2]) * 100);
        $data['attributes']['smv']['degrees'] = intval(substr($watch_att[3], 0, 3));
        $data['attributes']['smv']['speed_kt'] = intval(substr($watch_att[3], 2, 3));
        $data['attributes']['pds'] = ($watch_att[4] == "YES") ? true : false;

        return $data;
    }
}
