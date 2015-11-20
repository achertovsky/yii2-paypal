<?php

namespace achertovsky\paypal;

use achertovsky\paypal\components\ModuleTrait;
use yii\helpers\Url;
use Yii;

class Module extends \yii\base\Module
{
    use ModuleTrait;
    
    public $ipnUrl;
    public $successUrl;
    public $cancelUrl;
    public $currency = 'USD';
    public $modelMap = [
        'PaypalExpressPayment' => 'achertovsky\paypal\models\PaypalExpressPayment',
    ];
    
    public function __construct($id, $parent = null, $config = array())
    {
        parent::__construct($id, $parent, $config);
        $this->ipnUrl = Yii::$app->urlManager->createAbsoluteUrl($this->ipnUrl);
        $this->successUrl = Yii::$app->urlManager->createAbsoluteUrl($this->successUrl);
        $this->cancelUrl = Yii::$app->urlManager->createAbsoluteUrl($this->cancelUrl);
    }
    
    public function getPaypalExpressPayment()
    {
        $paypal = new $this->modelMap['PaypalExpressPayment'];
        $paypal->successUrl = $this->successUrl;
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
        $paypal->successUrl = $this->successUrl;
        $paypal->cancelUrl = $this->cancelUrl;
        $paypal->ipnUrl = $this->ipnUrl;
        $paypal->currency = $this->currency;
        return $paypal;
    }
}
