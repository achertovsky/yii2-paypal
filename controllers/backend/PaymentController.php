<?php

namespace modules\payment\controllers\backend;

use common\overrides\filter\AccessRule;
use yii\filters\AccessControl;
use modules\user\models\User;
use modules\payment\models\PaypalSettings;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\web\Response;

class PaymentController extends \yii\web\Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'ruleConfig' => [
                    'class' => AccessRule::className(),
                ],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => [
                            'configure',
                        ],
                        'roles' => [User::ROLE_HEAD_ADMIN],
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
