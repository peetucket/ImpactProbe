<?php 

header('content-type: image/png');

// TO DO: figure out if chid could be useful...
$api_url = 'http://chart.apis.google.com/chart?chid=' . md5(uniqid(rand(), true));

// Open chart file and extract data
$chart_file = $_GET['datafile'];
$file_handle = fopen($chart_file, "r");
$chart_params = array();
while (!feof($file_handle)) {
    $line = rtrim(fgets($file_handle));
    $line_ex = explode("=", $line);
    $chart_params[$line_ex[0]] = $line_ex[1];
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