<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "course".
 *
 * @property integer $id
 * @property string $rub
 * @property double $eur
 * @property double $gbp
 * @property double $jpy
 * @property string $day
 */
class Course extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'course';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['rub','eur', 'gbp', 'jpy'], 'required'],
            [['rub','eur', 'gbp', 'jpy'], 'number'],
            [['day'], 'safe'],
        ];
    }
}
