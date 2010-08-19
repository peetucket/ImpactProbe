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
?>
<a href="<?= Url::base() ?>">&laquo; Back</a>

<form name="gather_log" id="gather_log" method="post" action="">
<? if($errors) { 
    echo '<p class="errors">'; 
    foreach ($errors as $error_text) { echo $error_text."<br>"; }
    echo '</p>';
} ?>
<b>BROWSE</b>
&nbsp;&nbsp;
<b>From:</b>
<input class="date_field" name="datef_m" type="text" id="datef_m" value="<?= $field_data['datef_m'] ?>" maxlength="2">
/ <input class="date_field" name="datef_d" type="text" id="datef_d" value="<?= $field_data['datef_d'] ?>" maxlength="2">
/ <input class="date_field" name="datef_y" type="text" id="datef_y" value="<?= $field_data['datef_y'] ?>" maxlength="2">
&nbsp;
<b>To:</b>
<input class="date_field" name="datet_m" type="text" id="datet_m" value="<?= $field_data['datet_m'] ?>" maxlength="2">
/ <input class="date_field" name="datet_d" type="text" id="datet_d" value="<?= $field_data['datet_d'] ?>" maxlength="2">
/ <input class="date_field" name="datet_y" type="text" id="datet_y" value="<?= $field_data['datet_y'] ?>" maxlength="2">
&nbsp;&nbsp;
<b>Order:</b>
<select name="order">
   <option value="desc" <? if($field_data['order'] == 'desc') { echo("selected"); } ?>>desc</option>
   <option value="asc" <? if($field_data['order'] == 'asc') { echo("selected"); } ?>>asc</option>
</select>
&nbsp;&nbsp;
<b>Show: </b>
<select name="num_results">
  <option value="25" <? if($field_data['num_results'] == 25) { echo("selected"); } ?>>25</option>
  <option value="50" <? if($field_data['num_results'] == 50) { echo("selected"); } ?>>50</option>
  <option value="100" <? if($field_data['num_results'] == 100) { echo("selected"); } ?>>100</option>
  <option value="250" <? if($field_data['num_results'] == 250) { echo("selected"); } ?>>250</option>
  <option value="500" <? if($field_data['num_results'] == 500) { echo("selected"); } ?>>500</option>
  <option value="all" <? if($field_data['num_results'] == 'all') { echo("selected"); } ?>>all</option>
</select>
&nbsp;&nbsp;
<input type="submit" name="Submit" value="View">
</form>

<p>
<? if(count($results) > 0) { ?>
    <table width="600" border="0" cellspacing="0" cellpadding="5" style="border:1px solid #000;">
        <tr class="table_header">
            <td>&nbsp;</td>
            <td align="center"><span style="color:#FFF;"><b>Date</b></span></td>
            <td align="center"><span style="color:#FFF;"><b>Results</b></span></td>
        <td align="left"><span style="color:#FFF;"><b>Search Query</b></span></td>
        </tr>
        <? $i = 1;
        foreach($results as $result) { ?>
            <tr class="<? if($result['error']) {
                echo 'bg_red';
            } else { 
                echo ($i % 2 == 0) ? 'bg_grey' : 'bg_white' ;
            } ?>">
                <td align="left"><?= $i ?></td>
                <td align="center"><?= date("m/d/y", $result['date']) ?></td>
                <td align="center"><?= $result['results_gathered'] ?></td>
                <td align="left"><?= $result['search_query'] ?></td>
            </tr>
        <? $i++;
        } ?>
    </table>
<? } ?>
</p>