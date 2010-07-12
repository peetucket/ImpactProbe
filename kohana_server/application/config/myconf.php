<?php defined('SYSPATH') or die('No direct script access.');
 
return array(
    'site_name' => 'Project Aware',
    'url' => array(
        'images' => 'http://vuvuzela.eol.org/images/',
        'js' => 'http://vuvuzela.eol.org/js/',
        'css' => 'http://vuvuzela.eol.org/css/',
    ),
    'path' => array(
        'base' => '/data/www/googlecode', 
        'kohana' => '/data/www/googlecode/kohana'
    )
);

//USAGE: $options = Kohana::config('myconf.site_name');
