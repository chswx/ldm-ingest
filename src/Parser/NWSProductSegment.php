<?php

namespace UpdraftNetworks\Parser;

use UpdraftNetworks\Utils as Utils;

class NWSProductSegment
{
    /**
     * Segment text
     *
     * @var string
     */

    public $text;

    /**
     * Zones for this segment.
     *
     * @var array zones
     */
    public $zones;

    /**
     * Issuing time.
     *
     * @var int Timestamp
     */
    public $issued_time;

    /**
     * Issuing WFO (from parent product)
     *
     * @var string $office
     */
    public $office;

    /**
     * AFOS code (from parent product)
     *
     * @var string $afos
     */
    public $afos;

    /**
     * Constructor.
     *
     * @param string $segment_text
     */
    public function __construct($segment_text, $afos, $office)
    {
        $this->afos = $afos;
        $this->office = $office;
        $this->text = $segment_text;
        $this->zones = $this->parse_zones();
    }

    /**
     * Get this segment's text.
     *
     * @return string Raw text of the segment
     */
    public function get_text()
    {
        return $this->text;
    }

    /**
     * Return the zones for this product.
     *
     * @return array of zones
     */
    public function get_zones()
    {
        return $this->zones;
    }

    /**
     * Was this product issued for a particular zone(s)?
     *
     * @param array $zones Array of zone codes to check against
     *
     * @return boolean Array search result - true if found, false if not
     */
    public function in_zone($zones)
    {
        foreach ($zones as $zone) {
            if (in_array($zone, $this->zones)) {
                return true;
            } else {
                $array_search_result = false;
            }
        }

        return $array_search_result;
    }

    /*
     * Zone generation functions
     */

    /**
     * The NWS combines does not repeat the state code for multiple zones...not good for our purpose
     * All we want to do here is convert ranges like INZ021-028 to INZ021-INZ028
     * We will also call the function to expand the ranges here.
     * See: http://www.weather.gov/emwin/winugc.htm
     */
    protected function parse_zones()
    {
        $data = $this->text;

        $output = str_replace(array("\r\n", "\r"), "\n", $data);
        $lines = explode("\n", $output);
        $new_lines = array();

        foreach ($lines as $i => $line) {
            if (!empty($line)) {
                $new_lines[] = trim($line);
            }
        }
        $data = implode($new_lines);

        /* split up individual states - multiple states may be in the same forecast */
        $regex = '/(([A-Z]{2})(C|Z){1}([0-9]{3})((>|-)[0-9]{3})*)-/';

        $count = preg_match_all($regex, $data, $matches);
        $total_zones = '';

        foreach ($matches[0] as $field => $value) {
            /* since the NWS thought it was efficient to not repeat state codes, we have to reverse that */
            $state = substr($value, 0, 3);
            $zones = substr($value, 3);

            /* convert ranges like 014>016 to 014-015-016 */
            $zones = $this->expand_ranges($zones);

            /* hack off the last dash */
            $zones = substr($zones, 0, strlen($zones) - 1);

            $zones = $state . str_replace('-', '-' . $state, $zones);

            $total_zones .= $zones;

            // Need one last dash to temporarily buffer between state changes
            $total_zones .= '-';
        }

        /* One last cleanup */
        $total_zones = substr($total_zones, 0, strlen($total_zones) - 1);
        $total_zones = explode('-', $total_zones);

        return $total_zones;
    }

    /**
     * The NWS combines multiple zones into ranges...not good for our purpose
     * All we want to do here is convert ranges like 014>016 to 014-015-016
     * See: http://www.weather.gov/emwin/winugc.htm
     */
    protected function expand_ranges($data)
    {
        $regex = '/(([0-9]{3})(>[0-9]{3}))/';

        $count = preg_match_all($regex, $data, $matches);

        foreach ($matches[0] as $field => $value) {
            list($start, $end) = explode('>', $value);

            $new_value = array();
            for ($i = $start; $i <= $end; $i++) {
                $new_value[] = str_pad($i, 3, '0', STR_PAD_LEFT);
            }

            $data = str_replace($value, implode('-', $new_value), $data);
        }

        return $data;
    }
}
