<?php
/* @var array $model */
/* @var string $cur */
/* @var string $type */
/* @var array $courses */

use app\models\History;
?>
<div class="square">
	<p class="date"><?= date('d M Y')?></p>
	<table class="coins-list">
	<?php
	$cap_summ = 0;
	foreach(History::$coins as $k => $coin){
		$cap_summ += $model[$k][$type]; // на случай если это капитализация
		?>
		<tr class="coin-row">
			<td class="coin-name">
				<img src="img/<?= $model[$k]['name'] ?>.png"> &nbsp; <?= $coin[$model[$k]['name']] ?>
			</td>
			<td class="coin-value">
				<span class="price"><?= Yii::$app->formatter->asCurrency($model[$k][$type], $cur) ?></span>
				<span class="charge <?= $model[$k]['percent_change_24h'] > 0 ? 'up' : 'down' ?>">(<?= $model[$k]['percent_change_24h'] ?>%)</span>
			</td>
		</tr>
	<?php } ?>
	</table>
	<div class="cap">
<?php if($type == 'cap'){ ?>
		<b>Market Cap All Coins:</b> <?= Yii::$app->formatter->asCurrency($cap_summ, $cur) ?>
<?php } ?>
	</div>
	<div class="courses">
<?php foreach($courses as $k => $course){
		echo strtoupper(History::$currencies[$k]) .' '. $course . " &nbsp; &nbsp; ";
	} ?>
	</div>
</div>
