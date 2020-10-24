<?php

namespace chswx\LDMIngest\Tests;

use chswx\LDMIngest\Parser\Library\IBW;
use PHPUnit\Framework\TestCase;

require_once 'vendor/autoload.php';

date_default_timezone_set('UTC');

class IBWTest extends TestCase
{
    protected $mixed_ibw;

    protected function setUp()
    {
        $this->mixed_tor_ibw = new IBW(file_get_contents(getcwd() . '/tests/sample-data/TORCHS-mixed.txt'));
        $this->svr_ibw = new IBW(file_get_contents(getcwd() . '/tests/sample-data/SVRCHS.txt'));
        $this->mixed_svr_ibw = new IBW(file_get_contents(getcwd() . '/tests/sample-data/SVRCHS.txt'));
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
    }
}
