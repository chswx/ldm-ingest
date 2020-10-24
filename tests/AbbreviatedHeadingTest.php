<?php

namespace chswx\LDMIngest\Tests;

use chswx\LDMIngest\Parser\Library\WMO\AbbreviatedHeading;
use PHPUnit\Framework\TestCase;

require_once 'vendor/autoload.php';

date_default_timezone_set('UTC');

class AbbreviatedHeadingTest extends TestCase
{
    /**
     * @covers AbbreviatedHeading::__construct
     * @covers AbbreviatedHeading::generateTimestampFromWMO
     */
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

    public function testCorrections()
    {
        $heading = new AbbreviatedHeading("WFUS52 KCHS 221432 CCA");

        $this->assertEquals(
            "CCA",
            $heading->amendment,
            "Amendment was not equal to CCA"
        );
    }

    public function testNoCorrections()
    {
        $heading = new AbbreviatedHeading("WFUS52 KCHS 221432");

        $this->assertEquals(
            null,
            $heading->amendment,
            'Amendment was not null'
        );
    }
}
