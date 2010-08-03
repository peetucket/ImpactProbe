<?php defined('SYSPATH') or die('No direct script access.');
 
return array(
    'site_name' => 'Project Aware',
    'url' => array(
        'images' => 'http://localhost/ProjectAware/images/',
        'js' => 'http://localhost/ProjectAware/js/',
        'css' => 'http://localhost/ProjectAware/css/',
        'show_chart' => 'http://localhost/ProjectAware/show_chart.php'
    ),
    'path' => array(
        'base' => '/home/adrian/Documents/GSoC_2010/src/ProjectAware',
        'kohana' => '/home/adrian/Documents/GSoC_2010/src/ProjectAware/kohana',
        'charts' => '/home/adrian/Documents/GSoC_2010/src/ProjectAware/charts',
    ),
    'lemur' => array(
        'docs' => '/home/adrian/Documents/GSoC_2010/src/ProjectAware/lemur/docs',
        'indexes' => '/home/adrian/Documents/GSoC_2010/src/ProjectAware/lemur/indexes',
        'params' => '/home/adrian/Documents/GSoC_2010/src/ProjectAware/lemur/params',
        'bin' => '/home/adrian/Documents/GSoC_2010/src/lemur/bin'
    )
);

//USAGE: $options = Kohana::config('myconf.site_name');