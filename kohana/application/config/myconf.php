<?php defined('SYSPATH') or die('No direct script access.');
 
return array(
    'site_name' => 'Project Aware',
    'url' => array(
        'images' => 'http://localhost/ProjectAware/images/',
        'js' => 'http://localhost/ProjectAware/js/',
        'css' => 'http://localhost/ProjectAware/css/',
    ),
    'path' => array(
        'base' => '/home/adrian/Documents/GSoC_2010/src/ProjectAware', //'/data/www/googlecode',
        'kohana' => '/home/adrian/Documents/GSoC_2010/src/ProjectAware/kohana' //'/data/www/googlecode/kohana'
    )
);

//USAGE: $options = Kohana::config('myconf.site_name');