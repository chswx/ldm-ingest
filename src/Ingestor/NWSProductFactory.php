<?php
/*
 * Factory class that routes products to the most specific available parser.
 */

namespace UpdraftNetworks\Ingestor;

use UpdraftNetworks\Utils as Utils;
use UpdraftNetworks\Parser as Parser;

class NWSProductFactory
{
    /**
     * Dispatches a sanitized product to its parser.
     * It's up to the parser to generate and relay appropriate events.
     *
     * @param string $product_text Sanitized product text.
     *
     * @return Parsed Product object.
     */
    public static function getProduct($product_text)
    {
        // Get WMO header and issuing office
        $prod_info = self::getProductDetails($product_text);

        // Select a route (database and parser) based on AFOS
        $route = self::getRouteFromAfos($prod_info['afos']);

        Utils::log("Attempting to use {$route['parser']}");

        $parser = $route['parser'];
        $table = $route['table'];

        if (class_exists($parser)) {
            // Instantiate the class
            Utils::log("Using $parser to parse {$prod_info['wmo']} {$prod_info['office']} {$prod_info['afos']}");
            $product = new $parser($prod_info, $product_text);
        } else {
            // It's not here...return a generic parsing library.
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
    public static function getProductDetails($product_text)
    {
        $text_array = Utils::makeArray($product_text);
        $wmo_header = new Parser\Library\WMO\AbbreviatedHeading($text_array[1]);
        $wmo = $wmo_header->id;
        $office = $wmo_header->office;
        $timestamp = $wmo_header->timestamp;
        $afos = trim($text_array[2]);

        Utils::log("Product WMO: " . $wmo . '; Office: ' . $office . '; AFOS code: ' . $afos);

        return array(
            'wmo' => $wmo,
            'office' => $office,
            'afos' => $afos,
            'timestamp' => $timestamp
        );
    }

    /**
     * Retrieves the appropriate parser from the AFOS string.
     *
     * @param string $afos AFOS string
     *
     * @return array Parser to use and DB table to store
     */
    public static function getRouteFromAfos($afos)
    {
        // VTEC parsing
        // (MWW|FWW|CFW|TCV|RFW|FFA|SVR|TOR|SVS|SMW|MWS|NPW|WCN|WSW|EWW|FLS)
        // (FLW|FFW|FFS|HLS|TSU)
        if (preg_match('(MWW|FWW|CFW|TCV|RFW|FFA|SVR|TOR|SVS|SMW|MWS|NPW|WCN|WSW|EWW|FLS|FLW|FFW|FFS|HLS|TSU|WOU)', $afos)) {
            $parser = 'VTEC';
        } elseif (strpos($afos, 'SPS') !== false) {
            // SPS parsing
            // (SPS)
            $parser = 'SPS';
        } elseif (strpos($afos, 'WWP') !== false) {
            // Watch Probabilities
            // ^WWUS(40 KMKC|30 KWNS)
            $parser = "WatchProbs";
        } elseif (strpos($afos, 'SEL') !== false) {
            // Public Watch Notification
            // WWUS20
            $parser = "PublicWatch";
        } elseif (preg_match('(SWOMCD)', $afos)) {
            // Mesoscale convective discussions
            // (SWOMCD)
            $parser = "MesoDisc";
        } elseif (preg_match('(PFWFD1|PFWFD2|PFWF38|PTSDY1|PTSDY2|PTSDY3|PTSD48)', $afos)) {
            // SPC outlook points
            // (PFWFD1|PFWFD2|PFWF38|PTSDY1|PTSDY2|PTSDY3|PTSD48)
            $parser = "OutlookPoints";
        } elseif (preg_match('(SWODY)', $afos)) {
            // SPC outlook text
            $parser = "OutlookText";
        } elseif (strpos($afos, 'LSR') !== false) {
            // Local Storm Reports
            // (LSR)
            $parser = "LSR";
        } elseif (preg_match('(FFGMPD)', $afos)) {
            // Mesoscale precipitation discussions from WPC (FFGMPD)
            $parser = "MesoDisc";
        } elseif (preg_match('/RBG(94|98|99)E/', $afos)) {
            // Excessive Rainfall Outlook from WPC (analogous to SPC Convective outlook)
            // http://www.nws.noaa.gov/directives/sym/pd01009030curr.pdf
            $parser = "WPCOutlook";
        } else {
            $parser = "GenericProduct";
        }

        return array('parser' => 'UpdraftNetworks\\Parser\\' . $parser, 'table' => $table);
    }
}
