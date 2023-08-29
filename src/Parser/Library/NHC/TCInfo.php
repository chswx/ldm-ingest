<?php

namespace chswx\LDMIngest\Parser\Library\NHC;

class TCInfo
{
    public function parse($product_text): array
    {
        // Run a series of regular expression matches to extract the data we need
        // Ugly as sin? you bet. Does it work? You bet.
        preg_match("/(.*) Advisory Number\s+(.+)/", $product_text, $advisory_info);
        preg_match("/((AL|EP)\d+)/", $product_text, $storm_id_info);
        preg_match("/SUMMARY OF (.*)\.\.\.(\d+)+ UTC\.\.\.INFORMATION/", $product_text, $time_matches);
        preg_match("/LOCATION...(.*) (.*)/", $product_text, $location_matches);
        preg_match_all("/ABOUT (.*) MI\.\.\.(.*) KM (.*)/", $product_text, $distance_matches, PREG_SET_ORDER);
        preg_match("/MAXIMUM SUSTAINED WINDS\.\.\.(.*) MPH\.\.\.(.*) KM\/H/", $product_text, $max_winds_matches);
        preg_match("/PRESENT MOVEMENT\.\.\.(.*) OR (\d+) DEGREES AT (\d+) MPH...(\d+) KM\/H/", $product_text, $movement_matches);
        preg_match("/MINIMUM CENTRAL PRESSURE\.\.\.(.*) MB\.\.\.(.*) INCHES/", $product_text, $pressure_matches);

        $data['storm_name'] = $advisory_info[1];
        $data['advisory_num'] = $advisory_info[2];
        $data['storm_id'] = $storm_id_info[0];

        $data['time_local'] = $time_matches[1];
        $data['time_utc'] = $time_matches[2];
        $data['location_lat'] = $location_matches[1];
        $data['location_lon'] = $location_matches[2];
        $data['distances'] = [];
        foreach ($distance_matches as $distance) {
            $data['distances'][] = $distance;
        }
        $data['movement_dir'] = $movement_matches[1];
        $data['movement_dir_deg'] = $movement_matches[2];
        $data['movement_mph'] = $movement_matches[3];
        $data['movement_kmh'] = $movement_matches[4];
        $data['pressure_mb'] = $pressure_matches[1];
        $data['pressure_inhg'] = $pressure_matches[2];

        return $data;
    }
}
