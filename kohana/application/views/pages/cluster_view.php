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
</script>

<a href="<?= Url::base().'index.php/results/view/'.$project_data['project_id'] ?>">&laquo; Back</a>
<h3>Clustering - <?= $project_data['project_title'] ?></h3>

<form name="recluster_form" id="recluster_form" method="post" action="<?= Url::base().'index.php/results/cluster/'.$project_data['project_id'] ?>">
<p><b>Last clustered:</b> <?= date("m/d/y", $project_data['date_clustered']) ?><br>
<b>Threshold:</b>
<input name="cluster_threshold" type="text" id="cluster_threshold" value="<?= $project_data['cluster_threshold'] ?> " size="3" maxlength="8">
<input type="submit" id="submit_btn" name="submit_btn" value="Recluster">
</p>
</form>

<img src="<?= Kohana::config('myconf.url.show_chart').'?datafile='.$chart_datafile ?>">

<p>[<a href="<?= Url::base().'index.php/results/singleton_clusters/'.$project_data['project_id'] ?>" rel="lyteframe" title="Singleton Clusters (<?= $singleton_clusters ?>)" rev="width: 400px; height: 300px; scrolling: no;">view singleton clusters (<?= $singleton_clusters ?>)</a>]</p>