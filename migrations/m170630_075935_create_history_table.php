<?php

use yii\db\Migration;

/**
 * Handles the creation of table `history`.
 */
class m170630_075935_create_history_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('history', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull(),
            'price_usd' => $this->float(),
            'price_rub' => $this->float(),
            'price_btc' => $this->float(),
            'volume24h_usd' => $this->float(),
            'volume24h_rub' => $this->float(),
            'market_cap_usd' => $this->float(),
            'market_cap_rub' => $this->float(),
            'updated' => $this->timestamp(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('history');
    }
}
