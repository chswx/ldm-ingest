<?php

namespace UpdraftNetworks\Parser;

class AlertMessage
{
    public $message;
    public $targets;

    public function __construct($message, $targets)
    {
        $this->message = $message;
        $this->targets = $targets;
    }
}
