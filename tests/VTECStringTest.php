<?php
namespace UpdraftNetworks\Tests;

use UpdraftNetworks\Parser\Library\VTECString as VTECString;

require_once('vendor/autoload.php');

date_default_timezone_set('UTC');

class VTECStringTest extends \PHPUnit_Framework_TestCase
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
