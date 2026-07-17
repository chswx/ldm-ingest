<?php
/**
 * MesoDisc parser for SWOMCD (SPC Mesoscale Discussion) products.
 * 
 * MCDs are non-segmented products with polygon coordinates.
 * Per NWSI 10-517, covers convective severe potential, watch updates,
 * and winter weather types.
 */

namespace chswx\LDMIngest\Parser;

use chswx\LDMIngest\Utils as Utils;

class MesoDisc extends NWSProduct {
    
    /**
     * MCD number (resets annually on Jan 1 at 0000Z).
     * @var int
     */
    var $mcd_number;
    
    /**
     * MCD type: SEVERE_POTENTIAL, WATCH_UPDATE, WINTER_WEATHER.
     * @var string
     */
    var $mcd_type;
    
    /**
     * Probability of watch issuance (percentage, 5-95). Only for SEVERE_POTENTIAL.
     * @var int|null
     */
    var $probability_of_watch_issuance;
    
    /**
     * Active watch numbers being updated. Only for WATCH_UPDATE type.
     * @var array|null
     */
    var $active_watches;
    
    /**
     * Areas affected text.
     * @var string
     */
    var $areas_affected;
    
    /**
     * Summary section text.
     * @var string
     */
    var $summary;
    
    /**
     * Full discussion text (may be multi-paragraph).
     * @var string
     */
    var $discussion;
    
    /**
     * Valid time window start/end timestamps.
     * @var array|null
     */
    var $valid_window;
    
    /**
     * Forecaster names from signature line. Supports 1-3 forecasters.
     * @var array|null
     */
    var $forecasters;
    
    /**
     * Affected WFO list from ATTN line.
     * @var array|null
     */
    var $attn_wfo;
    
    /**
     * Polygon coordinates from LAT/LON line.
     * @var array|null
     */
    var $polygon;
    
    /**
     * Peak tornado intensity estimate (2026 enhancement).
     * @var string|null
     */
    var $peak_tornado_intensity;
    
    /**
     * Peak wind gust estimate (2026 enhancement).
     * @var string|null
     */
    var $peak_wind_gust;
    
    /**
     * Peak hail size estimate (2026 enhancement).
     * @var string|null
     */
    var $peak_hail_size;

    function __construct($prod_info, $product_text) {
        parent::__construct($prod_info, $product_text);
    }

    function parse() {
        $lines = Utils::make_array($this->raw_product);
        
        // Remove leading/trailing empty lines and find content
        $lines = array_values(array_filter($lines, function($line) {
            return trim($line) !== '';
        }));
        
        $text = implode("\n", $lines);
        
        // Parse all fields from the product text
        $this->mcd_number = $this->parse_mcd_number($lines);
        $this->areas_affected = $this->parse_areas_affected($lines);
        $this->mcd_type = $this->parse_mcd_type($lines);
        $this->active_watches = $this->parse_active_watches($lines);
        $this->valid_window = $this->parse_valid_window($lines);
        $this->probability_of_watch_issuance = $this->parse_probability($lines);
        $this->summary = $this->parse_summary($lines);
        $this->discussion = $this->parse_discussion($lines);
        $this->forecasters = $this->parse_forecasters($lines);
        $this->attn_wfo = $this->parse_attn_wfo($lines);
        $this->polygon = $this->parse_polygon($lines);
        
        // 2026 enhancements: peak threat estimates from discussion text
        $this->peak_tornado_intensity = $this->parse_peak_tornado($text);
        $this->peak_wind_gust = $this->parse_peak_wind($text);
        $this->peak_hail_size = $this->parse_peak_hail($text);
        
        // Build a single segment with all parsed data (MCDs are non-segmented)
        $segment = [
            'text' => $this->raw_product,
            'afos' => $this->afos,
            'office' => $this->office,
            'mcd_number' => $this->mcd_number,
            'mcd_type' => $this->mcd_type,
            'areas_affected' => $this->areas_affected,
            'valid_window' => $this->valid_window,
            'probability_of_watch_issuance' => $this->probability_of_watch_issuance,
            'active_watches' => $this->active_watches,
            'summary' => $this->summary,
            'discussion' => $this->discussion,
            'forecasters' => $this->forecasters,
            'attn_wfo' => $this->attn_wfo,
            'polygon' => $this->polygon,
            'peak_tornado_intensity' => $this->peak_tornado_intensity,
            'peak_wind_gust' => $this->peak_wind_gust,
            'peak_hail_size' => $this->peak_hail_size,
        ];
        
        return [$segment];
    }

