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
<script type='text/javascript' src='http://www.google.com/jsapi'></script>
<script type='text/javascript'>
    google.load('visualization', '1', {'packages':['annotatedtimeline']});
    google.setOnLoadCallback(drawChart);
    function drawChart() {
        var data = new google.visualization.DataTable();
        <?= $chart_data_js ?>
        var chart = new google.visualization.AnnotatedTimeLine(document.getElementById('chart_div'));
        chart.draw(data, {displayAnnotations: true, 'dateFormat': '<?= $date_format_chart ?>'});
    }
</script>

<a href="<?= Url::base().'index.php/results/view/'.$project_data['project_id'] ?>">&laquo; Back</a>
<h3>Trendline - <?= $project_data['project_title'] ?></h3>


<p><form name="trendline_form" id="trendline_form" method="post" action="">
<? if($errors) { 
    echo '<p class="errors">'; 
    foreach ($errors as $error_text) { echo $error_text."<br>"; }
    echo '</p>';
} ?>
<b>SHOW</b>
&nbsp;
<b>From:</b>
<input class="date_field" name="datef_m" type="text" id="datef_m" value="<?= $field_data['datef_m'] ?>" maxlength="2">
/ <input class="date_field" name="datef_d" type="text" id="datef_d" value="<?= $field_data['datef_d'] ?>" maxlength="2">
/ <input class="date_field" name="datef_y" type="text" id="datef_y" value="<?= $field_data['datef_y'] ?>" maxlength="2">
&nbsp;
<b>To:</b>
<input class="date_field" name="datet_m" type="text" id="datet_m" value="<?= $field_data['datet_m'] ?>" maxlength="2">
/ <input class="date_field" name="datet_d" type="text" id="datet_d" value="<?= $field_data['datet_d'] ?>" maxlength="2">
/ <input class="date_field" name="datet_y" type="text" id="datet_y" value="<?= $field_data['datet_y'] ?>" maxlength="2">
&nbsp;
<select name="display_mode">
  <option value="consensus" <? if($field_data['display_mode'] == "consensus") { echo("selected"); } ?>>consensus</option>
  <option value="by_keyword" <? if($field_data['display_mode'] == "by_keyword") { echo("selected"); } ?>>by keyword</option>
</select>
<input type="submit" name="View" value="View">
<input type="submit" name="Download" value="Download as .csv"><br>
<b>NOTE:</b> the &quot;consensus&quot; value represents the total number of entries found which contain any of the search keywords. So when viewing the results broken down by individual keywords the total of those values does not necessarily represent the consensus value (because a single entry may contain multiple keywords).
</form></p>

<p><b>Showing results published</b>: <?= $date_range ?></p>

<div id="chart_div" style="<?= $chart_dimensions ?>"></div>