# maplocation

Description
======
Module for easy yii2 payments

Features:  
Now only express payment

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
EXPRESS PAYMENT HOW TO:  
METHOD 1:  
Just redirect user to
```
use yii\helpers\Url;

$numericValue = 15;
//or
$numericValue = 15.2;
return $this->redirect(Url::toRoute(['/payment/payment/pay', 'price' => $numericValue]));
```
METHOD 2:

use yii\helpers\Url;
use achertovsky\paypal\models\PaypalExpressPayment;

$numericValue = 15;
//or
$numericValue = 15.2;

$payment = new PaypalExpressPayment();
$payment->setScenario('prepare');
$payment->setAttributes([
	'user_id' => 1,
	'payment_price' => $numericValue,
	'currency' => 'USD',
]);
$payment->save();
return $this->redirect(Url::toRoute(['/payment/payment/pay', 'price' => $numericValue, 'modelId' => $payment->id]));