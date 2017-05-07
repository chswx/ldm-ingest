<?php

namespace UpdraftNetworks\Parser\Library\WMO;

/**
 * Defines a WMO-compatible Abbreviated Heading.
 * http://www.nws.noaa.gov/tg/awips.php
 * http://www.nws.noaa.gov/tg/head.php
 */
class AbbreviatedHeading
{
    /**
     * WMO identifier.
     * @var string
     */
    public $id;

    /**
     * Issuing office `CCCC`
     * @var string
     */
    public $office;

    /**
     * UNIX timestamp of the issue time.
     * This is derived from the third part of the abbreviated heading.
     * @var int
     */
    public $timestamp;

    /**
     * Revisions or corrections should be found here.
     */
    public $amendment;

    public function __construct($wmo_header)
    {
        $wmo_header_arr = explode(' ', $wmo_header);
        $this->id = $wmo_header_arr[0];
        $this->office = $wmo_header_arr[1];
        $this->timestamp = $this->generateTimestampFromWMO($wmo_header_arr[2], time());
        $this->amendment = null;
        if (isset($wmo_header_arr[3])) {
            $this->amendment = $wmo_header_arr[3];
        }
    }

    /**
     * Given a WMO product issuance timestamp (YYGGgg) containing:
     * - YY (day of month)
     * - GG (UTC hour of issuance or observation)
     * - gg (Minute in hour of issuance or observation [if needed])
     * ...generate a UNIX timestamp.
     * CAVEAT: Sources the current month and year when putting together
     * the UNIX timestamp. OK for generating once and placing into
     * persistent storage, but adjust accordingly when unit testing!
     *
     * @param string $timestamp Incoming timestamp string
     * @param int    $seed_timestamp (optional) UNIX timestamp to seed the function
     *
     * @return string
     */
    public function generateTimestampFromWMO($timestamp, $seed_timestamp = 0)
    {
        // Get the current timestamp if not provided.
        if (empty($seed_timestamp)) {
            $seed_timestamp = time();
        }
        // Prepare a string compatible with strtotime().
        // Using XMLRPC (Compact): http://php.net/manual/en/datetime.formats.compound.php
        $curr_month_year = date('Ym', $seed_timestamp);
        $afos_day = substr($timestamp, 0, 2);
        $afos_time = substr($timestamp, 2, 4);

        // Put it all together and get us a timestamp.
        $unix_timestamp = strtotime($curr_month_year . $afos_day . 'T' . $afos_time . '00');

        return $unix_timestamp;
    }
}
