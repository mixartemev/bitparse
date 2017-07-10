<?php

/* @var $this yii\web\View */

use app\models\History;
use phpnt\chartJS\ChartJs;
use yii\helpers\ArrayHelper;
use yii\httpclient\Client;

$this->title = 'Graphics';

$h = History::find()
            ->select(['name', "TIME_FORMAT(FROM_UNIXTIME(updated), '%H:%i') as updated", 'price_rub'])
            ->orderBy('id desc')
            ->limit(240*4)->asArray()->all();
$h = ArrayHelper::map($h, 'updated', 'price_rub', 'name');
$dataSets = [];
foreach($h as $k => $v){
    $labels = array_reverse(array_keys($v));
    $dataSets[] = [
        'data' => array_reverse(array_values($v)),
        'label' => $k,
        'lineTension' => 0.2,
        'borderDashOffset' => 0.0,
        'borderJoinStyle' => 'miter',
        'pointBorderWidth' => 1,
        'pointRadius' => 1,
    ];
}
$btc = [
    'labels' => $labels,
    'datasets' => [
        $dataSets[3]
    ]
];
$eth = [
    'labels' => $labels,
    'datasets' => [
        $dataSets[2]
    ]
];
$xrp = [
    'labels' => $labels,
    'datasets' => [
        $dataSets[1]
    ]
];
$ltc = [
    'labels' => $labels,
    'datasets' => [
        $dataSets[0]
    ]
];

?>
<div class="site-about">
    <?= app\components\SquareWidget::widget() ?>
    <?= app\components\SquareWidget::widget(['cur' => 'usd']) ?>
    <?= app\components\SquareWidget::widget(['cur' => 'eur']) ?>
    <?= app\components\SquareWidget::widget(['cur' => 'jpy']) ?>
    <?= app\components\SquareWidget::widget(['cur' => 'usd', 'type' => 'cap', 'withCourses' => false]) ?>
    <?= ChartJs::widget([
    'type'  => ChartJs::TYPE_LINE,
    'data'  => $btc,
    'options'   => [
        'title' => [
            'display' => true,
            'text' => 'BitCoin',
        ]
    ]
]);
?>

<?= ChartJs::widget([
    'type'  => ChartJs::TYPE_LINE,
    'data'  => $eth,
    'options'   => []
]);
?>
<?= ''/*ChartJs::widget([
    'type'  => ChartJs::TYPE_LINE,
    'data'  => $xrp,
    'options'   => []
]);
*/?>
<?= ''/* ChartJs::widget([
    'type'  => ChartJs::TYPE_LINE,
    'data'  => $ltc,
    'options'   => []
]);*/
?>
</div>
