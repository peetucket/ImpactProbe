<?php defined('SYSPATH') OR die('No direct access allowed.'); 
 
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
);