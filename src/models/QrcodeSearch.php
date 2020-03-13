<?php

namespace prengicomponent\qrcode\models;

use Yii;
use prengicomponent\qrcode\Module;
use yii\data\ActiveDataProvider;
use yii\base\Model;

class QrcodeSearch extends Model
{
    public $id;
    public $type;

    public function search($params) {
        $query = Qrcode::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        return $dataProvider;
    }
}
