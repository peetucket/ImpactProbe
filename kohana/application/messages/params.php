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
defined('SYSPATH') OR die('No direct access allowed.'); 
 
return array 
( 
    'project_title' => array(
        'not_empty' => 'You must enter a title',
        'max_length' => 'Title must be less than 120 characters',
        'default'  => 'Title is invalid',
    ),
    'keywords_phrases' => array(
        'not_empty' => 'You must enter at least one keyword or phrase',
        'default'  => '1 or more keywords or phrases is invalid',
    ),
     'rss_feeds' => array(
        'not_empty' => 'You must enter at least one RSS feed',
        'default'  => '1 or more RSS feeds is invalid',
    ),
);