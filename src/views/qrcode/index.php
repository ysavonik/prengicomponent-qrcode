<?php

use yii\helpers\Html;
use yii\grid\GridView;
use prengicomponent\qrcode\assets\QrcodeAsset;
use yii\helpers\HtmlPurifier;
use prengicomponent\qrcode\Module;

$this->registerAssetBundle(QrcodeAsset::class);

$this->title = Module::t('app', 'Qrcode');
$this->params['breadcrumbs'][] = $this->title;
?>
<h4>
    <?= Html::encode($this->title) ?>
</h4>
<?php
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'options' => [
        'class' => 'table-responsive',
        ],
    'columns' => [
        [
            'attribute' => '#',
            'format' => 'raw',
            'value' => function ($model) {
                $txt = HtmlPurifier::process($model['id_qrcode']);
                return Module::t('app', $txt);
                },
            'contentOptions'=>[
                'style'=>'width: 80px; text-align: center;',
                'class' => 'hidden-xs'
            ],
            'headerOptions' => ['class' => 'hidden-xs'],
            'filterOptions' => ['class' => 'hidden-xs'],
        ],
        [
            'attribute' => Module::t('app', 'Type'),
            'format' => 'raw',
            'value' => function ($model) {
                $txt = HtmlPurifier::process($model['type']);
                return Module::t('app', $txt);
                },

        ],
        [
            'attribute' => '',
            'format' => 'raw',
            'value' => function ($model) {
                return Html::a(Module::t('app', 'Settings'), ['/qrcode/type/' . $model['id_qrcode']], [
                    'class' => 'type-settings-btn btn btn-primary',
                ]);
            },
            'contentOptions'=>['style'=>'width: 60px; text-align: center;'],
        ],
        [
            'attribute' => '',
            'value' => function ($model) {
                return Html::a(Module::t('app', 'Create file for print'), ['print/' . $model['id_qrcode']], [
                    'class' => 'type-print-btn btn btn-primary',
                ]);
            }
            ,
            'format' => 'raw',
            'contentOptions'=>['style'=>'width: 60px; text-align: center;'],
        ],
        ],
    ]);
?>
<script>
    var enjoyhint_instance = new EnjoyHint({});
    var enjoyhint_script_steps = [
        {
            'click .type-settings-btn' : 'This button open type\'s role settings page',
            "nextButton" : {
                className: "myNext",
                text: "Next"
            },
            "skipButton" : {
                className: "mySkip",
                text: "Skip"
            },
            showSkip: true,
            showNext: true,
        },
        {
            'click .type-print-btn' : 'This button open type\'s pdf file with qrcodes creation page',
            "nextButton" : {
                className: "myNext",
                text: "Next"
            },
            "skipButton" : {
                className: "mySkip",
                text: "Skip"
            },
            showSkip: true,
            showNext: true,
        },
    ];
    enjoyhint_instance.set(enjoyhint_script_steps);
    enjoyhint_instance.run();
</script>
