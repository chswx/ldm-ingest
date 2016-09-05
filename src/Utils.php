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
     * @return array
     */
    public static function make_array($text) {
        if(strpos("\x00..\x1F",$text) || strpos("\r\r\n",$text)) {
            $text = self::sanitize($text);
        }

        return explode("\n",$text);
    }

    /**
     * Strips newlines and replaces them with spaces.
     * @param string $text Text to replace
     * @return string
     */
    public static function strip_newlines($text) {
        return trim(str_replace("\n"," ",$text));
    }

    public static function deindent($text) {
        return trim(preg_replace('/\s\s+/'," ",$text));
    }

    public static function convert_coords_to_geojson($coords) {
        // Take the format LLLL OOOO
        // Explode into array
        $coords_arr = explode(" ", $coords);
        
        // Expand lat/long into regular coordinates
        // Easiest way is to coerce these into ints and then divide by 100
        // Note: In GeoJSON, it's lon then lat; not lat lon
        // Another note: Since we're dealing with CONUS only, lon will always be negative
        $coords_prepped = array(((int)$coords_arr[1] / -100),((int)$coords_arr[0] / 100));

        return $coords_prepped;
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

    public static function exit_with_error($message, $code = 1) {
        fwrite(STDERR, $message);
        exit($code);
    }
}
