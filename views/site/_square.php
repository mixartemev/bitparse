<?php
/** @var array $model */
/** @var string $cur */

use app\models\History;
?>
<div class="square">
	<p class="date"><?= date('d M Y')?></p>
	<table class="coins-list">
	<?php foreach(History::$coins as $k => $coin){ ?>
		<tr class="coin-row">
			<td class="coin-name">
				<img src="/img/<?= $model[$k]['name'] ?>.png"> &nbsp; <?= $coin[$model[$k]['name']] ?>
			</td>
			<td class="coin-value">
				<span class="price"><?= Yii::$app->formatter->asCurrency($model[$k]['price'], $cur) ?></span>
				<span class="charge <?= $model[$k]['percent_change_24h'] > 0 ? 'up' : 'down' ?>">(<?= $model[$k]['percent_change_24h'] ?>%)</span>
			</td>
		</tr>
	<?php } ?>
	</table>
</div>
