<?php
namespace prengicomponent\qrcode\models;

use Yii;
use yii\db\Query;
use yii\helpers\HtmlPurifier;

class QrCodefunction
{
    public static function getChecklists()
    {
        $id_user = Yii::$app->user->id;
        $obj = Yii::$app->objects->Objectslists();
        $objres = [];
        foreach ((array)$obj as $o) {
            $objres[] = $o['id_object'];
        }
        if(Yii::$app->user->identity->admin == 1) {
            $model = YII::$app->db->createCommand("
                SELECT distinct c.id_checklist,c.name
                FROM {{%checklist}} c");
            $lists = $model->queryAll();
        } else {
            if(!empty($objres)) {
                $model = YII::$app->db->createCommand("
                    SELECT distinct c.id_checklist,c.name
                    FROM {{%checklist}} c
                    left join {{%checklist_access_level}} cl on c.id_checklist=cl.id_checklist
                    left join {{%user_access_level}} ual on ual.id_auth_item=cl.id_auth_item
                    WHERE ual.id_user=:id_user and (c.setobj =0 or c.setobj is null or c.setobj in (".implode(',',$objres)."))");
                $lists = $model->bindValues([':id_user'=>$id_user])
                    ->queryAll();
            } else {
                $model = YII::$app->db->createCommand("
                    SELECT distinct c.id_checklist,c.name
                    FROM {{%checklist}} c
                    left join {{%checklist_access_level}} cl on c.id_checklist=cl.id_checklist
                    left join {{%user_access_level}} ual on ual.id_auth_item=cl.id_auth_item
                    WHERE ual.id_user=:id_user");
                $lists = $model->bindValues([':id_user'=>$id_user])
                    ->queryAll();
            }
        }
        $data = array();
        foreach ($lists as $checklist) {
            $data[$checklist['id_checklist']] = $checklist['name'];
        }
        return $data;
    }

    static function getMainObjId($id_object)
    {
        $main_object = (new \yii\db\Query())
            ->select(['id_object_main'])
            ->from('{{%objects}}')
            ->where(['id_object' => $id_object])
            ->one();
        $res = HtmlPurifier::process($main_object['id_object_main']);
        return $res;
    }

    public static function getTasks()
    {
        $tasks = array();
        $id_user = (int)Yii::$app->user->id;
        $model = YII::$app->db->createCommand("SELECT w.id_workflow, w.name, w.limittimefrom, w.limittimeto
                            FROM {{%auth_item}} a, {{%auth_item}} b, {{%user_access_level}} ul, {{%auth_workflow_edit}} we, {{%workflow}} w
                            inner join {{%status_forms}} sform on w.id_workflow = sform.id_workflow
                            inner join {{%status_fields}} sfield on sform.id_status_forms = sfield.id_status_forms
                            where sfield.type = 21 and a.tree=b.tree and a.lft >= b.lft AND a.rgt <= b.rgt and b.id_auth_item=ul.id_auth_item and a.id_auth_item=we.id_auth_item and w.id_workflow=we.id_workflow and  ul.id_user=:id_user
                            ORDER BY a.lft");
        $workflows = $model->bindValues([':id_user' => (int)$id_user])->queryAll();
        foreach ((array)$workflows as $w) {
            if((empty($w['limittimefrom']) && empty($w['limittimeto'])) || (!empty($w['limittimefrom']) && !empty($w['limittimeto']) && strtotime(date('d.m.Y '.$w['limittimefrom'], time()))<time() && strtotime(date('d.m.Y '.$w['limittimeto'], time())) > time())) {
                $tasks[$w['id_workflow']] = HtmlPurifier::process($w['name']);
            }
        }
        return $tasks;
    }

    public static function getWorkflowcreateObjTask()
    {
        $tasks = array();
        $id_user = (int)Yii::$app->user->id;
        $model = YII::$app->db->createCommand("SELECT w.id_workflow, w.name, w.limittimefrom, w.limittimeto
                            FROM {{%auth_item}} a, {{%auth_item}} b, {{%user_access_level}} ul, {{%auth_workflow_edit}} we, {{%workflow}} w
                            inner join {{%status_forms}} sform on w.id_workflow = sform.id_workflow
                            inner join {{%status_fields}} sfield on sform.id_status_forms = sfield.id_status_forms
                            where (w.hidetype IS NULL or w.hidetype!=1 or w.hidetype=0) and a.tree=b.tree and a.lft >= b.lft AND a.rgt <= b.rgt and b.id_auth_item=ul.id_auth_item and a.id_auth_item=we.id_auth_item and w.id_workflow=we.id_workflow and  ul.id_user=:id_user
                            ORDER BY a.lft");
        $workflows = $model->bindValues([':id_user' => (int)$id_user])->queryAll();
        foreach ((array)$workflows as $w) {
            if((empty($w['limittimefrom']) && empty($w['limittimeto'])) || (!empty($w['limittimefrom']) && !empty($w['limittimeto']) && strtotime(date('d.m.Y '.$w['limittimefrom'], time()))<time() && strtotime(date('d.m.Y '.$w['limittimeto'], time())) > time())) {
                $tasks[$w['id_workflow']] = $w['name'];
            }
        }
        return $tasks;
    }

    public static function getWorkflowcreatewithmainObjTask()
    {
        $workflows = array();
        $id_user = (int)Yii::$app->user->id;
        $model = YII::$app->db->createCommand("
            SELECT distinct w.id_workflow, w.name,mwn.id_mainworkflow, mwn.name as namemain, w.limittimefrom, w.limittimeto
            FROM {{%auth_item}} a, {{%auth_item}} b, {{%user_access_level}} ul, {{%auth_workflow_edit}} we, {{%workflow}} w
            left join {{%mainworkflow_workflow}} mw on w.id_workflow=mw.id_workflow
            left join {{%mainworkflow}} mwn on mw.id_mainworkflow=mwn.id_mainworkflow
            where a.tree=b.tree and a.lft >= b.lft AND a.rgt <= b.rgt and b.id_auth_item=ul.id_auth_item and a.id_auth_item=we.id_auth_item
            and w.id_workflow=we.id_workflow and  ul.id_user=:id_user and (w.hidetype IS NULL or w.hidetype!=1 or w.hidetype=0)
            ORDER BY a.lft");
        $workflows = $model->bindValues([':id_user' => (int)$id_user])->queryAll();

        return $workflows;
    }

    public static function getCategories()
    {
        $cat = YII::$app->db->createCommand("
                        SELECT category_id, title, tree, lft, rgt, depth
                        FROM {{%warehouse_categories}}
                        ORDER BY category_id");
        foreach ($cat->queryAll() as $c) {
            $categories[$c['category_id']] = $c;
        }
        return $categories;
    }

    public static function getObjNameById($obj)
    {
        $query = (new Query())
            ->select('name')
            ->from('{{%objects}}')
            ->where('{{%objects}}.id_object=:id_object', array(':id_object' => $obj))
            ->one();
        $res = HtmlPurifier::process($query['name']);
        return $res;
    }

    public static function getInvCategoryById($inv)
    {
        $query = (new Query())
            ->select('{{%warehouse_categories}}.title')
            ->from('{{%warehouse_categories}}')
            ->innerJoin('{{%warehouse_serials}} ON {{%warehouse_serials}}.category_id = {{%warehouse_categories}}.category_id')
            ->where('{{%warehouse_serials}}.serial_id=:serial_id', array(':serial_id' => $inv))
            ->one();
        $res = HtmlPurifier::process($query['title']);
        return $res;
    }

    public static function getInvSerialById($inv)
    {
        $query = (new Query())
            ->select('title')
            ->from('{{%warehouse_serials}}')
            ->where('{{%warehouse_serials}}.serial_id=:serial_id', array(':serial_id' => $inv))
            ->one();
        $res = HtmlPurifier::process($query['title']);
        return $res;
    }

    public static function getInvModelById($inv)
    {
        $query = (new Query())
            ->select('{{%warehouse_items}}.title')
            ->from('{{%warehouse_items}}')
            ->innerJoin('{{%warehouse_serials}} ON {{%warehouse_serials}}.item_id = {{%warehouse_items}}.item_id')
            ->where('{{%warehouse_serials}}.serial_id=:serial_id', array(':serial_id' => $inv))
            ->one();
        $res = HtmlPurifier::process($query['title']);
        return $res;
    }

    public static function getWrhSerialByInvId($inv_id)
    {
        $query = (new \yii\db\Query())
            ->select('id_object, item_id')
            ->from('{{%warehouse_serials}}')
            ->where('serial_id=:s_id', array(':s_id' => $inv_id))
            ->one();
        return $query;
    }

    public static function getAllAccessibleObjects()
    {
        $query = self::getAllAccessibleObjectsQuery();
        $acessible = $query->all();
        $result = array();
        foreach ((array)$acessible as $l) {
            $result[$l['id_object_main']]['id_object_main'] = $l['id_object_main'];
            $result[$l['id_object_main']]['name'] = $l['main_object_name'];
            $result[$l['id_object_main']]['objects'][$l['id_object']] = $l['object_name'];
        }
        return $result;
    }

    public static function getAllAccessibleObjectsQuery()
    {
        $query = new Query;
        $query->select('{{%objects}}.id_object, {{%objects}}.name as object_name, {{%objects_main}}.id_object_main as id_object_main, {{%objects_main}}.name as main_object_name')
            ->distinct()
            ->from('{{%objects}}')
            ->innerJoin('{{%objects_main}} ON {{%objects}}.id_object_main = {{%objects_main}}.id_object_main');
        if(!Yii::$app->core->checkaccess(65, 'objects')) {
            if (Yii::$app->user->identity->admin != 1) {
                $query->join('LEFT JOIN', '{{%objects_auth_item}}', '{{%objects}}.id_object={{%objects_auth_item}}.id_object');
                $query->join('LEFT JOIN', '{{%user_access_level}}', '{{%user_access_level}}.id_auth_item={{%objects_auth_item}}.id_auth_item and {{%user_access_level}}.id_objects_group ={{%objects_auth_item}}.id_objects_group');
                $query->andFilterWhere(['=', '{{%user_access_level}}.id_user', Yii::$app->user->id]);
            }
        }
        $query->join('Left JOIN', '{{%catalog_objects}} as p1', '{{%objects}}.id_object = p1.id_objects and p1.id_catalog = 1');
        $query->join('Left JOIN', '{{%catalog_items}} as it1', 'it1.item_id = p1.id_catalog_items and it1.id_catalog = p1.id_catalog');
        $query->join('Left JOIN', '{{%catalog_objects}} as p2', '{{%objects}}.id_object = p2.id_objects and p2.id_catalog = 5');
        $query->join('Left JOIN', '{{%catalog_items}} as it2', 'it2.item_id = p2.id_catalog_items and it2.id_catalog = p2.id_catalog');
        $query->join('Left JOIN', '{{%catalog_objects}} as p3', '{{%objects}}.id_object = p3.id_objects and p3.id_catalog = 2');
        $query->join('Left JOIN', '{{%catalog_items}} as it3', 'it3.item_id = p3.id_catalog_items and it3.id_catalog = p3.id_catalog');
        $query->join('Left JOIN', '{{%catalog_objects}} as p4', '{{%objects}}.id_object = p4.id_objects and p4.id_catalog = 3');
        $query->join('Left JOIN', '{{%catalog_items}} as it4', 'it4.item_id = p4.id_catalog_items and it4.id_catalog = p4.id_catalog');
        $query->join('Left JOIN', '{{%catalog_objects}} as p5', '{{%objects}}.id_object = p5.id_objects and p5.id_catalog = 4');
        $query->join('Left JOIN', '{{%catalog_items}} as it5', 'it5.item_id = p5.id_catalog_items and it5.id_catalog = p5.id_catalog');
        $query->orderBy('{{%objects}}.id_object DESC');
        return $query;
    }

    public static function getAccessibleWrhObjectsQuery()
    {
        $query = new Query;
        $query->select(['{{%warehouse_categories}}.title as category, {{%warehouse_items}}.title as item, {{%warehouse_serials}}.serial_id, {{%objects_main}}.name as main_object, {{%objects}}.name as object'])
            ->distinct()
            ->from('{{%warehouse_items}}')
            ->innerJoin('{{%warehouse_categories}} ON {{%warehouse_items}}.category_id = {{%warehouse_categories}}.category_id')
            ->innerJoin('{{%warehouse_serials}} ON {{%warehouse_items}}.item_id = {{%warehouse_serials}}.item_id')
            ->innerJoin('{{%objects}} ON {{%warehouse_serials}}.id_object = {{%objects}}.id_object')
            ->innerJoin('{{%objects_main}} ON {{%objects}}.id_object_main = {{%objects_main}}.id_object_main');
        if(!Yii::$app->core->checkaccess(65, 'objects')) {
            if (Yii::$app->user->identity->admin != 1) {
                $query->join('LEFT JOIN', '{{%objects_auth_item}}', '{{%objects}}.id_object={{%objects_auth_item}}.id_object');
                $query->join('LEFT JOIN', '{{%user_access_level}}', '{{%user_access_level}}.id_auth_item={{%objects_auth_item}}.id_auth_item and {{%user_access_level}}.id_objects_group ={{%objects_auth_item}}.id_objects_group');
                $query->andFilterWhere(['=', '{{%user_access_level}}.id_user', Yii::$app->user->id]);
            }
        }
        $query->join('Left JOIN', '{{%catalog_objects}} as p1', '{{%objects}}.id_object = p1.id_objects and p1.id_catalog = 1');
        $query->join('Left JOIN', '{{%catalog_items}} as it1', 'it1.item_id = p1.id_catalog_items and it1.id_catalog = p1.id_catalog');
        $query->join('Left JOIN', '{{%catalog_objects}} as p2', '{{%objects}}.id_object = p2.id_objects and p2.id_catalog = 5');
        $query->join('Left JOIN', '{{%catalog_items}} as it2', 'it2.item_id = p2.id_catalog_items and it2.id_catalog = p2.id_catalog');
        $query->join('Left JOIN', '{{%catalog_objects}} as p3', '{{%objects}}.id_object = p3.id_objects and p3.id_catalog = 2');
        $query->join('Left JOIN', '{{%catalog_items}} as it3', 'it3.item_id = p3.id_catalog_items and it3.id_catalog = p3.id_catalog');
        $query->join('Left JOIN', '{{%catalog_objects}} as p4', '{{%objects}}.id_object = p4.id_objects and p4.id_catalog = 3');
        $query->join('Left JOIN', '{{%catalog_items}} as it4', 'it4.item_id = p4.id_catalog_items and it4.id_catalog = p4.id_catalog');
        $query->join('Left JOIN', '{{%catalog_objects}} as p5', '{{%objects}}.id_object = p5.id_objects and p5.id_catalog = 4');
        $query->join('Left JOIN', '{{%catalog_items}} as it5', 'it5.item_id = p5.id_catalog_items and it5.id_catalog = p5.id_catalog');
        $query->orderBy('{{%objects}}.id_object DESC');
        return $query;
    }

    public static function getMainCategories($categories)
    {
        $m_categories = array();
        foreach ($categories as $category) {
            if ($category['tree'] == $category['category_id']) {
                array_push($m_categories, $category);
            }
        }
        return $m_categories;
    }


    public static function getBetweenLftAndRgt($categories_all, $tree, $lft, $rgt)
    {
        $result = array();
        foreach ($categories_all as $category) {
            if ($category['tree'] == $tree && $category['lft'] > $lft && $category['rgt'] < $rgt) {
                array_push($result, $category);
            }
        }
        return $result;
    }

    public static function getSubCategories($main_categories)
    {
        $result = array();
        $query = YII::$app->db->createCommand("
                        SELECT category_id, tree, lft, rgt
                        FROM {{%warehouse_categories}}
                        ORDER BY category_id");
        $categories_all = $query->queryAll();
        foreach ($categories_all as $c) {
            $result[$c['category_id']] = array();
        }
        foreach ($main_categories as $main_category) {
            $subs = self::getBetweenLftAndRgt($categories_all, $main_category['tree'], $main_category['lft'], $main_category['rgt']);
            foreach ($subs as $s) {
                array_push($result[$main_category['category_id']], $s);
            }
            $categories = array();
            $cat = YII::$app->db->createCommand("
                        SELECT category_id, tree, lft, rgt
                        FROM {{%warehouse_categories}}
                        WHERE {{%warehouse_categories}}.tree = :tree
                        AND {{%warehouse_categories}}.tree != {{%warehouse_categories}}.category_id
                        ORDER BY category_id");
            $cat->bindValues([':tree' => (int)$main_category['tree']]);
            foreach ($cat->queryAll() as $c) {
                $categories[$c['category_id']] = $c;
            }
            foreach ($categories as $category) {
                $subs = self::getBetweenLftAndRgt($categories_all, $category['tree'], $category['lft'], $category['rgt']);
                foreach ($subs as $s) {
                    array_push($result[$category['category_id']], $s);
                }
            }
        }
        return $result;
    }
}
