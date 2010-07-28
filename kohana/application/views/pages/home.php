<h3>Active Projects</h3>
<? if(count($projects) > 0) { ?>

<p><a href="<?= Url::base(TRUE) ?>params/new">New Monitoring Project</a></p>

<table width="600" border="0" cellspacing="0" cellpadding="5" style="border:1px solid #000;">
    <tr style="background:#278205;">
        <td align="left"><span style="color:#FFF;"><b>Title</b></span></td>
        <td align="center"><span style="color:#FFF;"><b>Date Created</b></span></td>
        <td align="center">&nbsp;</td>
    </tr>
    <? foreach($projects as $project) { ?>
        <tr>
            <td align="left"><?= $project['project_title'] ?></td>
            <td align="center"><?= date("m/d/y", $project['date_created']) ?></td>
            <td align="center">[<a href="<?= Url::base(TRUE).'results/view/'.$project['project_id'] ?>">results</a>] [<a href="<?= Url::base(TRUE).'gather/log/'.$project['project_id'] ?>">log</a>] [<a href="<?= Url::base(TRUE).'params/modify/'.$project['project_id'] ?>">params</a>]  [<a href="<?= Url::base(TRUE).'home/project_change_state/'.$project['project_id'] ?>"><?= ($project['active'] == 1) ? 'deactivate' : 'activate' ?></a>]<br></td>
        </tr>
    <? } ?>
</table>
<? } ?>

<p><a href="<?= Url::base(TRUE) ?>params/new">New Monitoring Project</a></p>