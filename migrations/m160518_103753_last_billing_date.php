<?php

use yii\db\Migration;

class m160518_103753_last_billing_date extends Migration
{
    public function up()
    {
        if ($this->db->getTableSchema('{{%paypal_subscription_express}}')->getColumn('next_billing_gmt')) {
            $this->dropColumn('{{%paypal_subscription_express}}', 'next_billing_gmt');
            $this->addColumn('{{%paypal_subscription_express}}', 'last_payment_date', $this->integer());
        }
    }

    public function down()
    {
        if ($this->db->getTableSchema('{{%paypal_subscription_express}}')->getColumn('last_payment_date')) {
            $this->dropColumn('{{%paypal_subscription_express}}', 'last_payment_date');
            $this->addColumn('{{%paypal_subscription_express}}', 'next_billing_gmt', $this->integer());
        }
    }
}
