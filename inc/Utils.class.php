<?php
/**
 * Static utility class for basic string manipulation, logging, etc.
 */

class Utils
{
	/**
	 * Basic sanitation of incoming products.
	 * 
	 * @param string $raw_text Raw product text, fresh off the LDM
	 */
	public static function sanitize($raw_text) {
		// Sanitize the file
		$output = trim($raw_text, "\x00..\x1F");

		// Replace newlines
		$output = str_replace("\r\r\n","\n",$output);

		return $output;
	}

	/**
	 * Return the product as an array of lines.
	 * Helpful when parsing through...
	 * 
	 * @param string $text Incoming text, preferably already sanitized
	 */
	public static function make_array($text) {
		if(strpos("\x00..\x1F",$text) || strpos("\r\r\n",$text)) {
			$text = self::sanitize($text);
		}
		
		return explode("\n",$text);
	}

	/**
	 * Write a message to the log or console depending on configuration.
	 * Wrapper for the built-in error_log PHP function.
	 * 
	 * @global Dispatcher $relay Global relay object
	 * @param string $message The message to log
	 * @param string $level The level to log at, notice by default
	 */
	public static function log($message, $level = 'NOTICE') {
		global $relay;
		$log_event = new Event('log',$level,$message);
		$relay->publish($log_event);
	}
}
?>