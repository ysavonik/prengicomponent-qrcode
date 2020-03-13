<?php

namespace prengicomponent\qrcode;

class Module extends \yii\base\Module
{
    public $controllerNamespace = 'prengicomponent\qrcode\controllers';
    public function init()
    {
        parent::init();
        $this->params['type'] = 'init';
        $this->registerTranslations();
    }

    public function registerTranslations()
    {
        \Yii::$app->i18n->translations['prengicomponent/qrcode/*'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'uk-UA',
            'forceTranslation' => true,
            'basePath' => '@vendor/prengicomponent/qrcode/src/messages',
            'fileMap' => [
                'prengicomponent/qrcode/messages' => 'app.php',
            ],
        ];
    }

    public static function t($category, $message, $params = [], $language = null)
    {
        return \Yii::t('prengicomponent/qrcode/' . $category, $message, $params, $language);
    }
}
