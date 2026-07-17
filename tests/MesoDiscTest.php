<?php
namespace chswx\LDMIngest\Parser\Tests;

use chswx\LDMIngest\Parser\MesoDisc;

require_once('vendor/autoload.php');

date_default_timezone_set('UTC');

class MesoDiscTest extends \PHPUnit\Framework\TestCase
{
    private function loadSampleData(string $filename): string
    {
        $path = __DIR__ . '/sample-data/' . $filename;
        $content = file_get_contents($path);
        if ($content === false) {
            throw new \RuntimeException("Could not load sample data: $filename");
        }
        return $content;
    }

    private function createMesoDisc(string $productText, string $office = 'KWNS', string $afos = 'SWOMCD'): MesoDisc
    {
        $prodInfo = [
            'office' => $office,
            'afos' => $afos,
        ];
        return new MesoDisc($prodInfo, $productText);
    }

    // ════════════════════════════════════════
    // SEVERE_POTENTIAL (Watch Likely 80%)
    // ════════════════════════════════════════

    public function testSeverePotentialParsesMcdNumber(): void
    {
        $text = $this->loadSampleData('SWOMCD-severe-potential.txt');
        $disc = $this->createMesoDisc($text);
        $segments = $disc->parse();
        $this->assertNotEmpty($segments);
        $this->assertEquals(973, $segments[0]['mcd_number']);
    }

    public function testSeverePotentialParsesMcdType(): void
    {
        $text = $this->loadSampleData('SWOMCD-severe-potential.txt');
        $disc = $this->createMesoDisc($text);
        $segments = $disc->parse();
        $this->assertEquals('SEVERE_POTENTIAL', $segments[0]['mcd_type']);
    }

    public function testSeverePotentialParsesProbability(): void
    {
        $text = $this->loadSampleData('SWOMCD-severe-potential.txt');
        $disc = $this->createMesoDisc($text);
        $segments = $disc->parse();
        $this->assertEquals(80, $segments[0]['probability_of_watch_issuance']);
    }

    public function testSeverePotentialNoActiveWatches(): void
    {
        $text = $this->loadSampleData('SWOMCD-severe-potential.txt');
        $disc = $this->createMesoDisc($text);
        $segments = $disc->parse();
        $this->assertNull($segments[0]['active_watches']);
    }

    public function testSeverePotentialParsesAreasAffected(): void
    {
        $text = $this->loadSampleData('SWOMCD-severe-potential.txt');
        $disc = $this->createMesoDisc($text);
        $segments = $disc->parse();
        $affected = $segments[0]['areas_affected'];
        $this->assertStringContainsString('EAST TX', $affected);
        $this->assertStringContainsString('WESTERN LA', $affected);
    }

    public function testSeverePotentialParsesValidWindow(): void
    {
        $text = $this->loadSampleData('SWOMCD-severe-potential.txt');
        $disc = $this->createMesoDisc($text);
        $segments = $disc->parse();
        $window = $segments[0]['valid_window'];
        $this->assertNotNull($window);
        $this->assertArrayHasKey('start', $window);
        $this->assertArrayHasKey('end', $window);
    }

    public function testValidWindowSpansMonthBoundary(): void
    {
        $text = "ACUS11 KWNS 302300\nSWOMCD\n\nMESOSCALE DISCUSSION 0001\nNWS STORM PREDICTION CENTER NORMAN OK\n1100 PM CDT THU APR 30 2026\n\nVALID 302300Z - 010200Z\n";
        $disc = $this->createMesoDisc($text);
        $window = $disc->parse()[0]['valid_window'];

        $this->assertEquals(gmmktime(23, 0, 0, 4, 30, 2026), $window['start']);
        $this->assertEquals(gmmktime(2, 0, 0, 5, 1, 2026), $window['end']);
    }

    public function testValidWindowSpansYearBoundary(): void
    {
        $text = "ACUS11 KWNS 312300\nSWOMCD\n\nMESOSCALE DISCUSSION 0001\nNWS STORM PREDICTION CENTER NORMAN OK\n1100 PM CST THU DEC 31 2026\n\nVALID 312300Z - 010600Z\n";
        $disc = $this->createMesoDisc($text);
        $window = $disc->parse()[0]['valid_window'];

        $this->assertEquals(gmmktime(23, 0, 0, 12, 31, 2026), $window['start']);
        $this->assertEquals(gmmktime(6, 0, 0, 1, 1, 2027), $window['end']);
    }

