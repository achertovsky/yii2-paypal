<?php

namespace achertovsky\paypal\controllers\frontend;

use yii\filters\AccessControl;
use Yii;
use achertovsky\paypal\models\PaypalExpressPayment;
use yii\web\BadRequestHttpException;
use achertovsky\paypal\models\PaypalSubscriptionExpress;

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
    

    /**
     * @param int $price
     * @param int $modelId
     * @return string
     */
    public function actionPay($price, $modelId = null)
    {
        if (!Yii::$app->getModule('payment')->enableExpressPayment) {
            throw new \yii\web\NotFoundHttpException('Page Not Found');
        }
        if ($modelId == 'null') {
            $modelId = null;
        }
        if (!is_null($modelId)) {
            $model = PaypalExpressPayment::findOne($modelId);
            $paypal = Yii::$app->getModule('payment')->paypalExpressPayment;
            $paypal->setAttributes($model->getAttributes());
            $paypal->id = $model->id;
            $paypal->isNewRecord = false;
        } else {
            $paypal = Yii::$app->getModule('payment')->paypalExpressPayment;
            $paypal->payment_price = $price;
        }
        if (empty($paypal)) {
            throw new \yii\web\NotFoundHttpException('Page Not Found');
        }
        try {
            $paypal->createExpressPayment($paypal->payment_price, 1);
        } catch (Exception $ex) {
            throw new \yii\web\NotFoundHttpException('Page Not Found');
        }
        $url = $paypal->paymentUrl;
        Yii::info('User #'.Yii::$app->user->getId().' redirected to paypal with payment #'.$paypal->id.' token is "'.$paypal->getToken().'"', 'payment');
        return $this->redirect($url);
    }
    

    public function actionExpressPayment($token = null, $PayerID = null)
    {
        if (is_null($token) || is_null($PayerID) || !Yii::$app->getModule('payment')->enableExpressPayment) {
            throw new \yii\web\NotFoundHttpException('Page Not Found');
        }
        $payment = Yii::$app->getModule('payment')->getPaypalExpressPaymentByToken($token);
        if (empty($payment)) {
            throw new \yii\web\NotFoundHttpException('Page Not Found');
        }
        if ($payment->doCheckout($PayerID)) {
            Yii::$app->getSession()->setFlash('success', 'Congratulations. You have successfully payed.');
        } else {
            Yii::$app->getSession()->setFlash('error', 'Sorry, but your payment was unsuccesfull.');
        }
        
        return $this->goHome();
    }

    public function actionSubscriptionExpressConfirm($token = null)
    {
        if (!Yii::$app->getModule('payment')->enableSubscriptionExpress || is_null($token)) {
            throw new \yii\web\NotFoundHttpException('Page Not Found');
        }
        $subscription = Yii::$app->getModule('payment')->getPaypalSubscriptionExpressByToken($token);
        if (empty($subscription)) {
            throw new \yii\web\NotFoundHttpException('Page Not Found');
        }
        try {
            if ($subscription->startSubscription()) {
                Yii::$app->getSession()->setFlash('success', 'Congratulations. You have successfully subscribed.');
            } else {
                Yii::$app->getSession()->setFlash('error', 'Sorry, but your subscription was not activated. Check paypal to be sure. If something wrong - contact us');
            }
        } catch (Exception $ex) {
            $subscription->delete();
            throw new \yii\web\NotFoundHttpException('Page Not Found');
        }
        return $this->goHome();
    }

    public function actionSubscriptionExpressCreate($price, $modelId = null, $period = 30, $description = null)
    {
        if (!Yii::$app->getModule('payment')->enableSubscriptionExpress) {
            throw new \yii\web\NotFoundHttpException('Page Not Found');
        }
        if ($modelId == 'null') {
            $modelId = null;
        }
        if (!is_null($modelId)) {
            $model = PaypalSubscriptionExpress::findOne($modelId);
            $subscription = Yii::$app->getModule('payment')->getPaypalSubscriptionExpress();
            $subscription->setAttributes($model->getAttributes());
            $subscription->id = $model->id;
            $subscription->isNewRecord = false;
        } else {
            $subscription = Yii::$app->getModule('payment')->getPaypalSubscriptionExpress();
            $subscription->period = $period;
            $subscription->price = $price;
        }
        if (empty($subscription)) {
            throw new \yii\web\NotFoundHttpException('Page Not Found');
        }
        try {
            if ($subscription->prepareSubscriptionUrl()) {
                $url = $subscription->paymentUrl;
                Yii::info('User #'.Yii::$app->user->getId().' redirected to paypal with token is "'.$subscription->getToken().'"', 'payment');
                return $this->redirect($url);
            }
        } catch (Exception $ex) {
            throw new \yii\web\NotFoundHttpException('Page Not Found');
        }
        throw new BadRequestHttpException('Something went wrong');
    }
}
