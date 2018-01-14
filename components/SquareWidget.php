<?php

namespace app\components;

use app\models\Course;
use app\models\History;
use Yii;
use yii\base\Widget;
use yii\httpclient\Client;

class SquareWidget extends Widget
{
	public static $TYPE_PRICE = 'price';
	public static $TYPE_CAP = 'cap';

	public $type = 'price';

	public $cur = 'usd';

	public $withCourses = true;

	public $period = '24h';

	public function run()
	{
		$model = [];
		$currencies = History::$currencies;
		$client = new Client();
		$response = $client->createRequest()
		                   ->setMethod('get')
		                   ->setUrl('https://api.coinmarketcap.com/v1/ticker/')
		                   ->setData(['convert' => $this->cur, 'limit' => 4])
		                   ->send();
		if ($response->isOk) {
			foreach($response->data as $ar) {
				$model[] = [
					'name'               => $ar['symbol'],
					'price'              => $ar[ 'price_' . $this->cur ],
					'cap'         => $ar[ 'market_cap_' . $this->cur ],
					'percent_change_1h'  => $ar['percent_change_1h'],
					'percent_change_24h' => $ar['percent_change_24h'],
					'percent_change_7d'  => $ar['percent_change_7d'],
					'usd_change_24h' => (float)$ar['price_usd'] - (float)History::find()->where("name = '$ar[symbol]' AND DAY(`updated`) = DAY(NOW())-1 AND MONTH(`updated`) = MONTH(NOW()) AND YEAR(`updated`) = YEAR(NOW())")->one()->price_usd,
				];
			}
		}
		if($this->withCourses){
			unset($currencies[$this->cur]);
			$c = Course::findOne(1);
				foreach($currencies as $k => &$val){
					if($this->cur != 'usd'){
						$val = ($k != 'usd'	? $c->$k : 1) / $c->{$this->cur} ;
					}else{
						$val = $c->$k;
					}
				}
		}
		$cap_charge_24h = [];
		if($this->type == 'cap'){
			$response = $client->createRequest()->setMethod('get')->setUrl('http://coincap.io/front')->send();
			if ($response->isOk) {
				foreach(array_keys(History::$coins) as $k => $coin){
					$cap_charge_24h[$coin] = $response->data[$k]['perc'];
				}
			}
		}
		return $this->render('_square', [
			'model' => $model,
			'cur' => $this->cur,
			'type' => $this->type,
			'withCourses' => $this->withCourses,
			'cap_charge_24h' => $cap_charge_24h,
			'courses' => $currencies,
			'period' => $this->period
		]);
	}
}