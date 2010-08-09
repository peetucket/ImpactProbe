<script type="text/javascript">
    $(document).ready(function(){
        $("#cluster_btn").click(function() {
            $("#cluster_btn").attr('value', 'Clustering...'); 
            $("#cluster_btn").attr('disabled', 'disabled'); // Disable submit button
            window.location.replace("<?= Url::base().'index.php/results/cluster/'.$project_data['project_id'] ?>");
        });
    });
</script>
<a href="<?= Url::base() ?>">&laquo; Back</a>
<h3>Results - <?= $project_data['project_title'] ?></h3>

<form name="results_basic" id="results_basic" method="post" action="">
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
<? /* <input id="date_now" name="date_now" type="checkbox" value="1"<? if(array_key_exists('date_now', $field_data)) echo ' selected'; ?>><label for="date_now">Now</label> */ ?>
&nbsp;&nbsp;
<b>Order:</b>
<select name="order">
   <option value="desc" <? if($field_data['order'] == 'desc') { echo("selected"); } ?>>desc</option>
   <option value="asc" <? if($field_data['order'] == 'asc') { echo("selected"); } ?>>asc</option>
</select>
&nbsp;&nbsp;
<b>Show: </b>
<? /*<select name="display">
   <option value="individual entries" <? if($field_data['display'] == 'individual entries') { echo("selected"); } ?>>individual entries</option>
   <option value="daily summaries" <? if($field_data['display'] == 'daily summaries') { echo("selected"); } ?>>daily summaries</option>
</select> */ ?>
<select name="num_results">
  <option value="25" <? if($field_data['num_results'] == 25) { echo("selected"); } ?>>25</option>
  <option value="50" <? if($field_data['num_results'] == 50) { echo("selected"); } ?>>50</option>
  <option value="100" <? if($field_data['num_results'] == 100) { echo("selected"); } ?>>100</option>
  <option value="250" <? if($field_data['num_results'] == 250) { echo("selected"); } ?>>250</option>
  <option value="500" <? if($field_data['num_results'] == 500) { echo("selected"); } ?>>500</option>
  <option value="1000" <? if($field_data['num_results'] == 1000) { echo("selected"); } ?>>1000</option>
  <option value="all" <? if($field_data['num_results'] == "all") { echo("selected"); } ?>>all</option>
</select>
&nbsp;&nbsp;
<input type="submit" name="Submit" value="View">
</form>

<? if($total_results > 0) { ?>
    <p><? if($clustered) { ?>
    <input type="button" name="cluster_view_btn" id="cluster_view_btn" value="View Clusters" onClick="parent.location='<?= Url::base() ?>index.php/results/cluster_view/<?= $project_data['project_id'] ?>'">
    <? } else { ?>
    <input type="button" name="cluster_btn" id="cluster_btn" value="Cluster All">
    <? } ?>
    <input type="button" name="trendline_view_btn" id="trendline_view_btn" value="View Trendline" onClick="parent.location='<?= Url::base() ?>index.php/results/trendline/<?= $project_data['project_id'] ?>'"></p>
    <p><table width="600" border="0" cellspacing="0" cellpadding="5" style="border:1px solid #000;">
        <tr class="table_header">
            <td colspan="5" align="center"><b>Summary</b></td>
        </tr>
        <tr>
            <td colspan="3" align="left">
                Showing <b><?= $field_data['num_results'] ?></b> of <b><?= $total_results ?></b> results<br>
                <b>Published between:</b> <?= $date_published_range ?><br>
                
            </td>
            <td colspan="2" align="left">
                <? $total_keywords = 0;
                $keyword_breakdown = "";
                foreach($keywords_phrases as $keyword_id => $keyword_phrase) { 
                    $keyword_breakdown .= "<b>$keyword_phrase:</b> $keyword_occurrences[$keyword_id]<br>";
                    $total_keywords += $keyword_occurrences[$keyword_id];
                } ?>
                <span style="text-decoration:underline;">Keyword Breakdown (Total: <?= $total_keywords ?>)</span><br>
                <?= $keyword_breakdown ?>
            </td>
        </tr>
        <? if($field_data['num_results'] > 0) { ?>
        <tr class="table_header">
            <td>&nbsp;</td>
            <td align="center"><span style="color:#FFF;"><b>Date Retrieved</b></span></td>
            <td align="center"><span style="color:#FFF;"><b>Date Published</b></span></td>
            <td align="left"><span style="color:#FFF;"><b>Keyword Metadata</b></span></td>
            <td align="center">&nbsp;</td>
        </tr>
        <?  $i = 1;
            foreach($results as $result) { ?>
            <tr class="<?= ($i % 2 == 0) ? 'bg_grey' : 'bg_white' ; ?>">
                <td align="left"><?= $i ?></td>
                <td align="center"><?= date("m/d/y", $result['date_retrieved']) ?></td>
                <td align="center"><? if($result['date_published'] > 0) echo date("m/d/y", $result['date_published']); ?></td>
                <td align="left">
                <? foreach($result['keywords'] as $keyword) {
                    echo $keyword['keyword'].": ".$keyword['num_occurrences']."<br>";
                } ?>
                </td>
                <td align="center"><?= '[<a href="'.$result['url'].'" target="_blank">url</a>]' 
                //[<a href="'.Url::base(TRUE).'results/text/'.$result['meta_id'].'" target="_blank">text</a>]
                ?></td>
            </tr>
            <? $i++;
            } 
        } ?>
    </table></p>
<? } ?>