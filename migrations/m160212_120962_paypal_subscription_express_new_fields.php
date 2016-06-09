<?php

use yii\db\Schema;
use yii\db\Migration;
use achertovsky\paypal\models\PaypalSubscriptionExpress;

class m160212_120962_paypal_subscription_express_new_fields extends Migration
{
    public function up()
    {
        if (empty($this->db->getTableSchema('{{%paypal_subscription_express}}')->getColumn('paypal_profile_id'))) {
            $this->addColumn('{{%paypal_subscription_express}}', 'paypal_profile_id', $this->string());
        }
        if (empty($this->db->getTableSchema('{{%paypal_subscription_express}}')->getColumn('subscription_status'))) {
            $this->addColumn('{{%paypal_subscription_express}}', 'subscription_status', $this->integer());
        }
    }

    public function down()
    {
        if (!empty($this->db->getTableSchema('{{%paypal_subscription_express}}')->getColumn('paypal_profile_id'))) {
            $this->dropColumn('{{%paypal_subscription_express}}', 'paypal_profile_id');
        }
        if (!empty($this->db->getTableSchema('{{%paypal_subscription_express}}')->getColumn('subscription_status'))) {
            $this->dropColumn('{{%paypal_subscription_express}}', 'subscription_status');
        }
    }
}
