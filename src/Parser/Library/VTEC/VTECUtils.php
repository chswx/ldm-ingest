<?php

namespace chswx\LDMIngest\Parser\Library\VTEC;

use chswx\LDMIngest\Utils;

/** @package chswx\LDMIngest\Parser\Library\VTEC */
class VTECUtils
{
    private static $vtec_regex = "/\/([A-Z]{1})\.(NEW|CON|EXP|CAN|EXT|EXA|EXB|UPG|COR|ROU)\.([A-Z]{4})\.([A-Z]{2})\.([A-Z]{1})\.([0-9]{4})\.([0-9]{6})T([0-9]{4})Z-([0-9]{6})T([0-9]{4})Z\//";

    public static function parse($product)
    {
        $matches = [];

        $success = preg_match_all(self::$vtec_regex, $product, $matches, PREG_SET_ORDER);
        if (empty($success)) {
            Utils::log("No preg matches detected");
        }

        return $matches;
    }
}
