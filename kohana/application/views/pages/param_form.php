<script type="text/javascript">
    $(document).ready(function(){
        
        $('#add_keyword_btn').click(function() {
            var new_keyword = $('#add_keyword_text').val().trim();
            if(new_keyword) {
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
        
        $('#params_form').submit(function() {
            $("#keywords_phrases *").attr("selected","selected"); // select all keywords on form submit
            
            // maybe do some form validation here later...
        });
    });
    
    //function clearFieldBg(field_id) { ... }
</script>

<a href="<?= Url::base() ?>">&laquo; Back</a>
<h3><?= $mode ?> Monitoring Project</h3>

<form name="params_form" id="params_form" action="<?= Url::base(TRUE).'params/new' ?>" method="post">

<? if($errors) { 
    echo '<p class="errors">'; 
    foreach ($errors as $error_text) { echo $error_text."<br>"; }
    echo '</p>';
} ?>
<p>
<b>Project Title</b><br>
<input type="text" name="project_title" id="project_title" value="<? if($field_data) { echo $field_data['project_title']; } ?>">
</p>

<p>
<? if($mode == "New") { ?>
    <b>Keywords and Phrases</b><br>
    <input type="text" name="add_keyword_text" id="add_keyword_text" value="">
    <input type="button" id="add_keyword_btn" name="add_keyword_btn" value="&#043;">
    <input type="button" id="remove_keyword_btn" name="remove_keyword_btn" value="&#8722;">
    <br>
    <select id="keywords_phrases" name="keywords_phrases[]" multiple="multiple">
        <? if($field_data AND array_key_exists('keywords_phrases', $field_data)) {
            foreach($field_data['keywords_phrases'] as $keyword_phrase) {
                echo '<option value="'.$keyword_phrase.'">'.$keyword_phrase.'</option>';
            }
        } ?>
    </select>
<? } elseif($mode == "Modify") { ?>
    <b>Keywords and Phrases</b><br>
    <select id="keywords_phrases" name="keywords_phrases[]" multiple="multiple">
        <? foreach($field_data['keywords_phrases'] as $keyword_phrase) {
            echo '<option value="'.$keyword_phrase['keyword_id'].'">'.$keyword_phrase['keyword_phrase'].'</option>';
        } ?>
    </select>
<? } ?>
</p>

<? if($mode == 'New') { ?>
<input type="submit" id="submit_btn" name="submit_btn" value="<?= ($mode == "New") ? "Submit" : "Modify" ?>">
<? } ?>

</form>