<?php

use yii\db\Schema;
use yii\db\Migration;

class m160212_120961_paypal_subscription_express_new_fields extends Migration
{
    public function up()
    {
        if (empty($this->db->getTableSchema('{{%paypal_subscription_express}}')->getColumn('description'))) {
            $this->addColumn('{{%paypal_subscription_express}}', 'description', $this->text());
        }
    }

    public function down()
    {
        if (!empty($this->db->getTableSchema('{{%paypal_subscription_express}}')->getColumn('description'))) {
            $this->dropColumn('{{%paypal_subscription_express}}', 'description');
        }
    }
}
