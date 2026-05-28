<?php

namespace chswx\LDMIngest\Tests;

use chswx\LDMIngest\Utils;
use PHPUnit\Framework\TestCase;

require_once 'vendor/autoload.php';

date_default_timezone_set('UTC');

class SPSTest extends TestCase
{
    protected $generic_sps;
    protected $ibw_sps_old_format;
    protected $ibw_sps_landspout;
    protected $ibw_sps_waterspout;
    protected $ibw_sps_wind_hail_line;
    protected $ibw_sps_wind_hail_single_storm;

    protected function setUp(): void
    {
        $this->generic_sps = Utils::sanitize(file_get_contents(getcwd() . '/tests/sample-data/SPSCHS.txt'));
        $this->ibw_sps_old_format = Utils::sanitize(file_get_contents(getcwd() . '/tests/sample-data/SPSCHS-IBW.txt'));
        $this->ibw_sps_landspout = Utils::sanitize(file_get_contents(getcwd() . '/tests/sample-data/april-2021-ibw/sps-landspout'));
        $this->ibw_sps_waterspout = Utils::sanitize(file_get_contents(getcwd() . '/tests/sample-data/april-2021-ibw/sps-waterspout-observed'));
        $this->ibw_sps_wind_hail_line = Utils::sanitize(file_get_contents(getcwd() . '/tests/sample-data/april-2021-ibw/sps-wind-hail-line'));
        $this->ibw_sps_wind_hail_single_storm = Utils::sanitize(file_get_contents(getcwd() . '/tests/sample-data/april-2021-ibw/sps-wind-hail-single-storm'));
    }

    public function testGenericSPS()
    {
        $prod_info = [
            'wmo' => 'WWUS82',
            'office' => 'KCHS',
            'pil' => 'SPSCHS',
            'timestamp' => strtotime('2016-03-12 11:22:00')
        ];

        $parser = new \chswx\LDMIngest\Parser\ProductTypes\SPS($prod_info, $this->generic_sps);
        $segments = $parser->parse();

        $this->assertEquals('sps', $parser->type);
        $this->assertEquals('SPSCHS', $parser->pil);
        $this->assertEquals('KCHS', $parser->office);

        // Verify headline extracted
        $this->assertNotNull($parser->headline);
        $this->assertEquals('PATCHY DENSE FOG DEVELOPING PRIOR TO DAYBREAK THIS MORNING', $parser->headline);

        // Verify expiration extracted from zone string
        $this->assertNotNull($parser->expiration_time);
        // Zone string has 121330 (day 12, 13:30 UTC)
        $this->assertEquals(13, date('G', $parser->expiration_time));
        $this->assertEquals(30, date('i', $parser->expiration_time));

        // Verify segments
        $this->assertCount(1, $segments);
        $segment = $segments[0];

        // Verify zones parsed (11 zones from SCZ040-042>045-047>052)
        $this->assertCount(11, $segment->zones);

        // Generic SPS should not have IBW metadata
        $this->assertNull($segment->ibw->hazard);
        $this->assertNull($segment->ibw->source);
        $this->assertNull($segment->ibw->impact);
        $this->assertNull($segment->ibw->landspout);
        $this->assertNull($segment->ibw->waterspout);

        // No polygon for generic SPS
        $this->assertIsArray($segment->polygon);
        $this->assertCount(0, $segment->polygon);

        // No SMV for generic SPS
        $this->assertNull($segment->smv);
    }

    public function testIBWSPSOldFormat()
    {
        $prod_info = [
            'wmo' => 'WWUS82',
            'office' => 'KCHS',
            'pil' => 'SPSCHS',
            'timestamp' => strtotime('2016-03-26 10:10:00')
        ];

        $parser = new \chswx\LDMIngest\Parser\ProductTypes\SPS($prod_info, $this->ibw_sps_old_format);
        $segments = $parser->parse();

        $this->assertEquals('sps', $parser->type);
        $this->assertEquals('SPSCHS', $parser->pil);

        // Verify headline extracted (multi-line)
        $this->assertNotNull($parser->headline);
        $this->assertEquals('STRONG THUNDERSTORMS WILL IMPACT PORTIONS OF CHATHAM COUNTY UNTIL 630 AM EDT', $parser->headline);

        // Verify expiration extracted from zone string
        $this->assertNotNull($parser->expiration_time);

        // Verify segments
        $this->assertCount(1, $segments);
        $segment = $segments[0];

        // Verify zones parsed
        $this->assertCount(1, $segment->zones);
        $this->assertEquals('GAZ119', $segment->zones[0]);

        // Old format IBW SPS doesn't have three-part HAZARD/SOURCE/IMPACT
        $this->assertNull($segment->ibw->hazard);

        // Should have polygon
        $this->assertNotNull($segment->polygon);
        $this->assertInstanceOf(\chswx\LDMIngest\Parser\Library\Geo\Polygon::class, $segment->polygon);

        // Should have SMV
        $this->assertNotNull($segment->smv);
        $this->assertEquals(1010, $segment->smv->time);
    }

