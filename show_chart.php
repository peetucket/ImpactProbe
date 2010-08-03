<?php 

header('content-type: image/png');

$chart_file = $_GET['datafile'];
$chid = ($_GET['chid']) ? $_GET['chid'] : md5(uniqid(rand(), true));

// TO DO: figure out if chid could be useful...
$api_url = "http://chart.apis.google.com/chart?chid=$chid";

// Open chart file and extract data
$file_handle = fopen($chart_file, "r");
$chart_params = array();
while (!feof($file_handle)) {
    $line = rtrim(fgets($file_handle));
    $param_ex = explode("=", $line);
    $param_name = $param_ex[0]; 
    $param_vals = $param_ex[1];
    // Ensure parameter is chart param ('mpids' & 'mps' are for image map)
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