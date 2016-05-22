<?php

use yii\db\Schema;
use yii\db\Migration;
use achertovsky\paypal\models\PaypalSubscriptionExpress;

class m160212_120963_paypal_subscription_express_new_fields extends Migration
{
    public function up()
    {
        if (empty($this->db->getTableSchema('{{%paypal_subscription_express}}')->getColumn('cycles_completed'))) {
            $this->addColumn('{{%paypal_subscription_express}}', 'cycles_completed', $this->integer());
        }
        if (empty($this->db->getTableSchema('{{%paypal_subscription_express}}')->getColumn('next_billing_gmt'))) {
            $this->addColumn('{{%paypal_subscription_express}}', 'next_billing_gmt', $this->integer());
        }
    }

    public function down()
    {
        if (!empty($this->db->getTableSchema('{{%paypal_subscription_express}}')->getColumn('cycles_completed'))) {
            $this->dropColumn('{{%paypal_subscription_express}}', 'cycles_completed');
        }
        if (!empty($this->db->getTableSchema('{{%paypal_subscription_express}}')->getColumn('next_billing_gmt'))) {
            $this->dropColumn('{{%paypal_subscription_express}}', 'next_billing_gmt');
        }
    }
}
