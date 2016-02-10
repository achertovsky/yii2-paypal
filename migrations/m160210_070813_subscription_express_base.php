<?php

use yii\db\Schema;
use yii\db\Migration;

class m160210_070813_subscription_express_base extends Migration
{
    public function up()
    {
    	if (empty($this->db->getTableSchema('{{%paypal_subscription_express}}'))) {
    		$this->createTable('{{%paypal_subscription_express}}', [
    			'id' => $this->primaryKey(),
    			'user_id' => $this->integer(),
			]);
			$this->addForeignKey('fk_paypal_subscription_express_user_id', '{{%paypal_subscription_express}}', 'user_id', '{{%user}}', 'id', 'CASCADE', 'CASCADE');
    	}
    }

    public function down()
    {
    	if (!empty($this->db->getTableSchema('{{%paypal_subscription_express}}'))) {
    		$this->dropForeignKey('fk_paypal_subscription_express_user_id', '{{%paypal_subscription_express}}');
    		$this->dropTable('{{%paypal_subscription_express}}');
    	}
    }
}
