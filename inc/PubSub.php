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
     * @param string $resourceName  name of the publisher
     * @param string $eventName     name of the event
     * @param mixed $data           [OPTIONAL] Additional event data
     */
    public function __construct($resourceName, $eventName, $data=null)
    {
        $this->resourceName = $resourceName;
        $this->eventName = $eventName;
        $this->data = $data;
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
    public function subscribe(Listener $listener, $resourceName='*', $event='*'){
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
    public function unsubscribe(Listener $listener, $resourceName='*', $event='*'){
    	unset($this->_listeners[$resourceName][$event][spl_object_hash($listener)]);
    	return $this;
    }
 
    /**
     * Publishes an event to all the listeners listening to the specified event for the specified resource
     *
     * @param Event $event
     * @return Dispatcher
     */
    public function publish(Event $event ){
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
	    	foreach($this->_listeners['*'] as $event =>; $listeners){
	    		if($event == $eventName){
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
?>