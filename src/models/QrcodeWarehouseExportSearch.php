<?php
namespace prengicomponent\qrcode\models;

use Yii;
use yii\data\ActiveDataProvider;
use yii\base\Model;
use yii\db\Query;
use yii\helpers\HtmlPurifier;

class QrcodeWarehouseExportSearch extends Model
{
    public $filters;
    public $filter_ch_main_obj;

    public function rules()
    {
        return [
            [['filters'], 'safe'],
            [['filter_ch_main_obj'], 'safe'],
        ];
    }

    public function search($params) {
        $query = $query = QrCodefunction::getAccessibleWrhObjectsQuery();
        $id_user = (int)Yii::$app->user->id;
        if (!($this->load($params, '') && $this->validate())) {
            $filter_m_obj = (new Query())
                ->select('id_main_object')
                ->from('{{%qrcode_export_filter}}')
                ->where('{{%qrcode_export_filter}}.user_id=:user_id', array(':user_id' => $id_user))
                ->andWhere('{{%qrcode_export_filter}}.id_qrcode_type=2')
                ->all();
            $filter_obj = (new Query())
                ->select('id_object')
                ->from('{{%qrcode_export_filter}}')
                ->where('{{%qrcode_export_filter}}.user_id=:user_id', array(':user_id' => $id_user))
                ->andWhere('{{%qrcode_export_filter}}.id_qrcode_type=2')
                ->all();
            $filter_cat = (new Query())
                ->select('id_category')
                ->from('{{%qrcode_export_filter}}')
                ->where('{{%qrcode_export_filter}}.user_id=:user_id', array(':user_id' => $id_user))
                ->andWhere('{{%qrcode_export_filter}}.id_qrcode_type=2')
                ->all();
            $obj_list = array();
            $cat_list = array();
            foreach ($filter_obj as $obj) {
                if ($obj['id_object'] != NULL) {
                    array_push($obj_list, $obj['id_object']);
                }
            }
            foreach ($filter_cat as $cat) {
                if ($cat['id_category'] != NULL) {
                    array_push($cat_list, $cat['id_category']);
                }
            }
            if (!empty($obj_list)) {
                $query->andFilterWhere(['{{%objects}}.id_object' => $obj_list]);
            }
            if (!empty($cat_list)) {
                $query->andFilterWhere(['IN', '{{%warehouse_categories}}.category_id', $cat_list]);
            }
            $activeDataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => 100,
                ],
            ]);
            return $activeDataProvider;
        }
        elseif (!empty($this->filter_ch_main_obj || !empty($this->filters))) {
            $this->clearFilters($id_user);
            $sql = '';
            if (!empty($this->filter_ch_main_obj)) {
                foreach ($this->filter_ch_main_obj as $m_obj) {
                    $sql .= "('" . $id_user . "', '2', '" . HtmlPurifier::process($m_obj) . "', NULL, NULL, NULL, NULL) ,";
                }
            }
            if (!empty($this->filters)) {
                if (isset($this->filters['ft_objects'])) {
                    $query->andFilterWhere(['IN', '{{%objects}}.id_object', $this->filters['ft_objects']]);
                    foreach ($this->filters['ft_objects'] as $obj) {
                        $sql .= "('" . $id_user . "', '2', NULL, '" . HtmlPurifier::process($obj) . "', NULL, NULL, NULL) ,";
                    }
                }
                if (isset($this->filters['categories'])) {
                    $query->andFilterWhere(['IN', '{{%warehouse_categories}}.category_id', $this->filters['categories']]);
                    foreach ($this->filters['categories'] as $cat) {
                        $sql .= "('" . $id_user . "', '2', NULL, NULL, NULL, '" . HtmlPurifier::process($cat) . "', NULL) ,";
                    }
                }
            }
//            if (isset($params['ft-select-objects'])) {
//                if (!empty($params['ft-select-objects'])) {
//                    $sql .= "('" . $id_user . "', '2', NULL, NULL, '" . HtmlPurifier::process($params['ft-select-objects']) . "', NULL, NULL) ,";
//                }
//            }
//            if (isset($params['ft-select-categories'])) {
//                if (!empty($params['ft-select-categories'])) {
//                    $sql .= "('" . $id_user . "', '2', NULL, NULL, NULL, NULL, '" . HtmlPurifier::process($params['ft-select-categories']) . "') ,";
//                }
//            }
            YII::$app->db->createCommand("INSERT INTO {{%qrcode_export_filter}}
                            (`user_id`, `id_qrcode_type`, `id_main_object`, `id_object`, `object_str`, `id_category`, `category_str`) VALUES " . substr($sql, 0, -1))
                ->execute();
            $activeDataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => 100,
                ],
            ]);
            return $activeDataProvider;
        }
        $activeDataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 100,
            ],
        ]);
        return $activeDataProvider;
    }

    public function clearFilters($id_user)
    {
        $command = YII::$app->db->createCommand("DELETE FROM  {{%qrcode_export_filter}}
                WHERE {{%qrcode_export_filter}}.user_id=" . $id_user . " and {{%qrcode_export_filter}}.id_qrcode_type=2");
        $command->execute();
    }

    public function getFlObjNames()
    {
        $objects = array();
        $id_user = (int)Yii::$app->user->id;
        $filter_obj = (new Query())
            ->select('id_object')
            ->from('{{%qrcode_export_filter}}')
            ->where('{{%qrcode_export_filter}}.user_id=:user_id', array(':user_id' => $id_user))
            ->andWhere('{{%qrcode_export_filter}}.id_qrcode_type=2')
            ->andWhere('{{%qrcode_export_filter}}.id_object!="NULL"')
            ->all();
        foreach ($filter_obj as $obj) {
            array_push($objects, $obj['id_object']);
        }
        if (!empty($filter_obj)) {
            $query = (new Query())
                ->select('{{%objects}}.name')
                ->from('{{%objects}}')
                ->orFilterWhere(['{{%objects}}.id_object' => $objects])
                ->all();
            $objects = array();
            foreach ($query as $key => $r) {
                array_push($objects, $r['name']);
            }
        }
        else {
            $objects = array();
        }
        return $objects;
    }

    public function getFlMainObj()
    {
        $id_user = (int)Yii::$app->user->id;
        $res = array();
        $fl_main_obj = (new Query())
            ->select('id_main_object')
            ->from('{{%qrcode_export_filter}}')
            ->where('{{%qrcode_export_filter}}.user_id=:user_id', array(':user_id' => $id_user))
            ->andWhere('{{%qrcode_export_filter}}.id_qrcode_type=2')
            ->andWhere('{{%qrcode_export_filter}}.id_main_object!="NULL"')
            ->all();
        foreach ($fl_main_obj as $a){
            array_push($res, $a['id_main_object']);
        }
        return $res;
    }

    public function getFlObj()
    {
        $id_user = (int)Yii::$app->user->id;
        $res = array();
        $fl_obj = (new Query())
            ->select('id_object')
            ->from('{{%qrcode_export_filter}}')
            ->where('{{%qrcode_export_filter}}.user_id=:user_id', array(':user_id' => $id_user))
            ->andWhere('{{%qrcode_export_filter}}.id_qrcode_type=2')
            ->andWhere('{{%qrcode_export_filter}}.id_object!="NULL"')
            ->all();
        foreach ($fl_obj as $a){
            array_push($res, $a['id_object']);
        }
        return $res;
    }

    public function getFlCat()
    {
        $id_user = (int)Yii::$app->user->id;
        $res = array();
        $fl_cat = (new Query())
            ->select('id_category')
            ->from('{{%qrcode_export_filter}}')
            ->where('{{%qrcode_export_filter}}.user_id=:user_id', array(':user_id' => $id_user))
            ->andWhere('{{%qrcode_export_filter}}.id_qrcode_type=2')
            ->andWhere('{{%qrcode_export_filter}}.id_category!="NULL"')
            ->all();
        foreach ($fl_cat as $c){
            array_push($res, $c['id_category']);
        }
        return $res;
    }

    public function getFlCatNames()
    {
        $categories = array();
        $id_user = (int)Yii::$app->user->id;
        $filter_cat = (new Query())
            ->select('id_category')
            ->from('{{%qrcode_export_filter}}')
            ->where('{{%qrcode_export_filter}}.user_id=:user_id', array(':user_id' => $id_user))
            ->andWhere('{{%qrcode_export_filter}}.id_qrcode_type=2')
            ->andWhere('{{%qrcode_export_filter}}.id_category!="NULL"')
            ->all();
        foreach ($filter_cat as $cat) {
            array_push($categories, $cat['id_category']);
        }
        if (!empty($categories)) {
            $query = (new Query())
                ->select('title')
                ->from('{{%warehouse_categories}}')
                ->orFilterWhere(['{{%warehouse_categories}}.category_id' => $categories])
                ->all();
            $categories = array();
            foreach ($query as $key => $r) {
                array_push($categories, $r['title']);
            }
        }
        else $categories = array();
        return $categories;


//        $id_user = (int)Yii::$app->user->id;
//        $filter_cat = (new Query())
//            ->select('category_str')
//            ->from('{{%qrcode_export_filter}}')
//            ->where('{{%qrcode_export_filter}}.user_id=:user_id', array(':user_id' => $id_user))
//            ->andWhere('{{%qrcode_export_filter}}.id_qrcode_type=2')
//            ->andWhere('{{%qrcode_export_filter}}.category_str!="NULL"')
//            ->one();
//        return $filter_cat['category_str'];
    }
}
