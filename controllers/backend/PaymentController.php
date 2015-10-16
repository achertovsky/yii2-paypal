<?php

namespace achertovsky\paypal\controllers\backend;

use yii\filters\AccessControl;
use achertovsky\paypal\models\PaypalSettings;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\web\Response;

class PaymentController extends \yii\web\Controller
{
		//please, override it
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => false,
                    ],
                ],
            ],
        ];
    }
    
    public function actionConfigure()
    {
        $settings = PaypalSettings::find()->one();
        
        if ($settings->load(Yii::$app->request->post())) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($settings);
            }
            
            if ($settings->save()) {
                Yii::$app->getSession()->setFlash('success', 'Paypal settings updated succesfully');
                return $this->refresh();
            }
        }
        
        return $this->render('configure', [
            'settings' => $settings,
        ]);
    }
}
