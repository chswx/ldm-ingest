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

        $this->assertEquals($this->april_2021_ibw_svr_base->hail, "1.00 IN");
        $this->assertEquals($this->april_2021_ibw_svr_considerable_wind->hail, "1.00 IN");
        $this->assertEquals($this->april_2021_ibw_svr_considerable_wind->hail_threat, "RADAR INDICATED");
        $this->assertEquals($this->april_2021_ibw_svr_base->wind, "60 MPH");
        $this->assertEquals($this->april_2021_ibw_svr_considerable_wind->wind_threat, "OBSERVED");
    }

    public function hasDependencies()
    {
        return null;
    }
}
