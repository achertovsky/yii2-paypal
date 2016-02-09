<?php

namespace achertovsky\paypal\models;

use yii\base\Exception;
use achertovsky\paypal\models\PaypalSettings;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;

class PaypalExpressPayment extends \yii\db\ActiveRecord
{
    const EXPRESS_CHECKOUT_SANDBOX = 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=';
    const EXPRESS_CHECKOUT_LIVE = 'https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=';
    
    const STATUS_INITIALIZED = 'INITIALIZED';
    const STATUS_CREATED_BY_SITE = 'CREATED_BY_SITE';
    const STATUS_ERROR = 'ERROR';
            
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'paypal_express_payment';
    }
    
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
            ],
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return ArrayHelper::merge(parent::scenarios(), [
            'search' => [],
        ]);
    }
    
    public function rules()
    {
        return [
            [
                [
                    'status',
                    'currency',
                    'errors',
                    'payment_token',
                ],
                'string'
            ],
            [
                [
                    'payment_price',
                ],
                'double'
            ],
            [
                [
                    'created_at',
                    'updated_at',
                    'user_id'
                ],
                'integer'
            ],
            [
                [
                    'status',
                    'currency',
                    'payment_price',
                    'user_id',
                    'payment_token',
                ],
                'required'
            ],
        ];
    }
    
    public function newRecord()
    {
        if (Yii::$app->user->isGuest) {
            return false;
        }
            
        $currentUser = Yii::$app->user->identity;

        $this->setAttributes([
            'user_id' => $currentUser->id,
            'status' => self::STATUS_INITIALIZED,
            'currency' => $this->currency,
        ]);
            
    }
    
    public $expressSuccessUrl;
    public $cancelUrl;
    public $ipnUrl;
    protected $token;
    
    /**
     * generates paypal transaction. saves it payment to base
     * @return string URL
     * @throws Exception
     */
    public function createExpressPayment($amount, $quantity, $name = 'Goods')
    {
        /*
         * INITIAL CHECKS
         */
        if (!is_int($quantity) || $quantity <= 0) {
            Yii::error('Quantity must be integer > 0');
            throw new Exception('Quantity must be integer > 0');
        }
        
        if (!is_numeric($amount) || $amount <= 0) {
            Yii::error('Amount must be number > 0');
            throw new Exception('Amount must be number > 0');
        }
        
        if (empty($this->expressSuccessUrl) || empty($this->cancelUrl) || empty($this->currency)) {
            Yii::error('Success url or cancel url or currency haven\'t initialized');
            throw new Exception('Success url or cancel url or currency haven\'t initialized');
        }
        
        /*
         * LOGIC
         */
        
        $settings = PaypalSettings::find()->one();
        
        if (empty($settings)) {
            Yii::error('Settings of PayPal haven\'t initialized');
            throw new Exception('Settings of PayPal haven\'t initialized');
        }
        
        $config = [
            'mode' => $settings->mode ? 'live' : 'sandbox',
            'acct1.UserName' => $settings->api_username,
            'acct1.Password' => $settings->api_password,
            'acct1.Signature' => $settings->api_signature,
        ];
        
        $paypalService = new \PayPalAPIInterfaceServiceService($config);
        $paymentDetails= new \PaymentDetailsType();

        $itemDetails = new \PaymentDetailsItemType();
        $itemDetails->Name = $name;
        $itemDetails->Amount = $amount;
        $itemDetails->Quantity = $quantity;

        $paymentDetails->PaymentDetailsItem[0] = $itemDetails;

        $orderTotal = new \BasicAmountType();
        $orderTotal->currencyID = $this->currency;
        $orderTotal->value = $amount * $quantity;

        $paymentDetails->PaymentAction = 'Sale';

        $setECReqDetails = new \SetExpressCheckoutRequestDetailsType();
        $setECReqDetails->PaymentDetails[0] = $paymentDetails;
        $setECReqDetails->CancelURL = $this->cancelUrl;
        $setECReqDetails->ReturnURL = $this->expressSuccessUrl;
        $setECReqDetails->OrderTotal = $orderTotal;

        $setECReqType = new \SetExpressCheckoutRequestType();
        $setECReqType->Version = '104.0';
        $setECReqType->SetExpressCheckoutRequestDetails = $setECReqDetails;

        $setECReq = new \SetExpressCheckoutReq();
        $setECReq->SetExpressCheckoutRequest = $setECReqType;

        $setECResponse = $paypalService->SetExpressCheckout($setECReq);
        
        if (is_null($setECResponse->Errors)) {
            Yii::info('Payment created succesfully. Token is "'.$setECResponse->Token.'"');
            $this->token = $setECResponse->Token;
            $this->setAttributes([
                'payment_price' => $amount,
                'payment_token' => $this->token,
                'currency' => $this->currency,
                'status' => self::STATUS_CREATED_BY_SITE,
            ]);
            $this->save();
            return $this;
        } else {
            Yii::error('Errors: '.var_export($setECResponse->Errors, true));
            throw new Exception('Errors: '.var_export($setECResponse->Errors, true));
        }
    }
    
    public function doCheckout($payerId)
    {
        if (empty($this->ipnUrl)) {
            Yii::error('Ipn url haven\'t initialized');
            throw new Exception('Ipn url haven\'t initialized');
        }
        $settings = PaypalSettings::find()->one();
        $config = [
            'mode' => $settings->mode ? 'live' : 'sandbox',
            'acct1.UserName' => $settings->api_username,
            'acct1.Password' => $settings->api_password,
            'acct1.Signature' => $settings->api_signature,
        ];
        $paypalService = new \PayPalAPIInterfaceServiceService($config);

        $orderTotal = new \BasicAmountType();
        $orderTotal->currencyID = $this->currency;
        $orderTotal->value = $this->payment_price;
        
        $paymentDetails= new \PaymentDetailsType();
        $paymentDetails->PaymentAction = 'Sale';
        $paymentDetails->NotifyURL = $this->ipnUrl;
        $paymentDetails->OrderTotal = $orderTotal;

        
        $DoECRequestDetails = new \DoExpressCheckoutPaymentRequestDetailsType();
        $DoECRequestDetails->PayerID = $payerId;
        $DoECRequestDetails->Token = $this->payment_token;
        $DoECRequestDetails->PaymentDetails[0] = $paymentDetails;

        $DoECRequest = new \DoExpressCheckoutPaymentRequestType();
        $DoECRequest->DoExpressCheckoutPaymentRequestDetails = $DoECRequestDetails;
        $DoECRequest->Version = '104.0';

        $DoECReq = new \DoExpressCheckoutPaymentReq();
        $DoECReq->DoExpressCheckoutPaymentRequest = $DoECRequest;

        $DoECResponse = $paypalService->DoExpressCheckoutPayment($DoECReq);
        if (!empty($DoECResponse->Errors)) {
            $this->errors = var_export($DoECResponse->Errors, true);
            $this->status = self::STATUS_ERROR;
            $this->save();
            Yii::error('Errors: '.var_export($DoECResponse->Errors, true));
            return false;
        }
        $status = strtoupper($DoECResponse->DoExpressCheckoutPaymentResponseDetails->PaymentInfo[0]->PaymentStatus);
        $this->status = $status;
        $this->save();
        return true;
    }
    
    /**
     * returns url for a current payment
     * @param string $token
     * @return string url
     * @throws Exception
     */
    public function getPaymentUrl($token = null)
    {
        if (is_null($token)) {
            $token = $this->token;
        }
        if (empty($token)) {
            return null;
        }
        $settings = PaypalSettings::find()->one();
        if ($settings->mode == PaypalSettings::MODE_SANDBOX) {
            return self::EXPRESS_CHECKOUT_SANDBOX.$token;
        }
        if ($settings->mode == PaypalSettings::MODE_LIVE) {
            return self::EXPRESS_CHECKOUT_LIVE.$token;
        }
        throw new Exception('Looks like settings paypal have no mode');
    }
    
    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }
    
    /**
     * @param string $token
     * @return modules\payment\models\PaypalExpressPayment
     */
    public static function findByToken($token)
    {
        return self::find()->where([
            'payment_token' => $token
        ])->one();
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Yii::$app->user->identityClass, ['id' => 'user_id']);
    }
}
