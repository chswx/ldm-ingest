<?php
namespace UpdraftNetworks\Parser\Library\Tests;

use UpdraftNetworks\Parser\Library\VTECString;
use PHPUnit\Framework\TestCase;

require_once('vendor/autoload.php');

date_default_timezone_set('UTC');

class VTECStringTest extends TestCase
{
    public $expireOperationalTestString = "/O.EXP.KCHS.FF.W.0010.000000T0000Z-120830T0245Z/";

    public function testFullVtecStringParser()
    {
        $vtec = new VTECString($this->expireOperationalTestString);

        $this->assertEquals($this->expireOperationalTestString, $vtec->vtec_string);
    }
    
    public function testIsOperational()
    {
        $vtec = new VTECString($this->expireOperationalTestString);
        
        $this->assertEquals(true, $vtec->is_operational());
    }

    public function testIsNotTest()
    {
        $vtec = new VTECString($this->expireOperationalTestString);

        $this->assertEquals(false, $vtec->is_test());
    }

    public function testIsExpired()
    {
        $vtec = new VTECString($this->expireOperationalTestString);

        $this->assertEquals('EXP', $vtec->get_action());
    }
}
