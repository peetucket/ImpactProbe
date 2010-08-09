<script type="text/javascript">
    $(document).ready(function(){
        $('#recluster_form').submit(function() {
            // Validate threshold value
            if(!isNumeric($("#cluster_threshold").val())) {
                alert("Threshold is invalid.");
                return false;
            }
            $("#submit_btn").attr('value', 'Clustering...'); 
            $("#submit_btn").attr('disabled', 'disabled'); // Disable submit button
        });
    });
    
    function isNumeric(n) {
        return !isNaN(parseFloat(n)) && isFinite(n);
    }

    function startLyteframe(title, url) { 
        var anchor = this.document.createElement('a'); 
        anchor.setAttribute('rev', 'width: 545px; height: 490px; scrolling: auto;'); 
        anchor.setAttribute('title', title); 
        anchor.setAttribute('href', url); 
        anchor.setAttribute('rel', 'lyteframe');
        myLytebox.start(anchor, false, true); 
        return false; 
    }
</script>

<a href="<?= Url::base().'index.php/results/view/'.$project_data['project_id'] ?>">&laquo; Back</a>
<h3>Clustering - <?= $project_data['project_title'] ?></h3>

<form name="recluster_form" id="recluster_form" method="post" action="<?= Url::base().'index.php/results/cluster/'.$project_data['project_id'] ?>">
<p><b>Last clustered:</b> <?= $cluster_log['date_clustered'] ?> (<?= $cluster_log['num_docs'] ?> documents)<br>
<b>Threshold:</b>
<input name="cluster_threshold" type="text" id="cluster_threshold" value="<?= $cluster_log['threshold'] ?> " size="3" maxlength="8">
<select name="cluster_order">
   <option value="arbitrarily"<? if($cluster_log['order'] == 'arbitrarily') { echo " selected"; } ?>>scatter clusters</option>
   <option value="cluster_size"<? if($cluster_log['order'] == 'cluster_size') { echo " selected"; } ?>>order by cluster size</option>
</select>
<input type="submit" id="submit_btn" name="submit_btn" value="Recluster">
</p>
</form>

<?= $chart_html ?>

<? if($singleton_clusters > 0) { ?>
<p>[<a href="javascript:startLyteframe('Singleton clusters (<?= $singleton_clusters ?> total)', '<?= Url::base().'index.php/results/singleton_clusters/'.$project_data['project_id'] ?>')">view singleton clusters (<?= $singleton_clusters ?>)</a>]</p>

<? /* ***OLD lyteframe ***
<p>[<a href="<?= Url::base().'index.php/results/singleton_clusters/'.$project_data['project_id'] ?>" rel="lyteframe" title="Singleton Clusters (<?= $singleton_clusters ?>)" rev="width: <?= Kohana::config('myconf.lyteframe.width') ?>; height: <?= Kohana::config('myconf.lyteframe.height') ?>; scrolling: yes;">view singleton clusters (<?= $singleton_clusters ?>)</a>]</p> */ ?>
<? } ?>