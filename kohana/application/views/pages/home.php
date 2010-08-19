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
    
    function DeleteProject(id) {
        if(confirm("Are you sure you want to delete this project?\nWARNING: all files and data related to this project will be permanently deleted.")) {
            window.location = '<?= Url::base(TRUE).'params/delete/'?>'+id;
        }
    }
</script>

<h3>Active Projects</h3>
<? if(count($projects) > 0) { ?>

<p><a href="<?= Url::base(TRUE) ?>params/new">New Monitoring Project</a></p>

<table width="600" border="0" cellspacing="0" cellpadding="5" style="border:1px solid #000;">
    <tr class="table_header">
        <td align="left"><b>Title</b></td>
        <td align="center"><b>Date Created</b></td>
        <td align="center">&nbsp;</td>
    </tr>
    <? foreach($projects as $project) { ?>
        <tr>
            <td align="left"><?= $project['project_title'] ?></td>
            <td align="center"><?= date("m/d/y", $project['date_created']) ?></td>
            <td align="center">[<a href="<?= Url::base(TRUE).'results/view/'.$project['project_id'] ?>">results</a>] [<a href="<?= Url::base(TRUE).'gather/log/'.$project['project_id'] ?>">log</a>] [<a href="<?= Url::base(TRUE).'params/modify/'.$project['project_id'] ?>">params</a>]  [<a href="<?= Url::base(TRUE).'home/project_change_state/'.$project['project_id'] ?>"><?= ($project['active']) ? 'deactivate' : 'activate' ?></a>]
            <? if(!$project['active']) echo '[<a href="javascript:DeleteProject('.$project['project_id'].')">delete</a>]'; ?></td>
        </tr>
    <? } ?>
</table>
<? } ?>

<p><a href="<?= Url::base(TRUE) ?>params/new">New Monitoring Project</a></p>