    public function testIBWSPSLandspout()
    {
        $prod_info = [
            'wmo' => 'WWUS82',
            'office' => 'KGRB',
            'pil' => 'SPSGRB',
            'timestamp' => strtotime('2020-05-18 06:40:00')
        ];

        $parser = new \chswx\LDMIngest\Parser\ProductTypes\SPS($prod_info, $this->ibw_sps_landspout);
        $segments = $parser->parse();

        $this->assertEquals('sps', $parser->type);
        $this->assertEquals('SPSGRB', $parser->pil);

        // Verify headline extracted
        $this->assertNotNull($parser->headline);
        $this->assertStringContainsString('strong thunderstorm', strtolower($parser->headline));

        // Verify expiration extracted from zone string
        $this->assertNotNull($parser->expiration_time);

        // Verify segments
        $this->assertCount(1, $segments);
        $segment = $segments[0];

        // Verify zones parsed (5 zones)
        $this->assertCount(5, $segment->zones);

        // Verify IBW metadata tags (hazard/source/impact require three-part format)
        $this->assertNotNull($segment->ibw->landspout);

        // Verify MAX HAIL SIZE
        $this->assertEquals('0.88 IN', trim($segment->ibw->hail_mag));

        // Verify MAX WIND GUST
        $this->assertEquals('40 MPH', trim($segment->ibw->wind_mag));

        // Should have polygon
        $this->assertNotNull($segment->polygon);
        $this->assertInstanceOf(\chswx\LDMIngest\Parser\Library\Geo\Polygon::class, $segment->polygon);

        // Should have SMV
        $this->assertNotNull($segment->smv);
    }

    public function testIBWSPSWaterspout()
    {
        $prod_info = [
            'wmo' => 'WWUS82',
            'office' => 'KGYX',
            'pil' => 'SPSGYX',
            'timestamp' => strtotime('2020-05-18 06:40:00')
        ];

        $parser = new \chswx\LDMIngest\Parser\ProductTypes\SPS($prod_info, $this->ibw_sps_waterspout);
        $segments = $parser->parse();

        $this->assertEquals('sps', $parser->type);
        $this->assertEquals('SPSGYX', $parser->pil);

        // Verify headline extracted
        $this->assertNotNull($parser->headline);

        // Verify IBW metadata tags
        $this->assertNotNull($segment = $segments[0]);
        $this->assertNotNull($segment->ibw->waterspout);

        // Verify MAX WIND GUST
        $this->assertNotNull($segment->ibw->wind_mag);

        // Should have polygon
        $this->assertNotNull($segment->polygon);
        $this->assertInstanceOf(\chswx\LDMIngest\Parser\Library\Geo\Polygon::class, $segment->polygon);
    }

    public function testIBWSPSWindHailLine()
    {
        $prod_info = [
            'wmo' => 'WWUS82',
            'office' => 'KPAH',
            'pil' => 'SPSPAH',
            'timestamp' => strtotime('2015-05-04 20:38:00')
        ];

        $parser = new \chswx\LDMIngest\Parser\ProductTypes\SPS($prod_info, $this->ibw_sps_wind_hail_line);
        $segments = $parser->parse();

        $this->assertEquals('sps', $parser->type);
        $this->assertNotNull($parser->headline);

        $segment = $segments[0];
        $this->assertEquals('0.25 IN', trim($segment->ibw->hail_mag));
        $this->assertEquals('50 MPH', trim($segment->ibw->wind_mag));
    }

    public function testIBWSPSWindHailSingleStorm()
    {
        $prod_info = [
            'wmo' => 'WWUS82',
            'office' => 'KDVN',
            'pil' => 'SPSDVN',
            'timestamp' => strtotime('2015-05-04 20:38:00')
        ];

        $parser = new \chswx\LDMIngest\Parser\ProductTypes\SPS($prod_info, $this->ibw_sps_wind_hail_single_storm);
        $segments = $parser->parse();

        $this->assertEquals('sps', $parser->type);
        $this->assertNotNull($parser->headline);

        $segment = $segments[0];
        $this->assertEquals('0.75 IN', trim($segment->ibw->hail_mag));
        $this->assertEquals('40 MPH', trim($segment->ibw->wind_mag));
    }

    public function hasDependencies()
    {
        return null;
    }
}
