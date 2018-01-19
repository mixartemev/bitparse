<?php
/* @var array $model */
/* @var string $cur */
/* @var string $type */
/* @var array $courses */
/* @var array $cap_charge_24h */
/* @var string $period */

use app\models\History;
$prd = ['24h' => 'day', '1h' => 'hour', '7d' => 'week']
?>
<div class="wrap b-map">
    <div class="b-map__year text-center"><?= date('Y')?></div>
    <div class="b-map__month text-center"><?= date('F d')?> day</div>

    <table class="b-map__table">
	    <?php
	    $cap_summ = 0;
	    $cap_charge_summ = 0;
	    //var_dump($model);die;
	    foreach(History::$coins as $k => $coin){
	        //print_r(History::find()->where("name = $k AND DAY(`updated`) = DAY(NOW())-1 AND MONTH(`updated`) = MONTH(NOW()) AND YEAR(`updated`) = YEAR(NOW())")->one()->price_usd);die;
		    //if($k!=2){
		    if($type=='cap'){
			    $cap_summ += $model[$k][$type];
			    $charge = $cap_charge_24h[$k];
			    $cap_charge_summ += $charge;
		    }else{
			    $charge = $model[$k]['percent_change_' . $period];
		    }
		    $cap_summ += $model[$k][$type]; // на случай если это капитализация
		    ?>
            <tr data-coin="<?= $model[$k]['name'] ?>">
                <td class="b-map__table-first">
                    <img src="/img/<?= $model[$k]['name'] ?>.png">
                    <span class="b-map__name-coin"><?= $coin[$model[$k]['name']] ?></span>
                </td>
                <td class="b-map__table-last">
                    <span class="b-map__num-coin"><!--<span class="b-rub">--><?= History::$currencies[$cur] ?><!--</span>--><?= Yii::$app->formatter->asDecimal($model[$k][$type], $type=='cap'?0:2, []) ?></span>
                    <span class="b-map__label <?= $charge > 0 ? 'green' : 'red' ?> b-map__diff-coin"><?= Yii::$app->formatter->asDecimal($charge) ?>% / <?= Yii::$app->formatter->asDecimal($model[$k]['usd_change_24h']) ?></span>
                </td>
            </tr>
		    <?php
		    //}
	    } ?>
    </table>

    <!--div class="cap">
		<?php if($type == 'cap'){ ?>
            <b>Market Cap All Coins:</b> <?= History::$currencies[$cur] . Yii::$app->formatter->asDecimal($cap_summ, 0) ?> / <span class="cap-sum"><?= Yii::$app->formatter->asDecimal($cap_charge_summ) ?>%</span>
		<?php } ?>
	</div-->

    <div class="b-map__charts">
        <table class="table-ch">
            <tr class="table-ch__currency-name">
	            <?php if($withCourses){
		            foreach($courses as $k => $course){ ?>
                        <td><?= strtoupper($k) ?></td>
		            <?php }
	            } ?>
            <tr class="table-ch__currency-num">
	            <?php if($withCourses){
		            foreach($courses as $k => $course){ ?>
                        <td><?= Yii::$app->formatter->asDecimal($course, 4) ?></td>
		            <?php }
	            } ?>
            </tr>
        </table>
    </div>
</div>
