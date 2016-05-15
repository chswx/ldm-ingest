<?php
/*
 * Factory class that routes products to the most specific available parser.
 *
 */

namespace UpdraftNetworks\Ingestor;
use UpdraftNetworks\Utils as Utils;
use UpdraftNetworks\Parser as Parser;

class NWSProductFactory {
    /**
     * Dispatches a sanitized product to its parser.
     * It's up to the parser to generate and relay appropriate events.
     *
     * @param string $product_text Sanitized product text.
     * @return Parsed Product object.
     */
    public static function get_product($product_text) {
        // Get WMO header and issuing office
        $prod_info = self::get_product_details($product_text);

        // Get AFOS for parser
        $parser = self::get_parser_from_afos($prod_info['afos']);

        // Construct the path to the parser
        $parser_path = "parsers/$parser.php";

        Utils::log("Trying $parser_path...");

        if(file_exists($parser_path)) {
            include($parser_path);
            // Instantiate the class
            $product = new $parser($prod_info, $product_text);
        }
        // It's not here...return a generic parsing library.
        else
        {
            Utils::log("There are no parsers available for {$prod_info['wmo']} {$prod_info['office']} {$prod_info['afos']}, trying a generic...");
            $product = new Parser\GenericProduct($prod_info, $product_text);
        }

        return $product;
    }

    /**
     * Get WMO product ID and authority from the second line.
     *
     * @param string $product_text Sanitized product text.
     * @return array WMO header ID, issuing office, and AWIPS code
     */
    private static function get_product_details($product_text) {
        $text_array = Utils::make_array($product_text);
        $wmo_and_office = explode(' ',$text_array[1]);
        $wmo = $wmo_and_office[0];
        $office = $wmo_and_office[1];
        $afos = $text_array[2];

        Utils::log("Product WMO: " . $wmo . '; Office: ' . $office . '; AFOS code: ' . $afos);

        return array(
            'wmo' => $wmo,
            'office' => $office,
            'afos' => $afos
        );
    }

    /**
     * Retrieves the appropriate parser from the AFOS string.
     *
     * @param string $afos AFOS string
     * @return string Parser to use
     */
    private static function get_parser_from_afos($afos) {
        // VTEC parsing
        // (MWW|FWW|CFW|TCV|RFW|FFA|SVR|TOR|SVS|SMW|MWS|NPW|WCN|WSW|EWW|FLS)
        // (FLW|FFW|FFS|HLS|TSU)
        if(preg_match('(MWW|FWW|CFW|TCV|RFW|FFA|SVR|TOR|SVS|SMW|MWS|NPW|WCN|WSW|EWW|FLS|FLW|FFW|FFS|HLS|TSU|WOU)',$afos)) {
            $parser = 'VTEC';
        }
        // SPS parsing
        // (SPS)
        else if(strpos($afos, 'SPS') !== false) {
            $parser = 'SPS';
        }
        // Watch Probabilities
        // ^WWUS(40 KMKC|30 KWNS)
        else if(strpos($afos, 'WWP') !== false) {
            $parser = "WatchProbs";
        }
        // Mesoscale convective/precip discussions
        // (SWOMCD|FFGMPD)
        else if(preg_match('(SWOMCD|FFGMPD)',$afos)) {
            $parser = "MesoDisc";
        }
        // SPC outlook points
        // (PFWFD1|PFWFD2|PFWF38|PTSDY1|PTSDY2|PTSDY3|PTSD48)
        else if(preg_match('(PFWFD1|PFWFD2|PFWF38|PTSDY1|PTSDY2|PTSDY3|PTSD48)', $afos)) {
            $parser = "OutlookPoints";
        }
        // SPC outlook text
        else if(preg_match('(SWODY)',$afos)) {
            $parser = "OutlookText";
        }
        // Local Storm Reports
        // (LSR)
        else if(strpos($afos, 'LSR') !== false) {
            $parser = "LSR";
        }
        else {
            $parser = "GenericProduct";
        }

        return $parser;
    }
}
