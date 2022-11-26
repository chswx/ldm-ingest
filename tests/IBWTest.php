<?php

namespace chswx\LDMIngest\Tests;

use chswx\LDMIngest\Parser\Library\IBW;
use PHPUnit\Framework\TestCase;

require_once 'vendor/autoload.php';

date_default_timezone_set('UTC');

class IBWTest extends TestCase
{
    protected $mixed_ibw;

    protected function setUp(): void
    {
        $this->mixed_tor_ibw = new IBW(file_get_contents(getcwd() . '/tests/sample-data/TORCHS-mixed.txt'));
        $this->mixed_svr_ibw = new IBW(file_get_contents(getcwd() . '/tests/sample-data/SVRCHS-mixed.txt'));

        // Updated IBW for SVR going live April 2021
        $this->april_2021_ibw_svr_base = new IBW(file_get_contents(getcwd() . '/tests/sample-data/april-2021-ibw/svr-base-no-damage-tag'));
        $this->april_2021_ibw_svr_considerable_wind = new IBW(file_get_contents(getcwd() . '/tests/sample-data/april-2021-ibw/svr-considerable-wind-damage-tag'));
        $this->april_2021_ibw_svr_destructive_hail = new IBW(file_get_contents(getcwd() . '/tests/sample-data/april-2021-ibw/svr-destructive-hail'));
        $this->april_2021_ibw_svs_considerable_pcancel = new IBW(file_get_contents(getcwd() . '/tests/sample-data/april-2021-ibw/svs-considerable-wind-partial-cancel'));
        $this->april_2021_ibw_svs_destructive_wind = new IBW(file_get_contents(getcwd() . '/tests/sample-data/april-2021-ibw/svs-destructive-wind'));

        // Updated IBW for SPS going live April 2021
        $this->april_2021_ibw_sps_landspout = new IBW(file_get_contents(getcwd() . '/tests/sample-data/april-2021-ibw/sps-landspout'));
        $this->april_2021_ibw_sps_waterspout_observed = new IBW(file_get_contents(getcwd() . '/tests/sample-data/april-2021-ibw/sps-waterspout-observed'));

        // Flash Flood Warning IBW
        $this->ffw_ibw = new IBW(file_get_contents(getcwd() . '/tests/sample-data/ffw-sequence-jul142022/issuance'));
    }

    public function testMetadataSearch()
    {
        //
        // Tornado warning: Exercise the tornado tag
        //

        $this->assertEquals($this->mixed_tor_ibw->tornado, "RADAR INDICATED");
        $this->assertNotEquals($this->mixed_tor_ibw->tornado, "OBSERVED");
        // These would technically be out of compliance with the standard
        $this->assertNotEquals($this->mixed_tor_ibw->tornado, "RADAR CONFIRMED");
        $this->assertNotEquals($this->mixed_tor_ibw->tornado, "SPOTTED");

        //
        // Legacy hail and wind tags
        //

        $this->assertEquals($this->mixed_svr_ibw->hail, "<.75IN");
        $this->assertEquals($this->mixed_svr_ibw->wind, "60MPH");

        //
        // New hail and wind tags
        //

        $this->assertEquals("1.00 IN", $this->april_2021_ibw_svr_base->hail);
        $this->assertEquals("1.00 IN", $this->april_2021_ibw_svr_considerable_wind->hail);
        $this->assertEquals("RADAR INDICATED", $this->april_2021_ibw_svr_considerable_wind->hail_threat);
        $this->assertEquals("60 MPH", $this->april_2021_ibw_svr_base->wind);
        $this->assertEquals("OBSERVED", $this->april_2021_ibw_svr_considerable_wind->wind_threat);

        //
        // Flash Flood tags
        //

        $this->assertEquals("RADAR INDICATED", $this->ffw_ibw->flash_flood);
        $this->assertEquals("CONSIDERABLE", $this->ffw_ibw->flash_flood_threat);
        $this->assertEquals("1-2.5 INCHES IN 1 HOUR", $this->ffw_ibw->rain_rate);
    }

    public function hasDependencies()
    {
        return null;
    }
}
