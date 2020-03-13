<?php
namespace prengicomponent\qrcode\models;

use Yii;
use prengicomponent\qrcode\Module;

class Qrcode extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */

    public static function tableName()
    {
        return '{{%qrcode}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'string', 'max' => 255],
            [['name'], 'required'],

        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => Module::t('app', 'Name')
        ];
    }


    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
    }

    public function afterDelete()
    {
        parent::afterDelete();
    }


}
