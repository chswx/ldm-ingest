<?php
/*
 * Factory class that routes products to the most specific available parser.
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
     *
     * @return Parsed Product object.
     */
    public static function get_product($product_text) {
        // Get WMO header and issuing office
        $prod_info = self::get_product_details($product_text);

        // Select a route (database and parser) based on AFOS
        $route = self::get_route_from_afos($prod_info['afos']);

        Utils::log("Attempting to use {$route['parser']}");

        $parser = $route['parser'];
        $table = $route['table'];

        if (class_exists($parser)) {
            // Instantiate the class
            Utils::log("Using $parser to parse {$prod_info['wmo']} {$prod_info['office']} {$prod_info['afos']}");
            $product = new $parser($prod_info, $product_text);
        } // It's not here...return a generic parsing library.
        else {
            Utils::log("There are no parsers available for {$prod_info['wmo']} {$prod_info['office']} {$prod_info['afos']}, trying a generic...");
            $product = new Parser\GenericProduct($prod_info, $product_text);
        }

        $product->table = $table;

        return $product;
    }

    /**
     * Get WMO product ID and authority from the second line.
     *
     * @param string $product_text Sanitized product text.
     *
     * @return array WMO header ID, issuing office, and AWIPS code
     */
    public static function get_product_details($product_text) {
        $text_array = Utils::make_array($product_text);
        $wmo_and_office = explode(' ', $text_array[1]);
        $wmo = $wmo_and_office[0];
        $office = $wmo_and_office[1];
        $afos = trim($text_array[2]);

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
     *
     * @return array Parser to use and DB table to store
     */
    public static function get_route_from_afos($afos) {
        // VTEC parsing
        // (MWW|FWW|CFW|TCV|RFW|FFA|SVR|TOR|SVS|SMW|MWS|NPW|WCN|WSW|EWW|FLS)
        // (FLW|FFW|FFS|HLS|TSU)
        if (preg_match('(MWW|FWW|CFW|TCV|RFW|FFA|SVR|TOR|SVS|SMW|MWS|NPW|WCN|WSW|EWW|FLS|FLW|FFW|FFS|HLS|TSU|WOU)', $afos)) {
            $parser = 'VTEC';
            $table = 'wwa';
        }
        // SPS parsing
        // (SPS)
        else if (strpos($afos, 'SPS') !== false) {
            $parser = 'SPS';
            $table = 'sps';
        }
        // Watch Probabilities
        // ^WWUS(40 KMKC|30 KWNS)
        else if (strpos($afos, 'WWP') !== false) {
            $parser = "WatchProbs";
            $table = 'spc_watch';
        }
        // Public Watch Notification
        // WWUS20
        else if (strpos($afos, 'SEL') !== false) {
            $parser = "PublicWatch";
            $table = 'spc_watch';
        }
        // Mesoscale convective discussions
        // (SWOMCD)
        else if (preg_match('(SWOMCD)', $afos)) {
            $parser = "MesoDisc";
            $table = 'mesodisc';
        }
        // SPC outlook points
        // (PFWFD1|PFWFD2|PFWF38|PTSDY1|PTSDY2|PTSDY3|PTSD48)
        else if (preg_match('(PFWFD1|PFWFD2|PFWF38|PTSDY1|PTSDY2|PTSDY3|PTSD48)', $afos)) {
            $parser = "OutlookPoints";
            $table = 'spc_outlook';
        } // SPC outlook text
        else if (preg_match('(SWODY)', $afos)) {
            $parser = "OutlookText";
            $table = 'spc_outlook';
        }
        // Local Storm Reports
        // (LSR)
        else if (strpos($afos, 'LSR') !== false) {
            $parser = "LSR";
            $table = 'lsr';
        } // Mesoscale precipitation discussions from WPC (FFGMPD)
        else if (preg_match('(FFGMPD)', $afos)) {
            $parser = "MesoDisc";
            $table = 'wpc_mpd';
        }
        // Excessive Rainfall Outlook from WPC (analogous to SPC Convective outlook)
        // http://www.nws.noaa.gov/directives/sym/pd01009030curr.pdf
        else if (preg_match('/RBG(94|98|99)E/', $afos)) {
            $parser = "WPCOutlook";
            $table = "wpc_outlook";
        } else {
            $parser = "GenericProduct";
            $table = 'misc';
        }

        return array('parser' => 'UpdraftNetworks\\Parser\\' . $parser, 'table' => $table);
    }
}
