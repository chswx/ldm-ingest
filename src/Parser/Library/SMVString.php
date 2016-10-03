<?php
/**
 * Storm Motion Vector support
 * Grabs the TIME...MOT...LOC info from a warning and extracts it to be useful
 */

namespace UpdraftNetworks\Parser\Library;

use UpdraftNetworks\Utils as Utils;
use UpdraftNetworks\Parser\Library\Geo\Point as Point;

class SMVString {
    /**
     *  Stores the time of the storm motion vector fix.
     *  Should only be one per segment.
     *  The TIME in TIME...MOT...LOC
     *
     * @var int
     */
    var $time;

    /**
     * Stores the actual vector itself.
     * What's your vector Victor?
     * The MOT in TIME...MOT...LOC
     *
     * @var array
     */
    var $motion;

    /**
     * Stores the coordinates of the storm fix point as set in WarnGen by the warning forecaster.
     * The LOC in TIME...MOT...LOC
     *
     * @var mixed
     */
    var $location;

    /**
     * Constructor. Takes in segment text and spits out the Storm Motion Vector string for that segment.
     *
     * @param string $segment_text Text of NWSProductSegment
     *
     * @return string Storm Motion Vector or null
     */
    function __construct($segment_text) {
        $smv = $this->_extract_storm_motion_vector($segment_text);
        $this->time = $smv['time'];
        $this->motion = $smv['mot'];
        $this->location = $smv['loc'];
    }

    /**
     * Extract the storm motion vector data from the SMV string and parse to an array.
     *
     * @access protected
     *
     * @param string $segment_text Segment text.
     *
     * @return array Array with storm motion vector data inside.
     */
    protected function _extract_storm_motion_vector($segment_text) {
        preg_match('/TIME\.\.\.MOT\.\.\.LOC\ (\d*)Z\ (\d*)DEG\ (\d*)KT\ (.... ....)/', $segment_text, $matches);

        return array(
            'time' => (int)$matches[1],
            'mot' => array(
                'vector' => (int)$matches[2],
                'speed' => (int)$matches[3]
            ),
            'loc' => (new Point($matches[4]))->to_array()
        );
    }


}
