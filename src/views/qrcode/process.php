<?php

use prengicomponent\qrcode\models\QrCodefunction;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use prengicomponent\qrcode\Module;

$this->params['breadcrumbs'][] = ['label' => Module::t('app', 'Qrcode'), 'url' => ['/qrcode']];
$this->title = Module::t('app', 'Process');
$this->params['breadcrumbs'][] = $this->title;
?>
<h4>
    <?= Html::encode($this->title) ?>
</h4>
<?php echo Html::encode(Module::t('app', $header)) ?>
<?php
if ($type == 1) {
    $obj_name = QrCodefunction::getObjNameById($id_object);
    if (!empty($obj_name)) {
        echo '</br>' . $obj_name . '</br>';
    }
    if (!empty($permissions)) {?>
        <?php echo Html::encode(Module::t('app', $description))?>
        <?php foreach ($permissions as $but) {
            if ($but['name'] == 'Open Object') {
                echo Html::a(Module::t('app', $but['name']), [$but['url']], [
                    'class' => 'btn btn-primary btn-block',
                ]);
            }
            if ($but['name'] == 'Open Checklist'): ?>
                <a class="btn btn-primary btn-block mb-5" data-toggle="modal" data-target="#w0"><?= Module::t('app', "Choose checklist") ?></a>
                <div id="w0" class="fade modal in" role="dialog" tabindex="-1" style="display: none;">
                    <div class="modal-dialog ">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                <h4><?= Module::t('app', 'Choose checklist') ?></h4>
                            </div>
                            <div class="modal-body">
                                <form id="open_checklist" action="/qrcode/openchecklist?id_object=<?= $but['id_object'] ?>&amp;type=<?= $type ?>" method="post">
                                    <?php $form = ActiveForm::begin([
                                    'id' => 'open_checklist',
                                    'action' => ['openchecklist', 'id_object' => $but['id_object'], 'type' => $type],
                                    ]); ?>
                                    <?=Html::dropDownList('Checklists', 'id', $checklists, ['class' => 'form-control']);?>
                            </div>
                            <div class="modal-footer">
                                <?=Html::submitButton(Module::t('app', $but['name']), ['class' => 'btn btn-primary btn-items-run',]);?>
                            </div>
                            <?php ActiveForm::end(); ?>
                        </div>
                    </div>
                </div>
            <?php endif;
            if ($but['name'] == 'Create task on object') { ?>
                <button data-toggle="modal" data-target="#setworkflow" type="button" class="btn btn-primary btn-block"><i class="ion-plus-round mr-5"></i><?=Module::t('app', 'Create task on object')?></button>
                <div class="col-md-3 col-sm-4 mb-5" id="bl-create-1" >
                    <?= $this->render('common/_setworkflow_modal', [
                        'id_object' => $id_object,
                        'workflows' => $workflows,
                        'mainworkflowslist' => $mainworkflowslist,
                        'mainworkflowslistdata' => $mainworkflowslistdata,
                        'url' => $but['url'],
                    ]) ?>
                </div>
            <?php }
        }
    }
    else {
        echo Module::t('app', 'You dont have access to any actions!');
    }
}
if ($type == 2) {
    $inv_ser = QrCodefunction::getInvSerialById($inv_id);
    $inv_cat = QrCodefunction::getInvCategoryById($inv_id);
    $inv_model = QrCodefunction::getInvModelById($inv_id);
    if (!empty($inv_ser) && !empty($inv_cat) && !empty($inv_model)) {
        echo '</br>' . $inv_ser . '</br>' . $inv_cat . '</br>' . $inv_model . '</br>';
    }
    if (!empty($permissions)){ ?>
        <?php echo Module::t('app', $description)?>
        <?php foreach ($permissions as $but) { ?>
            <?php if ($but['name'] == 'Create task on inventory'):?>
                <a class="btn btn-primary btn-block" data-toggle="modal" style="margin-bottom: 5px;" data-target="#w0"><i class="ion-plus-round mr-5"></i><?= Module::t('app', $but['name']); ?></a>
                <div id="w0" class="fade modal in" role="dialog" tabindex="-1" style="display: none;">
                    <div class="modal-dialog ">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                <h4><?= Module::t('app', 'Choose a task type') ?></h4>
                            </div>
                            <div class="modal-body">
                                <?php $form = ActiveForm::begin([
                                    'id' => 'create_task',
                                    'action' => ['/items/index',
                                        'action_get' => 'createitemfromqr',
                                    ],
                                ]); ?>
                                <?=Html::dropDownList('id_workflow', 'id', $tasks, ['class' => 'form-control']);?>
                                <?=Html::hiddenInput('action', 'add');?>
                                <?=Html::hiddenInput('wrh_id_object', $wrh_id_object);?>
                                <?=Html::hiddenInput('wrh_id_element_warhouse', $wrh_id_element_warhouse);?>
                                <?=Html::hiddenInput('wrh_id_serial', $wrh_id_serial);?>
                                <?=Html::hiddenInput('register_tab_pst', 0);?>
                                <?=Html::hiddenInput('register_block_pst', 0);?>
                                <?=Html::hiddenInput('register_date_pst', '');?>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary mr-5" data-dismiss="modal"><?=Module::t('app', 'Cancel')?></button>
                                <?=Html::submitButton(Module::t('app', 'Create'), ['class' => 'btn btn-primary btn-items-run',]);?>
                            </div>
                            <?php ActiveForm::end(); ?>
                        </div>
                    </div>
                </div>
            <?php endif;
        }
    }
    else {
        echo Module::t('app', 'You dont have access to any actions!');
    }
}



