<?php defined('SYSPATH') or die('No direct script access.'); ?>

2010-07-02 14:51:37 --- ERROR: ErrorException [ 8 ]: Undefined variable: project_id ~ APPPATH/classes/controller/gather.php [ 140 ]
2010-07-02 14:54:53 --- ERROR: Database_Exception [ 1054 ]: Unknown column &#039;project_id&#039; in &#039;where clause&#039; [ SELECT COUNT(url) AS `total` FROM `metadata_urls` WHERE (`project_id` = 3 AND `url` = &#039;http://twitter.com/JoseGregorio/status/17591305853&#039;) ] ~ MODPATH/database/classes/kohana/database/mysql.php [ 178 ]
2010-07-02 15:15:15 --- ERROR: ErrorException [ 1 ]: Call to undefined method DateTime::createFromFormat() ~ APPPATH/classes/controller/gather.php [ 109 ]
2010-07-02 15:15:51 --- ERROR: ErrorException [ 1 ]: Call to undefined function date_create_from_format() ~ APPPATH/classes/controller/gather.php [ 109 ]
2010-07-02 15:17:32 --- ERROR: ErrorException [ 1 ]: Call to undefined function date_parse_from_format() ~ APPPATH/classes/controller/gather.php [ 109 ]
2010-07-02 15:29:44 --- ERROR: ErrorException [ 4 ]: syntax error, unexpected T_STRING ~ APPPATH/classes/controller/gather.php [ 106 ]
2010-07-02 15:30:56 --- ERROR: ErrorException [ 4 ]: syntax error, unexpected T_STRING ~ APPPATH/classes/controller/gather.php [ 106 ]
2010-07-02 15:33:43 --- ERROR: ErrorException [ 4 ]: syntax error, unexpected T_STRING ~ APPPATH/classes/controller/gather.php [ 106 ]
2010-07-02 15:40:03 --- ERROR: ErrorException [ 1 ]: Call to undefined function datestr_to_timestamp() ~ APPPATH/classes/controller/gather.php [ 110 ]
2010-07-02 15:40:21 --- ERROR: ErrorException [ 1 ]: Call to undefined function date_to_timestamp() ~ APPPATH/classes/controller/gather.php [ 110 ]