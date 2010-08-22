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
    'app_name' => 'Impact Probe',
    'url' => array(
        'images' => 'http://localhost/ImpactProbe/images/',
        'js' => 'http://localhost/ImpactProbe/js/',
        'css' => 'http://localhost/ImpactProbe/css/',
        'show_chart' => 'http://localhost/ImpactProbe/show_chart.php',
        'download' => 'http://localhost/ImpactProbe/download.php'
    ),
    'path' => array(
        'base' => '~/ImpactProbe',
        'charts' => '~/ImpactProbe/data/charts',
    ),
    'lemur' => array(
        'bin' => '~/lemur/bin',
        'docs' => '~/ImpactProbe/data/lemur/docs',
        'indexes' => '~/ImpactProbe/data/lemur/indexes',
        'params' => '~/ImpactProbe/data/lemur/params',
        'stopwords_list' => '~/ImpactProbe/data/lemur/stopwords.list' 
    )
);