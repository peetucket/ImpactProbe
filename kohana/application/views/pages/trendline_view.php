<a href="<?= Url::base().'index.php/results/view/'.$project_data['project_id'] ?>">&laquo; Back</a>
<h3>Trendline - <?= $project_data['project_title'] ?></h3>

<p><b>Date range</b>: <?= $date_range ?></p>
<?= $chart_html ?>