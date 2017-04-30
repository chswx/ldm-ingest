<?php
namespace UpdraftNetworks\Parser\Library\Tests;

use UpdraftNetworks\Parser\Library\VTECString;
use PHPUnit\Framework\TestCase;

require_once('vendor/autoload.php');

date_default_timezone_set('UTC');

class VTECStringTest extends TestCase
{
    public $expireOperationalString = "/O.EXP.KCHS.FF.W.0010.000000T0000Z-120830T0245Z/";

    public $newOperationalString = "/O.NEW.KCHS.SV.W.0005.160326T1847Z-160326T1930Z/";

    public function testVtecOperationalExpiration()
    {
        $vtec = new VTECString($this->expireOperationalString);

        // test case 1: Make sure the parser is parsing properly
        $this->assertEquals($this->expireOperationalString, $vtec->vtec_string);

        // test case 2: Make sure the operational checks are good
        $this->assertEquals(true, $vtec->isOperational());

        // test case 3: Assert that this is not a test string
        $this->assertEquals(false, $vtec->isTest());

        // test case 4: test it is an expiration
        $this->assertEquals('EXP', $vtec->getAction());

        // test case 5: test it is not an issuance
        $this->assertNotEquals('NEW', $vtec->getAction());

        // test case 6: test the ETN parser
        $this->assertEquals('0010', $vtec->getETN());
    }

    public function testVtecOperationalIssuance()
    {
        $vtec = new VTECString($this->newOperationalString);

        // test case 1: Make sure the parser is parsing properly
        $this->assertEquals($this->newOperationalString, $vtec->vtec_string);

        // test case 2: Make sure the operational checks are good
        $this->assertEquals(true, $vtec->isOperational());

        // test case 3: Assert that this is not a test string
        $this->assertEquals(false, $vtec->isTest());

        // test case 4: test it is a new issuance
        $this->assertEquals('NEW', $vtec->getAction());

        // test case 5: test it is not an expiration
        $this->assertNotEquals('EXP', $vtec->getAction());

        // test case 6: test the ETN parser
        $this->assertEquals('0005', $vtec->getETN());
    }
}
