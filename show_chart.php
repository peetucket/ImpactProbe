<?php
/*******************************************************************

Copyright 2010, Adrian Laurenzi

This file is part of ImpactProbe.

ImpactProbe is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
at your option) any later version.

ImpactProbe is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with ImpactProbe. If not, see <http://www.gnu.org/licenses/>.

*******************************************************************/

header('content-type: image/png');

$chart_file = $_GET['datafile'];
$chid = ($_GET['chid']) ? $_GET['chid'] : md5(uniqid(rand(), true));

$api_url = "http://chart.apis.google.com/chart?chid=$chid";

// Open chart file and extract data
$file_handle = fopen($chart_file, "r");
$chart_params = array();
while (!feof($file_handle)) {
    $line = rtrim(fgets($file_handle));
    $param_ex = explode("=", $line);
    $param_name = $param_ex[0]; 
    $param_vals = $param_ex[1];
    // Ensure parameter is chart param ('mpids' & 'mps' are for image map; mpd is for trendline dates)
    if(substr($param_name, 0, 2) != "mp")
        $chart_params[$param_name] = $param_vals;
}
fclose($file_handle);

// Send the request, and print out the returned bytes.
$context = stream_context_create(
    array('http' => array(
        'method' => 'POST',
        'content' => http_build_query($chart_params)
    )));
fpassthru(fopen($api_url, 'r', false, $context));

?>