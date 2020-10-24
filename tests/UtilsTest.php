<?php

namespace chswx\LDMIngest\Tests;

use chswx\LDMIngest\Utils;
use PHPUnit\Framework\TestCase;

require_once 'vendor/autoload.php';

date_default_timezone_set('UTC');

class UtilsTest extends TestCase
{
    public function testZoneParsing()
    {
        // test case 1: straightforward county layout (one state, no expansion)
        $ugc = "SCC015-043-089-091507-";
        $parsed = Utils::parseZones($ugc);
        $expected = array(
            'SCC015',
            'SCC043',
            'SCC089',
        );
        $this->assertEquals($expected, $parsed, "Did not get expected list of counties");

        // test case 2: UGC with zone expansion, one state
        // This uses zones, not counties
        $ugc = "SCZ040-042>045-047>052-121330-";
        $parsed = Utils::parseZones($ugc);
        $expected = array(
            'SCZ040',
            'SCZ042',
            'SCZ043',
            'SCZ044',
            'SCZ045',
            'SCZ047',
            'SCZ048',
            'SCZ049',
            'SCZ050',
            'SCZ051',
            'SCZ052'
        );
        $this->assertEquals($expected, $parsed, "Did not get expected list of zones");

        // test case 3: UGC with multiple states, no expansion
        $ugc = "GAC029-031-043-051-103-109-165-251-267-SCC005-015-029-035-049-053-011900-";
        $parsed = Utils::parseZones($ugc);
        $expected = array(
            'GAC029',
            'GAC031',
            'GAC043',
            'GAC051',
            'GAC103',
            'GAC109',
            'GAC165',
            'GAC251',
            'GAC267',
            'SCC005',
            'SCC015',
            'SCC029',
            'SCC035',
            'SCC049',
            'SCC053'
        );
        $this->assertEquals($expected, $parsed, "Did not get expected list of zones");
    }
}