    public function testValidWindowCanStartInPreviousYear(): void
    {
        $text = "ACUS11 KWNS 010100\nSWOMCD\n\nMESOSCALE DISCUSSION 0001\nNWS STORM PREDICTION CENTER NORMAN OK\n0100 AM CST FRI JAN 1 2027\n\nVALID 312300Z - 010600Z\n";
        $disc = $this->createMesoDisc($text);
        $window = $disc->parse()[0]['valid_window'];

        $this->assertEquals(gmmktime(23, 0, 0, 12, 31, 2026), $window['start']);
        $this->assertEquals(gmmktime(6, 0, 0, 1, 1, 2027), $window['end']);
    }

    public function testSeverePotentialParsesSummary(): void
    {
        $text = $this->loadSampleData('SWOMCD-severe-potential.txt');
        $disc = $this->createMesoDisc($text);
        $segments = $disc->parse();
        $summary = $segments[0]['summary'];
        $this->assertStringContainsString('SEVERE THUNDERSTORMS', $summary);
    }

    public function testSeverePotentialParsesDiscussion(): void
    {
        $text = $this->loadSampleData('SWOMCD-severe-potential.txt');
        $disc = $this->createMesoDisc($text);
        $segments = $disc->parse();
        $discussion = $segments[0]['discussion'];
        $this->assertStringContainsString('LOW-LEVEL JET', $discussion);
    }

    public function testSeverePotentialParsesForecasters(): void
    {
        $text = $this->loadSampleData('SWOMCD-severe-potential.txt');
        $disc = $this->createMesoDisc($text);
        $segments = $disc->parse();
        $forecasters = $segments[0]['forecasters'];
        $this->assertNotNull($forecasters);
        $this->assertContains('CHALMERS', $forecasters);
        $this->assertContains('LYONS', $forecasters);
        $this->assertContains('HART', $forecasters);
    }

    public function testSeverePotentialParsesAttnWfo(): void
    {
        $text = $this->loadSampleData('SWOMCD-severe-potential.txt');
        $disc = $this->createMesoDisc($text);
        $segments = $disc->parse();
        $attn = $segments[0]['attn_wfo'];
        $this->assertNotNull($attn);
        $this->assertContains('LCH', $attn);
        $this->assertContains('SHV', $attn);
        $this->assertContains('HGX', $attn);
        $this->assertContains('FWD', $attn);
    }

    public function testSeverePotentialParsesPolygon(): void
    {
        $text = $this->loadSampleData('SWOMCD-severe-potential.txt');
        $disc = $this->createMesoDisc($text);
        $segments = $disc->parse();
        $polygon = $segments[0]['polygon'];
        $this->assertNotNull($polygon);
        $this->assertEquals('Polygon', $polygon['type']);
        $this->assertNotEmpty($polygon['coordinates']);
        $this->assertNotEmpty($polygon['coordinates'][0]);
    }

    // ════════════════════════════════════════
    // WATCH_UPDATE (Tornado Watch 186)
    // ════════════════════════════════════════

    public function testWatchUpdateParsesMcdNumber(): void
    {
        $text = $this->loadSampleData('SWOMCD-watch-update.txt');
        $disc = $this->createMesoDisc($text);
        $segments = $disc->parse();
        $this->assertEquals(103, $segments[0]['mcd_number']);
    }

    public function testWatchUpdateParsesType(): void
    {
        $text = $this->loadSampleData('SWOMCD-watch-update.txt');
        $disc = $this->createMesoDisc($text);
        $segments = $disc->parse();
        $this->assertEquals('WATCH_UPDATE', $segments[0]['mcd_type']);
    }

    public function testWatchUpdateNoProbability(): void
    {
        $text = $this->loadSampleData('SWOMCD-watch-update.txt');
        $disc = $this->createMesoDisc($text);
        $segments = $disc->parse();
        $this->assertNull($segments[0]['probability_of_watch_issuance']);
    }

