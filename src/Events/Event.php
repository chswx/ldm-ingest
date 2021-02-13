<?php

namespace chswx\LDMIngest\Event;

class Event
{
    /**
     * @var $ID;
     *
     * Unique identifier for the event.
     * Takes the format <office>.<phen>.<sig>.<etn>.<year>
     */
    public $ID;
    public $message;
    public $geo;
    public $startTime;
    public $endTime;
}
