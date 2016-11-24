<?php
/*
 * Not Irritable Bowel Weather, Impact-Based Warnings.
 */

namespace UpdraftNetworks\Parser\Library;

use UpdraftNetworks\Utils as Utils;

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
        $this->tornado = $this->find_metadata($segment_text, 'tornado');
        $this->wind = $this->find_metadata($segment_text, 'wind');
        $this->hail = $this->find_metadata($segment_text, 'hail');
        $this->tornado_damage = $this->find_metadata($segment_text, 'tornado damage threat');
        $this->waterspout = $this->find_metadata($segment_text, 'waterspout');
        $impacts = $this->find_impacts_in_text($segment_text, "hazard");
        if (!is_null($impacts)) {
            $this->hazard = $impacts[0];
            $this->source = $impacts[1];
            $this->impact = $impacts[2];
        }
    }

    public function find_metadata($text, $type)
    {
        $type = strtoupper($type);
        if (preg_match("/$type\.\.\.(.*)/", $text, $matches)) {
            Utils::log(print_r($matches));
        } else {
            return null;
        }

        return $matches[1];
    }

    public function find_impacts_in_text($text, $type)
    {
        // Normalize the type to uppercase.
        $type = strtoupper($type);

        // Get the product on one line and remove extra indenting spaces for maximum parsability.
        $sanitized_text = Utils::deindent(Utils::strip_newlines($text));
        if (preg_match('/HAZARD\.\.\.(.*)SOURCE\.\.\.(.*)IMPACT\.\.\.(.*)(?=\*)/', $sanitized_text, $matches)) {
            $impacts = array();
            unset($matches[0]);
            foreach ($matches as $impact) {
                $impacts[] = trim($impact);
            }

            return $impacts;
        }

        return null;
    }
}
