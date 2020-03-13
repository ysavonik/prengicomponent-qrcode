<?php

use prengicomponent\qrcode\Module;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

?>
<?php if(Yii::$app->core->checkaccess(25, 'items')){?>

    <div class="modal fade" id="setworkflow" tabindex="-1" role="dialog" aria-labelledby="commentLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <?php $form = ActiveForm::begin(['action'=>$url, 'options' => [ 'id'=>'form-set-workflow', 'data-pjax' => false ]]); ?>
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="buyclickLabel"><?=Module::t('app', 'Choose a task type')?></h4>
                </div>
                    <div class="modal-body">
                        <?php if(count($mainworkflowslist)==1){ ?>
                            <input type="hidden" name="id_mainworkflow" id="id_mainworkflow" value="<?=key($mainworkflowslist)?>">
                        <?php }else{ ?>
                            <div class="form-group">
                                <label class="control-label" for="id_mainworkflow"><?=Module::t('app', 'Main Workflow')?></label>
                                <select name="id_mainworkflow" id="id_mainworkflow" class="form-control sel_mainworkflow_set">
                                    <option value=""></option>
                                    <?php foreach ((array)$mainworkflowslist as $key=>$workflow){?>
                                        <option value="<?=$key?>"><?=$workflow?></option>
                                    <?php }?>
                                </select>
                            </div>
                        <?php }?>


                        <div class="form-group">
                            <label class="control-label" for="id_workflow"><?=Module::t('app', 'Workflow')?></label>
                            <select name="id_workflow" id="id_workflow" class="form-control runprocess sel_workflow_set">
                                <option value=""></option>
                                <?php foreach ((array)$workflows as $key=>$workflow){?>
                                    <option value="<?=$key?>"><?=$workflow?></option>
                                <?php }?>
                            </select>
                        </div>
                    </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary mr-5" data-dismiss="modal"><?=Module::t('app', 'Cancel')?></button>
                    <?php if (!empty($workflows)){ ?>
                        <input type="hidden" value="" name="id_objectspassport_items_tasks" id="id_objectspassport_items_tasks">
                        <button type="submit" class="btn btn-primary btn-items-run"><?=Module::t('app', 'Create')?></button>
                    <?php }?>
                </div>
                <?php
                    $script = '
                    $(function() {
                        $(document).on(\'click change\', \'.has-error-sel\', function(e){
                            $(\'.sel_workflow_set\').removeClass("has-error-sel");
                        });                                                               
                                                                                  
                        $(\'#form-set-workflow\').submit(function(e) {
                            if($(\'.sel_workflow_set\').val()==\'\'){
                                $(\'.sel_workflow_set\').addClass("has-error-sel");
                                return false;
                            }
                    
                            return true;
                        });
                        $(document).on(\'change\', \'.sel_mainworkflow_set\', function(e){
                            $(".sel_workflow_set").html("<option value=\"\"></option>");
                            var selmaindata=' . (!empty($mainworkflowslistdata) ? $mainworkflowslistdata : '[]') . ';
                            $.each(selmaindata, function(i, item) {
                                if(selmaindata[i].id_mainworkflow==$(\'.sel_mainworkflow_set\').val()){
                                    $(".sel_workflow_set").append($("<option></option>").attr("value",selmaindata[i].id_workflow).text(selmaindata[i].name)); 
                                }
                                //console.log(selmaindata[i]);
                            })
                        });
                    });';
                    $this->registerJs($script);
                ?>

                <?=Html::hiddenInput('wrh_id_object', $id_object);?>
                <?=Html::hiddenInput('action', 'add');?>
                <?=Html::hiddenInput('register_tab_pst', 0);?>
                <?=Html::hiddenInput('register_block_pst', 0);?>
                <?=Html::hiddenInput('register_date_pst', '');?>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
<?php }?>
