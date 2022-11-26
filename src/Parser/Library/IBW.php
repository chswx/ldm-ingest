<?php

/*
 * Not Irritable Bowel Weather, Impact-Based Warnings.
 */

namespace chswx\LDMIngest\Parser\Library;

use chswx\LDMIngest\Utils;

class IBW
{
    public $tornado;
    public $wind;
    public $hail;
    public $tornado_damage;
    public $waterspout;
    public $hazard;
    public $source;
    public $impact;
    public $landspout;
    public $thunderstorm_damage;
    public $hail_threat;
    public $wind_threat;
    public $flash_flood;
    public $flash_flood_threat;
    public $rain_rate;
    public $is_pds;
    public $is_emergency;

    /**
     * Constructor.
     * Brings in segment text and extracts metadata and other impact-based warning data.
     *
     * @param string $segment_text Block of text coming in for examination.
     *
     * @return IBW
     */
    public function __construct($segment_text)
    {
        $this->tornado = $this->findMetadata($segment_text, 'tornado');
        $this->wind = $this->findMetadata($segment_text, 'wind');
        // Compatibility shim for the new April 2021 impact-based data.
        // Should remove this check and the above 'wind' search for perf after new IBW goes operational.
        if (is_null($this->wind)) {
            $this->wind = $this->findMetadata($segment_text, 'max wind gust');
        }
        $this->hail = $this->findMetadata($segment_text, 'hail');
        // Compatibility shim for the new April 2021 impact-based data.
        // Should remove this check and the above 'hail' search for perf after new IBW goes operational.
        if (is_null($this->hail)) {
            $this->hail = $this->findMetadata($segment_text, 'max hail size');
        }
        $this->tornado_damage = $this->findMetadata($segment_text, 'tornado damage threat');
        $this->waterspout = $this->findMetadata($segment_text, 'waterspout');
        // New as of April 2021
        $this->landspout = $this->findMetadata($segment_text, 'landspout');
        $this->thunderstorm_damage = $this->findMetadata($segment_text, 'thunderstorm damage threat');
        $this->hail_threat = $this->findMetadata($segment_text, 'hail threat');
        $this->wind_threat = $this->findMetadata($segment_text, 'wind threat');
        // Impact-based flash flood warnings are online with Hazard Services as of Summer 2022
        $this->flash_flood = $this->findMetadata($segment_text, 'flash flood');
        $this->flash_flood_threat = $this->findMetadata($segment_text, 'flash flood damage threat');
        $this->rain_rate = $this->findMetadata($segment_text, 'expected rainfall rate');
        $impacts = $this->findHazSrcImpact($segment_text);
        if (!is_null($impacts)) {
            $this->hazard = $impacts['hazard'];
            $this->source = $impacts['source'];
            $this->impact = $impacts['impact'];
        }
        // Is this a PDS watch/warning?
        $this->is_pds = Utils::findPDS($segment_text);
        // Is this an "emergency" level warning, such as a tornado or flash flood emergency?
        $this->is_emergency = Utils::findEmergency($segment_text);
    }

    /**
     * Find the specified metadata within the warning.
     * @param mixed $text Warning product text.
     * @param mixed $type The type of metadata to find (tornado, wind, etc.)
     * @return string|null The matching string, if found, otherwise null
     */
    public function findMetadata($text, $type)
    {
        $type = strtoupper($type);
        if (!preg_match("/$type\.\.\.(.*)/", $text, $matches)) {
            return null;
        }

        return $matches[1];
    }

    /**
     * Finds the HAZARD...SOURCE...IMPACT... line in the warning and extracts the contents.
     * @param mixed $text Warning text
     * @return array|null Array of hazard/source/impact, otherwise null
     */
    public function findHazSrcImpact($text)
    {
        $keys = ['hazard', 'source', 'impact'];
        $impacts = null;

        // Get the product on one line and remove extra indenting spaces for maximum parsability.
        $sanitized_text = Utils::deindent(Utils::stripNewlines($text));
        if (preg_match('/HAZARD\.\.\.(.*)SOURCE\.\.\.(.*)IMPACT\.\.\.(.*)(?=\*)/', $sanitized_text, $matches)) {
            $impacts = array();
            unset($matches[0]);
            foreach ($matches as $impact) {
                $impacts[] = trim($impact);
            }

            // Make the impacts array available using plain-value keys in addition to numerical indexes
            $impacts = array_combine($keys, $impacts);
        }

        return $impacts;
    }
}
