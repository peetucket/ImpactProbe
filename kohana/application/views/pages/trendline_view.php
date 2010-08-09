<a href="<?= Url::base().'index.php/results/view/'.$project_data['project_id'] ?>">&laquo; Back</a>
<h3>Trendline - <?= $project_data['project_title'] ?></h3>

<form name="gather_log" id="gather_log" method="post" action="">
<? /* if($errors) { 
    echo '<p class="errors">'; 
    foreach ($errors as $error_text) { echo $error_text."<br>"; }
    echo '</p>';
} ?>
<b>SHOWING</b>
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
<b>Scale:</b>
<select name="scale">
   <option value="day" <? if($field_data['scale'] == "day") { echo("selected"); } ?>>day</option>
   <option value="month" <? if($field_data['scale'] == "month") { echo("selected"); } ?>>month</option>
</select>
&nbsp;&nbsp;
<b>Show: </b>
<select name="display_mode">
  <option value="25" <? if($field_data['display_mode'] == "consensus") { echo("selected"); } ?>>consensus</option>
  <option value="50" <? if($field_data['display_mode'] == "by_keyword") { echo("selected"); } ?>>by keyword</option>
</select>
&nbsp;&nbsp;
<input type="submit" name="Submit" value="View">
<br>NOTE: by keyword does not equal consensus...
</form>
*/ ?>
<p><b>Published between</b>: <?= $date_range ?></p>
<?= $chart_html ?>