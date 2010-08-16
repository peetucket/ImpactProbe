<script type="text/javascript">
    $(document).ready(function(){
        
        $('#add_keyword_btn').click(function() {
            var new_keyword = $('#add_keyword_text').val().replace(/["]/g,'').trim(); // Remove quotes(") and trim whitespace
            if(new_keyword) {
                if($('#exact_phrase').is(':checked')) {
                    new_keyword = '"' + new_keyword + '"';
                    $('#exact_phrase').attr('checked', false);
                }
                $("#keywords_phrases").addOption(new_keyword, new_keyword); // add new keyword to combo box
                $('#add_keyword_text').val(""); // clear 'add keyword' textfield
            }
        });
        
        $('#remove_keyword_btn').click(function() {
            // remove selected keywords from combobox
            $("#keywords_phrases option:selected").each(function () {
                $("#keywords_phrases").removeOption($(this).val());
            });
        });
        
        <? if($mode == "Modify") { ?>
        $('#deactivate_keyword_btn').click(function() {
            // move selected keywords from active to deactivated combobox
            $("#keywords_phrases option:selected").each(function () {
                var keyword_phrase = $(this).val();
                $("#keywords_phrases").removeOption(keyword_phrase);
                if(isInteger(keyword_phrase)) {
                    // Only move to deactivated if this keyword was added previously
                    $("#deactivated_keywords_phrases").addOption(keyword_phrase, $(this).text());
                }
            });
        });
        $('#reactivate_keyword_btn').click(function() {
            // move selected keywords from deactivated to active combobox
            $("#deactivated_keywords_phrases option:selected").each(function () {
                var keyword_phrase = $(this).val();
                $("#deactivated_keywords_phrases").removeOption(keyword_phrase);
                $("#keywords_phrases").addOption(keyword_phrase, $(this).text());
            });
        });
        <? } ?>
        
        $('#api_rss_feed').click(function() {
            if($('#api_rss_feed').is(':checked')) {
                $('#rss_feed_form').css('display', '');
            } else {
                $('#rss_feed_form').css('display', 'none');
            }
        });
        
        $('#add_rss_feed_btn').click(function() {
            var new_rss_feed = $('#add_rss_feed_text').val().trim();
            if(new_rss_feed) {
                if($('#searchable').is(':checked')) {
                    new_rss_feed = 'Searchable: ' + new_rss_feed;
                    $('#searchable').attr('checked', false);
                }
                $("#rss_feeds").addOption(new_rss_feed, new_rss_feed); // add new RSS feed to combo box
                $('#add_rss_feed_text').val(""); // clear 'add rss feed' textfield
            }
        });
        
        $('#remove_rss_feed_btn').click(function() {
            // remove selected rss_feeds from combobox
            $("#rss_feeds option:selected").each(function () {
                $("#rss_feeds").removeOption($(this).val());
            });
        });
        
        <? if($mode == "Modify") { ?>
        $('#deactivate_rss_feed_btn').click(function() {
            // move selected RSS feeds from active to deactivated combobox
            $("#rss_feeds option:selected").each(function () {
                var rss_feed = $(this).val();
                $("#rss_feeds").removeOption(rss_feed);
                if(isInteger(rss_feed)) {
                    // Only move to deactivated if this RSS feed was added previously
                    $("#deactivated_rss_feeds").addOption(rss_feed, $(this).text());
                }
            });
        });
        $('#reactivate_rss_feed_btn').click(function() {
            // move selected keywords from deactivated to active combobox
            $("#deactivated_rss_feeds option:selected").each(function () {
                var rss_feed = $(this).val();
                $("#deactivated_rss_feeds").removeOption(rss_feed);
                $("#rss_feeds").addOption(rss_feed, $(this).text());
            });
        });
        <? } ?>
        
        $('#params_form').submit(function() {
            // Select all keywords & RSS feeds on form submit (so they are added to $field_data array)
            $("#keywords_phrases *").attr("selected","selected"); 
            $("#rss_feeds *").attr("selected","selected");
            if($('#gather_now').is(':checked')) {
                $("#submit_btn").attr('value', 'Loading...this may take a while'); 
            } else {
                $("#submit_btn").attr('value', 'Loading...'); 
            }
            $("#submit_btn").attr('disabled', 'disabled'); // Disable submit button
            <? if($mode == "Modify") { ?>
                $("#deactivated_keywords_phrases *").attr("selected","selected");
                $("#deactivated_rss_feeds *").attr("selected","selected");
            <? } ?>
        });
    });

    function isInteger(s) {
        return (s.toString().search(/^-?[0-9]+$/) == 0);
    }
</script>

<a href="<?= Url::base() ?>">&laquo; Back</a>
<h3><?= $mode ?> Monitoring Project</h3>

<form name="params_form" id="params_form" action="<?= Url::base(TRUE) ?>params/<?= ($mode == "New") ? "new" : "modify/".$field_data['project_id'] ?>" method="post">

<? if($errors) { 
    echo '<p class="errors">'; 
    foreach ($errors as $error_text) { echo $error_text."<br>"; }
    echo '</p>';
} ?>
<p>
<b>Project Title</b><br>
<input type="text" name="project_title" id="project_title" value="<?= $field_data['project_title'] ?>">
</p>

<table width="600" border="0" cellspacing="0" cellpadding="3">
<tr>
    <td align="left">
    <b>Keywords and Phrases</b><br>
    <input type="text" name="add_keyword_text" id="add_keyword_text" value="">
    <label for="exact_phrase"><input name="exact_phrase" id="exact_phrase" type="checkbox" value="1">exact</label>
    <input type="button" id="add_keyword_btn" name="add_keyword_btn" value="&#043;">
<? if($mode == "New") { ?>
    <input type="button" id="remove_keyword_btn" name="remove_keyword_btn" value="&#8722;">
    <br>
    <select id="keywords_phrases" name="keywords_phrases[]" multiple="multiple">
        <? if(array_key_exists('keywords_phrases', $field_data)) {
            foreach($field_data['keywords_phrases'] as $keyword_phrase) {
                echo '<option value="'.$keyword_phrase.'">'.$keyword_phrase.'</option>';
            }
        } ?>
    </select>
<? } elseif($mode == "Modify") { ?>
    <input type="button" id="deactivate_keyword_btn" name="deactivate_keyword_btn" value="&#8722;">
    <br>
    <select id="keywords_phrases" name="keywords_phrases[]" multiple="multiple">
        <? if(array_key_exists('keywords_phrases', $field_data)) { 
            foreach($field_data['keywords_phrases'] as $keyword_phrase_id) {
                $quotes = ($field_data['keyword_phrase_data'][$keyword_phrase_id]['exact_phrase']) ? '"' : '';
                echo '<option value="'.$keyword_phrase_id.'">'.$quotes.$field_data['keyword_phrase_data'][$keyword_phrase_id]['keyword_phrase'].$quotes.'</option>';
            }
        } ?>
    </select>
    </td>
    <td align="left">
        <p><b>Deactivated Keywords and Phrases</b><br>
        <input type="button" id="reactivate_keyword_btn" name="reactivate_keyword_btn" value="Reactivate">
        <br>
        <select id="deactivated_keywords_phrases" name="deactivated_keywords_phrases[]" multiple="multiple">
            <? if(array_key_exists('deactivated_keywords_phrases', $field_data)) {  
                foreach($field_data['deactivated_keywords_phrases'] as $keyword_phrase_id) {
                    $quotes = ($field_data['keyword_phrase_data'][$keyword_phrase_id]['exact_phrase']) ? '"' : '';
                    echo '<option value="'.$keyword_phrase_id.'">'.$quotes.$field_data['keyword_phrase_data'][$keyword_phrase_id]['keyword_phrase'].$quotes.'</option>';
                }
            } ?>
        </select></p>
    </td>
<? } ?>
</tr>
</table>

<p><b>Enable/Disable Data Source APIs</b><br>
<? 
$rss_feed_chkbox_html = ""; 
foreach($api_sources as $api_source) { 
    $chkbox_html = '<label for="api_'.$api_source['gather_method_name'].'"><input name="api_'.$api_source['gather_method_name'].'" id="api_'.$api_source['gather_method_name'].'" type="checkbox" value="1"'; 
    if(array_key_exists('api_'.$api_source['gather_method_name'], $field_data)) 
        $chkbox_html .= ' checked="true"'; 
    $chkbox_html .= '> '.$api_source['api_name'].'</label><br>';
    
    // Make sure RSS feed is placed at the end of the list
    if($api_source['gather_method_name'] == 'rss_feed')
        $rss_feed_chkbox_html = $chkbox_html;
    else
        echo $chkbox_html;
}
echo $rss_feed_chkbox_html; ?>

<div id="rss_feed_form"<? if(!array_key_exists('api_rss_feed', $field_data)) echo ' style="display:none;"'; ?>>
<table width="700" border="0" cellspacing="0" cellpadding="3">
<tr>
    <td align="left">
    <b>RSS Feed URLs</b><br>
    <input type="text" name="add_rss_feed_text" id="add_rss_feed_text" value="">
    <label for="searchable"><input name="searchable" id="searchable" type="checkbox" value="1">searchable (<a href="'.Url::base().'index.php/params/help_searchable" rel="lyteframe" title="Help: Searchable RSS Feeds" rev="width: 400px; height: 170px; scrolling: no;">?</a>)</label>
    <input type="button" id="add_rss_feed_btn" name="add_rss_feed_btn" value="&#043;">
<? if($mode == "New") { ?>
    <input type="button" id="remove_rss_feed_btn" name="remove_rss_feed_btn" value="&#8722;">
    <br>
    <select id="rss_feeds" name="rss_feeds[]" multiple="multiple">
        <? if(array_key_exists('rss_feeds', $field_data)) {
            foreach($field_data['rss_feeds'] as $rss_feed) {
                echo '<option value="'.$rss_feed.'">'.$rss_feed.'</option>';
            }
        } ?>
    </select>
<? } elseif($mode == "Modify") { ?>
    <input type="button" id="deactivate_rss_feed_btn" name="deactivate_rss_feed_btn" value="&#8722;">
    <br>
    
    <select id="rss_feeds" name="rss_feeds[]" multiple="multiple">
        <? if(array_key_exists('rss_feeds', $field_data)) {
            foreach($field_data['rss_feeds'] as $feed_id) {
                $searchable = ($field_data['rss_feed_data'][$feed_id]['searchable']) ? 'Searchable: ' : '';
                echo '<option value="'.$feed_id.'">'.$searchable.$field_data['rss_feed_data'][$feed_id]['url'].'</option>';
            }
        } ?>
    </select>
    </td>
    <td colspan="5" align="left">
        <p><b>Deactivated RSS Feeds</b><br>
        <input type="button" id="reactivate_rss_feed_btn" name="reactivate_rss_feed_btn" value="Reactivate">
        <br>
        <select id="deactivated_rss_feeds" name="deactivated_rss_feeds[]" multiple="multiple">
            <? if(array_key_exists('deactivated_rss_feeds', $field_data)) {  
                foreach($field_data['deactivated_rss_feeds'] as $feed_id) {
                    $searchable = ($field_data['rss_feed_data'][$feed_id]['searchable']) ? 'Searchable: ' : '';
                    echo '<option value="'.$feed_id.'">'.$searchable.$field_data['rss_feed_data'][$feed_id]['url'].'</option>';
                }
            } ?>
        </select></p>
    </td>
<? } ?>
</tr>
</table>
</div></p>

<p><b>Gather interval</b>
<select id="gather_interval" name="gather_interval">
    <option value="daily"<? if($field_data['gather_interval'] == 'daily') echo " selected"; ?>>daily</option>
    <option value="twice_daily"<? if($field_data['gather_interval'] == 'twice_daily') echo " selected"; ?>>twice daily</option>
    <option value="weekly"<? if($field_data['gather_interval'] == 'weekly') echo " selected"; ?>>weekly</option>
    <option value="twice_weekly"<? if($field_data['gather_interval'] == 'twice_weekly') echo " selected"; ?>>twice weekly</option>
    <option value="monthly"<? if($field_data['gather_interval'] == 'monthly') echo " selected"; ?>>monthly</option>
    <option value="twice_monthly"<? if($field_data['gather_interval'] == 'twice_monthly') echo " selected"; ?>>twice monthly</option>
</select></p>

<label for="gather_now"><input name="gather_now" id="gather_now" type="checkbox" value="1"<? if(array_key_exists('gather_now', $field_data)) echo ' checked="true"'; ?>> <?= ($mode == 'New') ? 'Immediately activate project and start gathering data' : 'Start gathering data immediately' ?></label><br> 
<input type="submit" id="submit_btn" name="submit_btn" value="<?= ($mode == "New") ? "Submit" : "Modify" ?>">
</form>