    public function testWatchUpdateParsesActiveWatches(): void
    {
        $text = $this->loadSampleData('SWOMCD-watch-update.txt');
        $disc = $this->createMesoDisc($text);
        $segments = $disc->parse();
        $watches = $segments[0]['active_watches'];
        $this->assertNotNull($watches);
        $this->assertContains(186, $watches);
    }

    public function testWatchUpdateParsesAttnWfo(): void
    {
        $text = $this->loadSampleData('SWOMCD-watch-update.txt');
        $disc = $this->createMesoDisc($text);
        $segments = $disc->parse();
        $attn = $segments[0]['attn_wfo'];
        $this->assertNotNull($attn);
        $this->assertContains('OUN', $attn);
        $this->assertContains('TSA', $attn);
        $this->assertContains('LZK', $attn);
        $this->assertContains('MEG', $attn);
    }

    public function testWatchUpdateParsesForecasters(): void
    {
        $text = $this->loadSampleData('SWOMCD-watch-update.txt');
        $disc = $this->createMesoDisc($text);
        $segments = $disc->parse();
        $forecasters = $segments[0]['forecasters'];
        $this->assertNotNull($forecasters);
        $this->assertContains('CHALMERS', $forecasters);
        $this->assertContains('SMITH', $forecasters);
    }

    // ════════════════════════════════════════
    // WINTER_WEATHER (Blizzard)
    // ════════════════════════════════════════

    public function testWinterWeatherParsesMcdNumber(): void
    {
        $text = $this->loadSampleData('SWOMCD-winter-weather.txt');
        $disc = $this->createMesoDisc($text);
        $segments = $disc->parse();
        $this->assertEquals(114, $segments[0]['mcd_number']);
    }

    public function testWinterWeatherParsesType(): void
    {
        $text = $this->loadSampleData('SWOMCD-winter-weather.txt');
        $disc = $this->createMesoDisc($text);
        $segments = $disc->parse();
        $this->assertEquals('WINTER_WEATHER', $segments[0]['mcd_type']);
    }

    public function testWinterWeatherNoProbability(): void
    {
        $text = $this->loadSampleData('SWOMCD-winter-weather.txt');
        $disc = $this->createMesoDisc($text);
        $segments = $disc->parse();
        $this->assertNull($segments[0]['probability_of_watch_issuance']);
    }

    public function testWinterWeatherNoActiveWatches(): void
    {
        $text = $this->loadSampleData('SWOMCD-winter-weather.txt');
        $disc = $this->createMesoDisc($text);
        $segments = $disc->parse();
        $this->assertNull($segments[0]['active_watches']);
    }

    public function testWinterWeatherParsesSummary(): void
    {
        $text = $this->loadSampleData('SWOMCD-winter-weather.txt');
        $disc = $this->createMesoDisc($text);
        $segments = $disc->parse();
        $summary = $segments[0]['summary'];
        $this->assertStringContainsString('HEAVY SNOW', $summary);
        $this->assertStringContainsString('BLIZZARD', $summary);
    }

    public function testWinterWeatherParsesForecasters(): void
    {
        $text = $this->loadSampleData('SWOMCD-winter-weather.txt');
        $disc = $this->createMesoDisc($text);
        $segments = $disc->parse();
        $forecasters = $segments[0]['forecasters'];
        $this->assertNotNull($forecasters);
        $this->assertContains('LYONS', $forecasters);
        $this->assertContains('HART', $forecasters);
    }

    public function testWinterWeatherParsesAttnWfo(): void
    {
        $text = $this->loadSampleData('SWOMCD-winter-weather.txt');
        $disc = $this->createMesoDisc($text);
        $segments = $disc->parse();
        $attn = $segments[0]['attn_wfo'];
        $this->assertNotNull($attn);
        $this->assertContains('ILX', $attn);
        $this->assertContains('IND', $attn);
        $this->assertContains('LSX', $attn);
    }

    public function testWinterWeatherParsesAreasAffected(): void
    {
        $text = $this->loadSampleData('SWOMCD-winter-weather.txt');
        $disc = $this->createMesoDisc($text);
        $segments = $disc->parse();
        $affected = $segments[0]['areas_affected'];
        $this->assertStringContainsString('NORTHEAST IL', $affected);
        $this->assertStringContainsString('SOUTHWEST IN', $affected);
        $this->assertStringContainsString('WESTERN MO', $affected);
    }

