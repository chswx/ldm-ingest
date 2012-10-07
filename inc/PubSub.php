<?php
/**
 * Publish/subscribe system based on code from http://dustint.com/post/38/building-a-php-publish-subscribe-system
 */

class Event {
    /**
     * The name of the resource publishing this event
     * @var string
     */
    public $resourceName;
 
    /**
     * The name of this event
     * @var string
     */
    public $eventName;
 
    /**
     * Any data associated with this event
     * @var mixed
     */
    public $data;

    /**
     * ID of the event.
     * Private variable.
     */
    private $event_id;
 
    /**
     * @param string $resourceName  name of the publisher
     * @param string $eventName     name of the event
     * @param mixed $data           [OPTIONAL] Additional event data
     */
    public function __construct($resourceName, $eventName, $data=null)
    {
        $this->resourceName = $resourceName;
        $this->eventName = $eventName;
        $this->data = $data;
        if(isset($this->data->stamp)) {
            $this->event_id = $this->data->stamp;
        }
        else
        {
            $this->event_id = $resourceName . "-" . $eventName . "-" . time();
        }
    }

    /**
     * @return string $event_id Event's unique ID
     */
    public function ID() {
        return $this->event_id;
    }
}

interface ListenerInterface
{
    /**
     * Accepts an event and does something with it
     *
     * @param Event $event  The event to process
     */
    public function publish(Event $event);
}

class Dispatcher {
 
    /**
     * Associative array of listeners.
     * Indicies are: [resourceName][event][listener hash]
     *
     * @var array
     */
    protected $_listeners = array();
 
    /**
     * Subscribes the listener to the resource's events.
     * If $resourceName is *, then the listener will be dispatched when the specified event is fired
     * If $event is *, then the listener will be dispatched for any dispatched event of the specified resource
     * If $resourceName and $event is *, the listener will be dispatched for any dispatched event for any resource
     *
     * @param Listener $listener
     * @param String $resourceName
     * @param Mixed $event
     * @return Dispatcher
     */
    public function subscribe($listener, $resourceName='*', $event='*'){
        $this->_listeners[$resourceName][$event][spl_object_hash($listener)] = $listener;
        return $this;
    }
 
    /**
     * Unsubscribes the listener from the resource's events
     *
     * @param Listener $listener
     * @param String $resourceName
     * @param Mixed $event
     * @return Dispatcher
     */
    public function unsubscribe($listener, $resourceName='*', $event='*'){
        unset($this->_listeners[$resourceName][$event][spl_object_hash($listener)]);
        return $this;
    }
 
    /**
     * Publishes an event to all the listeners listening to the specified event for the specified resource
     *
     * @param Event $event
     * @return Dispatcher
     */
    public function publish(Event $event){
        $resourceName = $event->resourceName;
        $eventName = $event->eventName;
 
        //Loop through all the wildcard handlers
        if(isset($this->_listeners['*']['*'])){
            foreach($this->_listeners['*']['*'] as $listener){
                $listener->publish($event);
            }
        }
 
        //Dispatch wildcard Resources
        //These are events that are published no matter what the resource
        if(isset($this->_listeners['*'])){
            foreach($this->_listeners['*'] as $curr_event => $listeners){
                if($curr_event == $eventName){
                    foreach($listeners as $listener){
                        $listener->publish($event);
                    }
                }
            }
        }
 
        //Dispatch wildcard Events
        //these are listeners that are dispatched for a certain resource, despite the event
        if(isset($this->_listeners[$resourceName]['*'])){
            foreach($this->_listeners[$resourceName]['*'] as $listener){
                //echo "Type of event is: " . get_class($event) . "\n";
                $listener->publish($event);
            }
        }
 
        //Dispatch to a certain resource event
        if(isset($this->_listeners[$resourceName][$eventName])){
            foreach($this->_listeners[$resourceName][$eventName] as $listener){
                $listener->publish($event);
            }
        }
 
        return $this;
    }
}

/**
 * Generic listener
 * Won't be used directly, should be extended
 */
class Listener implements ListenerInterface
{
    var $event_signature;

    // Let the child function take care of this
    public function publish(Event $event) {
        return;
    }

    /**
     * Determine if a duplicate event
     */
    protected function is_duplicate(Event $event) {
        if($this->event_signature === $event->ID()) {
            //Utils::log("Duplicate event {$event->ID()} found");
            return true;
        }
        else {
            //Utils::log("Event {$event->ID()} is OK");
            $this->event_signature = $event->ID();
            return false;
        }
    }
}

class EventFactory
{
    /**
     * Generate events from this product, if applicable.
     * We will publish these events later.
     * 
     * Sends two events by default: 
     * - Generic product event (TOR, SVS, etc.)
     * - Product with issuing office event (TORCHS, SVSCHS, etc.)
     * 
     * Additional events can be added by child product classes.
     * 
     * @static
     * @return array Event
     */
    public static function generate_events($product) {
        foreach($product->segments as $segment) {
            // Generic product
            $events[] = new Event('ldm',substr($product->afos, 0, 3),$segment);
            // Product + issuing office
            $events[] = new Event('ldm',$product->afos, $segment);
            // If VTEC, generate VTEC-related events
            if($segment->get_vtec()) {
                // Multiple strings? Generate events accordingly.
                foreach($segment->get_vtec() as $vtec) {
                    if($vtec->is_operational()) {
                        // Now it gets nuts. Generate a VTEC-based event for each zone!
                        foreach($segment->zones as $zone) {
                            $events[] = new Event('ldm',$vtec->phenomena . "." . $vtec->significance . "." . $zone, $segment);
                        }
                    }
                    // Generate an event for test products
                    else {
                        $events[] = new Event('ldm',$vtec->phenomena . "." . $vtec->significance . ".NON-OP", $segment);
                    }
                }
            }
        }

        return $events;
    }
}