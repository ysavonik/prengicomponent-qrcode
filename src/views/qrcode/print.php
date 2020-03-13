<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use prengicomponent\qrcode\assets\QrcodeAsset;
use prengicomponent\qrcode\Module;

$bundle = $this->registerAssetBundle(QrcodeAsset::class);
$this->params['breadcrumbs'][] = ['label' => Module::t('app', 'Qrcode'), 'url' => ['/qrcode']];
$this->title = Module::t('app', 'Export') . ' ' . Module::t('app', $type);
$this->params['breadcrumbs'][] = $this->title;
?>
<h4>
    <?= Html::encode($this->title) ?>
</h4>
<div class="export-set" style="padding-bottom: 0.5%;">
    <?=Html::beginForm(['print/' . $id ],'post');?>
    <?=Html::dropDownList('Sizes', 'size', $sizes, [
            'class' => 'btn-qr-sizes btn btn-secondary mb-5 mr-5',
            'id' => 'code-size',
        ]
    );?>

    <button type="button" id="sizes-pic-but" class="btn-qr-sizes-comparison btn btn-sm mb-5 mr-5" data-toggle="modal" data-target="#w1">?</button>
    <div id="w1" class="fade modal in" role="dialog" tabindex="-1" style="display: none;">
        <div class="modal-dialog ">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h3><?php echo Module::t('app', 'Qrcode sizes comparison with A4') ?></h3>
                </div>
                <div class="modal-body">
                    <?= Html::img($bundle->baseUrl . '/images/A4.png', [
                        'style'=>"width:100%;height:100%;"
                    ]); ?>
                </div>

            </div>
        </div>
    </div>
    <?=Html::Button(Module::t('app', 'Export'), [
        'class' => 'btn-export btn btn-info btn-sm mb-5 mr-5',
        'id' => 'apply-export',
        'onclick'=>"export_confirm()",
    ]);?>
