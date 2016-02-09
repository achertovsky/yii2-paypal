<?php

namespace achertovsky\paypal;

use achertovsky\paypal\components\ModuleTrait;
use yii\helpers\Url;
use Yii;

class Module extends \yii\base\Module
{
    use ModuleTrait;
    
    public $ipnUrl;
    public $expressSuccessUrl;
    public $subscriptionSuccessUrl;
    public $cancelUrl;
    public $currency = 'USD';
    public $modelMap = [
        'PaypalExpressPayment' => 'achertovsky\paypal\models\PaypalExpressPayment',
    ];
    
    public function __construct($id, $parent = null, $config = array())
    {
        parent::__construct($id, $parent, $config);
        $this->ipnUrl = Yii::$app->urlManager->createAbsoluteUrl($this->ipnUrl);
        $this->expressSuccessUrl = Yii::$app->urlManager->createAbsoluteUrl($this->expressSuccessUrl);
        $this->subscriptionSuccessUrl = Yii::$app->urlManager->createAbsoluteUrl($this->subscriptionSuccessUrl);
        $this->cancelUrl = Yii::$app->urlManager->createAbsoluteUrl($this->cancelUrl);
    }
    
    public function getPaypalExpressPayment()
    {
        $paypal = new $this->modelMap['PaypalExpressPayment'];
        $paypal->expressSuccessUrl = $this->expressSuccessUrl;
        $paypal->cancelUrl = $this->cancelUrl;
        $paypal->ipnUrl = $this->ipnUrl;
        $paypal->currency = $this->currency;
        $paypal->newRecord();
        return $paypal;
    }
    
    public function getPaypalExpressPaymentByToken($token)
    {
        $paypal = call_user_func([
            $this->modelMap['PaypalExpressPayment'],
            'findByToken'
        ], $token);
        $paypal->expressSuccessUrl = $this->expressSuccessUrl;
        $paypal->cancelUrl = $this->cancelUrl;
        $paypal->ipnUrl = $this->ipnUrl;
        $paypal->currency = $this->currency;
        return $paypal;
    }
}
