<?php

use yii\db\Schema;
use yii\db\Migration;

class m160212_120960_paypal_subscription_express_new_fields extends Migration
{
    public function up()
    {
        if (empty($this->db->getTableSchema('{{%paypal_subscription_express}}')->getColumn('period'))) {
            $this->addColumn('{{%paypal_subscription_express}}', 'period', $this->integer());
        }
        if (empty($this->db->getTableSchema('{{%paypal_subscription_express}}')->getColumn('created_at'))) {
            $this->addColumn('{{%paypal_subscription_express}}', 'created_at', $this->integer());
        }
        if (empty($this->db->getTableSchema('{{%paypal_subscription_express}}')->getColumn('updated_at'))) {
            $this->addColumn('{{%paypal_subscription_express}}', 'updated_at', $this->integer());
        }
        if (empty($this->db->getTableSchema('{{%paypal_subscription_express}}')->getColumn('status'))) {
            $this->addColumn('{{%paypal_subscription_express}}', 'status', $this->string());
        }
        if (empty($this->db->getTableSchema('{{%paypal_subscription_express}}')->getColumn('errors'))) {
            $this->addColumn('{{%paypal_subscription_express}}', 'errors', $this->text());
        }
    }

    public function down()
    {
        if (!empty($this->db->getTableSchema('{{%paypal_subscription_express}}')->getColumn('period'))) {
            $this->dropColumn('{{%paypal_subscription_express}}', 'period');
        }
        if (!empty($this->db->getTableSchema('{{%paypal_subscription_express}}')->getColumn('created_at'))) {
            $this->dropColumn('{{%paypal_subscription_express}}', 'created_at');
        }
        if (!empty($this->db->getTableSchema('{{%paypal_subscription_express}}')->getColumn('updated_at'))) {
            $this->dropColumn('{{%paypal_subscription_express}}', 'updated_at');
        }
        if (!empty($this->db->getTableSchema('{{%paypal_subscription_express}}')->getColumn('status'))) {
            $this->dropColumn('{{%paypal_subscription_express}}', 'status');
        }
        if (!empty($this->db->getTableSchema('{{%paypal_subscription_express}}')->getColumn('errors'))) {
            $this->dropColumn('{{%paypal_subscription_express}}', 'errors');
        }
    }
}
