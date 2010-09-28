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
?>
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
#header {
    background:url(<?= Kohana::config('myconf.url.images'); ?>header_bg.jpg);
    height:124px;
    width:100%;
}
</style>
</head>

<div>

<div id="header">
<img src="<?= Kohana::config('myconf.url.images'); ?>impact_probe_logo.jpg" width="322" height="124" alt="Impact Probe">
</div>

<div id="container">
<?= $page_content ?>
</div>

</body>
</html>