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
                            'subscription-express-create',
                        ],
                        'roles'   => ['@'],
                    ],
                    [
                        'allow'   => true,
                        'actions' => [
                            'express-payment',
                            'payment-notification',
                            'subscription-express-confirm'
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
        if (!Yii::$app->getModule('payment')->enableExpressPayment) {
            throw new \yii\web\NotFoundHttpException;
        }
        $paypal = Yii::$app->getModule('payment')->paypalExpressPayment;
        try {
            $paypal->createExpressPayment($price, 1);
        } catch (Exception $ex) {
            throw new \yii\web\NotFoundHttpException;
        }
        $url = $paypal->paymentUrl;
        Yii::info('User #'.Yii::$app->user->getId().' redirected to paypal with payment #'.$paypal->id.' token is "'.$paypal->getToken().'"', 'payment');
        return $this->redirect($url);
    }
    
    public function actionExpressPayment($token = null, $PayerID = null)
    {
        if (is_null($token) || is_null($PayerID) || !Yii::$app->getModule('payment')->enableExpressPayment) {
            throw new \yii\web\NotFoundHttpException;
        }
        $payment = Yii::$app->getModule('payment')->getPaypalExpressPaymentByToken($token);
        if (empty($payment)) {
            throw new \yii\web\NotFoundHttpException;
        }
        if ($payment->doCheckout($PayerID)) {
            Yii::$app->getSession()->setFlash('success', 'Congratulations. You have successfully payed.');
        } else {
            Yii::$app->getSession()->setFlash('error', 'Sorry, but your payment was unsuccesfull');
        }
        
        return $this->goHome();
    }

    public function actionSubscriptionExpressConfirm($token = null)
    {
        if (!Yii::$app->getModule('payment')->enableSubscriptionExpress || is_null($token)) {
            throw new \yii\web\NotFoundHttpException;
        }
        //TODO continue flow
    }

    public function actionSubscriptionExpressCreate($description = null)
    {
        if (!Yii::$app->getModule('payment')->enableSubscriptionExpress) {
            throw new \yii\web\NotFoundHttpException;
        }
        $subscription = Yii::$app->getModule('payment')->getPaypalSubscriptionExpress();
        try {
            if ($subscription->prepareSubscriptionUrl()) {
                $url = $subscription->paymentUrl;
                Yii::info('User #'.Yii::$app->user->getId().' redirected to paypal with token is "'.$subscription->getToken().'"', 'payment');
                return $this->redirect($url);
            }
        } catch (Exception $ex) {
            throw new \yii\web\NotFoundHttpException;
        }
    }
}
