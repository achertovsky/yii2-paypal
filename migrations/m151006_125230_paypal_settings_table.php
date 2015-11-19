<?php

use yii\db\Schema;
use yii\db\Migration;

class m151006_125230_paypal_settings_table extends Migration
{
    public function up()
    {
        if (empty($this->db->getTableSchema('{{%paypal_settings}}'))) {
            $this->createTable('{{%paypal_settings}}', [
                'id' => $this->primaryKey(),
                'api_username' => $this->string(500),
                'api_password' => $this->string(500),
                'api_signature' => $this->string(500),
                'app_id' => $this->string(255),
                'merchant_email' => $this->string(500),
                'mode' => $this->smallInteger()->defaultValue(0),
            ]);
        }
        $this->insert('{{%paypal_settings}}', [
            'api_username' => 'devm_api1.gmail.com',
            'api_password' => '7HZPHQNC8RMMBSM8',
            'api_signature' => 'AFcWxV21C7fd0v3bYYYRCpSSRl31AOvRcycvnXBlMcY0G-9gBMD31JwN',
            'app_id' => 'APP-80W284485P519543T',
            'merchant_email' => 'devm@gmail.com',
            'mode' => 0,
        ]);
    }

    public function down()
    {
        if (!empty($this->db->getTableSchema('{{%paypal_settings}}'))) {
            $this->dropTable('{{%paypal_settings}}');
        }
    }
}
