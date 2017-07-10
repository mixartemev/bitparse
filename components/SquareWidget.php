<?php

namespace app\components;

use app\models\History;
use yii\base\Widget;
use yii\httpclient\Client;

class SquareWidget extends Widget
{
	public static $TYPE_PRICE = 'price';
	public static $TYPE_CAP = 'cap';

	public $type = 'price';

	public $cur = 'rub';

	public $withCourses = true;

	public function run()
	{
		$model = [];
		$courses = [];
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
				];
			}
		}
		if($this->withCourses){
			unset($currencies[$this->cur]);
			foreach($currencies as $k => &$val){
				$val = strtoupper($this->cur . $k);
			};
			$response = $client->createRequest()
			                   ->setMethod('get')
			                   ->setUrl('https://query.yahooapis.com/v1/public/yql')
			                   ->setData([
				                   'q' => 'select Rate from yahoo.finance.xchange where pair="'.implode(',', $currencies).'"',
				                   'env' => 'store://datatables.org/alltableswithkeys',
			                   ])
			                   ->send();
			if ($response->isOk) {
				foreach($response->data['results']['rate'] as $k => $ar) {
					$courses[array_keys($currencies)[$k]] = $ar['Rate'];
				}
			}
		}
		return $this->render('_square', [
			'model' => $model,
			'cur' => $this->cur,
			'type' => $this->type,
			'withCourses' => $this->withCourses,
			'courses' => $courses,
		]);
	}
}