<?php
/**
 * Console listener.
 * Outputs messages from the bot to the console.
 * By default will subscribe to all channels.
 */

class ConsoleListener extends Listener implements ListenerInterface
{
	public function publish(Event $event) {
		echo "Received {$event->eventName} from {$event->resourceName}: ";

		$product = $event->data;

		print_r($product);
	}
}