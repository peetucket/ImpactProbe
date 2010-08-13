<?php defined('SYSPATH') or die('No direct script access.');
 
return array(
    'app_name' => 'Project Aware',
    'url' => array(
        'images' => 'http://localhost/ProjectAware/images/',
        'js' => 'http://localhost/ProjectAware/js/',
        'css' => 'http://localhost/ProjectAware/css/',
        'show_chart' => 'http://localhost/ProjectAware/show_chart.php',
        'download' => 'http://localhost/ProjectAware/download.php'
    ),
    'path' => array(
        'base' => '/home/adrian/Documents/GSoC_2010/src/ProjectAware',
        'charts' => '/home/adrian/Documents/GSoC_2010/src/ProjectAware/data/charts',
    ),
    'lemur' => array(
        'bin' => '/home/adrian/Documents/GSoC_2010/src/lemur/bin',
        'docs' => '/home/adrian/Documents/GSoC_2010/src/ProjectAware/data/lemur/docs',
        'indexes' => '/home/adrian/Documents/GSoC_2010/src/ProjectAware/data/lemur/indexes',
        'params' => '/home/adrian/Documents/GSoC_2010/src/ProjectAware/data/lemur/params',
        'stopwords_list' => '/home/adrian/Documents/GSoC_2010/src/ProjectAware/data/lemur/stopwords.list'
    )
);

//USAGE: $options = Kohana::config('myconf.site_name');