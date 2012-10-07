<?php
/**
 * Log listener.
 * Routes output to the error log (or stderr if you so choose).
 */

class LogListener extends Listener implements ListenerInterface
{
	public function publish(Event $event) {
		$log_format = "[" . date('m-d-Y g:i:s A') . "] " . $event->data . "\n";
		$log_location = '/home/ldm/chswx-error.log';
		$log_mode = 0; 	// defaults to syslog/stderr

		//echo $message;

		if(file_exists('/home/ldm/chswx-error.log') && $event->eventName == "ERR") {
			$log_mode = 3;
			error_log($log_format,$log_mode,$log_location);
		}
		else {
			error_log($log_format,$log_mode);
		}
		
		return;
	}
}
?>