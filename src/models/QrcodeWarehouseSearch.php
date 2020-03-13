<?php

namespace prengicomponent\qrcode\models;

use yii\data\ActiveDataProvider;
use yii\base\Model;
use yii\db\Query;

class QrcodeWarehouseSearch extends Model
{
    public $roles;
    public $description;

    public function rules()
    {
        return [
            [['description', 'string'], 'safe'],
        ];
    }

    public function search($params)
    {
        $query = (new Query())
            ->select('{{%auth_item}}.name, {{%auth_item}}.description, {{%qrcode_warehouse}}.id_role, {{%qrcode_warehouse}}.permission_create_task')
            ->from('{{%qrcode_warehouse}}')
            ->innerJoin('{{%auth_item}} ON {{%auth_item}}.id_auth_item={{%qrcode_warehouse}}.id_role');
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
//$sql = 'SELECT {{%auth_item}}.name, {{%qrcode_warehouse}}.id_role, {{%qrcode_warehouse}}.permission_create_task
//        FROM {{%qrcode_warehouse}} INNER JOIN {{%auth_item}} ON {{%auth_item}}.id_auth_item={{%qrcode_warehouse}}.id_role
//        ORDER BY name';
//$sqlDataProvider = new SqlDataProvider([
//    'sql' => $sql,
//    'pagination' => [
//        'pageSize' => 20,
//    ],
//]);
//if (!($this->load($params) && $this->validate())) {
//    return $sqlDataProvider;
//}
//return $sqlDataProvider;
