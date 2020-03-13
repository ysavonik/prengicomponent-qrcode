<?php
namespace prengicomponent\qrcode\models;

use yii\data\ActiveDataProvider;
use yii\base\Model;
use yii\db\Query;

class QrcodeObjectsSearch extends Model
{
    public $description;

    public function rules()
    {
        return [
            [['description', 'string'], 'safe'],
        ];
    }

    public function search($params) {
        $query = (new Query())
            ->select('{{%auth_item}}.name as name, {{%auth_item}}.description as description, {{%qrcode_objects}}.id_role, {{%qrcode_objects}}.permission_open, {{%qrcode_objects}}.permission_checklist, {{%qrcode_objects}}.permission_create_task')
            ->from('{{%qrcode_objects}}')
            ->innerJoin('{{%auth_item}} ON {{%auth_item}}.id_auth_item={{%qrcode_objects}}.id_role');
        if (!($this->load($params) && $this->validate())) {
            $query->orderBy('description');
            $activeDataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => 20,
                ],
            ]);
            return $activeDataProvider;
        }
        if ($this->description != '') {
            $query->where('{{%auth_item}}.description LIKE :description', array(':description' => '%' . $this->description . '%'));
        }
        $query->orderBy('description');
        $activeDataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        return $activeDataProvider;
    }
}
