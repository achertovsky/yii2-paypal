<?php

namespace achertovsky\paypal\console\controllers;

use Yii;
use achertovsky\paypal\models\PaypalSubscriptionExpress;

class CronController extends \yii\console\Controller
{
	public function actionSubscriptions()
	{
		$subscriptions = PaypalSubscriptionExpress::find()->where([
			'and',
			['!=', 'status', PaypalSubscriptionExpress::SUBSCRIPTION_STATUS_CANCELLED],
		])->all();
		foreach ($subscriptions as $subscription) {
			$subscription->isSubscriptionActive();
		}
	}
}
