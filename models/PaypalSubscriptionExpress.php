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

class PaypalSubscriptionExpress extends \yii\db\ActiveRecord
{
    const EXPRESS_CHECKOUT_SANDBOX = 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=';
    const EXPRESS_CHECKOUT_LIVE = 'https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=';

    public $subscriptionUrl;
    public $cancelUrl;
    public $currency;
    public $successUrl;
    public $ECVersion;
    protected $token;

    /**
     * creates subscription url
     * @return string
     */
    public function prepareSubscriptionUrl($description = 'This is subscription flow. Be careful before accept it')
    {
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
        $billingAgreementDetails->BillingAgreementDescription = $description;
        $setECReqDetails->BillingAgreementDetails = array($billingAgreementDetails);

        $setECReqType = new SetExpressCheckoutRequestType();
        $setECReqType->Version = $this->ECVersion;
        $setECReqType->SetExpressCheckoutRequestDetails = $setECReqDetails;

        $setECReq = new SetExpressCheckoutReq();
        $setECReq->SetExpressCheckoutRequest = $setECReqType;

        $ECResponse = $paypalService->SetExpressCheckout($setECReq);
        if (strtolower($ECResponse->Ack) == 'success') {
            $this->token = $ECResponse->Token;
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
}
