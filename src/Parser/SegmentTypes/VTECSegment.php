<?php

namespace chswx\LDMIngest\Parser\SegmentTypes;

use chswx\LDMIngest\Parser\NWSProductSegment;
use chswx\LDMIngest\Parser\Library\SMVString;
use chswx\LDMIngest\Parser\Library\IBW;
use chswx\LDMIngest\Parser\Library\SBW;
use chswx\LDMIngest\Parser\Library\VTECString;

/**
 * Extends the NWSProductSegment with attributes specific to VTEC-enabled products.
 */
class VTECSegment extends NWSProductSegment
{
    /**
     * Array of VTEC strings.
     *
     * @var array VTECString
     */
    public $vtec_strings;

    /**
     * Storm motion vector info.
     *
     * @var array SMVString
     */
    public $smv;

    /**
     * Impact-based warning info (if available.)
     */
    public $impacts;

    /**
     * Storm-based warning polygon geometry (if available)
     * Treat with the utmost respect
     */
    public $polygon;

    public function __construct($segment_text, $parentProduct)
    {
        parent::__construct($segment_text, $parentProduct);
        $this->vtec_strings = $this->parseVTEC($segment_text);
        // Only attempt to parse out storm motion vector and impact-based information for:
        // - tornado warnings
        // - severe thunderstorm warnings
        // - severe weather followup statements
        // - special marine warnings
        // - marine weather statements (questionable)
        if (preg_match('/(TOR|SVR|SVS|MWW|MWS)/', $this->pil)) {
            $this->smv = new SMVString($segment_text);
            $this->impacts = new IBW($segment_text);
        }

        // Extract the polygon from the product and save.
        // Will be null if the polygon does not exist in the product.
        $sbw = new SBW($segment_text);
        $this->polygon = $sbw->polygon;

        //
        // TODO: Dedupe this
        //

        // Generate additional channels from each VTEC segment
        $channels = $this->generateChannels();
        // Append per-segment channels
        $this->appendChannels($channels);
        // Append channels to the parent product
        $parentProduct->appendChannels($channels);
    }

    //
    // Valid Time Extent Code (VTEC) support
    // Lots of useful information in one string about nature of product, start and end times, etc.
    // TODO: Implement H-VTEC for hydrological hazards
    //

    /**
     * Get VTEC strings if they exist. Returns a blank array if there are none found.
     *
     * @return array VTEC strings
     */
    public function getVTEC()
    {
        $strings = array();
        if (!empty($this->vtec_strings)) {
            foreach ($this->vtec_strings as $vtec_string) {
                $strings[] = $vtec_string;
            }
        }

        // Return an array of VTEC strings
        return $strings;
    }

    /**
     * Quick check if this segment has VTEC
     *
     * @return  boolean
     */
    public function hasVTEC()
    {
        return !empty($this->vtec_strings);
    }

    /**
     * Checks if a segment has a VTEC message.
     *
     * @return boolean
     */
    public function parseVTEC($segment_text)
    {
        $data = $segment_text;
        $vtec_strings = array();

        // Fun regex to find VTEC strings
        // TODO: Reconcile where this regex should live. Right now it is duplicated in VTECString.php
        $regex = "/\/([A-Z]{1})\.(NEW|CON|EXP|CAN|EXT|EXA|EXB|UPG|COR|ROU)\.([A-Z]{4})\.([A-Z]{2})\.([A-Z]{1})\.([0-9]{4})\.([0-9]{6})T([0-9]{4})Z-([0-9]{6})T([0-9]{4})Z\//";

        if (preg_match_all($regex, $data, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $key => $match) {
                $vtec_strings[$key] = new VTECString($match);
            }
        }

        return $vtec_strings;
    }

    public function generateChannels()
    {
        $channels = [];

        if (!empty($this->vtec_strings)) {
            foreach ($this->vtec_strings as $vtec_string) {
                // Add channels for phenomena and significance
                $channels[] = $vtec_string->getPhenSig();
                // Add channels for phenomena and signficance by office
                $channels[] = $vtec_string->getPhenSig() . "." . $vtec_string->getOffice();
                // Add phensig channels for zones attached to this warning
                foreach ($this->getZones() as $zone) {
                    $channels[] = $vtec_string->getPhenSig() . "." . $zone;
                }
                // Add phensig and action channels
                $channels[] = $vtec_string->getPhenSig() . '.' . $vtec_string->getAction();
                // Add office, phensig, action
                // Use case: Suppress initial watch issuances from WFOs
                // in favor of faster issuances from SPC
                $channels[] = $vtec_string->getOffice() . '.' . $vtec_string->getPhenSig() . '.' . $vtec_string->getAction();
            }
        }

        return $channels;
    }
}
