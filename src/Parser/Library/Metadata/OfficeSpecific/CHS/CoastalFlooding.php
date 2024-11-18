<?php

namespace chswx\LDMIngest\Parser\Library\Metadata\OfficeSpecific\CHS;

use chswx\LDMIngest\Utils;

class CoastalFlooding
{
    public static function totalWaterLevelForecastRange($segment_text): array
    {
        $result = [];

        // Example: (7.8 to 8.0 feet Mean Lower Low Water at Charleston)
        $pattern = '/\((\d\.\d) to (\d\.\d) feet Mean Lower Low Water at (\w+)\)]/';

        // Grab the bit of text that will have the forecast range
        $text = Utils::findBulletPoint($segment_text, "WHAT");
        if (preg_match($pattern, $text, $matches)) {
            $result = [
                'twl_min'        => $matches[1],
                'twl_max'        => $matches[2],
                'twl_fcst_point' => $matches[3]
            ];
        } else {
            Utils::debug("No match found within $text");
        }

        return $result;
    }
}
