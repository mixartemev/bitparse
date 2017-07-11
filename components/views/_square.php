<?php
/* @var array $model */
/* @var string $cur */
/* @var string $type */
/* @var array $courses */
/* @var array $cap_charge_24h */
/* @var string $period */

use app\models\History;
?>
<div class="square">
	<p class="date"><?= date('d M Y')?></p>
	<table class="coins-list">
	<?php
	$cap_summ = 0;
	foreach(History::$coins as $k => $coin){
		if($type=='cap'){
			$cap_summ += $model[$k][$type];
			$charge = $cap_charge_24h[$k];
		}else{
			$charge = $model[$k]['percent_change_' . $period];
		}
		$cap_summ += $model[$k][$type]; // на случай если это капитализация
		?>
		<tr class="coin-row">
			<td class="coin-name">
				<img src="img/<?= $model[$k]['name'] ?>.png"> &nbsp; <?= $coin[$model[$k]['name']] ?>
			</td>
			<td class="coin-value">
				<span class="price"><?= History::$currencies[$cur] . Yii::$app->formatter->asDecimal($model[$k][$type]/*, $type=='cap'?0:2*/) ?></span>
				<span class="charge <?= $charge > 0 ? 'up' : 'down' ?>">(<?= Yii::$app->formatter->asDecimal($charge) ?>%)</span>
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
if($cap_charge_24h){
	foreach($courses as $k => $course){
		echo strtoupper($k) .' '. Yii::$app->formatter->asDecimal($course, 4) . " &nbsp; &nbsp; ";
	}
}
 ?>
	</div>
</div>