    /**
     * Parse MCD number from product text.
     * Format: "MESOSCALE DISCUSSION 0186"
     * NOTE: "SPC MCD NNNNNN" is the CMC header date-time group, not the MCD number.
     */
    protected function parse_mcd_number($lines) {
        foreach ($lines as $line) {
            // Match "MESOSCALE DISCUSSION NNNN" - canonical MND header format
            if (preg_match('/MESOSCALE\s+DISCUSSION\s+(\d{3,4})/i', $line, $matches)) {
                return (int)$matches[1];
            }
        }
        return 0;
    }

    /**
     * Parse AREAS AFFECTED section.
     */
    protected function parse_areas_affected($lines) {
        $in_section = false;
        $result = '';
        
        foreach ($lines as $line) {
            if (preg_match('/^AREAS\s+AFFECTED\.\.\./i', $line)) {
                $in_section = true;
                // Get text after the "..." on same line
                if (preg_match('/^AREAS\s+AFFECTED\.\.\.(.+)$/i', $line, $matches)) {
                    $result = trim($matches[1]);
                }
                continue;
            }
            
            if ($in_section) {
                // Empty line or next section ends this block
                if (trim($line) === '') {
                    break;
                }
                $result .= ' ' . trim($line);
            }
        }
        
        return trim($result);
    }

    /**
     * Parse CONCERNING line to determine MCD type.
     */
    protected function parse_mcd_type($lines) {
        foreach ($lines as $line) {
            if (preg_match('/^CONCERNING\.\.\./i', $line)) {
                // WATCH_UPDATE types
                if (preg_match('/(?:TORNADO\s+WATCH|SEVERE\s+THUNDERSTORM\s+WATCH)\s+\d+/i', $line)) {
                    return 'WATCH_UPDATE';
                }
                
                // WINTER_WEATHER types
                if (preg_match('/(?:HEAVY\s+SNOW|BLIZZARD|FREEZING\s+RAIN|SNOW\s+SQUALL)/i', $line)) {
                    return 'WINTER_WEATHER';
                }
                
                // SEVERE_POTENTIAL types (default for "Severe potential...")
                if (preg_match('/SEVERE\s+POTENTIAL/i', $line)) {
                    return 'SEVERE_POTENTIAL';
                }
            }
        }
        
        return 'SEVERE_POTENTIAL'; // Default type
    }

    /**
     * Parse active watch numbers from WATCH_UPDATE MCDs.
     */
    protected function parse_active_watches($lines) {
        $watches = [];
        
        foreach ($lines as $line) {
            if (preg_match('/(?:TORNADO\s+WATCH|SEVERE\s+THUNDERSTORM\s+WATCH)\s+(\d+)/i', $line, $matches)) {
                $watches[] = (int)$matches[1];
            }
        }
        
        return !empty($watches) ? array_unique($watches) : null;
    }

    /**
     * Parse VALID time window (DDHHMMZ - DDHHMMZ).
     */
    protected function parse_valid_window($lines) {
        $reference_time = $this->parse_product_date($lines) ?? time();

        foreach ($lines as $line) {
            if (preg_match('/^VALID\s+(\d{6})Z\s*-\s*(\d{6})Z/i', $line, $matches)) {
                $start = $this->parse_valid_time($matches[1], $reference_time);
                $end = $this->parse_valid_time($matches[2], $start, $start);

                return [
                    'start' => $start,
                    'end' => $end
                ];
            }
        }
        
        return null;
    }

    /**
     * Parse the calendar date from the MND issuance line.
     * Example: "1046 PM CDT WED JUN 25 2026".
     */
    protected function parse_product_date($lines) {
        $months = [
            'JAN' => 1, 'FEB' => 2, 'MAR' => 3, 'APR' => 4,
            'MAY' => 5, 'JUN' => 6, 'JUL' => 7, 'AUG' => 8,
            'SEP' => 9, 'OCT' => 10, 'NOV' => 11, 'DEC' => 12,
        ];

        foreach ($lines as $line) {
            if (preg_match('/\b(JAN|FEB|MAR|APR|MAY|JUN|JUL|AUG|SEP|OCT|NOV|DEC)\s+(\d{1,2})\s+(\d{4})\b/i', $line, $matches)) {
                return gmmktime(12, 0, 0, $months[strtoupper($matches[1])], (int)$matches[2], (int)$matches[3]);
            }
        }

        return null;
    }

