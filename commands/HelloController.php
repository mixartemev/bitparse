<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use app\models\History;
use yii\console\Controller;
use yii\httpclient\Client;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class HelloController extends Controller
{
    /**
     * @param string $cur
     */
    public function actionIndex($cur = 'RUB')
    {
        $client = new Client();
        $response = $client->createRequest()
                           ->setMethod('get')
                           ->setUrl('https://api.coinmarketcap.com/v1/ticker/')
                           ->setData(['convert' => $cur, 'limit' => 4])
                           ->send();
        if ($response->isOk) {
            foreach($response->data as $ar){
                /** @var History $ar */
                (new History([
                    'name' => $ar['symbol'],
                    'price_usd' => $ar['price_usd'],
                    'price_rub' => $ar['price_rub'],
                    'price_btc' => $ar['price_btc'],
                    'volume24h_usd' => $ar['24h_volume_usd'],
                    'volume24h_rub' => $ar['24h_volume_rub'],
                    'market_cap_usd' => $ar['market_cap_usd'],
                    'market_cap_rub' => $ar['market_cap_rub'],
                    'updated' => (int) $ar['last_updated'],
                ]))->save();
            }
        }
    }
}
