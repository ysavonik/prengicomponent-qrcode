<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\HtmlPurifier;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;
use prengicomponent\qrcode\assets\QrcodeAsset;
use prengicomponent\qrcode\Module;

$this->registerAssetBundle(QrcodeAsset::class);
$this->params['breadcrumbs'][] = ['label' => Module::t('app', 'Qrcode'), 'url' => ['/qrcode']];
$this->title = Module::t('app','Type') . ' ' . Module::t('app', $type);
$this->params['breadcrumbs'][] = $this->title;
?>
<h4>
    <?= Html::encode($this->title) ?>
</h4>
<button type="button" class="btn-add-role btn btn-primary btn-sm mb-5 mr-5" data-toggle="modal" data-target="#w0"><?= Module::t('app',"Add role") ?></button>
<div id="w0" class="fade modal in" role="dialog" tabindex="-1" style="display: none;"
     aria-labelledby="commentLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4><?= Module::t('app','Add Role')?></h4>
                </div>
                <div class="modal-body">
                    <?php if ($type == "Warehouse"): ?>
                        <?php $form = ActiveForm::begin([
                            'id' => 'add',
                            'action' => ['add', 'type' => 'Warehouse'],
                        ]); ?>
                        <?= $form->field($model, 'id_role')->dropDownList($model->getUnusedNames())->label((Module::t('app',"Choose Role")))?>
                        <div class="form-check-block">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="qrcodeobjects-permission_create_task" name="QrcodeWarehouse[permission_create_task]">
                                <label class="form-check-label" for="qrcodeobjects-permission_create_task">
                                    <?= Module::t('app',"Permission Create Task") ?>
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <?= Html::SubmitButton(Module::t('app','Add'), [
                                'class' => 'btn-add-role-success btn btn-success',
                                'name' => 'add-role-button',
                            ]) ?>
                        </div>
                        <?php ActiveForm::end(); ?>
                    <?php endif; ?>

                    <?php if ($type == "Objects"): ?>
                        <?php $form = ActiveForm::begin([
                            'id' => 'add',
                            'action' => ['add', 'type' => 'Objects'],
                        ]); ?>
                        <?= $form->field($model, 'id_role')->dropDownList($model->getUnusedNames())->label(Module::t('app',"Choose Role"))?>
                        <div class="form-check-block">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="qrcodeobjects-permission_open" name="QrcodeObjects[permission_open]">
                                <label class="form-check-label" for="qrcodeobjects-permission_open">
                                    <?= Module::t('app',"Permission Open") ?>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="qrcodeobjects-permission_checklist" name="QrcodeObjects[permission_checklist]">
                                <label class="form-check-label" for="qrcodeobjects-permission_checklist">
                                    <?= Module::t('app',"Permission Checklist") ?>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="qrcodeobjects-permission_create_task" name="QrcodeObjects[permission_create_task]">
                                <label class="form-check-label" for="qrcodeobjects-permission_create_task">
                                    <?= Module::t('app',"Permission Create Task") ?>
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <?= Html::SubmitButton(Module::t('app','Add'), [
                                'class' => 'btn-add-role-success btn btn-success btn-sm mb-5 mr-5',
                                'name' => 'add-role-button',
                            ]) ?>
                        </div>
                        <?php ActiveForm::end(); ?>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                </div>
            </div>
        </div>
    </div>
