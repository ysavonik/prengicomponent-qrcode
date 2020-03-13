<?php
namespace prengicomponent\qrcode\models;

use phpDocumentor\Reflection\Types\Integer;
use Yii;
use prengicomponent\qrcode\Module;

class QrcodeAddModel extends \yii\db\ActiveRecord
{
    public $role_id;
    public $role_name;

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
    }

    public function afterDelete()
    {
        parent::afterDelete();
    }
}
