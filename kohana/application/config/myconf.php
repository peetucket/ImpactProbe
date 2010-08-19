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
defined('SYSPATH') or die('No direct script access.');
 
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