</div>
<?php
if ($type == "Objects") {
    Pjax::begin();
    echo GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'label' => Module::t('app','Roles'),
                'attribute' => 'description',
                'format' => 'raw',
                'value' => function ($model) {
                    $res = HtmlPurifier::process($model['description']);
                    return $res;
                },
            ],
            [
                'attribute' => Module::t('app','Permission open'),
                'format' => 'raw',
                'value' => function ($model) {
                    if($model['permission_open']) $checked = true;
                    else $checked = false;
                    return Html::Checkbox($model, $checked, [
                        'class' => 'permission_open',
                        'id' => 'checkbox_open_' . $model['id_role'],
                        'onclick' => "objCheckboxOnclick(" . $model['id_role'] . ", " . "'permission_open'" . ")",
                    ]);
                },
                'contentOptions' => [
                    'style'=>'text-align: left;',
                ],
            ],
            [
                'attribute' =>  Module::t('app','Permission checklist'),
                'format' => 'raw',
                'value' => function ($model) {
                    if($model['permission_checklist']) $checked = true;
                    else $checked = false;
                    return Html::Checkbox($model, $checked, [
                        'class' => 'permission_checklist',
                        'id' => 'permission_checklist_' . $model['id_role'],
                        'onchange' => "objCheckboxOnclick(" . $model['id_role'] . ", " . "'permission_checklist'" . ")",
                    ]);
                },
                'contentOptions'=> [
                    'style' => 'text-align: left;',
                ],
            ],
            [
                'attribute' => Module::t('app','Permission create task'),
                'format' => 'raw',
                'value' => function ($model) {
                    if($model['permission_create_task']) $checked = true;
                    else $checked = false;
                    return Html::Checkbox($model, $checked, [
                        'class' => 'permission_create_task',
                        'id' => 'permission_create_task_' . $model['id_role'],
                        'onchange' => "objCheckboxOnclick(" . $model['id_role'] . ", " . "'permission_create_task'" . ")",
                    ]);
                },
                'contentOptions' => [
                    'style'=>'text-align: left;',
                ],
            ],
            [
                'attribute' => '',
                'value' => function ($model) {
                    return Html::a('<i class="glyphicon glyphicon-trash"></i>',  Yii::$app->request->url, [
                        'data'=> [
                            'method' => 'post',
                            'confirm' => Module::t('app', 'Are you sure?'),
                            'params'=> [
                                'action'=>'delete_role',
                                'id_role'=>$model['id_role'],
                            ],
                        ],
                        'title' => Module::t('app', 'Delete role')
                    ]);
                },
                'format' => 'raw',
                'contentOptions' => ['style'=>'width: 80px; text-align: center;']
            ]
        ],
    ]);
    Pjax::end();
}
if ($type == "Warehouse") {
    Pjax::begin();
    echo GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'label' => Module::t('app','Roles'),
                'attribute' => 'description',
                'format' => 'raw',
                'value' => function ($model) {
                    $res = HtmlPurifier::process($model['description']);
                    return $res;
                },
            ],
            [
                'attribute' => Module::t('app','Permission create task'),
                'format' => 'raw',
                'value' => function ($model) {
                    if($model['permission_create_task']) $checked = true;
                    else $checked = false;
                    return Html::Checkbox($model, $checked, [
                        'class' => 'permission_create_task',
                        'id' => 'checkbox_create_task_' . $model['id_role'],
                        'onchange' => "warCheckboxOnclick(" . $model['id_role'] . ", " . "'permission_create_task'" . ")",
                    ]);
                },
                'contentOptions' => [
                    'style' => 'text-align: left;',
                ],
            ],
            [
                'attribute' => '',
                'value' => function ($model) {
                    return Html::a('<i class="glyphicon glyphicon-trash"></i>',  Yii::$app->request->url, [
                        'data' => [
                            'method' => 'post',
                            'confirm' => Module::t('app', 'Are you sure?'),
                            'params' => [
                                'action' => 'delete_role',
                                'id_role' => $model['id_role'],
                            ],
                        ],
                        'title' => Module::t('app', 'Delete role')
                    ]);
                },
                'format' => 'raw',
                'contentOptions' => ['style'=>'width: 80px; text-align: center;']
            ]
        ],
    ]);
    Pjax::end();
}
?>
<script>
    var enjoyhint_instance = new EnjoyHint({});
    var enjoyhint_script_steps = [
        {
            'show .table' : 'This table show roles and their settings. Click on checkbox will change roles permission. Click on trash will delete all permissions for this role.',
            "nextButton" : {
                className: "myNext",
                text: "Next"
            },
            showNext: true,
        },
        {
            'click .btn-add-role' : 'Click the "Add role" button to add role to settings page',
        },
        {
            'click .<?php if ($type == "Warehouse") echo "field-qrcodewarehouse-id_role";
                elseif ($type == "Objects") echo "field-qrcodeobjects-id_role";?>' : 'Choose role',
        },
        {
            'show .form-check-block' : 'Choose selected role\'s permissions',
            "nextButton" : {
                className: "myNext",
                text: "Next"
            },
            "skipButton" : {
                className: "mySkip",
                text: "Skip"
            },
            showNext: true,
            showSkip: false,
        },
        {
            'click .btn-add-role-success' : 'Press to submit',
            "nextButton" : {
                className: "myNext",
                text: "Next"
            },
            showNext: true,
        }
    ];
    enjoyhint_instance.set(enjoyhint_script_steps);
    enjoyhint_instance.run();
</script>

