<?php

namespace achertovsky\paypal\models;

use achertovsky\paypal\models\PaypalSettings;
use PayPalAPIInterfaceServiceService;
use PaymentDetailsType;
use BasicAmountType;
use SetExpressCheckoutRequestDetailsType;
use BillingAgreementDetailsType;
use SetExpressCheckoutRequestType;
use SetExpressCheckoutReq;
use yii\helpers\ArrayHelper;
use yii\behaviors\TimestampBehavior;
use Yii;
use RecurringPaymentsProfileDetailsType;
use BillingPeriodDetailsType;
use ScheduleDetailsType;
use CreateRecurringPaymentsProfileRequestDetailsType;
use CreateRecurringPaymentsProfileRequestType;
use CreateRecurringPaymentsProfileReq;
use GetRecurringPaymentsProfileDetailsRequestType;
use GetRecurringPaymentsProfileDetailsReq;
use ManageRecurringPaymentsProfileStatusReq;
use ManageRecurringPaymentsProfileStatusRequestType;
use ManageRecurringPaymentsProfileStatusRequestDetailsType;

/**
 * @param int $user_id
 * @param double $price
 * @param string $token
 * @param string $currency
 * @param int $period
 * @param int $created_at
 * @param int $updated_at
 * @param string $status
 * @param text $errors
 * @param text $description
 * @param string $paypal_profile_id
 */
class PaypalSubscriptionExpress extends \yii\db\ActiveRecord
{
    const EXPRESS_CHECKOUT_SANDBOX = 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=';
    const EXPRESS_CHECKOUT_LIVE = 'https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=';

    const STATUS_INITIALIZED = 'INITIALIZED';
    const STATUS_CREATED_BY_SITE = 'CREATED_BY_SITE';
    const STATUS_ERROR = 'ERROR';
    const STATUS_SUCCESS = 'SUCCESS';

    const SUBSCRIPTION_STATUS_ACTIVE = 1;
    const SUBSCRIPTION_STATUS_UNACTIVE = 0;
    const SUBSCRIPTION_STATUS_CANCELLED = 3;

    public $subscriptionUrl;
    public $cancelUrl;
    public $successUrl;
    public $ECVersion;

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
     *  @inheritdoc 
     */
    public function rules()
    {
        return [
            [['user_id', 'created_at', 'updated_at', 'period', 'subscription_status', 'next_billing_gmt', 'cycles_completed'], 'integer', 'min' => 0],
            [['errors', 'description', 'paypal_profile_id'], 'string'],
            ['status', 'safe'],
            [['price', 'currency', 'period', 'token', 'description'], 'required'],
        ];
    }

    /**
     *  @inheritdoc 
     */
    public function scenarios()
    {
        return ArrayHelper::merge(parent::scenarios(), [
            'prepare' => ['user_id', 'price', 'currency', 'description', 'period'],
        ]);
    }

