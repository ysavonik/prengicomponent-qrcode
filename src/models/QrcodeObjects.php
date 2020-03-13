<?php
namespace prengicomponent\qrcode\models;

use Yii;
use prengicomponent\qrcode\Module;
use yii\rbac\Role;

class QrcodeObjects extends \yii\db\ActiveRecord
{

    public function getUnusedIds() {
        $add_model = array(new QrcodeAddModel);
        $all_roles = Yii::$app->db->createCommand("SELECT id_auth_item 
            FROM {{%auth_item}}")
            ->queryAll();
        $obj_roles = Yii::$app->db->createCommand("SELECT id_role 
            FROM {{%qrcode_objects}}")
            ->queryAll();
        $unused_roles = array();
        foreach ($all_roles as $r) {
            $flag = false;
            foreach ($obj_roles as $o_r) {
                if ($r['id_auth_item'] == $o_r['id_role']) {
                    $flag = true;
                    break;
                }
            }
            if (!$flag) {
                $role = Yii::$app->db->createCommand("SELECT name 
                    FROM {{%auth_item}} 
                    WHERE id_auth_item=:id_auth_item")
                    ->bindValue(':id_auth_item', $r['id_auth_item'])
                    ->queryOne();
                array_push($add_model, [
                    'role_id' => $r['id_auth_item'],
                    'role_name' => $role['name'],
                ]);
                array_push($unused_roles, $r['id_auth_item']);
            }
        }
        return $unused_roles;
    }

    public function getUnusedNames() {
        $add_model = array(new QrcodeAddModel);
        $all_roles = Yii::$app->db->createCommand("SELECT id_auth_item 
            FROM {{%auth_item}}")
            ->queryAll();
        $obj_roles = Yii::$app->db->createCommand("SELECT id_role 
            FROM {{%qrcode_objects}}")
            ->queryAll();
        $unused_names = array();
        foreach ($all_roles as $r) {
            $flag = false;
            foreach ($obj_roles as $o_r) {
                if ($r['id_auth_item'] == $o_r['id_role']) {
                    $flag = true;
                    break;
                }
            }
            if (!$flag) {
                $role = Yii::$app->db->createCommand("SELECT description 
                    FROM {{%auth_item}} 
                    WHERE id_auth_item=:id_auth_item")
                    ->bindValue(':id_auth_item', $r['id_auth_item'])
                    ->queryOne();
                array_push($add_model, [
                    'role_id' => $r['id_auth_item'],
                    'role_description' => $role['description'],
                ]);
                array_push($unused_names, $role['description']);
            }
        }
        return $unused_names;
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
