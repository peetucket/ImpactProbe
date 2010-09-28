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
<? } ?>