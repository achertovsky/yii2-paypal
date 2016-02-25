# Paypal-yii2

Description
======
Module for easy yii2 payments

Features:  
Express payment  
Subscription (via express payment)

I hope it will be useful for you. 


Installing
======
The preferred way to install this extension is through composer.

```
{
	"require": {
	    "achertovsky/paypal-yii2": "@dev"
    }
}
```

or

```
	composer require achertovsky/paypal-yii2 "@dev"
```

update your db schema

```
php yii migrate/up --migrationPath=@vendor/achertovsky/paypal-yii2/migrations
```
Usage
======

_Currently application is in sandbox. You can edit values in DB or using GUI in backend (if you have backend configured)_  

to start using it - please, add it to your modules section

you can use your attribute names.

fox example: 
```
'payment' => [
    'class' => 'achertovsky\paypal\Module',
],
```
[EXPRESS PAYMENT HOW TO](https://github.com/achertovsky/paypal-yii2/wiki/Express-payment)  
[SUBSCRIPTION VIA EXPRESS PAYMENT HOW TO](https://github.com/achertovsky/paypal-yii2/wiki/Subscription-via-express-payment)  

**Configuration variables listing**
```
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
```
