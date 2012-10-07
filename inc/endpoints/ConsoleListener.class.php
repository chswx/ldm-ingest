<?php
/**
 * Console listener.
 * Outputs messages from the bot to the console.
 * By default will subscribe to all channels.
 */

class ConsoleListener extends Listener implements ListenerInterface
{
	public function publish(Event $event) {
		if($this->is_duplicate($event)) {
			echo "Duplicate {$event->eventName} received via {$event->resourceName}\n";
		}
		else
		{
			echo "{$event->resourceName}: ";
			print_r($event->data);
		}
	}
}
?>