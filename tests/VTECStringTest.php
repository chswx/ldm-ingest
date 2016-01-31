<?php

include('inc/VTECString.class.php');

class VTECStringTest extends PHPUnit_Framework_TestCase {

    var $expireOperationalTestString = "/O.EXP.KCHS.FF.W.0010.000000T0000Z-120830T0245Z/";

    function testFullVtecString() {
        $vtec = new VTECString($this->expireOperationalTestString);

        var_dump($vtec);

        $this->assertEquals($this->expireOperationalTestString,$vtec->vtec_string);
    }
    
    function testOperational() {
        $vtec = new VTECString($this->expireOperationalTestString);
        
        $this->assertEquals(true,$vtec->is_operational());
    }

    function testExpired() {
        $vtec = new VTECString($this->expireOperationalTestString);

        $this->assertEquals('EXP',$vtec->get_action());
    }

}

