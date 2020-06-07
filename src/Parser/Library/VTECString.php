<?php
namespace UpdraftNetworks\Parser\Library;

/**
 * Class to assist with VTEC string operations.
 * VTECString object properties are externally accessible
 */
class VTECString
{
    /**
     * Raw VTEC string.
     *
     * @var string
     */
    public $vtec_string;

    /**
     * @var string Product class
     */
    public $product_class;

    /**
     * @var string Action
     */
    public $action;

    /**
     * @var string Issuing office ID
     */
    public $office;

    /**
     * @var string Phenomena
     */
    public $phenomena;

    /**
     * @var string Significance
     */
    public $significance;

    /**
     * @var string Event Tracking Number
     */
    public $etn;

    /**
     * @var int Event effective time as a UNIX timestamp
     */
    public $effective_timestamp;

    /**
     * @var int Event expiration time as a UNIX timestamp
     */
    public $expire_timestamp;

    /**
     * Constructor.
     * Take product text and parse out VTEC string(s).
     *
     * @param array|string $vtec Product text.
     */
    public function __construct($vtec)
    {
        if (is_array($vtec)) {
            $this->_create_obj($vtec);
        } else {
            $this->_parse($vtec);
        }
    }

    /**
     * Checks if an operational VTEC string.
     *
     * @return boolean
     */
    public function isOperational()
    {
        return $this->product_class === 'O';
    }

    /**
     * Checks if a test VTEC product.
     *
     * @return boolean
     */
    public function isTest()
    {
        return $this->product_class === 'T';
    }

    public function isUpgrade()
    {
        return $this->product_class === 'U';
    }

    /**
     * Returns the action type from the VTEC dictionary.
     *
     * @return string action
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Returns the Event Tracking Number.
     *
     * @return int ETN
     */
    public function getETN()
    {
        return $this->etn;
    }

    ///
    /// Private functions /////////////////////////////////////////////////
    ///

    /**
     * Parse out the VTEC string into its properties
     */
    private function _create_obj($vtec_string_array)
    {
        // Save the VTEC string in its entirety
        $this->vtec_string = $vtec_string_array[0];

        // VTEC product class
        $this->product_class = $vtec_string_array[1];

        // VTEC action
        $this->action = $vtec_string_array[2];

        // VTEC issuing WFO
        $this->office = $vtec_string_array[3];

        // VTEC phenomena
        $this->phenomena = $vtec_string_array[4];

        // VTEC significance
        $this->significance = $vtec_string_array[5];

        // VTEC event number
        $this->etn = $vtec_string_array[6];

        // Effective time (as UNIX timestamp)
        $this->effective_timestamp = $this->vtecToTimestamp($vtec_string_array[7], $vtec_string_array[8]);

        // Expire time (as UNIX timestamp)
        $this->expire_timestamp = $this->vtecToTimestamp($vtec_string_array[9], $vtec_string_array[10]);
    }

    private function _parse($vtec_string)
    {
        $regex = "/\/([A-Z]{1})\.(NEW|CON|EXP|CAN|EXT|EXA|EXB|UPG|COR|ROU)\.([A-Z]{4})\.([A-Z]{2})\.([A-Z]{1})\.([0-9]{4})\.([0-9]{6})T([0-9]{4})Z-([0-9]{6})T([0-9]{4})Z\//";

        if (preg_match($regex, $vtec_string, $matches)) {
            $this->_create_obj($matches);
        }
    }

    /**
     * Converts a VTEC timestamp to a UNIX timestamp (normalized to Z time)
     *
     * @return int UNIX timestamp
     */
    private function vtecToTimestamp($vtec_date, $vtec_time)
    {
        // Don't bother with blank dates
        if ($vtec_date == "OOOOOO") {
            $stamp = 0;
        } else {
            // Break out the VTEC datestamp into chunks to reassemble shortly
            $year = substr($vtec_date, 0, 2);
            $month = substr($vtec_date, 2, 2);
            $day = substr($vtec_date, 4, 2);

            // Y2.1K problem (read: not mine unless I live to be 130)
            $stamp = strtotime('20' . $year . '-' . $month . '-' . $day . ' ' . $vtec_time . 'Z');
        }

        return $stamp;
    }
}
