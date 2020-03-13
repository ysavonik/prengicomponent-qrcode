<?php
namespace prengicomponent\qrcode\controllers;

use prengicomponent\qrcode\models\QrcodeObjectsExportSearch;
use prengicomponent\qrcode\models\QrcodeWarehouseExportSearch;
use tFPDF;
use prengicomponent\qrcode\models\QrcodeObjectsSearch;
use Yii;
use prengicomponent\qrcode\models\QrCodefunction;
use yii\base\ErrorException;
use yii\db\Exception;
use yii\filters\AccessControl;
use prengicomponent\qrcode\models\Qrcode;
use prengicomponent\qrcode\models\QrcodeSearch;
use prengicomponent\qrcode\models\QrcodeObjects;
use prengicomponent\qrcode\models\QrcodeWarehouse;
use prengicomponent\qrcode\models\QrcodeWarehouseSearch;
use yii\helpers\ArrayHelper;
use yii\helpers\HtmlPurifier;
use yii\helpers\Url;
use yii\web\ForbiddenHttpException;
use prengicomponent\qrcode\Module;

class QrcodeController extends \yii\web\Controller
{

    public function beforeAction($action)
    {
        return parent::beforeAction($action);
    }

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    ['allow' => true, 'actions' => ['index'], 'roles' => ['153:qrcode']],
                    ['allow' => true, 'actions' => ['category'], 'roles' => ['153:qrcode']],
                    ['allow' => true, 'actions' => ['type'], 'roles' => ['153:qrcode']],
                    ['allow' => true, 'actions' => ['process'], 'roles' => ['153:qrcode']],
                    ['allow' => true, 'actions' => ['objectsave'], 'roles' => ['153:qrcode']],
                    ['allow' => true, 'actions' => ['warehousesave'], 'roles' => ['153:qrcode']],
                    ['allow' => true, 'actions' => ['add'], 'roles' => ['153:qrcode']],
                    ['allow' => true, 'actions' => ['print'], 'roles' => ['153:qrcode']],
                    ['allow' => true, 'actions' => ['export'], 'roles' => ['153:qrcode']],
                    ['allow' => true, 'actions' => ['createtask'], 'roles' => ['@']],
                    ['allow' => true, 'actions' => ['openchecklist'], 'roles' => ['@']],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $model = new Qrcode();
        $searchModel = new QrcodeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('/qrcode/index', [
            'module' => $this->module,
            'dataProvider' => $dataProvider,
            'model' => $model,
        ]);
    }

    public function actionType($id)
    {
        $st = Yii::$app->request->post('action');
        $action = HtmlPurifier::process($st);
        $st = Yii::$app->request->post('id_role');
        $id_role = HtmlPurifier::process($st);
        if($action == 'delete_role') {
            if ($id == 1) {
                $modeldel = QrcodeObjects::findOne(['id_role' => $id_role]);
                if ($modeldel !== null) {
                    $modeldel->delete();
                }
            }
            if ($id == 2){
                $modeldel = QrcodeWarehouse::findOne(['id_role' => $id_role]);
                if ($modeldel !== null) {
                    $modeldel->delete();
                }
            }
        }
        $qrcode = Qrcode::find()->where(['id_qrcode' => $id])->one();
        if ($id == 1) {
            $type_model = new QrcodeObjects();
            $searchModel = new QrcodeObjectsSearch();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        }
        elseif ($id == 2) {
            $type_model = new QrcodeWarehouse();
            $searchModel = new QrcodeWarehouseSearch();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        }
        else {
            throw new ForbiddenHttpException();
        }
        return $this->render('/qrcode/type',[
            'module' => $this->module,
            'type' => $qrcode['type'],
            'model' => $type_model,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'type_id' => $id,
        ]);
    }

    public function actionObjectsave($id, $p)
    {
        $id_pure = HtmlPurifier::process($id);
        $obj = QrcodeObjects::find()->where(['id_role' => $id_pure])->one();
        $p_pure = HtmlPurifier::process($p);
        if ($obj[$p_pure]) {
            $obj[$p_pure] = false;
        } else {
            $obj[$p_pure] = true;
        }
        $obj->save();
    }

    public function actionWarehousesave($id, $p)
    {
        $id_pure = HtmlPurifier::process($id);
        $inv = QrcodeWarehouse::find()->where(['id_role' => $id_pure])->one();
        $p_pure = HtmlPurifier::process($p);
        if ($inv[$p_pure]) {
            $inv[$p_pure] = false;
        } else {
            $inv[$p_pure] = true;
        }
        $inv->save();
    }

    public function actionProcess($type, $id_object=0, $id_inventory=0)
    {
        $usersroles = Yii::$app->users->RolesCurentUser();
        $tasks = array();
        $permissions = array();
        $checklists = array();
        if ($type == 1) {
            foreach($usersroles as $role) {
                $flag = false;
                $role_perm = QrcodeObjects::find()->where(['id_role' => $role['id_auth_item']])->one();
                if ($role_perm['permission_open'] == true) {
                    foreach ($permissions as $p) {
                        if ($p['name'] == 'Open Object') {
                            $flag = true;
                        }
                    }
                    if (!$flag) {
                        $id_main_object = QrCodefunction::getMainObjId($id_object);
                        array_push($permissions, [
                            'name'=>'Open Object',
                            'url'=> '/objects/info/' . $id_object . '?id_main_object=' . $id_main_object,
                        ]);
                    }
                }
                if ($role_perm['permission_checklist'] == true) {
                    foreach ($permissions as $p) {
                        if ($p['name'] == 'Open Checklist') {
                            $flag =true;
                        }
                    }
                    if (!$flag) {
                        $checklists = QrCodefunction::getChecklists();
                        if (!empty($checklists)) {
                            array_push($permissions, [
                                'name' => 'Open Checklist',
                                'id_object' => $id_object,
                            ]);
                        }
                    }
                }
                if ($role_perm['permission_create_task'] == true) {
                    foreach ($permissions as $p) {
                        if ($p['name'] == 'Create task on object') {
                            $flag = true;
                        }
                    }
                    if (!$flag) {
                        array_push($permissions, [
                            'name'=>'Create task on object',
                            'url'=>'/items/index?action_get=createitemfromqr',
                            //action_get=createitemfromqr&id_object=' . $id_object . '&typeqr=' . $type,
                        ]);
                    }
                }
            }
        }
        elseif ($type == 2) {
            foreach($usersroles as $role) {
                $flag = false;
                $role_perm = QrcodeWarehouse::find()->where(['id_role' => $role['id_auth_item']])->one();
                if ($role_perm['permission_create_task'] == true) {
                    $tasks = QrCodefunction::getTasks();
                    if (!empty($tasks)) {
                        foreach ($permissions as $p) {
                            if ($p['name'] == 'Create task on inventory') {
                                $flag = true;
                            }
                        }
                        if (!$flag) {
                            array_push($permissions, [
                                'name' => 'Create task on inventory',
                                'url' => '/warehouse/id_main_object=' . $id_object,
                            ]);
                        }
                    }
                }
            }
        }
        else {
            throw new ForbiddenHttpException();
        }
        $wrh = QrCodefunction::getWrhSerialByInvId((int)$id_inventory);
        $workflows = QrCodefunction::getWorkflowcreateObjTask();
        $mainworkflows = QrCodefunction::getWorkflowcreatewithmainObjTask();
        $mainworkflowslist=array();
        $mainworkflowslistdata=array();
        foreach ((array)$mainworkflows as $m){
            if($m['namemain']!=''){
                $mainworkflowslist[$m['id_mainworkflow']]=$m['namemain'];
                if((empty($m['limittimefrom'])&&empty($m['limittimeto']))||(!empty($m['limittimefrom'])&&!empty($m['limittimeto'])&&strtotime(date('d.m.Y '.$m['limittimefrom'], time()))<time()&&strtotime(date('d.m.Y '.$m['limittimeto'], time()))>time())) {
                    $mainworkflowslistdata[] = array(
                        'id_mainworkflow' => $m['id_mainworkflow'],
                        'id_workflow' => $m['id_workflow'],
                        'name' => $m['name']
                    );
                }
            }
        }
        $mainworkflowslistdata=json_encode($mainworkflowslistdata);
        return $this->render('process', [
            'module' => $this->module,
            'type' => $type,
            'permissions' => $permissions,
            'header' => 'Qrcode has been scanned succesfully!',
            'description' => 'Choose action:',
            'tasks' => $tasks,
            'wrh_id_serial' => (int)$id_inventory,
            'wrh_id_element_warhouse' => (int)$wrh['item_id'],
            'wrh_id_object' => (int)$wrh['id_object'],
            'workflows' => $workflows,
            'mainworkflowslist' => $mainworkflowslist,
            'mainworkflowslistdata' => $mainworkflowslistdata,
            'inv_id' => $id_inventory,
            'id_object' => $id_object,
            'checklists' => $checklists,
        ]);
    }

    public function actionAdd($type)
    {
        $formData = Yii::$app->request->post();
        $qrcode = Qrcode::find()->where(['type' => $type])->one();
        $id = $qrcode['id_qrcode'];
        if ($type == 'Objects') {
            $obj = new QrcodeObjects();
            if (!array_key_exists('id_role', $formData['QrcodeObjects'])) return $this->redirect(['type/' . $id]);
            $unused_ids = $obj->getUnusedIds();
            $pure_id = HtmlPurifier::process($formData['QrcodeObjects']['id_role']);
            $obj_id = $unused_ids[$pure_id];
            if ($obj['id_role'] = $obj_id) {
                if(isset($formData['QrcodeObjects']['permission_open'])) {
                    $obj['permission_open'] = HtmlPurifier::process($formData['QrcodeObjects']['permission_open']);
                } else {
                    $obj['permission_open'] = false;
                }
                if(isset($formData['QrcodeObjects']['permission_checklist'])) {
                    $obj['permission_checklist'] = HtmlPurifier::process($formData['QrcodeObjects']['permission_checklist']);
                } else {
                    $obj['permission_checklist'] = false;
                }
                if(isset($formData['QrcodeObjects']['permission_create_task'])) {
                    $obj['permission_create_task'] = HtmlPurifier::process($formData['QrcodeObjects']['permission_create_task']);
                } else {
                    $obj['permission_create_task'] = false;
                }
                $obj->save();
                return $this->redirect(['type/' . $id]);
            }
        }
        if ($type == 'Warehouse') {
            $inv = new QrcodeWarehouse();
            if (!array_key_exists('id_role', $formData['QrcodeWarehouse'])) return $this->redirect(['type/' . $id]);
            $unused_ids = $inv->getUnusedIds();
            $pure_id = HtmlPurifier::process($formData['QrcodeWarehouse']['id_role']);
            $inv_id = $unused_ids[$pure_id];
            if ($inv['id_role'] = $inv_id) {
                if(isset($formData['QrcodeWarehouse']['permission_create_task'])) {
                    $inv['permission_create_task'] = HtmlPurifier::process($formData['QrcodeWarehouse']['permission_create_task']);
                    $inv->save();
                    return $this->redirect(['type/' . $id]);
                } else {
                    $inv['permission_create_task'] = false;
                    $inv->save();
                    return $this->redirect(['type/' . $id]);
                }
            }
        }
        return $this->redirect(['type/' . $id]);
    }

    public function actionPrint($id)
    {
        $qrcode = Qrcode::find()->where(['id_qrcode' => $id])->one();
        $sub_cats = array();
        $inventory_serials = array();
        $objects = QrCodefunction::getAllAccessibleObjects();
        if ($id == 1) {
            $sizes = [
                0 => Module::t('app', 'Choose size of Qrcodes'),
                300 => 'L',
                400 => 'XL',
                500 => 'XXL'
            ];
            $fl_main_obj = array();
            $fl_obj = array();
            $fl_obj_names = array();
            $object_ids = array();
            $searchModel = new QrcodeObjectsExportSearch();
            $post = Yii::$app->request->post();
            if (isset($post['action'])) {
                if ($post['action'] == 'clearfilter') {
                    $searchModel->clearFilters((int)Yii::$app->user->id);
                }
            }
            $dataProvider = $searchModel->search($post);
            if (isset($post['filter_ch_main_obj'])) {
                foreach ($post['filter_ch_main_obj'] as $m) {
                    array_push($fl_main_obj, $m);
                }
            }

            foreach (array_keys($post) as $filters) {
                if ($filters == 'filters') {
                    foreach ($post['filters'] as $key => $b) {
                        if ($key == 'ft_objects') {
                            $fl_obj = $b;
                        }
                    }
                }
                if ($filters == 'ft-select-objects') {
                    array_push($fl_obj_names, $post['ft-select-objects']);
                }
            }
            if (empty($fl_main_obj)) {
                $fl_main_obj = $searchModel->getFlMainObj();
            }
            if (empty($fl_obj)) {
                $fl_obj = $searchModel->getFlObj();
            }
            if (empty($fl_obj_names)) {
                $fl_obj_names = $searchModel->getFlObjNames();
            }
            foreach ($dataProvider->query->all() as $el){
                array_push($object_ids, $el['id_object']);
            }
            return $this->render('/qrcode/print', [
                'module' => $this->module,
                'type' => $qrcode['type'],
                'id' => $id,
                'dataProvider' => $dataProvider,
                'objects' => $objects,
                'fl_obj' => $fl_obj,
                'fl_main_obj' => $fl_main_obj,
                'resval_obj' => $fl_obj_names,
                'sub_cats' => $sub_cats,
                'inventory_serials' => $inventory_serials,
                'object_ids' => $object_ids,
                'sizes' => $sizes,
            ]);
        }
        elseif ($id == 2) {
            $sizes = [
                0 => Module::t('app', 'Choose size of Qrcodes'),
                100 =>'XS',
                150 => 'S',
                200 => 'M',
                300 => 'L',
                400 => 'XL',
                500 => 'XXL'
            ];
            $fl_main_obj = array();
            $fl_obj_names = array();
            $fl_cat_names = array();
            $fl_cat = array();
            $fl_obj = array();
            $object_ids = array();
            $searchModel = new QrcodeWarehouseExportSearch();
            $post = Yii::$app->request->post();
            if (isset($post['action'])) {
                if ($post['action'] == 'clearfilter') {
                    $searchModel->clearFilters((int)Yii::$app->user->id);
                }
            }
            $dataProvider = $searchModel->search($post);
            if (isset($post['filter-ch-main-obj'])) {
                foreach ($post['filter-ch-main-obj'] as $m) {
                    array_push($fl_main_obj, $m);
                }
            }
            foreach (array_keys($post) as $filters) {
                if ($filters == 'filters') {
                    foreach ($post['filters'] as $key => $b) {
                        if ($key == 'categories') {
                            $fl_cat = $b;
                        }
                        if ($key == 'ft_objects') {
                            $fl_obj = $b;
                        }
                    }
                }
            }
            $categories = QrCodefunction::getCategories();
            $m_categories = QrCodefunction::getMainCategories($categories);
            $sub_cats = QrCodefunction::getSubCategories($m_categories);
            foreach ($m_categories as $m_c) {
                unset($categories[$m_c['category_id']]);
            }
            ArrayHelper::multisort($categories, ['tree', 'lft'], [SORT_ASC, SORT_ASC]);
            if (empty($fl_main_obj)) {
                $fl_main_obj = $searchModel->getFlMainObj();
            }
            if (empty($fl_obj)) {
                $fl_obj = $searchModel->getFlObj();
            }
            if (empty($fl_obj_names)) {
                $fl_obj_names = $searchModel->getFlObjNames();
            }
            if (empty($fl_cat)) {
                $fl_cat = $searchModel->getFlCat();
            }
            if (empty($fl_cat_names)) {
                $fl_cat_names = $searchModel->getFlCatNames();
            }
            foreach ($dataProvider->query->all() as $el) {
                array_push($inventory_serials, $el['serial_id']);
            }
            return $this->render('/qrcode/print', [
                'module' => $this->module,
                'type' => $qrcode['type'],
                'id' => $id,
                'dataProvider' => $dataProvider,
                'objects' => $objects,
                'fl_obj' => $fl_obj,
                'fl_main_obj' => $fl_main_obj,
                'resval_obj' => $fl_obj_names,
                'resval_cat' => $fl_cat_names,
                'fl_cat' => $fl_cat,
                'm_categories' => $m_categories,
                'sub_cats' => $sub_cats,
                'categories' => $categories,
                'object_ids' => $object_ids,
                'inventory_serials' => $inventory_serials,
                'sizes' => $sizes,
            ]);
        }
        else {
            throw new ForbiddenHttpException();
        }
    }

    public function actionExport($id, $size, $object_ids_str='', $inventory_serials_str='')
    {
        $object_ids = array_map('intval', explode(',', $object_ids_str));
        array_pop($object_ids);
        $inventory_serials = array_map('intval', explode(',', $inventory_serials_str));
        array_pop($inventory_serials);
        date_default_timezone_set('Europe/Kiev');
        $domain = Url::base('https');
        $path = Yii::getAlias('@runtime');
        $pdf_filename = $path . '/' ;
        $text_high = 6;
        $board = 8;
        $size = HtmlPurifier::process($size);
        if ($size == 100) {
            $text_high = 6;
            $pdf_filename .= 'XS_';
        }
        if ($size == 150) {
            $text_high = 6;
            $pdf_filename .= 'S_';
        }
        if ($size == 200) {
            $text_high = 6;
            $pdf_filename .= 'M_';
        }
        if ($size == 300) {
            $text_high = 6;
            $pdf_filename .= 'L_';
        }
        if ($size == 400) {
            $text_high = 6;
            $pdf_filename .= 'XL_';
        }
        if ($size == 500) {
            $text_high = 6;
            $pdf_filename .= 'XXL_';
        }
        if ($id == 1) {
            $pdf = new tFPDF('P', 'pt', array($size * 0.75 + $text_high, $size * 0.75 + $text_high));
        }
        if ($id == 2) {
            $pdf = new tFPDF('P', 'pt', array($size * 0.75 + 3 * $text_high, $size * 0.75 + 3 * $text_high));
        }
        $pdf->AddFont('DejaVu','','DejaVuSansCondensed.ttf',true);
        $pdf->SetFont('DejaVu', '', 6);
        if ($size == 100) {
            $pdf->SetFont('DejaVu', '', 6);
        }
        if ($size == 150) {
            $pdf->SetFont('DejaVu', '', 6);
        }
        if ($size == 200) {
            $pdf->SetFont('DejaVu', '', 6);
        }
        if ($size == 300) {
            $pdf->SetFont('DejaVu', '', 6);
        }
        if ($size == 400) {
            $pdf->SetFont('DejaVu', '', 6);
        }
        if ($size == 500) {
            $pdf->SetFont('DejaVu', '', 6);
        }
        if ($id == 1) {
            $pdf_filename .= 'obj_' . date('Y-m-d_H-i-s');
            foreach ($object_ids as $obj) {
                $pdf->AddPage();
                try {
                    $pdf->Image('https://chart.googleapis.com/chart?cht=qr&chs=' . $size . 'x' . $size . '&chl=' . $domain . '/qrcode/process%3Ftype%3D' . $id . '%26id_object%3D' . $obj . '&chld=H|0',$board + $text_high * 0.5, $board,$size * 0.75 - $board * 2,0,'PNG');
                }
                catch (ErrorException $errorException) {
                    try {
                        $pdf->Image('https://chart.googleapis.com/chart?cht=qr&chs=' . $size . 'x' . $size . '&chl=' . $domain . '/qrcode/process%3Ftype%3D' . $id . '%26id_object%3D' . $obj . '&chld=H|0',$board + $text_high * 0.5, $board,$size * 0.75 - $board * 2,0,'PNG');
                    }
                    catch (ErrorException $errorException) {

                    }
                }
                $obj_name = QrCodefunction::getObjNameById($obj);
                if ($pdf->GetStringWidth($obj_name) <= $size * 0.75 - $board * 2) {
                    $pdf->Text(($size * 0.75 + $text_high) / 2 - $pdf->GetStringWidth($obj_name) / 2, $size * 0.75 + $text_high - $board, $obj_name);
                }
                else {
                    while ($pdf->GetStringWidth($obj_name) > $size * 0.75 - $board * 2) {
                        $obj_name = substr($obj_name,0,-1);
                    }
                    $pdf->Text(($size * 0.75 + $text_high) / 2 - $pdf->GetStringWidth($obj_name) / 2, $size * 0.75 + $text_high - $board, $obj_name);
                }

            }
            $pdf_filename = substr_replace($pdf_filename ,"", -1);
        }
        elseif ($id == 2) {
            $pdf_filename .= 'inv_' . date('Y-m-d_H-i-s');
            foreach ($inventory_serials as $inv) {
                $pdf->AddPage();
                try {
                    $pdf->Image('https://chart.googleapis.com/chart?cht=qr&chs=' . $size . 'x' . $size . '&chl=' . $domain . '/qrcode/process%3Ftype%3D' . $id . '%26id_inventory%3D' . $inv . '&chld=H|0',$board + $text_high * 1.5, $board,$size * 0.75 - $board * 2,0,'PNG');
                }
                catch (ErrorException $errorException) {
                    try {
                        $pdf->Image('https://chart.googleapis.com/chart?cht=qr&chs=' . $size . 'x' . $size . '&chl=' . $domain . '/qrcode/process%3Ftype%3D' . $id . '%26id_inventory%3D' . $inv . '&chld=H|0',$board + $text_high * 1.5, $board,$size * 0.75 - $board * 2,0,'PNG');
                    }
                    catch (ErrorException $errorException){

                    }
                }
                $inv_ser = QrCodefunction::getInvSerialById($inv);
                $inv_cat = QrCodefunction::getInvCategoryById($inv);
                $inv_model = QrCodefunction::getInvModelById($inv);
                if ($pdf->GetStringWidth($inv_ser) <= $size * 0.75 - $board * 2) {
                    $pdf->Text(($size * 0.75 + 3 * $text_high) / 2 - $pdf->GetStringWidth($inv_ser) / 2, $size * 0.75 + $text_high - $board, $inv_ser);
                    $pdf->Text(($size * 0.75 + 3 * $text_high) / 2 - $pdf->GetStringWidth($inv_cat) / 2, $size * 0.75 + 2 * $text_high - $board, $inv_cat);
                    $pdf->Text(($size * 0.75 + 3 * $text_high) / 2 - $pdf->GetStringWidth($inv_model) / 2, $size * 0.75 + 3 * $text_high - $board, $inv_model);
                }
                elseif ($pdf->GetStringWidth($inv_ser) + $pdf->GetStringWidth($inv_cat) + 2 <= 2 * ($size * 0.75 - $board * 2)) {
                    $inv_ser_1_line = $inv_ser;
                    $inv_ser_2_line = $inv_ser;
                    while ($pdf->GetStringWidth($inv_ser_1_line) > $size * 0.75 - $board * 2) {
                        $inv_ser_1_line = substr($inv_ser_1_line,0,-1);
                    }
                    $inv_ser_2_line = substr($inv_ser_2_line, strlen($inv_ser_1_line));
                    $pdf->Text(($size * 0.75 + 3 * $text_high) / 2 - $pdf->GetStringWidth($inv_ser_1_line) / 2, $size * 0.75 + $text_high - $board, $inv_ser_1_line);
                    if ($pdf->GetStringWidth($inv_ser_2_line) + $pdf->GetStringWidth($inv_cat) + 2 <= $size * 0.75 - $board * 2) {
                        $line_2 = $inv_ser_2_line . ' ' . $inv_cat;
                        $pdf->Text(($size * 0.75 + 3 * $text_high) / 2 - $pdf->GetStringWidth($line_2) / 2, $size * 0.75 + 2 * $text_high - $board, $line_2);
                    }
                    else {
                        $pdf->Text(($size * 0.75 + 3 * $text_high) / 2 - $pdf->GetStringWidth($inv_ser_2_line) / 2, $size * 0.75 + 2 * $text_high - $board, $inv_ser_2_line);
                    }
                    $pdf->Text(($size * 0.75 + 3 * $text_high) / 2 - $pdf->GetStringWidth($inv_model) / 2, $size * 0.75 + 3 * $text_high - $board, $inv_model);
                }
                elseif ($pdf->GetStringWidth($inv_ser) <= ($size * 0.75 - $board * 2) * 2) {
                    $inv_ser_1_line = $inv_ser;
                    $inv_ser_2_line = $inv_ser;
                    while ($pdf->GetStringWidth($inv_ser_1_line) > $size * 0.75 - $board * 2) {
                        $inv_ser_1_line = substr($inv_ser_1_line,0,-1);
                    }
                    $inv_ser_2_line = substr($inv_ser_2_line, strlen($inv_ser_1_line));
                    $pdf->Text(($size * 0.75 + 3 * $text_high) / 2 - $pdf->GetStringWidth($inv_ser_1_line) / 2, $size * 0.75 + $text_high - $board, $inv_ser_1_line);
                    $pdf->Text(($size * 0.75 + 3 * $text_high) / 2 - $pdf->GetStringWidth($inv_ser_2_line) / 2, $size * 0.75 + 2 * $text_high - $board, $inv_ser_2_line);
                    if ($pdf->GetStringWidth($inv_cat)  + $pdf->GetStringWidth($inv_model) + 2 <= $size * 0.75 - $board * 2) {
                        $line_3 = $inv_cat . ' ' . $inv_model;
                        $pdf->Text(($size * 0.75 + 3 * $text_high) / 2 - $pdf->GetStringWidth($line_3) / 2, $size * 0.75 + 3 * $text_high - $board, $line_3);
                    }
                    elseif ($pdf->GetStringWidth($inv_model) + 2 <= $size * 0.75 - $board * 2) {
                        $pdf->Text(($size * 0.75 + 3 * $text_high) / 2 - $pdf->GetStringWidth($inv_model) / 2, $size * 0.75 + 3 * $text_high - $board, $inv_model);
                    }
                }
                elseif ($pdf->GetStringWidth($inv_ser) + $pdf->GetStringWidth($inv_cat) + $pdf->GetStringWidth($inv_model) + 4 <= ($size * 0.75 - $board * 2) * 3) {
                    $inv_ser_1_line = $inv_ser;
                    $inv_ser_2_line = $inv_ser;
                    $inv_ser_3_line = $inv_ser;
                    while ($pdf->GetStringWidth($inv_ser_1_line) > $size * 0.75 - $board * 2) {
                        $inv_ser_1_line = substr($inv_ser_1_line,0,-1);
                    }
                    $inv_ser_2_line = substr($inv_ser_2_line, strlen($inv_ser_1_line));
                    while ($pdf->GetStringWidth($inv_ser_2_line) > $size * 0.75 - $board * 2) {
                        $inv_ser_2_line = substr($inv_ser_2_line,0,-1);
                    }
                    $inv_ser_3_line = substr($inv_ser_3_line, strlen($inv_ser_1_line) + strlen($inv_ser_2_line));
                    $inv_ser_3_line = $inv_ser_3_line . ' ' . $inv_cat . ' ' . $inv_model;
                    $pdf->Text(($size * 0.75 + 3 * $text_high) / 2 - $pdf->GetStringWidth($inv_ser_1_line) / 2, $size * 0.75 + $text_high - $board, $inv_ser_1_line);
                    $pdf->Text(($size * 0.75 + 3 * $text_high) / 2 - $pdf->GetStringWidth($inv_ser_2_line) / 2, $size * 0.75 + 2 * $text_high - $board, $inv_ser_2_line);
                    $pdf->Text(($size * 0.75 + 3 * $text_high) / 2 - $pdf->GetStringWidth($inv_ser_3_line) / 2, $size * 0.75 + 3 * $text_high - $board, $inv_ser_3_line);
                }
                elseif ($pdf->GetStringWidth($inv_ser) + $pdf->GetStringWidth($inv_cat) + 2 <= ($size * 0.75 - $board * 2) * 3) {
                    $inv_ser_1_line = $inv_ser;
                    $inv_ser_2_line = $inv_ser;
                    $inv_ser_3_line = $inv_ser;
                    while ($pdf->GetStringWidth($inv_ser_1_line) > $size * 0.75 - $board * 2) {
                        $inv_ser_1_line = substr($inv_ser_1_line,0,-1);
                    }
                    $inv_ser_2_line = substr($inv_ser_2_line, strlen($inv_ser_1_line));
                    while ($pdf->GetStringWidth($inv_ser_2_line) > $size * 0.75 - $board * 2) {
                        $inv_ser_2_line = substr($inv_ser_2_line,0,-1);
                    }
                    $inv_ser_3_line = substr($inv_ser_3_line, strlen($inv_ser_1_line) + strlen($inv_ser_2_line));
                    $inv_ser_3_line = $inv_ser_3_line . ' ' . $inv_cat;
                    $pdf->Text(($size * 0.75 + 3 * $text_high) / 2 - $pdf->GetStringWidth($inv_ser_1_line) / 2, $size * 0.75 + $text_high - $board, $inv_ser_1_line);
                    $pdf->Text(($size * 0.75 + 3 * $text_high) / 2 - $pdf->GetStringWidth($inv_ser_2_line) / 2, $size * 0.75 + 2 * $text_high - $board, $inv_ser_2_line);
                    $pdf->Text(($size * 0.75 + 3 * $text_high) / 2 - $pdf->GetStringWidth($inv_ser_3_line) / 2, $size * 0.75 + 3 * $text_high - $board, $inv_ser_3_line);
                }
                elseif ($pdf->GetStringWidth($inv_ser) <= ($size * 0.75 - $board * 2) * 3) {
                    $inv_ser_1_line = $inv_ser;
                    $inv_ser_2_line = $inv_ser;
                    $inv_ser_3_line = $inv_ser;
                    while ($pdf->GetStringWidth($inv_ser_1_line) > $size * 0.75 - $board * 2) {
                        $inv_ser_1_line = substr($inv_ser_1_line,0,-1);
                    }
                    $inv_ser_2_line = substr($inv_ser_2_line, strlen($inv_ser_1_line));
                    while ($pdf->GetStringWidth($inv_ser_2_line) > $size * 0.75 - $board * 2) {
                        $inv_ser_2_line = substr($inv_ser_2_line,0,-1);
                    }
                    $inv_ser_3_line = substr($inv_ser_3_line, strlen($inv_ser_1_line) + strlen($inv_ser_2_line));
                    $pdf->Text(($size * 0.75 + 3 * $text_high) / 2 - $pdf->GetStringWidth($inv_ser_1_line) / 2, $size * 0.75 + $text_high - $board, $inv_ser_1_line);
                    $pdf->Text(($size * 0.75 + 3 * $text_high) / 2 - $pdf->GetStringWidth($inv_ser_2_line) / 2, $size * 0.75 + 2 * $text_high - $board, $inv_ser_2_line);
                    $pdf->Text(($size * 0.75 + 3 * $text_high) / 2 - $pdf->GetStringWidth($inv_ser_3_line) / 2, $size * 0.75 + 3 * $text_high - $board, $inv_ser_3_line);
                }
                else {
                    $inv_ser_1_line = $inv_ser;
                    $inv_ser_2_line = $inv_ser;
                    $inv_ser_3_line = $inv_ser;
                    while ($pdf->GetStringWidth($inv_ser_1_line) > $size * 0.75 - $board * 2) {
                        $inv_ser_1_line = substr($inv_ser_1_line,0,-1);
                    }
                    $inv_ser_2_line = substr($inv_ser_2_line, strlen($inv_ser_1_line));
                    while ($pdf->GetStringWidth($inv_ser_2_line) > $size * 0.75 - $board * 2) {
                        $inv_ser_2_line = substr($inv_ser_2_line,0,-1);
                    }
                    $inv_ser_3_line = substr($inv_ser_3_line, strlen($inv_ser_1_line) + strlen($inv_ser_2_line));
                    while ($pdf->GetStringWidth($inv_ser_3_line) > $size * 0.75 - $board * 2) {
                        $inv_ser_3_line = substr($inv_ser_3_line,0,-1);
                    }
                    $pdf->Text(($size * 0.75 + 3 * $text_high) / 2 - $pdf->GetStringWidth($inv_ser_1_line) / 2, $size * 0.75 + $text_high - $board, $inv_ser_1_line);
                    $pdf->Text(($size * 0.75 + 3 * $text_high) / 2 - $pdf->GetStringWidth($inv_ser_2_line) / 2, $size * 0.75 + 2 * $text_high - $board, $inv_ser_2_line);
                    $pdf->Text(($size * 0.75 + 3 * $text_high) / 2 - $pdf->GetStringWidth($inv_ser_3_line) / 2, $size * 0.75 + 3 * $text_high - $board, $inv_ser_3_line);
                }
            }
            $pdf_filename = substr_replace($pdf_filename, "", -1);
        }
        else {
            throw new ForbiddenHttpException();
        }
        $pdf_filename .= '.pdf';
        $pdf->Output($pdf_filename,'F');
        header('Content-Type: application/pdf');
        header("Content-Transfer-Encoding: Binary");
        header('Cache-Control: max-age=0');
        header("Content-disposition: attachment; filename=\"" . basename($pdf_filename) . "\"");
        echo file_get_contents($pdf_filename);
        unlink($pdf_filename);
        exit;
    }

    public function actionCreatetask($inv_id, $type)
    {
        $formData = Yii::$app->request->post();
        $wrh = QrCodefunction::getWrhSerialByInvId($inv_id);
        $tasks = HtmlPurifier::process($formData['Tasks']);
        Yii::$app->response->redirect(['/items',
            'action_get' => 'createitemfromqr',
            'wrkfl' => $tasks,
            'wrh_id_serial' => $inv_id,
            'wrh_id_element_warehouse' => $wrh['item_id'],
            'wrh_id_object' => $wrh['id_object'],
            'typeqr' => $type,
        ]);
    }

    public function actionOpenchecklist($id_object, $type)
    {
        $formData = Yii::$app->request->post();
        $checklists = HtmlPurifier::process($formData['Checklists']);
        Yii::$app->response->redirect(['/checklist/view/',
            'id' => $checklists,
            'id_object' => $id_object,
            'typeqr' => $type,
        ]);
    }
}



