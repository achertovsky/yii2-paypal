<?php

use yii\db\Schema;
use yii\db\Migration;

class m151007_080907_paypal_express_payments extends Migration
{
    public function up()
    {
        if (empty($this->db->getTableSchema('{{%paypal_express_payment}}'))) {
            $this->createTable('{{%paypal_express_payment}}', [
                'id' => $this->primaryKey(),
                'user_id' => $this->integer(),
                'status' => $this->string(255),
                'payment_token' => $this->string(500),
                'payment_price' => $this->decimal(12, 2),
                'currency' => $this->string(10),
                'errors' => $this->text(),
                'created_at' => $this->integer(),
                'updated_at' => $this->integer(),
            ]);
        }
    }

    public function down()
    {
        if (!empty($this->db->getTableSchema('{{%paypal_express_payment}}'))) {
            $this->dropTable('{{%paypal_express_payment}}');
        }
    }
}