    /**
     * Convert DDHHMMZ to Unix timestamp using the nearest valid calendar date.
     *
     * DDHHMM does not include a month or year. Resolve it against the product
     * date, considering the previous, current, and next month. For an end time,
     * $not_before prevents a month-boundary rollover from preceding its start.
     */
    protected function parse_valid_time($vtime, $reference_time = null, $not_before = null) {
        $day = (int)substr($vtime, 0, 2);
        $hour = (int)substr($vtime, 2, 2);
        $minute = (int)substr($vtime, 4, 2);
        $reference_time = $reference_time ?? time();

        $reference_month = new \DateTimeImmutable(
            gmdate('Y-m-01 00:00:00', $reference_time),
            new \DateTimeZone('UTC')
        );
        $candidates = [];

        foreach ([-1, 0, 1] as $month_offset) {
            $month = $reference_month->modify(sprintf('%+d month', $month_offset));
            $year = (int)$month->format('Y');
            $month_number = (int)$month->format('m');

            if (!checkdate($month_number, $day, $year)) {
                continue;
            }

            $timestamp = gmmktime($hour, $minute, 0, $month_number, $day, $year);
            if ($not_before === null || $timestamp >= $not_before) {
                $candidates[] = $timestamp;
            }
        }

        if (empty($candidates)) {
            return null;
        }

        usort($candidates, function ($a, $b) use ($reference_time) {
            return abs($a - $reference_time) <=> abs($b - $reference_time);
        });

        return $candidates[0];
    }

    /**
     * Parse PROBABILITY OF WATCH ISSUANCE percentage.
     */
    protected function parse_probability($lines) {
        foreach ($lines as $line) {
            if (preg_match('/PROBABILITY\s+OF\s+WATCH\s+ISSUANCE\.\.\.(\d+)\s*(?:PERCENT)?/i', $line, $matches)) {
                return (int)$matches[1];
            }
        }
        
        return null;
    }

    /**
     * Parse SUMMARY section text.
     */
    protected function parse_summary($lines) {
        $in_section = false;
        $result = '';
        
        foreach ($lines as $line) {
            if (preg_match('/^SUMMARY\.\.\./i', $line)) {
                $in_section = true;
                // Get text after "..." on same line
                if (preg_match('/^SUMMARY\.\.\.(.+)$/i', $line, $matches)) {
                    $result = trim($matches[1]);
                }
                continue;
            }
            
            if ($in_section) {
                // Empty line or next section ends this block
                if (trim($line) === '') {
                    break;
                }
                $result .= ' ' . trim($line);
            }
        }
        
        return trim($result);
    }

    /**
     * Parse DISCUSSION section (multi-paragraph).
     */
    protected function parse_discussion($lines) {
        $in_section = false;
        $result = '';
        
        foreach ($lines as $line) {
            if (preg_match('/^DISCUSSION\.\.\./i', $line)) {
                $in_section = true;
                if (preg_match('/^DISCUSSION\.\.\.(.+)$/i', $line, $matches)) {
                    $result = trim($matches[1]);
                }
                continue;
            }
            
            if ($in_section) {
                // Empty line ends the discussion section
                if (trim($line) === '') {
                    break;
                }
                $result .= ' ' . trim($line);
            }
        }
        
        return trim($result);
    }

    /**
     * Parse forecaster signature line.
     * Format: "..NAME1/NAME2/NAME3.. MM/DD/YYYY" or "..NAME1/NAME2.. MM/DD/YYYY"
     * Supports 1-3 forecasters.
     */
    protected function parse_forecasters($lines) {
        foreach ($lines as $line) {
            if (preg_match('/\.\.([A-Z][A-Za-z\/]+)\.\.\s*(\d{2}\/\d{2}\/\d{4})/', $line, $matches)) {
                $names = explode('/', $matches[1]);
                // Filter out empty names and clean up
                $names = array_values(array_filter($names, function($n) {
                    return trim($n) !== '';
                }));
                
                if (!empty($names)) {
                    return $names;
                }
            }
        }
        
        return null;
    }

    /**
     * Parse ATTN WFO list.
     * Format: "...ATTN...WFO...MEG...LZK..." or "...ATTN...WFO...MEG, LZK..."
     */
    protected function parse_attn_wfo($lines) {
        foreach ($lines as $line) {
            if (preg_match('/ATTN\.\.\.WFO\.\.\.(.+)$/i', $line, $matches)) {
                $wfo_str = trim($matches[1]);
                
                // Split by comma or ellipsis (...)
                $wfos = preg_split('/[,\.\.]+/', $wfo_str);
                $wfos = array_values(array_filter(array_map('trim', $wfos), function($w) {
                    return preg_match('/^[A-Z]{3}$/', $w);
                }));
                
                return !empty($wfos) ? $wfos : null;
            }
        }
        
        return null;
    }

    /**
     * Parse LAT/LON polygon coordinates.
     * Format: "LAT...LON   29999435 30079572 ..."
     * Coordinates are in tenths of degrees (e.g., 29999435 = 29.999435, -94.350792)
     */
    protected function parse_polygon($lines) {
        foreach ($lines as $line) {
            if (preg_match('/^LAT\.\.\.LON\s+(.+)$/i', $line, $matches)) {
                return $this->parse_latlon_coords($matches[1]);
            }
        }
        
        return null;
    }

