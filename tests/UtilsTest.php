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

    public function testParseZoneExpiration()
    {
        // Test case 1: zone string with trailing timestamp
        $ugc = "SCZ040-042>045-121330-";
        $seed = strtotime('2024-03-12 10:00:00');
        $result = Utils::parseZoneExpiration($ugc, $seed);
        $expected = strtotime('2024-03-12 13:30:00');
        $this->assertEquals($expected, $result);

        // Test case 2: zone string with different timestamp
        $ugc = "GAZ119-261030-";
        $seed = strtotime('2024-03-26 08:00:00');
        $result = Utils::parseZoneExpiration($ugc, $seed);
        $expected = strtotime('2024-03-26 10:30:00');
        $this->assertEquals($expected, $result);

        // Test case 3: zone string without trailing timestamp
        $ugc = "SCZ040-042>045-";
        $result = Utils::parseZoneExpiration($ugc);
        $this->assertNull($result);

        // Test case 4: month/year rollover (zone string day 01 issued on Jan 31)
        $ugc = "SCZ040-010600-";
        $seed = strtotime('2024-01-31 23:00:00');
        $result = Utils::parseZoneExpiration($ugc, $seed);
        $expected = strtotime('2024-02-01 06:00:00');
        $this->assertEquals($expected, $result);

        // Test case 5: segment text with zone string embedded
        $text = "SCZ040-042>045-121330-\nIncluding the cities of Town A, Town B";
        $seed = strtotime('2024-03-12 10:00:00');
        $result = Utils::parseZoneExpiration($text, $seed);
        $expected = strtotime('2024-03-12 13:30:00');
        $this->assertEquals($expected, $result);

        // Test case 6: segment text without zone timestamp
        $text = "Some body text with no zone string.";
        $result = Utils::parseZoneExpiration($text);
        $this->assertNull($result);

        // Test case 7: default seed_timestamp (uses current time)
        $ugc = "SCZ040-281500-";
        $result = Utils::parseZoneExpiration($ugc);
        // Just verify it returns a non-null int (month/year from current time)
        $this->assertIsInt($result);
    }

    public function hasDependencies()
    {
        return null;
    }
}
