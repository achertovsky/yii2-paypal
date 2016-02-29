<?php

namespace achertovsky\paypal;

use achertovsky\paypal\components\ModuleTrait;
use yii\helpers\Url;
use Yii;

class Module extends \yii\base\Module
{
    use ModuleTrait;

    //here is arrays like for Url::toRoute()
    public $ipnUrl = ['/payment/payment/payment-notification'];
    public $expressSuccessUrl = ['/payment/payment/express-payment'];
    public $subscriptionExpressSuccessUrl = ['/payment/payment/subscription-express-confirm'];
    public $cancelUrl = ['/', '#' => 'cancel'];
    //default currency
    public $currency = 'USD';
    //models for this module
    public $modelMap = [
        'PaypalExpressPayment' => 'achertovsky\paypal\models\PaypalExpressPayment',
        'PaypalSubscriptionExpress' => 'achertovsky\paypal\models\PaypalSubscriptionExpress',
    ];
    //paypal express checkout version
    public $ECVersion = '104.0';
    //boolean which indicates is express payment is enabled in app
    public $enableExpressPayment = true;
    //boolean which indicates is subscription flow is enabled in app
    public $enableSubscriptionExpress = true;

    //getter to receive users name. it must be assigned to your option
    public $subscriptionUsernameGetter = 'username';
    //defines if user receive notifications via email when subscription status changed
    public $subscriptionEmailNotification = false;
    
    public function __construct($id, $parent = null, $config = array())
    {
        parent::__construct($id, $parent, $config);
        $this->ipnUrl = Yii::$app->urlManager->createAbsoluteUrl($this->ipnUrl);
        $this->expressSuccessUrl = Yii::$app->urlManager->createAbsoluteUrl($this->expressSuccessUrl);
        $this->subscriptionExpressSuccessUrl  = Yii::$app->urlManager->createAbsoluteUrl($this->subscriptionExpressSuccessUrl);
        $this->cancelUrl = Yii::$app->urlManager->createAbsoluteUrl($this->cancelUrl);
    }
    
    /**
     * creates clear model for express payment
     * @return achertovsky\paypal\models\PaypalExpressPayment
     */
    public function getPaypalExpressPayment()
    {
        $paypal = new $this->modelMap['PaypalExpressPayment'];
        $paypal->expressSuccessUrl = $this->expressSuccessUrl;
        $paypal->cancelUrl = $this->cancelUrl;
        $paypal->ipnUrl = $this->ipnUrl;
        $paypal->currency = $this->currency;
        $paypal->ECVersion = $this->ECVersion;
        $paypal->newRecord();
        return $paypal;
    }
    
    /**
     * find existing record in DB by token and fills the model
     * @param string $token
     * @return achertovsky\paypal\models\PaypalExpressPayment
     */
    public function getPaypalExpressPaymentByToken($token)
    {
        $paypal = call_user_func([
            $this->modelMap['PaypalExpressPayment'],
            'findByToken'
        ], $token);
        if (empty($paypal)) {
            return null;
        }
        $paypal->expressSuccessUrl = $this->expressSuccessUrl;
        $paypal->cancelUrl = $this->cancelUrl;
        $paypal->ipnUrl = $this->ipnUrl;
        $paypal->currency = $this->currency;
        $paypal->ECVersion = $this->ECVersion;
        $paypal->newRecord();
        return $paypal;
    }

    /**
     * creates clear model for subscription via express payment
     * @return achertovsky\paypal\models\PaypalSubscriptionExpress
     */
    public function getPaypalSubscriptionExpress()
    {
        $subscription = new $this->modelMap['PaypalSubscriptionExpress'];
        $subscription->successUrl = $this->subscriptionExpressSuccessUrl;
        $subscription->cancelUrl = $this->cancelUrl;
        $subscription->currency = $this->currency;
        $subscription->ECVersion = $this->ECVersion;
        $subscription->newRecord();
        return $subscription;
    }

    /**
     * find existing record in DB by token and fills the model
     * @param string $token
     * @return achertovsky\paypal\models\PaypalExpressPayment
     */
    public function getPaypalSubscriptionExpressByToken($token)
    {
        $subscription = call_user_func([
            $this->modelMap['PaypalSubscriptionExpress'],
            'findByToken'
        ], $token);
        if (empty($subscription)) {
            return null;
        }
        $subscription->successUrl = $this->subscriptionExpressSuccessUrl;
        $subscription->cancelUrl = $this->cancelUrl;
        $subscription->currency = $this->currency;
        $subscription->ECVersion = $this->ECVersion;
        $subscription->newRecord();
        return $subscription;
    }
}
