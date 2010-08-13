<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Document - <?= Kohana::config('myconf.site_name') ?></title>
<meta http-equiv="Content-Type" content="text/html; utf-8">

<link rel="stylesheet" href="<?= Kohana::config('myconf.url.css'); ?>main.css" type="text/css">
<style type="text/css">
#cell_container {
    padding:6px;
}
</style>
</head>

<div>

<div id="cell_container">
<?= $text ?>
</div>
</body>
</html>