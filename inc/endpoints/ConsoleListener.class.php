<?php
/**
 * Console listener.
 * Outputs messages from the bot to the console.
 * By default will subscribe to all channels.
 */

class ConsoleListener extends Listener implements ListenerInterface
{
	function __construct() {
		global $relay;

		$relay->subscribe($this,'ldm','*');
		$relay->subscribe($this,'parser','*');
	}

	public function publish(Event $event) {
		echo "Received {$event->eventName} from {$event->resourceName}: ";

		$product = $event->data;

		print_r($product);
	}
}

$console_endpoint = new ConsoleListener();