    /**
     * creates subscription url
     * @return string
     */
    public function prepareSubscriptionUrl($description = null)
    {
        if (is_null($description)) {
            $description = 'This is subscription flow. Be careful before accept it. Price is '.$this->price.' '.$this->currency.' and periodicity of payment is '.$this->period.' days.';
        } else {
            $this->description = $description;
        }
        if (empty($this->description)) {
            $this->description = $description;
        }
        $this->setScenario('prepare');
        if (!$this->validate()) {
            return false;
        }
        $this->setScenario('default');
        $settings = PaypalSettings::find()->one();
        $config = [
            'mode' => $settings->mode ? 'live' : 'sandbox',
            'acct1.UserName' => $settings->api_username,
            'acct1.Password' => $settings->api_password,
            'acct1.Signature' => $settings->api_signature,
        ];
        $paypalService = new PayPalAPIInterfaceServiceService($config);
        $paymentDetails= new PaymentDetailsType();

        $orderTotal = new BasicAmountType();
        $orderTotal->currencyID = 'USD';
        $orderTotal->value = 0;

        $paymentDetails->OrderTotal = $orderTotal;
        $paymentDetails->PaymentAction = 'Sale';

        $setECReqDetails = new SetExpressCheckoutRequestDetailsType();
        $setECReqDetails->PaymentDetails[0] = $paymentDetails;
        $setECReqDetails->CancelURL = $this->cancelUrl;
        $setECReqDetails->ReturnURL = $this->successUrl;
          
        $billingAgreementDetails = new BillingAgreementDetailsType('RecurringPayments');
        $billingAgreementDetails->BillingAgreementDescription = $this->description;
        $setECReqDetails->BillingAgreementDetails = array($billingAgreementDetails);

        $setECReqType = new SetExpressCheckoutRequestType();
        $setECReqType->Version = $this->ECVersion;
        $setECReqType->SetExpressCheckoutRequestDetails = $setECReqDetails;

        $setECReq = new SetExpressCheckoutReq();
        $setECReq->SetExpressCheckoutRequest = $setECReqType;

        $ECResponse = $paypalService->SetExpressCheckout($setECReq);
        if (strtolower($ECResponse->Ack) == 'success') {
            $this->token = $ECResponse->Token;
            $this->status = self::STATUS_CREATED_BY_SITE;
            $this->save();
        }
        if (empty($this->paymentUrl)) {
            return false;
        }
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
     * initializes new model by default values
     */
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

    /**
     * @param string $token
     * @return modules\payment\models\PaypalSubscriptionExpress
     */
    public static function findByToken($token)
    {
        return self::find()->where([
            'token' => $token
        ])->one();
    }

    /**
     * creates subscription on ebay
     * @return boolean
     */
    public function startSubscription()
    {
        if (!$this->validate() || $this->status == self::STATUS_ERROR) {
            return false;
        }
        if ($this->status == self::STATUS_SUCCESS) {
            return true;
        }

        $settings = PaypalSettings::find()->one();
        $config = [
            'mode' => $settings->mode ? 'live' : 'sandbox',
            'acct1.UserName' => $settings->api_username,
            'acct1.Password' => $settings->api_password,
            'acct1.Signature' => $settings->api_signature,
        ];

        $profileDetails = new RecurringPaymentsProfileDetailsType();
        $profileDetails->BillingStartDate = gmdate("Y-m-d H:i:s");;

        $paymentBillingPeriod = new BillingPeriodDetailsType();
        $paymentBillingPeriod->BillingFrequency = $this->period;
        $paymentBillingPeriod->BillingPeriod = "Day";
        $paymentBillingPeriod->Amount = new BasicAmountType("USD", $this->price);

        $scheduleDetails = new ScheduleDetailsType();
        $scheduleDetails->Description = $this->description;
        $scheduleDetails->PaymentPeriod = $paymentBillingPeriod;

        $createRPProfileRequestDetails = new CreateRecurringPaymentsProfileRequestDetailsType();
        $createRPProfileRequestDetails->Token = $this->token;
        $createRPProfileRequestDetails->ScheduleDetails = $scheduleDetails;
        $createRPProfileRequestDetails->RecurringPaymentsProfileDetails = $profileDetails;

        $createRPProfileRequest = new CreateRecurringPaymentsProfileRequestType();
        $createRPProfileRequest->CreateRecurringPaymentsProfileRequestDetails = $createRPProfileRequestDetails;

        $createRPProfileReq = new CreateRecurringPaymentsProfileReq();
        $createRPProfileReq->CreateRecurringPaymentsProfileRequest = $createRPProfileRequest;

        $paypalService = new PayPalAPIInterfaceServiceService($config);
        $createRPProfileResponse = $paypalService->CreateRecurringPaymentsProfile($createRPProfileReq);
        if (strtolower($createRPProfileResponse->Ack) == 'failure') {
            $this->errors = var_export($createRPProfileResponse->Errors, true);
            $this->status = self::STATUS_ERROR;
            $this->save();
            Yii::error('Errors: '.var_export($createRPProfileResponse->Errors, true));
            return false;
        }
        if (strtolower($createRPProfileResponse->Ack) == 'success') {
            $this->status = self::STATUS_SUCCESS;
            $this->subscription_status = self::SUBSCRIPTION_STATUS_ACTIVE;
            $this->paypal_profile_id = $createRPProfileResponse->CreateRecurringPaymentsProfileResponseDetails->ProfileID;
            return $this->save();
        }
        return false;
    }

    /**
     * @return boolean
     */
    public function isSubscriptionActive()
    {
        $settings = PaypalSettings::find()->one();
        $config = [
            'mode' => $settings->mode ? 'live' : 'sandbox',
            'acct1.UserName' => $settings->api_username,
            'acct1.Password' => $settings->api_password,
            'acct1.Signature' => $settings->api_signature,
        ];

        $reqType = new GetRecurringPaymentsProfileDetailsRequestType();
        $reqType->ProfileID = $this->paypal_profile_id;
        $req = new GetRecurringPaymentsProfileDetailsReq();
        $req->GetRecurringPaymentsProfileDetailsRequest = $reqType;

        $service = new PayPalAPIInterfaceServiceService($config);
        $response = $service->GetRecurringPaymentsProfileDetails($req);

        if (strtolower($response->Ack) == 'success') {
            $test = $response->GetRecurringPaymentsProfileDetailsResponseDetails->RecurringPaymentsSummary->OutstandingBalance;
            if ($response->GetRecurringPaymentsProfileDetailsResponseDetails->ProfileStatus == 'ActiveProfile') {
                $nextBillingGMTTimestamp = strtotime($response->GetRecurringPaymentsProfileDetailsResponseDetails->RecurringPaymentsSummary->NextBillingDate);
                $cyclesCompleted = $response->GetRecurringPaymentsProfileDetailsResponseDetails->RecurringPaymentsSummary->NumberCyclesCompleted;
                if (gmmktime() < $nextBillingGMTTimestamp && $cyclesCompleted == $this->cycles_completed) {
                    return true;
                } else {
                    if ($this->cycles_completed == $cyclesCompleted || $cyclesCompleted == 0) {
                        return false;
                    }
                    $this->subscription_status = SUBSCRIPTION_STATUS_ACTIVE;
                    $this->cycles_completed = $cyclesCompleted;
                    $this->next_billing_gmt = $nextBillingGMTTimestamp;
                    return $this->save();
                }
            } else {
                $this->subscription_status = SUBSCRIPTION_STATUS_UNACTIVE;
                $this->save();
                if (gmmktime() < $nextBillingGMTTimestamp && $cyclesCompleted == $this->cycles_completed) {
                    return true;
                }
                return false;
            }
        }
        
        return false;
    }

    /**
     * @return boolean
     */
    public function cancelSubscription()
    {
        $settings = PaypalSettings::find()->one();
        $config = [
            'mode' => $settings->mode ? 'live' : 'sandbox',
            'acct1.UserName' => $settings->api_username,
            'acct1.Password' => $settings->api_password,
            'acct1.Signature' => $settings->api_signature,
        ];

        $details = new ManageRecurringPaymentsProfileStatusRequestDetailsType($this->paypal_profile_id, 'Cancel');
        $reqType = new ManageRecurringPaymentsProfileStatusRequestType();
        $reqType->ManageRecurringPaymentsProfileStatusRequestDetails = $details;
        $req = new ManageRecurringPaymentsProfileStatusReq();
        $req->ManageRecurringPaymentsProfileStatusRequest = $reqType;

        $service = new PayPalAPIInterfaceServiceService($config);
        $response = $service->ManageRecurringPaymentsProfileStatus($req);

        if (strtolower($response->Ack) == 'success') {
            $this->subscription_status = self::SUBSCRIPTION_STATUS_CANCELLED;
            return $this->save();
        } else {
            foreach ($response->Errors as $error) {
                if ($error->ErrorCode == '11556') {
                    $this->subscription_status = self::SUBSCRIPTION_STATUS_CANCELLED;
                    return $this->save();
                }
            }
        }

        
        return false;
    }
}