    // ════════════════════════════════════════
    // Segment structure
    // ════════════════════════════════════════

    public function testSegmentHasAllRequiredFields(): void
    {
        $text = $this->loadSampleData('SWOMCD-severe-potential.txt');
        $disc = $this->createMesoDisc($text);
        $segments = $disc->parse();
        $segment = $segments[0];

        $requiredFields = [
            'text', 'afos', 'office',
            'mcd_number', 'mcd_type', 'areas_affected',
            'valid_window', 'probability_of_watch_issuance',
            'active_watches', 'summary', 'discussion',
            'forecasters', 'attn_wfo', 'polygon',
            'peak_tornado_intensity', 'peak_wind_gust', 'peak_hail_size',
        ];

        foreach ($requiredFields as $field) {
            $this->assertArrayHasKey($field, $segment, "Missing required segment field: $field");
        }
    }

    // ════════════════════════════════════════
    // Office flexibility (AFWA backup)
    // ════════════════════════════════════════

    public function testAcceptsAlternateOffice(): void
    {
        $text = $this->loadSampleData('SWOMCD-severe-potential.txt');
        $disc = $this->createMesoDisc($text, 'AFWA');
        $segments = $disc->parse();
        $this->assertEquals('AFWA', $segments[0]['office']);
    }

    // ════════════════════════════════════════
    // MCD number parsing robustness
    // ════════════════════════════════════════

    public function testParsesMcdNumberFromMesoscaleDiscussionFormat(): void
    {
        $text = "ACUS11 KWNS 262325\nSWOMCD\n\nLAZ000-TXZ000-262345-\n\nMESOSCALE DISCUSSION 0121\n";
        $disc = $this->createMesoDisc($text);
        $segments = $disc->parse();
        $this->assertEquals(121, $segments[0]['mcd_number']);
    }

    public function testParsesMcdNumber0001(): void
    {
        $text = "ACUS11 KWNS 010000\nSWOMCD\n\nMESOSCALE DISCUSSION 0001\n";
        $disc = $this->createMesoDisc($text);
        $segments = $disc->parse();
        $this->assertEquals(1, $segments[0]['mcd_number']);
    }

    // ════════════════════════════════════════
    // MCD type classification robustness
    // ════════════════════════════════════════

    public function testClassifiesSevereThunderstormWatchAsWatchUpdate(): void
    {
        $text = "ACUS11 KWNS 010000\nSWOMCD\n\nMESOSCALE DISCUSSION 0001\n\nCONCERNING...SEVERE THUNDERSTORM WATCH 245...\n";
        $disc = $this->createMesoDisc($text);
        $segments = $disc->parse();
        $this->assertEquals('WATCH_UPDATE', $segments[0]['mcd_type']);
    }

    public function testClassifiesHeavySnowAsWinterWeather(): void
    {
        $text = "ACUS11 KWNS 010000\nSWOMCD\n\nMESOSCALE DISCUSSION 0001\n\nCONCERNING...HEAVY SNOW...\n";
        $disc = $this->createMesoDisc($text);
        $segments = $disc->parse();
        $this->assertEquals('WINTER_WEATHER', $segments[0]['mcd_type']);
    }

    public function testClassifiesBlizzardAsWinterWeather(): void
    {
        $text = "ACUS11 KWNS 010000\nSWOMCD\n\nMESOSCALE DISCUSSION 0001\n\nCONCERNING...BLIZZARD...\n";
        $disc = $this->createMesoDisc($text);
        $segments = $disc->parse();
        $this->assertEquals('WINTER_WEATHER', $segments[0]['mcd_type']);
    }

    // ════════════════════════════════════════
    // Peak threat parsing (2026 enhancement)
    // ════════════════════════════════════════

    public function testParsesPeakHailSizeFromDiscussion(): void
    {
        $text = "ACUS11 KWNS 262325\nSWOMCD\n\nMESOSCALE DISCUSSION 0973\n\nSUPERCELLS PRODUCING 1.5-2.0 INCH HAIL\n";
        $disc = $this->createMesoDisc($text);
        $segments = $disc->parse();
        $this->assertEquals('1.5-2.0 IN', $segments[0]['peak_hail_size']);
    }