</div>
<?php if ($id == 1): ?>
    <div class="mb-10">
        <a href="<?= Yii::$app->request->url ?>" data-method="post" data-params="{&quot;action&quot;:&quot;clearfilter&quot;}" class="btn-clear-filters btn btn-sm btn-primary btn-primary mr-5 mb-5"><?=Module::t('app', 'Clear');?></a>
        <button type="submit" id="apply-filters" class="btn-apply-filters btn btn-sm btn-primary btn-success mr-5 mb-5"><?=Module::t('app', 'Filter');?></button>
    </div>
    <div class="filters-row row">
        <div class="col-md-3">
            <div class="row">
                <div id="formobjectselect" class="modal fade" tabindex="-1" role="dialog"
                     aria-labelledby="gridModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title"
                                    id="gridModalLabel"><?= Module::t('app', 'Select Object'); ?></h4>
                            </div>
                            <div class="modal-body">
                                <div class="text-right mb-10">
                                    <button type="button" class="btn btn-secondary mr-5" data-dismiss="modal"><?= Module::t('app', 'Close'); ?></button>
                                    <button type="button" class="btn btn-primary button-select-object" data-dismiss="modal"><?= Module::t('app', 'Select'); ?></button>
                                </div>
                                <div class="col-md-3">
                                    <select id="ftsub-mainobjects" class="form-control input-sm mb-10"
                                            placeholder="<?= Module::t('app', 'Main Objects'); ?>">
                                        <option value="0"><?= Module::t('app', 'All Objects'); ?></option>
                                        <?php foreach ((array)$objects as $fbject) { ?>
                                            <option value="<?= $fbject['id_object_main']; ?>"><?= $fbject['name']; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <input type="text" id="ftsub-objects" name=""
                                           class="form-control input-sm"
                                           placeholder="<?= Module::t('app', 'Select Object'); ?>">
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <span id="ftsub-resetobject"><?= Module::t('app', 'Reset All Objects'); ?></span>
                                    </div>
                                    <div class="col-md-3">
                                        <span class="ftsub-selectallobject" id="ftsub-selectallobject"><?= Module::t('app', 'Select All Objects'); ?></span>
                                    </div>
                                </div>
                                <div class="row-obj-filter row">
                                    <div class="col-md-12">
                                        <?php foreach ((array)$objects as $fobject) { ?>
                                            <div class="cnt-main-object cnt-main-object-<?= $fobject['id_object_main']; ?>">
                                                <?php
                                                $mainsel = '';
                                                $res = '';
                                                foreach ((array)$fobject['objects'] as $key => $fsubobject) {
                                                    $sel = '';
                                                    if (in_array($key, $fl_obj)) {
                                                        $sel = 'checked';
                                                    }
                                                    $res .= "<li class=\"col-md-4 col-sm-6\"><span class=\"ch-obj-el\"><input " . $sel . " id=\"ch-obj\" ch-obj=\"" . $key . "\" name=\"filters[ft_objects][]\" type=\"checkbox\" value=\"" . $key . "\"><span>" . Html::encode($fsubobject) . "</span></span></li>";
                                                }
                                                ?>
                                                <div class="cnt-main-sub-object">
                                                    <input <?= $mainsel ?> id="ch-main-obj"
                                                                           ch-main-obj="<?= $fobject['id_object_main']; ?>"
                                                                           name="filter_ch_main_obj[]"
                                                                           type="checkbox"
                                                        <?php if (in_array($fobject['id_object_main'], $fl_main_obj)): ?> checked <?php endif; ?>
                                                                           value="<?= Html::encode($fobject['id_object_main']); ?>"><span><b><?= Html::encode($fobject['name']); ?></b></span>
                                                </div>
                                                <ul class="clearfix cnt-sub-object list_style_type_n">
                                                    <?= $res ?>
                                                </ul>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary"
                                        data-dismiss="modal"><?= Module::t('app', 'Close'); ?></button>
                                <button type="button" class="btn btn-primary button-select-object"
                                        data-dismiss="modal"><?= Module::t('app', 'Select'); ?></button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 field_item">
                    <div class="form-group">
                        <div class="input-group">
                            <span class="left-input-group-addon input-group-addon"><?= Module::t('app', 'Objects'); ?></span>
                                <input type="text" id="ft-select-objects" name="ft-select-objects" autocomplete="off"
                                       class="form-control input-sm"
                                       placeholder="<?= Module::t('app', 'All Objects'); ?>"
                                       value="<?= Html::encode(!empty($resval_obj) ? implode(',', $resval_obj) : '') ?>">
                            <a id="obj-filter-modal-open" href="#" data-toggle="modal" data-target="#formobjectselect"
                               class="input-group-addon g-obj-select"><span
                                        class="glyphicons-obj glyphicons glyphicons-check"></span></a>
                        </div>
                        <?php !empty($resval_obj) ? $span[] = implode(',', $resval_obj) : '' ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    Pjax::begin();
    echo GridView::widget([
        'dataProvider' => $dataProvider,
        'layout' => "{summary}\n{pager}\n{items}\n{pager}",
        'columns' => [
            [
                'attribute' => '#',
                'format' => 'raw',
                'value' => function ($model) {
                    $res = Html::encode($model['id_object_main']);
                    return $res;
                },
                'contentOptions' => [
                    'style' => 'width: 80px; text-align: center;',
                ],
            ],
            [
                'attribute' => Module::t('app', 'Main object'),
                'format' => 'raw',
                'value' => function ($model) {
                    $res = Html::encode($model['main_object_name']);
                    return $res;
                },
            ],
            [
                'attribute' => '#',
                'format' => 'raw',
                'value' => function ($model) {
                    $res = Html::encode($model['id_object']);
                    return $res;
                },
                'contentOptions' => [
                    'style'=>'width: 80px; text-align: center;',
                ],
            ],
            [
                'attribute' => Module::t('app', 'Object'),
                'format' => 'raw',
                'value' => function ($model) {
                    $res = Html::encode($model['object_name']);
                    return $res;
                },
            ],
        ],
    ]);
    Pjax::end();
    ?>
<?php endif; ?>

<?php if ($id == 2): ?>
    <div class="mb-10">
        <a href="<?= Yii::$app->request->url ?>" data-method="post" data-params="{&quot;action&quot;:&quot;clearfilter&quot;}" class="btn-clear-filters btn btn-sm btn-primary btn-primary mr-5 mb-5"><?=Module::t('app', 'Clear');?></a>
        <button type="submit" id="apply-filters" class="btn-apply-filters btn btn-sm btn-primary btn-success mr-5 mb-5"><?=Module::t('app', 'Filter');?></button>
    </div>
    <div class="filters-row row">
        <div class="col-md-3">
            <div class="row">
                <div id="formcategoryselect" class="modal fade" tabindex="-1" role="dialog"
                     aria-labelledby="gridModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title"
                                    id="gridModalLabel"><?= Module::t('app', 'Select Category'); ?></h4>
                            </div>
                            <div class="modal-body">
                                <div class="text-right mb-10">
                                    <button type="button" class="btn btn-secondary mr-5" data-dismiss="modal"><?= Module::t('app', 'Close'); ?></button>
                                    <button type="button" class="btn btn-primary button-select-category" data-dismiss="modal"><?= Module::t('app', 'Select'); ?></button>
                                </div>
                                <div class="col-md-3">
                                    <select id="ftsub-mainocats" class="form-control input-sm mb-10"
                                            placeholder="<?= Module::t('app', 'Categories'); ?>">
                                        <option value="0"><?= Module::t('app', 'All Categories'); ?></option>
                                        <?php foreach ((array)$m_categories as $m) { ?>
                                            <option value="<?= Html::encode($m['category_id']); ?>"><?= Html::encode($m['title']); ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <span id="ftsub-resetcategory" style="text-decoration:underline; cursor:pointer;"><?= Module::t('app', 'Reset All Categories'); ?></span>
                                    </div>
                                    <div class="col-md-3">
                                        <span id="ftsub-selectallcategory" style="text-decoration:underline; cursor:pointer;"><?= Module::t('app', 'Select All Categories'); ?></span>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <?php foreach ((array)$m_categories as $m) { ?>
                                            <div class="m-cat m-cat-<?= $m['category_id'] ?>">
                                                    <input  <?php if (in_array($m['category_id'], $fl_cat)) echo 'checked'?>
                                                            id="cat"
                                                            cat="<?= $m['category_id']; ?>"
                                                            name="filters[categories][]"
                                                            type="checkbox"
                                                            value="<?= Html::encode($m['category_id']); ?>"><span><b><?= Html::encode($m['title']); ?></b></span>
                                                    <br />
                                                <?php
                                                foreach ($categories as $category) {
                                                    if ($category['tree'] == $m['tree']) { ?>
                                                    <span><input <?php if (in_array($category['category_id'], $fl_cat)) echo 'checked'?> id="cat" cat="<?= Html::encode($category['category_id']); ?>" name="filters[categories][]"  style='margin-left:<?php echo $category['depth'] * 25 ?>px;' type="checkbox" value="<?= Html::encode($category['category_id']); ?>"><span><?php echo Html::encode($category['title']) ?></span></span>
                                                    </br>

                                                    <?php } ?>
                                                <?php }
                                                ?>
                                            </div>
                                            <?php } ?>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary"
                                        data-dismiss="modal"><?= Module::t('app', 'Close'); ?></button>
                                <button type="button" class="btn btn-primary button-select-category"
                                        data-dismiss="modal"><?= Module::t('app', 'Select'); ?></button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 field_item">
                    <div class="form-group">
                        <div class="input-group">
                            <span class="left-input-group-addon input-group-addon"><?= Module::t('app', 'Categories'); ?></span>
                            <input type="text" id="ft-select-categories" name="ft-select-categories" autocomplete="off"
                                   class="form-control input-sm"
                                   placeholder="<?= Module::t('app', 'All Categories'); ?>"
                                   value="<?= Html::encode(!empty($resval_cat) ? implode(',', $resval_cat) : '') ?>">
                            <a href="#" data-toggle="modal" data-target="#formcategoryselect"
                               class="input-group-addon g-cat-select"><span
                                        class="glyphicons-ctg glyphicons glyphicons-check"></span></a>
                        </div>
                        <?php !empty($resval_cat) ? $span[] = implode(',', $resval_cat) : '' ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="row" >
                <div id="formobjectselect" class="modal fade" tabindex="-1" role="dialog"
                     aria-labelledby="gridModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title"
                                    id="gridModalLabel"><?= Module::t('app', 'Select Object'); ?></h4>
                            </div>
                            <div class="modal-body">
                                <div class="text-right mb-10">
                                    <button type="button" class="btn btn-secondary mr-5" data-dismiss="modal"><?= Module::t('app', 'Close'); ?></button>
                                    <button type="button" class="btn btn-primary button-select-object" data-dismiss="modal"><?= Module::t('app', 'Select'); ?></button>
                                </div>
                                <div class="col-md-3">
                                    <select id="ftsub-mainobjects" class="form-control input-sm mb-10"
                                            placeholder="<?= Module::t('app', 'Main Objects'); ?>">
                                        <option value="0"><?= Module::t('app', 'All Objects'); ?></option>
                                        <?php foreach ((array)$objects as $fbject) { ?>
                                            <option value="<?= $fbject['id_object_main']; ?>"><?= $fbject['name']; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <input type="text" id="ftsub-objects" name=""
                                           class="form-control input-sm"
                                           placeholder="<?= Module::t('app', 'Select Object'); ?>">
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <span id="ftsub-resetobject"><?= Module::t('app', 'Reset All Objects'); ?></span>
                                    </div>
                                    <div class="col-md-3">
                                        <span class="ftsub-selectallobject" id="ftsub-selectallobject"><?= Module::t('app', 'Select All Objects'); ?></span>
                                    </div>
                                </div>
                                <div class="row-obj-filter row">
                                    <div class="col-md-12">
                                        <?php foreach ((array)$objects as $fobject) { ?>
                                            <div class="cnt-main-object cnt-main-object-<?= $fobject['id_object_main']; ?>">
                                                <?php
                                                $mainsel = '';
                                                $res = '';
                                                foreach ((array)$fobject['objects'] as $key => $fsubobject) {
                                                    $sel = '';
                                                    if (in_array($key, $fl_obj)) {
                                                        $sel = 'checked';
                                                    }
                                                    $res .= "<li class=\"col-md-4 col-sm-6\"><span class=\"ch-obj-el\"><input " . $sel . " id=\"ch-obj\" ch-obj=\"" . $key . "\" name=\"filters[ft_objects][]\" type=\"checkbox\" value=\"" . Html::encode($key) . "\"><span>" . Html::encode($fsubobject) . "</span></span></li>";
                                                }
                                                ?>
                                                <div class="cnt-main-sub-object">
                                                    <input <?= $mainsel ?> id="ch-main-obj"
                                                                           ch-main-obj="<?= $fobject['id_object_main']; ?>"
                                                                           name="filter_ch_main_obj[]"
                                                                           type="checkbox"
                                                                           <?php if (in_array($fobject['id_object_main'], $fl_main_obj)): ?> checked <?php endif; ?>
                                                                           value="<?= $fobject['id_object_main']; ?>"><span><b><?= Html::encode($fobject['name']); ?></b></span>
                                                </div>
                                                <ul class="clearfix cnt-sub-object list_style_type_n">
                                                    <?= $res ?>
                                                </ul>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary"
                                        data-dismiss="modal"><?= Module::t('app', 'Close'); ?></button>
                                <button type="button" class="btn btn-primary button-select-object"
                                        data-dismiss="modal"><?= Module::t('app', 'Select'); ?></button>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="col-md-12 field_item">
                    <div class="form-group">
                        <div class="input-group">
                            <span class="left-input-group-addon input-group-addon"><?= Module::t('app', 'Objects'); ?></span>
                            <input type="text" id="ft-select-objects" name="ft-select-objects" autocomplete="off"
                                   class="form-control input-sm"
                                   placeholder="<?= Module::t('app', 'All Objects'); ?>"
                                   value="<?= Html::encode(!empty($resval_obj) ? implode(',', $resval_obj) : '') ?>">
                            <a id="obj-filter-modal-open" href="#" data-toggle="modal" data-target="#formobjectselect"
                               class="input-group-addon g-obj-select"><span
                                        class="glyphicons-obj glyphicons glyphicons-check"></span></a>
                        </div>
                        <?php !empty($resval_obj) ? $span[] = implode(',', $resval_obj) : '' ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    Pjax::begin();
    echo GridView::widget([
        'dataProvider' => $dataProvider,
        'layout' => "{summary}\n{pager}\n{items}\n{pager}",
        'columns' => [
            [
                'attribute' =>  Module::t('app', 'Category'),
                'format' => 'raw',
                'value' => function ($model) {
                    $res = Html::encode($model['category']);
                    return $res;
                },
            ],
            [
                'attribute' =>  Module::t('app', 'Item'),
                'format' => 'raw',
                'value' => function ($model) {
                    $res = Html::encode($model['item']);
                    return $res;
                },
            ],
            [
                'attribute' =>  Module::t('app', 'Serial ID'),
                'format' => 'raw',
                'value' => function ($model) {
                    $res = Html::encode($model['serial_id']);
                    return $res;
                },
            ],
            [
                'attribute' =>  Module::t('app', 'Main object'),
                'format' => 'raw',
                'value' => function ($model) {
                    $res = Html::encode($model['main_object']);
                    return $res;
                },
            ],
            [
                'attribute' =>  Module::t('app', 'Object'),
                'format' => 'raw',
                'value' => function ($model) {
                    $res = Html::encode($model['object']);
                    return $res;
                },
            ],
        ],
    ]);
    Pjax::end();
    ?>
<?php endif; ?>

<script>
    $('body').on('click', '#cat', function (){
        var tree = $(this).attr("cat");
        var sub_c = [
            <?php
            foreach ($sub_cats as $key => $s_c){
                echo '[' . $key . ',' . '[';
                foreach ($s_c as $c) echo $c['category_id'] . ',';
                echo ']]'. ',';
            }
            ?>
        ];
        if($(this).is(":checked")){
            sub_c.forEach(function (element) {
                if (element[0] == tree) {
                    element[1].forEach(function (el) {
                        var a = document.querySelector('input[cat="'+el+'"]');
                        a.checked = true;
                    });
                }
            });
        }else{
            sub_c.forEach(function (element) {
                if (element[0] == tree) {
                    element[1].forEach(function (el) {
                        var a = document.querySelector('input[cat="'+el+'"]');
                        a.checked = false;
                    });
                }
            });
        }
    });

    function openWindowWithPost(url, data) {
        var form = document.createElement("form");
        form.target = "_blank";
        form.method = "GET";
        form.action = url;
        form.style.display = "none";

        for (var key in data) {
            var input = document.createElement("input");
            input.type = "hidden";
            input.name = key;
            input.value = data[key];
            form.appendChild(input);
        }

        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }

    function export_pdf(){
        var e = document.getElementById("code-size");
        if (e.options[e.selectedIndex].value != 0) {
            e.classList.remove("btn-danger");
            var id = <?= $id ?>;
            var qr_size = e.options[e.selectedIndex].value;
            var inventory_ids = <?= json_encode($inventory_serials); ?>;
            var object_ids = <?= json_encode($object_ids); ?>;
            var qr_size = e.options[e.selectedIndex].value;
            let str_object_ids = "";
            let str_inventory_ids = "";
            for (var key in object_ids) {
                str_object_ids += object_ids[key] + ',';
            }
            for (var key in inventory_ids) {
                str_inventory_ids += inventory_ids[key] + ',';
            }
            if (str_object_ids.length > 8000 || str_inventory_ids.length > 8000) {
                alert('<?= Module::t('app', 'You choose too many qrcodes!') ?>');
                return;
            }
                openWindowWithPost("/qrcode/export", {
                    id: id,
                    size: qr_size,
                    object_ids_str: str_object_ids,
                    inventory_serials_str: str_inventory_ids
                });
        }
        else {
            e.classList.add("btn-danger");
            alert('<?= Module::t('app', 'Choose size of Qrcodes') ?>')
        }
    };

    function export_confirm(){
        if(confirm('<?= Module::t('app', 'Are you sure?') ?>')) {
            var e = document.getElementById("apply-export");
            export_pdf();
        }
    }
</script>

<script>
    var enjoyhint_instance = new EnjoyHint({});
    var enjoyhint_script_steps = [
        {
            'click .table' : 'This table shows list of elements chosen for print',
            "skipButton" : {
                className: "mySkip",
                text: "Skip"
            },
            "nextButton" : {
                className: "myNext",
                text: "Next"
            },
            showSkip: false,
            showNext: true,
            'timeout': 200,
        },
        {
            'click .filters-row' : 'This is filters, which helps to select table data for print',
            "nextButton" : {
                className: "myNext",
                text: "Next"
            },
            "skipButton" : {
                className: "mySkip",
                text: "Skip"
            },
            showSkip: false,
            showNext: true,
            'timeout': 200,
        },
        {
            'click .btn-apply-filters' : 'This button refreshes page with new selected filters',
            "nextButton" : {
                className: "myNext",
                text: "Next"
            },
            "skipButton" : {
                className: "mySkip",
                text: "Skip"
            },
            showSkip: false,
            showNext: true,
            'timeout': 200,
        },
        {
            'click .btn-clear-filters' : 'This button clear all filters. After this action table will show all existing elements',
            "nextButton" : {
                className: "myNext",
                text: "Next"
            },
            "skipButton" : {
                className: "mySkip",
                text: "Skip"
            },
            showSkip: false,
            showNext: true,
            'timeout': 200,
        },
        {
            'click .btn-qr-sizes' : 'After you chose all needed table data with using filters, choose which size of qrcodes you need',
            "nextButton" : {
                className: "myNext",
                text: "Next"
            },
            "skipButton" : {
                className: "mySkip",
                text: "Skip"
            },
            showSkip: false,
            showNext: true,
            'timeout': 200,
        },
        {
            'click .btn-qr-sizes-comparison' : 'This button opens image with comparison qrcode sizes and A4 paper',
            "nextButton" : {
                className: "myNext",
                text: "Next"
            },
            "skipButton" : {
                className: "mySkip",
                text: "Skip"
            },
            showSkip: false,
            showNext: true,
            'timeout': 200,
        },
        {
            'click .btn-export' : 'This button downloads pdf file with qrcodes',
            "nextButton" : {
                className: "myNext",
                text: "Next"
            },
            "skipButton" : {
                className: "mySkip",
                text: "Skip"
            },
            showSkip: false,
            showNext: true,
            'timeout': 200,
        },
        {
            'click .glyphicons-obj' : 'Lets try to filter data table by object',
            "nextButton" : {
                className: "myNext",
                text: "Next"
            },
            "skipButton" : {
                className: "mySkip",
                text: "Skip"
            },
            showSkip: true,
            showNext: false,
            'timeout': 500,
        },
        {
            'click #ftsub-selectallobject' : 'Press this button to select all objects',
            "nextButton" : {
                className: "myNext",
                text: "Next"
            },
            "skipButton" : {
                className: "mySkip",
                text: "Skip"
            },
            showSkip: true,
            showNext: false,
            'timeout': 500,
        },
        {
            'click #ftsub-resetobject' : 'Press this button to reset all objects',
            "nextButton" : {
                className: "myNext",
                text: "Next"
            },
            "skipButton" : {
                className: "mySkip",
                text: "Skip"
            },
            showSkip: true,
            showNext: false,
            'timeout': 500,
        },
        {
            'click #ftsub-mainobjects' : 'Choose one main object from this dropdown list',
            "nextButton" : {
                className: "myNext",
                text: "Next"
            },
            "skipButton" : {
                className: "mySkip",
                text: "Skip"
            },
            showSkip: true,
            showNext: false,
            'timeout': 500,
        },
        {
            'show #ftsub-objects' : 'One more usefull feature is search objects by name! Press next, or enter some text',
            "nextButton" : {
                className: "myNext",
                text: "Next"
            },
            "skipButton" : {
                className: "mySkip",
                text: "Skip"
            },
            showSkip: false,
            showNext: true,
            'timeout': 500,
        },
        {
            'show .row-obj-filter' : 'Choose main objects and objects for filter',
            "nextButton" : {
                className: "myNext",
                text: "Next"
            },
            "skipButton" : {
                className: "mySkip",
                text: "Skip"
            },
            showSkip: false,
            showNext: true,
            'timeout': 500,
        },
        {
            'click .button-select-object' : 'Press this button to confirm filter',
            'timeout': 200,
        },
        {
            'click .btn-apply-filters' : 'Finally press this button to refresh table data',
            'timeout': 200,
        },
    ];
    enjoyhint_instance.set(enjoyhint_script_steps);
    enjoyhint_instance.run();
</script>
