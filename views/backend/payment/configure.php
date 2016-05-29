<?php

use yii\bootstrap\ActiveForm;
use achertovsky\paypal\models\PaypalSettings;
use yii\helpers\Html;
use achertovsky\paypal\PaypalAsset;

PaypalAsset::register($this);
$this->title = 'Paypal Settings';
?>

<div class="row">
    <div class="col-md-8 col-md-offset-2">
        <?php $form = ActiveForm::begin([
            'id' => 'paypalSettingsSaveForm',
            'enableAjaxValidation' => true,
            'enableClientValidation' => false,
        ]);?>

        <?=$form->field($settings, 'api_username')->textInput()?>
        <?=$form->field($settings, 'api_password')->textInput()?>
        <?=$form->field($settings, 'api_signature')->textInput()?>
        <?=$form->field($settings, 'app_id')->textInput()?>
        <?=$form->field($settings, 'merchant_email')->textInput()?>
        <?=$form->field($settings, 'mode')->dropDownList([
            PaypalSettings::MODE_SANDBOX => 'Sandbox',
            PaypalSettings::MODE_LIVE => 'Live',
        ])?>

        <?=Html::submitButton('Save', [
            'id' => 'paypalSettingsSaveBtn',
            'class' => 'btn btn-success col-md-2 col-md-offset-5'
        ])?>
        
        <?php ActiveForm::end()?>
    </div>
</div> 
