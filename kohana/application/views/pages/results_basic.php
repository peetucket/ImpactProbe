<a href="<?= Url::base() ?>">&laquo; Back</a>

<form name="results_basic" id="results_basic" method="post" action="">
<? if($errors) { 
    echo '<p class="errors">'; 
    foreach ($errors as $error_text) { echo $error_text."<br>"; }
    echo '</p>';
} ?>
<b>BROWSE</b>
&nbsp;&nbsp;
<b>From:</b>
<input name="datef_m" type="text" id="datef_m" value="<?= $field_data['datef_m'] ?>" size="1" maxlength="2">
/ <input name="datef_d" type="text" id="datef_d" value="<?= $field_data['datef_d'] ?>" size="1" maxlength="2">
/ <input name="datef_y" type="text" id="datef_y" value="<?= $field_data['datef_y'] ?>" size="1" maxlength="2">
&nbsp;
<b>To:</b>
<input name="datet_m" type="text" id="datet_m" value="<?= $field_data['datet_m'] ?>" size="1" maxlength="2">
/ <input name="datet_d" type="text" id="datet_d" value="<?= $field_data['datet_d'] ?>" size="1" maxlength="2">
/ <input name="datet_y" type="text" id="datet_y" value="<?= $field_data['datet_y'] ?>" size="1" maxlength="2">
<? /* <input id="date_now" name="date_now" type="checkbox" value="1"<? if(array_key_exists('date_now', $field_data)) echo ' selected'; ?>><label for="date_now">Now</label> */ ?>
&nbsp;&nbsp;
<b>Order:</b>
<select name="order">
   <option value="desc" <? if($field_data['order'] == 'desc') { echo("selected"); } ?>>desc</option>
   <option value="asc" <? if($field_data['order'] == 'asc') { echo("selected"); } ?>>asc</option>
</select>
&nbsp;&nbsp;
<b>Show: </b>
<!--<select name="display">
   <option value="individual entries" <? if($field_data['display'] == 'individual entries') { echo("selected"); } ?>>individual entries</option>
   <option value="daily summaries" <? if($field_data['display'] == 'daily summaries') { echo("selected"); } ?>>daily summaries</option>
</select>-->
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
<? if(count($results) > 0) { 
    $total_occurrences = 0;
    foreach($keywords_phrases as $keyword_id => $keyword_phrase) { 
        echo "$keyword_phrase: $keyword_occurrences[$keyword_id]<br>";
        $total_occurrences += $keyword_occurrences[$keyword_id];
    } 
    echo "Total: $total_occurrences"; ?>
    <table width="600" border="0" cellspacing="0" cellpadding="5" style="border:1px solid #000;">
        <tr style="background:#278205;">
            <td>&nbsp;</td>
            <td align="center"><span style="color:#FFF;"><b>Date Retrieved</b></span></td>
            <td align="center"><span style="color:#FFF;"><b>Date Published</b></span></td>
            <td align="center"><span style="color:#FFF;"><b>Keyword Metadata</b></span></td>
            <td align="center">&nbsp;</td>
        </tr>
        <? $i = 1;
        foreach($results as $result) { ?>
            <tr<?= ($i % 2 == 0) ? ' style="background:#ECECEC;"' : '' ; ?>>
                <td align="left"><?= $i ?></td>
                <td align="center"><?= date("m/d/y", $result['date_retrieved']) ?></td>
                <td align="center"><? if($result['date_published'] > 0) echo date("m/d/y", $result['date_published']); ?></td>
                <td align="left">
                <? foreach($result['keywords'] as $keyword) {
                    echo $keyword['keyword'].": ".$keyword['num_occurrences']."<br>";
                } ?>
                </td>
                <td align="center"><?= '[<a href="'.$result['url'].'" target="_blank">url</a>] [text]' 
                //<a href="'.Url::base(TRUE).'results/text/'.$result['meta_id'].'" target="_blank">text</a>
                ?></td>
            </tr>
        <? $i++;
        } ?>
    </table>
<? } ?>
</p>