<?php
/* 
 * Generic product ingestor. Fallback for non-specific products. VTEC-capable.
 */

class GenericProduct extends NWSProduct {
    function __construct($prod_info, $product_text)
    {
        parent::__construct($prod_info,$product_text);
        $this->populate_channels();
    }

    function populate_channels()
    {
        Utils::log("Populating channels...");
        foreach($this->segments as $segment)
        {
            Utils::log("Processing channels for $segment->afos");
            array_push($segment->channels,$segment->afos);                    // e.g. TORCHS, SVSCHS, TCWAT1
            array_push($segment->channels,substr($segment->office, 1));        // e.g. CHS
            if($segment->has_vtec())
            {
                // Generate VTEC channel for each county. Retains NWSChat compatibility.
                // e.g. TO.W.SCC040
                foreach($segment->zones as $zone)
                {
                    // One event per VTEC
                    foreach($segment->vtec_strings as $vtec_string)
                    {
                        array_push($segment->channels,$vtec_string->phenomena . '.' . $vtec_string->significance . '.' . $zone);
                        Utils::log("Pushed {$vtec_string->phenomena}.{$vtec_string->significance}.$zone onto the channel stack");
                    }
                }

                // Generate VTEC channel per WFO (retains NWSChat compatibility)
                // e.g. TO.W.CHS
                foreach($segment->vtec_strings as $vtec_string)
                {
                    array_push($segment->channels,$vtec_string->phenomena . '.' . $vtec_string->significance . '.' . substr($segment->office, 1) );
                }
            }

            // Generate channels for the current UGC
            foreach($segment->zones as $zone)
            {
                array_push($segment->channels,$zone);
            }
        }
    }
}
