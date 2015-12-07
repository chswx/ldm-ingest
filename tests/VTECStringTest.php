<?php

include('inc/VTECString.class.php');

class VTECStringTest extends PHPUnit_Framework_TestCase {

    var $expireOperationalTestString = "/O.EXP.KCHS.FF.W.0010.000000T0000Z-120830T0245Z/";

    function testFullVtecString() {
        $vtec = new VTECString($this->expireOperationalTestString);

        $this->assertEquals($vtec->vtec_string,$this->expireOperationalTestString);
    }
    
    function testOperational() {
        $vtec = new VTECString($this->expireOperationalTestString);
        
        $this->assertEquals($vtec->is_operational(),true);
    }

    function testExpired() {
        $vtec = new VTECString($this->expireOperationalTestString);

        $this->assertEquals($vtec->get_action(),'EXP');
    }

}

