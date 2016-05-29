<?php

namespace achertovsky\paypal;

use yii\web\AssetBundle;

class PaypalAsset extends AssetBundle
{
    public $sourcePath = '@vendor/achertovsky/paypal-yii2/js';

    public $js = [
        'paypal.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}