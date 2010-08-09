<?
if(file_exists($_GET['file_path'])) {
    header ("Content-Type: text/csv");
    header ("Content-Disposition: attachment; filename=\"".$_GET['file_name']."\"");
    readfile($_GET['file']);
    //exit;
} else {
    echo $_GET['file_path'].": file does not exist.";
}
?>