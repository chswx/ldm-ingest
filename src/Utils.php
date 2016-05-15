<?php
/**
 * Static utility class for basic string manipulation, logging, etc.
 */

namespace UpdraftNetworks;

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

    public static function generate_stamp($afos, $timestamp) {
        return $afos . '-' . $timestamp;
    }

    /**
     * Write a message to the log or console depending on configuration.
     * Wrapper for the built-in error_log PHP function.
     *
     * @param string $message The message to log
     * @param string $level The level to log at, notice by default (currently unused, needs some work)
     */
    public static function log($message, $level = 'NOTICE') {
        error_log($message,0);
    }
}
