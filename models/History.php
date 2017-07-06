<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "history".
 *
 * @property integer $id
 * @property string $name
 * @property double $price_usd
 * @property double $price_rub
 * @property double $price_btc
 * @property double $volume24h_usd
 * @property double $volume24h_rub
 * @property double $market_cap_usd
 * @property double $market_cap_rub
 * @property string $updated
 */
class History extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'history';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['price_usd', 'price_rub', 'price_btc', 'volume24h_usd', 'volume24h_rub', 'market_cap_usd', 'market_cap_rub', 'updated'], 'number'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'price_usd' => 'Price Usd',
            'price_rub' => 'Price Rub',
            'price_btc' => 'Price Btc',
            'volume24h_usd' => 'Volume24h Usd',
            'volume24h_rub' => 'Volume24h Rub',
            'market_cap_usd' => 'Market Cap Usd',
            'market_cap_rub' => 'Market Cap Rub',
            'updated' => 'Updated',
        ];
    }

    public static $coins = [
        ['BTC' => 'BitCoin'],
        ['ETH' => 'Ethereum'],
        ['XRP' => 'Ripple'],
        ['LTC' => 'Litecoin']
    ];

    public static $currencies = [
        'rub',
        'gbp',
        'eur',
        'jpy'
    ];
}
