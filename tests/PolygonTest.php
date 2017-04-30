<?php
namespace UpdraftNetworks\Parser\Tests;

use UpdraftNetworks\Parser\Library\Geo\Polygon;
use PHPUnit\Framework\TestCase;

require_once('vendor/autoload.php');

date_default_timezone_get('UTC');

class PolygonTest extends TestCase
{
    public function testPolygonClass()
    {
        // test case: Ensure the first coordinate is also the last
        $polygon = array(
            array(-81.8523, 32.5323),
            array(-81.7600, 32.9100)
        );
        $polygon_with_last_coord = array(
            array(-81.8523, 32.5323),
            array(-81.7600, 32.9100),
            array(-81.8523, 32.5323)
        );
        $expected = array('type' => 'Polygon', 'coordinates' => array($polygon_with_last_coord));
        $actual = new Polygon($polygon);
        $this->assertEquals($expected, $actual->to_array(), 'Arrays did not match');
    }
}
