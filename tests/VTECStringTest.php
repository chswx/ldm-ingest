<?php

date_default_timezone_set('UTC');
include('inc/VTECString.class.php');

class VTECStringTest extends PHPUnit_Framework_TestCase {

    var $expireOperationalTestString = "/O.EXP.KCHS.FF.W.0010.000000T0000Z-120830T0245Z/";

    function testFullVtecStringParser() {
        $vtec = new VTECString($this->expireOperationalTestString);

        $this->assertEquals($this->expireOperationalTestString,$vtec->vtec_string);
    }
    
    function testIsOperational() {
        $vtec = new VTECString($this->expireOperationalTestString);
        
        $this->assertEquals(true,$vtec->is_operational());
    }

    function testIsNotTest() {
        $vtec = new VTECString($this->expireOperationalTestString);

        $this->assertEquals(false,$vtec->is_test());
    }

    function testIsExpired() {
        $vtec = new VTECString($this->expireOperationalTestString);

        $this->assertEquals('EXP',$vtec->get_action());
    }

}

