<?php

use yii\db\Migration;

/**
 * Handles the creation of table `course`.
 */
class m170630_075936_create_course_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('course', [
            'id' => $this->primaryKey(),
            'rub' => $this->string(255)->notNull(),
            'eur' => $this->float(),
            'gbp' => $this->float(),
            'jpy' => $this->float(),
            'day' => $this->date(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('course');
    }
}
