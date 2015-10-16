<?php

namespace achertovsky\paypal-yii2\models;

use Yii;
use PayPalAPIInterfaceServiceService;

/**
 * This is the model class for table "paypal_settings".
 *
 * @property integer $id
 * @property string $api_username
 * @property string $api_password
 * @property string $api_signature
 * @property string $app_id
 * @property string $merchant_email
 * @property integer $mode
 */
class PaypalSettings extends \yii\db\ActiveRecord
{
    const MODE_SANDBOX = 0;
    const MODE_LIVE = 1;
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'paypal_settings';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                [
                    'api_username',
                    'api_password',
                    'api_signature',
                    'mode',
                    'app_id',
                    'merchant_email'
                ],
                'required'
            ],
            [
                [
                    'api_username',
                    'api_password',
                    'api_signature',
                    'app_id',
                ], function ($attribute) {
                    $this->$attribute = \yii\helpers\HtmlPurifier::process(
                        $this->$attribute,
                        [
                            'HTML.Allowed' => '',
                        ]
                    );
                }
            ],
            [
                [
                    'mode'
                ],
                'integer'
            ],
            [
                [
                    'api_username',
                    'api_password',
                    'api_signature'
                ],
                'string',
                'max' => 500
            ],
            [
                [
                    'marchant_email'
                ],
                'email'
            ],
            [
                [
                    'app_id'
                ],
                'string',
                'max' => 255
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'api_username' => 'Api Username',
            'api_password' => 'Api Password',
            'api_signature' => 'Api Signature',
            'app_id' => 'App ID',
            'merchant_email' => 'Merchant Email',
            'mode' => 'Mode',
            'pay_price' => 'Premium account price (USD)'
        ];
    }
    
    
    /**
     * this model must have only one row in table
     * @param type $insert
     * @return boolean
     */
    public function beforeSave($insert)
    {
        parent::beforeSave($insert);
        if ($insert) {
            return false;
        }
        return true;
    }
    
    /**
     * this model must have only one row in table
     * @return boolean
     */
    public function beforeDelete()
    {
        parent::beforeDelete();
        return false;
    }
}
