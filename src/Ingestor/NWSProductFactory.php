<?php

/*
 * Factory class that routes products to the most specific available parser.
 */

namespace chswx\LDMIngest\Ingestor;

use chswx\LDMIngest\Utils;
use chswx\LDMIngest\Parser;

class NWSProductFactory
{
    /**
     * Dispatches a sanitized product to its parser.
     *
     * @param string $product_text Sanitized product text.
     *
     * @return Parsed Product object.
     */
    public static function getProduct($product_text)
    {
        // Get WMO header and issuing office
        $prod_info = self::getProductDetails($product_text);

        // Select a route (database and parser) based on PIL
        $route = self::getRouteFromPil($prod_info['pil']);

        Utils::log("Attempting to use {$route['parser']}");

        $parser = $route['parser'];

        if (class_exists($parser)) {
            // Instantiate the class
            Utils::log("Using $parser to parse {$prod_info['wmo']} {$prod_info['office']} {$prod_info['pil']}");
            $product = new $parser($prod_info, $product_text);
        } else {
            Utils::log("We really should never get here.");
            Utils::exitWithError("Parsing failed. No suitable parser available, and no generic was found.");
        }

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
        // Is there a valid WMO header?
        preg_match('/([A-Z]{4}[0-9]{2})\s([A-Z]{4})\s([0-9]{6})\s?([R]{2}[A-Z]|[C]{2}[A-Z]|[A]{2}[A-Z]|[P][A-Z]{2})?/', $product_text, $wmo_matches);
        if (!empty($wmo_matches)) {
            $wmo_header = new Parser\Library\WMO\AbbreviatedHeading($wmo_matches);
            $wmo = $wmo_header->id;
            $office = $wmo_header->office;
            $timestamp = $wmo_header->timestamp;
        } else {
            Utils::log("No valid WMO header found");
        }
        $pil = trim($text_array[2]);

        Utils::log("Product WMO Header: " . $wmo . '; Issuing Office: ' . $office . '; AWIPS PIL: ' . $pil);

        return array(
            'wmo' => $wmo,
            'office' => $office,
            'pil' => $pil,
            'timestamp' => $timestamp
        );
    }

    /**
     * Retrieves the appropriate parser from the product identifier.
     *
     * @param string $pil Product Identifier Line
     *
     * @return array Parser to use
     */
    public static function getRouteFromPil($pil)
    {
        // VTEC parsing
        // (MWW|FWW|CFW|TCV|RFW|FFA|SVR|TOR|SVS|SMW|MWS|NPW|WCN|WSW|EWW|FLS)
        // (FLW|FFW|FFS|HLS|TSU)
        if (preg_match('(MWW|FWW|CFW|TCV|RFW|FFA|SVR|TOR|SVS|SMW|MWS|NPW|WCN|WSW|EWW|FLS|FLW|FFW|FFS|TSU|WOU)', $pil)) {
            $parser = 'VTEC';
        } elseif (strpos($pil, 'SPS') !== false) {
            // SPS parsing
            // (SPS)
            $parser = 'SPS';
        } elseif (strpos($pil, 'WWP') !== false) {
            // Watch Probabilities
            // ^WWUS(40 KMKC|30 KWNS)
            $parser = "WatchProbs";
        } elseif (strpos($pil, 'SEL') !== false) {
            // Public Watch Notification
            // WWUS20
            $parser = "PublicWatch";
        } elseif (preg_match('(SWOMCD)', $pil)) {
            // Mesoscale convective discussions
            // (SWOMCD)
            $parser = "MesoDisc";
        } elseif (preg_match('(PFWFD1|PFWFD2|PFWF38|PTSDY1|PTSDY2|PTSDY3|PTSD48)', $pil)) {
            // SPC outlook points
            // (PFWFD1|PFWFD2|PFWF38|PTSDY1|PTSDY2|PTSDY3|PTSD48)
            $parser = "OutlookPoints";
        } elseif (preg_match('(SWODY)', $pil)) {
            // SPC outlook text
            $parser = "OutlookText";
        } elseif (strpos($pil, 'LSR') !== false) {
            // Local Storm Reports
            // (LSR)
            $parser = "LSR";
        } elseif (preg_match('(FFGMPD)', $pil)) {
            // Mesoscale precipitation discussions from WPC (FFGMPD)
            $parser = "MesoDisc";
        } elseif (preg_match('/RBG(94|98|99)E/', $pil)) {
            // Excessive Rainfall Outlook from WPC (analogous to SPC Convective outlook)
            // http://www.nws.noaa.gov/directives/sym/pd01009030curr.pdf
            $parser = "WPCOutlook";
        } elseif (strpos($pil, 'HLS') !== 'false') {
            // Hurricane Local Statements
            $parser = "GenericProduct";
        } else {
            $parser = "GenericProduct";
        }

        return array('parser' => 'chswx\\LDMIngest\\Parser\\ProductTypes\\' . $parser);
    }
}
