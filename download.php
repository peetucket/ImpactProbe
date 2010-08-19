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

if(file_exists($_GET['file'])) {
    $filename = ($_GET['name']) ? $_GET['name'] : 'chart.txt';
    $content_type = ($_GET['type']) ? $_GET['type'] : 'text/plain';
    
    header ("Content-Type: $content_type");
    header ("Content-Disposition: attachment; filename=\"$filename\"");
    readfile($_GET['file']);
    
    if($_GET['delete_file'])
        unlink($_GET['file']);
} else {
    echo $_GET['file'].": file does not exist.";
}
?>