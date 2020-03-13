<?php
namespace prengicomponent\qrcode\assets;

class QrcodeAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@vendor/prengicomponent/qrcode/src/media';
    public $css = [
        'css/style.css',
//        'css/enjoyhint.css'
    ];
   public $js = [
       'js/script-qr.js',
       'js/type-qr.js',
//       'js/enjoyhint.js'
   ];
   public $images = [
       'images/A4.png'
   ];
//   public $depends = [
//       'yii\web\JqueryAsset',
//   ];
}
