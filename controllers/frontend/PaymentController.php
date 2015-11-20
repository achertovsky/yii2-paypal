<?php

namespace achertovsky\paypal\controllers\frontend;

use yii\filters\AccessControl;
use Yii;

class PaymentController extends \yii\web\Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow'   => true,
                        'actions' => [
                            'index',
                            'pay',
                        ],
                        'roles'   => ['@'],
                    ],
                    [
                        'allow'   => true,
                        'actions' => [
                            'express-payment',
                            'payment-notification'
                        ],
                        'roles'   => ['?', '@'],
                    ],
                ]
            ],
        ];
    }
    
    public function actionIndex()
    {
        return $this->render('index');
    }
    
    public function actionPay($price)
    {
        $paypal = Yii::$app->getModule('payment')->paypalExpressPayment;
        try {
            $paypal->createExpressPayment($price, 1);
        } catch (Exception $ex) {
            throw new \yii\web\NotFoundHttpException;
        }
        $url = $paypal->paymentUrl;
        Yii::info('User #'.Yii::$app->user->getId().' redirected to paypal with payment #'.$paypal->id.' token is "'.$paypal->getToken().'"');
        return $this->redirect($url);
    }
    
    public function actionExpressPayment($token, $PayerID)
    {
        $payment = Yii::$app->getModule('payment')->getPaypalExpressPaymentByToken($token);
        if (empty($payment)) {
            throw new \yii\web\NotFoundHttpException;
        }
        if ($payment->doCheckout($payerId)) {
            Yii::$app->getSession()->setFlash('success', 'Congratulations. You have successfully payed for a premium account. It will be enabled soon.');
        } else {
            Yii::$app->getSession()->setFlash('success', 'Sorry, but your payment was unsuccesfull');
        }
        
        return $this->goHome();
    }
}
