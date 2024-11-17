<?php

namespace chswx\LDMIngest\Parser\Library\Metadata\OfficeSpecific\CHS;

use chswx\LDMIngest\Utils;

class CoastalFlooding
{
    public static function totalWaterLevelForecastRange($segment_text): array
    {
        /* (7.8 to 8.0 feet Mean Lower Low Water at Charleston) */

        $pattern = "";

        // Grab the bit of text that will have the forecast range
        $text = Utils::findBulletPoint($segment_text, "WHAT");
        if (preg_match($pattern, $text, $matches)) {
        }

        return [];
    }
}
