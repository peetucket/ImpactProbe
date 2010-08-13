<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title><?= $page_title ?> - <?= Kohana::config('myconf.app_name') ?></title>
<meta http-equiv="Content-Type" content="text/html; utf-8">
<meta name="description" content="">
<meta name="keywords" content="">
<link rel="stylesheet" href="<?= Kohana::config('myconf.url.css'); ?>main.css" type="text/css">
<script src="<?= Kohana::config('myconf.url.js'); ?>jquery.min.js" type="text/javascript"></script>
<script src="<?= Kohana::config('myconf.url.js'); ?>jquery.selectboxes.min.js" type="text/javascript"></script>
<script type="text/javascript" src="<?= Kohana::config('myconf.url.js'); ?>mapper.js"></script>

<script type="text/javascript" language="javascript" src="<?= Kohana::config('myconf.url.js'); ?>lytebox.js"></script>
<link rel="stylesheet" href="<?= Kohana::config('myconf.url.css'); ?>lytebox.css" type="text/css" media="screen" />
<style type="text/css">
#lbClose.grey { background: url(<?= Kohana::config('myconf.url.images'); ?>close_grey.png) no-repeat; }
#lbLoading {
    position: absolute; top: 45%; left: 0%; height: 32px; width: 100%; text-align: center; line-height: 0; background: url(<?= Kohana::config('myconf.url.images'); ?>loading.gif) center no-repeat;
}
</style>
</head>

<body>

<?= $page_content ?>

</body>
</html>