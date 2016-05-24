<?php

use yii\db\Migration;

class m160524_185836_change_status_field extends Migration
{
    public function up()
    {
        if (!empty($this->db->getTableSchema('{{%paypal_subscription_express}}'))) {
            $this->alterColumn('{{%paypal_subscription_express}}', 'subscription_status', $this->string());
        }
    }

    public function down()
    {
        echo "m160524_185836_change cannot be reverted.\n";
    }
}
