<?php

namespace achertovsky\paypal;

use common\components\ModuleTrait;
use yii\helpers\Url;
use Yii;
use achertovsky\paypal\models\PaypalExpressPayment;

class Module extends \yii\base\Module
{
    use ModuleTrait;
    
    public $ipnUrl;
    public $successUrl;
    public $cancelUrl;
    public $currency = 'USD';
    
    public function __construct($id, $parent = null, $config = array())
    {
        parent::__construct($id, $parent, $config);
        $this->ipnUrl = Yii::$app->urlManager->createAbsoluteUrl($this->ipnUrl);
        $this->successUrl = Yii::$app->urlManager->createAbsoluteUrl($this->successUrl);
        $this->cancelUrl = Yii::$app->urlManager->createAbsoluteUrl($this->cancelUrl);
    }
    
    public function getPaypalExpressPayment()
    {
        $paypal = new PaypalExpressPayment();
        $paypal->successUrl = $this->successUrl;
        $paypal->cancelUrl = $this->cancelUrl;
        $paypal->ipnUrl = $this->ipnUrl;
        $paypal->currency = $this->currency;
        $paypal->newRecord();
        return $paypal;
    }
    
    public function getPaypalExpressPaymentByToken($token)
    {
        $paypal = PaypalExpressPayment::findByToken($token);
        $paypal->successUrl = $this->successUrl;
        $paypal->cancelUrl = $this->cancelUrl;
        $paypal->ipnUrl = $this->ipnUrl;
        $paypal->currency = $this->currency;
        return $paypal;
    }
}
