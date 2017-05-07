<?php

namespace UpdraftNetworks\Tests;

use UpdraftNetworks\Parser\Library\WMO\AbbreviatedHeading;
use PHPUnit\Framework\TestCase;

require_once 'vendor/autoload.php';

date_default_timezone_set('UTC');

class AbbreviatedHeadingTest extends TestCase
{
    public function testTimestampGenerator()
    {
        $heading = new AbbreviatedHeading("WFUS52 KCHS 221432");
        
        $seed_timestamp = 1494120476;   // from May 2017
        $timestamp = "221432";
        
        $this->assertEquals(
            1495463520,
            $heading->generateTimestampFromWMO($timestamp, $seed_timestamp),
            "Did not get equal times"
        );
    }
}
