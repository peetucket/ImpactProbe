<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title><?= $page_title ?> - <?= Kohana::config('myconf.site_name') ?></title>
<meta http-equiv="Content-Type" content="text/html; utf-8">
<meta name="description" content="">
<meta name="keywords" content="">
<link rel="stylesheet" href="<?= Kohana::config('myconf.url.css'); ?>main.css" type="text/css">
<script src="<?= Kohana::config('myconf.url.js'); ?>jquery.min.js" type="text/javascript"></script>
<script src="<?= Kohana::config('myconf.url.js'); ?>jquery.selectboxes.min.js" type="text/javascript"></script>
</head>

<body>

<?= $page_content ?>

</body>
</html>