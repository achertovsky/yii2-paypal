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
to start using it - please, add it to your modules section

you can use your attribute names.

fox example: 
```
'payment' => [
    'class' => 'achertovsky\paypal\Module',
    //here is arrays like in Url::toRoute()
    'ipnUrl' => ['/payment/payment/payment-notification'],
    'expressSuccessUrl' => ['/payment/payment/express-payment'],
    'cancelUrl' => ['/', '#' => 'cancel'],
],
```
[EXPRESS PAYMENT HOW TO](https://github.com/achertovsky/paypal-yii2/wiki/Express-payment)  

Configuration variables listing
======
```
public $ipnUrl = ['/payment/payment/payment-notification'];
public $expressSuccessUrl = ['/payment/payment/express-payment'];
public $subscriptionExpressSuccessUrl = ['/payment/payment/subscription-express-confirm'];
public $cancelUrl = ['/', '#' => 'cancel'];
public $currency = 'USD';
public $modelMap = [
    'PaypalExpressPayment' => 'achertovsky\paypal\models\PaypalExpressPayment',
    'PaypalSubscriptionExpress' => 'achertovsky\paypal\models\PaypalSubscriptionExpress',
];
public $ECVersion = '104.0';
public $enableExpressPayment = true;
public $enableSubscriptionExpress = true;
```
