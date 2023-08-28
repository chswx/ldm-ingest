<?php

// SUMMARY OF (.*)\.\.\.(\d+)+ UTC\.\.\.INFORMATION
// LOCATION...(.*) (.*)
// ABOUT (.*) MI\.\.\.(.*) KM (.*)
// MAXIMUM SUSTAINED WINDS\.\.\.(.*) MPH\.\.\.(.*) KM\/H
// PRESENT MOVEMENT\.\.\.(.*)
// MINIMUM CENTRAL PRESSURE\.\.\.(.*) MB\.\.\.(.*) INCHES

namespace chswx\LDMIngest\Parser\ProductTypes;

use chswx\LDMIngest\Parser\NWSProduct;

class TCP extends NWSProduct
{
    public function parse(): array
    {
        $this->type = 'tcp';

        $data = [];

        $output = $this->raw_product;

        // Not a segmented product
        // Run a series of regular expression matches to extract the data we need
        preg_match("/(.*) Advisory Number\s+(.+)/", $output, $advisory_info);
        preg_match("/((AL|EP)\d+)/", $output, $storm_id_info);
        preg_match("/SUMMARY OF (.*)\.\.\.(\d+)+ UTC\.\.\.INFORMATION/", $output, $time_matches);
        preg_match("/LOCATION...(.*) (.*)/", $output, $location_matches);
        preg_match_all("/ABOUT (.*) MI\.\.\.(.*) KM (.*)/", $output, $distance_matches, PREG_SET_ORDER);
        preg_match("/MAXIMUM SUSTAINED WINDS\.\.\.(.*) MPH\.\.\.(.*) KM\/H/", $output, $max_winds_matches);
        preg_match("/PRESENT MOVEMENT\.\.\.(.*) OR (\d+) DEGREES AT (\d+) MPH...(\d+) KM\/H/", $output, $movement_matches);
        preg_match("/MINIMUM CENTRAL PRESSURE\.\.\.(.*) MB\.\.\.(.*) INCHES/", $output, $pressure_matches);

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

        var_dump($data);
        die();

        return $data;
    }

    public final function generateChannels(): void
    {
        parent::generateChannels();
    }
}
