<?php

use yii\db\Schema;
use yii\db\Migration;

class m160212_120959_paypal_subscription_express_new_fields extends Migration
{
    public function up()
    {
        if (empty($this->db->getTableSchema('{{%paypal_subscription_express}}')->getColumn('price'))) {
            $this->addColumn('{{%paypal_subscription_express}}', 'price', $this->decimal(12, 2));
        }
        if (empty($this->db->getTableSchema('{{%paypal_subscription_express}}')->getColumn('token'))) {
            $this->addColumn('{{%paypal_subscription_express}}', 'token', $this->string(1000));
        }
        if (empty($this->db->getTableSchema('{{%paypal_subscription_express}}')->getColumn('currency'))) {
            $this->addColumn('{{%paypal_subscription_express}}', 'currency', $this->string(3));
        }
        if (empty($this->db->getTableSchema('{{%paypal_subscription_express}}')->getColumn('currency'))) {
            $this->addColumn('{{%paypal_subscription_express}}', 'currency', $this->decimal(12, 2));
        }
    }

    public function down()
    {
        if (!empty($this->db->getTableSchema('{{%paypal_subscription_express}}')->getColumn('price'))) {
            $this->addColumn('{{%paypal_subscription_express}}', 'price', $this->decimal(12, 2));
        }
        if (!empty($this->db->getTableSchema('{{%paypal_subscription_express}}')->getColumn('token'))) {
            $this->addColumn('{{%paypal_subscription_express}}', 'token', $this->string(1000));
        }
        if (!empty($this->db->getTableSchema('{{%paypal_subscription_express}}')->getColumn('currency'))) {
            $this->addColumn('{{%paypal_subscription_express}}', 'currency', $this->string(3));
        }
        if (!empty($this->db->getTableSchema('{{%paypal_subscription_express}}')->getColumn('currency'))) {
            $this->addColumn('{{%paypal_subscription_express}}', 'currency', $this->decimal(12, 2));
        }
    }
}