    /**
     * Parse LAT/LON coordinate string into polygon points.
     *
     * Per NWSI 10-1701 Table 11 (National Center format used by SPC):
     * - Each coordinate number = lat_digits + lon_digits concatenated in a single integer
     * - Latitude: first 4 digits, decimal after position 2 (e.g., `2999` → 29.99)
     * - Longitude: remaining digits, decimal after position 2 (e.g., `9435` → -94.35)
     * - Longitudes > 100 degrees drop the leading "1" (e.g., 102.54W → `0254`)
     * - CONUS = always West (negative longitude)
     *
     * Example: `29999435` → lat=29.99, lon=-94.35 (near Houston, TX)
     * Example: `30759602` → lat=30.75, lon=-96.02 (near Waco, TX)
     *
     * Coordinates may span multiple lines with leading whitespace continuation.
     */
    protected function parse_latlon_coords($coord_str) {
        // Collect all coordinate tokens from this line and continuation lines
        $tokens = preg_split('/\s+/', trim($coord_str));

        // Find the LAT/LON line and any continuation lines in the original product
        $lines = Utils::make_array($this->raw_product);
        foreach ($lines as $line) {
            if (preg_match('/^\s{8,}(\d+\s+\S+)/', $line)) {
                // Continuation line (8 spaces indent)
                $cont_tokens = preg_split('/\s+/', trim($line));
                $tokens = array_merge($tokens, $cont_tokens);
            }
        }

        // Filter to only numeric tokens
        $numbers = array_values(array_filter($tokens, function ($t) {
            return is_numeric($t);
        }));

        // Each number encodes one lat/lon pair, so we need an even count
        if (count($numbers) < 2 || count($numbers) % 2 !== 0) {
            return null;
        }

        // Convert each combined number to (lat, lon) in decimal degrees.
        // Per NWSI 10-1701 Table 11: first 4 digits = lat, remaining = lon
        $points = [];
        foreach ($numbers as $num) {
            $str = (string) $num;

            // First 4 characters are latitude digits
            $lat_str = substr($str, 0, 4);
            // Remaining characters are longitude digits
            $lon_str = substr($str, 4);

            if (strlen($lat_str) < 4 || strlen($lon_str) < 2) {
                continue;
            }

            $lat = (float) ($lat_str / 100.0);
            // CONUS longitudes are always West (negative)
            $lon = -(float) ($lon_str / 100.0);

            $points[] = [
                'lat' => round($lat, 2),
                'lon' => round($lon, 2),
            ];
        }

        if (empty($points)) {
            return null;
        }

        // Build polygon in GeoJSON-like format
        return [
            'type' => 'Polygon',
            'coordinates' => [$points],
        ];
    }

    /**
     * Parse peak tornado intensity from discussion text (2026 enhancement).
     * Format: "UP TO 90 MPH" or similar
     */
    protected function parse_peak_tornado($text) {
        if (preg_match('/UP\s+TO\s+(\d+)\s*MPH/i', $text, $matches)) {
            return "UP TO {$matches[1]} MPH";
        }
        
        if (preg_match('/(\d+)\s*MPH\s+(?:TORNADO|WIND)/i', $text, $matches)) {
            return "{$matches[1]} MPH";
        }
        
        return null;
    }

    /**
     * Parse peak wind gust from discussion text (2026 enhancement).
     * Format: "55-70 MPH" or similar
     */
    protected function parse_peak_wind($text) {
        if (preg_match('/(\d+)\s*-\s*(\d+)\s*MPH/i', $text, $matches)) {
            return "{$matches[1]}-{$matches[2]} MPH";
        }
        
        if (preg_match('/(?:GUST|WIND)\s+(?:UP\s+TO\s+)?(\d+)\s*MPH/i', $text, $matches)) {
            return "{$matches[1]} MPH";
        }
        
        return null;
    }

    /**
     * Parse peak hail size from discussion text (2026 enhancement).
     * Format: "1.00-1.75 IN" or similar (inches)
     */
    protected function parse_peak_hail($text) {
        if (preg_match('/(\d+\.?\d*)\s*-\s*(\d+\.?\d*)\s*IN/i', $text, $matches)) {
            return "{$matches[1]}-{$matches[2]} IN";
        }
        
        if (preg_match('/(?:HAIL)\s+(?:UP\s+TO\s+)?(\d+\.?\d*)\s*IN/i', $text, $matches)) {
            return "{$matches[1]} IN";
        }
        
        // Also check for baseball-sized, etc. descriptions
        if (preg_match('/(\d+\.?\d*"\s*(?:INCH|"))/i', $text, $matches)) {
            return trim($matches[1]);
        }
        
        return null;
    }
}
