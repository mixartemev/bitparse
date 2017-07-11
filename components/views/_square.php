<?php
/* @var array $model */
/* @var string $cur */
/* @var string $type */
/* @var array $courses */
/* @var bool $withCourses */
/* @var string $period */

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
				<span class="price"><?= History::$currencies[$cur] . Yii::$app->formatter->asDecimal($model[$k][$type], $type=='cap'?0:2) ?></span>
				<span class="charge <?= $model[$k]['percent_change_' . $period] > 0 ? 'up' : 'down' ?>">(<?= $model[$k]['percent_change_' . $period] ?>%)</span>
			</td>
		</tr>
	<?php } ?>
	</table>
	<div class="cap">
<?php if($type == 'cap'){ ?>
		<b>Market Cap All Coins:</b> <?= History::$currencies[$cur] . Yii::$app->formatter->asDecimal($cap_summ, 0) ?>
<?php } ?>
	</div>
	<div class="courses">
<?php
if($withCourses){
	foreach($courses as $k => $course){
		echo strtoupper($k) .' '. $course . " &nbsp; &nbsp; ";
	}
}
 ?>
	</div>
</div>