    // ════════════════════════════════════════
    // Single forecaster
    // ════════════════════════════════════════

    public function testParsesSingleForecaster(): void
    {
        $sampleSig = "ACUS11 KWNS 010000\nSWOMCD\n\nMESOSCALE DISCUSSION 0001\n\n..SMITH.. 06/26/2026\n";
        $disc = $this->createMesoDisc($sampleSig);
        $segments = $disc->parse();
        $this->assertNotNull($segments[0]['forecasters']);
        $this->assertCount(1, $segments[0]['forecasters']);
        $this->assertContains('SMITH', $segments[0]['forecasters']);
    }

    // ════════════════════════════════════════
    // Parse multi-paragraph discussion
    // ════════════════════════════════════════

    public function testParsesDiscussionThroughEmptyLines(): void
    {
        $text = $this->loadSampleData('SWOMCD-severe-potential.txt');
        $disc = $this->createMesoDisc($text);
        $segments = $disc->parse();
        $discussion = $segments[0]['discussion'];
        $this->assertNotEmpty($discussion);
        $this->assertStringContainsString('LOW-LEVEL JET', $discussion);
        $this->assertStringContainsString('HODOGRAPHS', $discussion);
    }

    // ════════════════════════════════════════
    // Office/AFOS propagation
    // ════════════════════════════════════════

    public function testOfficePropagatedToSegment(): void
    {
        $text = $this->loadSampleData('SWOMCD-severe-potential.txt');
        $disc = $this->createMesoDisc($text, 'KWNS');
        $segments = $disc->parse();
        $this->assertEquals('KWNS', $segments[0]['office']);
    }

    public function testAfosPropagatedToSegment(): void
    {
        $text = $this->loadSampleData('SWOMCD-severe-potential.txt');
        $disc = $this->createMesoDisc($text, 'KWNS', 'SWOMCD');
        $segments = $disc->parse();
        $this->assertEquals('SWOMCD', $segments[0]['afos']);
    }

    // ════════════════════════════════════════
    // Polygon edge cases
    // ════════════════════════════════════════

    public function testPolygonWithThreeCoordinatesReturnsPolygon(): void
    {
        $text = "ACUS11 KWNS 010000\nSWOMCD\n\nMESOSCALE DISCUSSION 0001\n\nLAT...LON   29999435 30079572 30759602\n";
        $disc = $this->createMesoDisc($text);
        $polygon = $disc->parse()[0]['polygon'];

        $this->assertNotNull($polygon);
        $this->assertCount(3, $polygon['coordinates'][0]);
    }

    public function testPolygonWithFourCoordinatesReturnsPolygon(): void
    {
        $text = "ACUS11 KWNS 010000\nSWOMCD\n\nMESOSCALE DISCUSSION 0001\n\nLAT...LON   29999435 30079572 30759602 31929559\n";
        $disc = $this->createMesoDisc($text);
        $segments = $disc->parse();
        $this->assertNotNull($segments[0]['polygon']);
        $this->assertEquals('Polygon', $segments[0]['polygon']['type']);
    }

    /**
     * @dataProvider polygonContinuationWhitespaceProvider
     */
    public function testPolygonContinuationAcceptsVariableWhitespace(string $indent): void
    {
        $text = "ACUS11 KWNS 010000\nSWOMCD\n\nMESOSCALE DISCUSSION 0001\n\nLAT...LON   29999435 30079572 30759602\n{$indent}31929559 29999435\n";
        $disc = $this->createMesoDisc($text);
        $polygon = $disc->parse()[0]['polygon'];

        $this->assertNotNull($polygon);
        $this->assertCount(5, $polygon['coordinates'][0]);
        $this->assertEquals($polygon['coordinates'][0][0], $polygon['coordinates'][0][4]);
    }

    public function polygonContinuationWhitespaceProvider(): array
    {
        return [
            'four spaces' => ['    '],
            'eight spaces' => ['        '],
            'tab' => ["\t"],
        ];
    }
}
