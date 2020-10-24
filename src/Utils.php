<?php

/**
 * Static utility class for basic string manipulation, logging, etc.
 */

namespace chswx\LDMIngest;

class Utils
{
    /**
     * Basic sanitation of incoming products.
     *
     * @param string $raw_text Raw product text, fresh off the LDM
     */
    public static function sanitize($raw_text)
    {
        // Sanitize the file
        $output = trim($raw_text, "\x00..\x1F");

        // Replace newlines
        $output = str_replace("\r\r\n", "\n", $output);

        return $output;
    }

    /**
     * Return the product as an array of lines.
     * Helpful when parsing through...
     *
     * @param string $text Incoming text, preferably already sanitized
     *
     * @return array
     */
    public static function makeArray($text)
    {
        if (strpos("\x00..\x1F", $text) || strpos("\r\r\n", $text)) {
            $text = self::sanitize($text);
        }

        return explode("\n", $text);
    }

    /**
     * Strips newlines and replaces them with spaces.
     *
     * @param string $text Text to replace
     *
     * @return string
     */
    public static function stripNewlines($text)
    {
        return trim(str_replace("\n", " ", $text));
    }

    /**
     * Remove indents from the text. Important for IBW applications.
     *
     * @param string $text Text to deindent
     *
     * @return string
     */
    public static function deindent($text)
    {
        return trim(preg_replace('/\s\s+/', " ", $text));
    }

    public static function convertCoordsToGeojson($coords)
    {
        // Take the format LLLL OOOO
        // Explode into array
        $coords_arr = explode(" ", $coords);

        // Expand lat/long into regular coordinates
        // Easiest way is to coerce these into ints and then divide by 100
        // Note: In GeoJSON, it's lon then lat; not lat lon
        // Another note: Since we're dealing with CONUS only, lon will always be negative
        $coords_prepped = array(((int)$coords_arr[1] / -100), ((int)$coords_arr[0] / 100));

        return $coords_prepped;
    }

    /**
     * Generate a unique identifier for the incoming product.
     *
     * @param string $afos AFOS identifier
     *
     * @return string
     */
    public static function generateStamp($afos, $timestamp)
    {
        return $afos . '-' . $timestamp;
    }

    /**
     * The NWS combines does not repeat the state code for multiple zones...not good for our purpose
     * All we want to do here is convert ranges like INZ021-028 to INZ021-INZ028
     * We will also call the function to expand the ranges here.
     * See: http://www.weather.gov/emwin/winugc.htm
     *
     * @param string $data Incoming data to parse
     *
     * @return array
     */
    public static function parseZones($data)
    {
        $output = str_replace(array("\r\n", "\r"), "\n", $data);
        $lines = explode("\n", $output);
        $new_lines = array();

        foreach ($lines as $i => $line) {
            if (!empty($line)) {
                $new_lines[] = trim($line);
            }
        }
        $data = implode($new_lines);

        /* split up individual states - multiple states may be in the same forecast */
        $regex = '/(([A-Z]{2})(C|Z){1}([0-9]{3})((>|-)[0-9]{3})*)-/';

        $count = preg_match_all($regex, $data, $matches);
        $total_zones = '';

        foreach ($matches[0] as $field => $value) {
            /* since the NWS thought it was efficient to not repeat state codes, we have to reverse that */
            $state = substr($value, 0, 3);
            $zones = substr($value, 3);

            /* convert ranges like 014>016 to 014-015-016 */
            $zones = self::expandRanges($zones);

            /* hack off the last dash */
            $zones = substr($zones, 0, strlen($zones) - 1);

            $zones = $state . str_replace('-', '-' . $state, $zones);

            $total_zones .= $zones;

            // Need one last dash to temporarily buffer between state changes
            $total_zones .= '-';
        }

        /* One last cleanup */
        $total_zones = substr($total_zones, 0, strlen($total_zones) - 1);
        $total_zones = explode('-', $total_zones);

        return $total_zones;
    }

    /**
     * The NWS combines multiple zones into ranges...not good for our purpose
     * All we want to do here is convert ranges like 014>016 to 014-015-016
     * See: http://www.weather.gov/emwin/winugc.htm
     */
    public static function expandRanges($data)
    {
        $regex = '/(([0-9]{3})(>[0-9]{3}))/';

        $count = preg_match_all($regex, $data, $matches);

        foreach ($matches[0] as $field => $value) {
            list($start, $end) = explode('>', $value);

            $new_value = array();
            for ($i = $start; $i <= $end; $i++) {
                $new_value[] = str_pad($i, 3, '0', STR_PAD_LEFT);
            }

            $data = str_replace($value, implode('-', $new_value), $data);
        }

        return $data;
    }

    /**
     * Write a message to the log or console depending on configuration.
     * Wrapper for the built-in error_log PHP function.
     *
     * @param   string  $message    The message to log
     * @param   string  $level      The level to log at, notice by default (currently unused, needs some work)
     */
    public static function log($message, $level = 'notice')
    {
        global $log;

        // For compatibility with older calls
        $level = strtolower($level);
        // Info = notice in PHP land
        if ($level === 'notice') {
            $level = 'info';
        }
        call_user_func([$log, $level], $message);
    }

    /**
     * Writes a debug message to the log or standard output.
     *
     * @param   string  $message    The message to log
     */
    public static function debug($message)
    {
        self::log($message, 'DEBUG');
    }

    /**
     * Exit the application with an error message written to stderr.
     *
     * @param   string  $message    Message to exit with
     * @param   int     $code       (Optional) Error code to send back
     */
    public static function exitWithError($message, $code = 1)
    {
        global $log;
        $log->error($message);
        self::exit($code);
    }

    /**
     * Exit the application.
     */
    public static function exit(int $exit_code)
    {
        global $time_start, $log;

        $execution_time = microtime(true) - $time_start;
        $with_errors = $exit_code != 0 ? " with errors (exit code {$exit_code})" : '';

        $log->info("Script completed in {$execution_time} seconds{$with_errors}.");
        exit($exit_code);
    }
}
