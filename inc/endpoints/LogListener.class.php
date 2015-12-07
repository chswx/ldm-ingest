<?php
/**
 * Log listener.
 * Routes output to the error log (or stderr if you so choose).
 */

// Plain English definitions for the logging modes, for some reason PHP doesn't provide this
define('LOG_DEFAULT',0);
define('LOG_EMAIL',1);
define('LOG_FILE_APPEND',3);
define('LOG_SAPI',4);

class LogListener extends Listener implements ListenerInterface
{
	function __construct() {
		global $relay;

		if(defined('DEBUG_MODE') && DEBUG_MODE)
		{
			$log_level = "*";
		}
		else
		{
			$log_level = "ERR";
		}
		$relay->subscribe($this,'log',$log_level);
	}

	public function publish(Event $event) {
		$log_format = "[" . date('m-d-Y g:i:s A') . "] " . $event->data . "\n";
		$log_location = LOGFILE_PATH;
		$log_mode = LOG_DEFAULT; 	// defaults to syslog/stderr

		if(file_exists(LOGFILE_PATH) && $event->eventName == "ERR") {
			$log_mode = LOG_FILE_APPEND;
			error_log($log_format,$log_mode,$log_location);
		}
		else {
			error_log($log_format,$log_mode);
		}
		
		return;
	}
}

$log_endpoint = new LogListener();