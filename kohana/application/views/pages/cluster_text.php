<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Clusters - <?= Kohana::config('myconf.site_name') ?></title>
<meta http-equiv="Content-Type" content="text/html; utf-8">
<meta name="description" content="">
<meta name="keywords" content="">

<link rel="stylesheet" href="<?= Kohana::config('myconf.url.css'); ?>main.css" type="text/css">
<style type="text/css">
#cell_container {
    position:relative; float:left;
    width:512px;
    padding-bottom:8px;
}
#cell_container div {
    position:relative; float:left;
    width:500px;
    padding:6px;
}
</style>
</head>

<div>

<div id="cell_container">
<? $num_clusters = count($cluster_data); 
if($num_clusters > 0) { 
    if(!$singleton_display) { ?>
        <form name="cluster_text" id="cluster_text" method="post" action="">
        <? if($errors) { 
            echo '<p class="errors">'; 
            foreach ($errors as $error_text) { echo $error_text."<br>"; }
            echo '</p>';
        } ?>
        <b>Show: </b>
        <select name="num_results">
        <option value="10" <? if($field_data['num_results'] == 10) { echo("selected"); } ?>>10</option>
        <option value="25" <? if($field_data['num_results'] == 25) { echo("selected"); } ?>>25</option>
        <option value="50" <? if($field_data['num_results'] == 50) { echo("selected"); } ?>>50</option>
        <option value="100" <? if($field_data['num_results'] == 100) { echo("selected"); } ?>>100</option>
        <option value="all" <? if($field_data['num_results'] == 'all') { echo("selected"); } ?>>all</option>
        </select>
        <select name="score_order">
        <option value="desc" <? if($field_data['score_order'] == 'desc') { echo("selected"); } ?>>most representative</option>
        <option value="asc" <? if($field_data['score_order'] == 'asc') { echo("selected"); } ?>>least representative</option>
        </select>
        <input type="submit" name="Submit" value="View">
        </form><? 
    } 
    
    $i = 1;
    foreach($cluster_data as $cluster) { ?>
        <div class="cell_unit <?= ($i % 2 == 0) ? 'bg_white' : 'bg_grey' ?>"><?= $cluster['text'] ?></div><? 
        $i++;
    }

} else {
    echo 'No clusters to display.';
} ?>
</div>
</body>
</html>