<?
if(file_exists($_GET['file'])) {
    $filename = ($_GET['name']) ? $_GET['name'] : 'chart.txt';
    $content_type = ($_GET['type']) ? $_GET['type'] : 'text/plain';
    
    header ("Content-Type: $content_type");
    header ("Content-Disposition: attachment; filename=\"$filename\"");
    readfile($_GET['file']);
    
    if($_GET['delete_file'])
        unlink($_GET['file']);
} else {
    echo $_GET['file'].": file does not exist.";
}
?>