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
        
        $('#params_form').submit(function() {
            $("#keywords_phrases *").attr("selected","selected"); // Select all keywords on form submit
            if($('#gather_now').is(':checked')) {
                $("#submit_btn").attr('value', 'Loading...this may take a while'); 
            } else {
                $("#submit_btn").attr('value', 'Loading...'); 
            }
            $("#submit_btn").attr('disabled', 'disabled'); // Disable submit button
            <? if($mode == "Modify") { ?>
            $("#deactivated_keywords_phrases *").attr("selected","selected");
            <? } ?>
            
            // MAYBE do some form validation here later...
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

<p>
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
    
    <p>
    <b>Deactivated Keywords and Phrases</b><br>
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
<? } ?>
</p>

<label for="gather_now"><input name="gather_now" id="gather_now" type="checkbox" value="1"<? if(array_key_exists('gather_now', $field_data)) echo ' checked="true"'; ?>> <?= ($mode == 'New') ? 'Immediately activate project and start gathering data<i><br>NOTE: data is automatically gathered everyday at midnight</i>' : 'Start gathering data immediately' ?></label><br> 
<input type="submit" id="submit_btn" name="submit_btn" value="<?= ($mode == "New") ? "Submit" : "Modify" ?>">
</form>