<?php

namespace achertovsky\paypal\models;

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
                    'merchant_email'
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
        ];
    }
    
    
    /**
     * this model must have only one row in table
     * @param type $insert
     * @return boolean
     */
    public function beforeSave($insert)
    {
        Yii::$app->cache->delete(PaypalSettings::className().'settings');
        parent::beforeSave($insert);
        if ($insert) {
            return false;
        }
        return true;
    }
    
    /**
     * @return PaypalSettings
     */
    public static function getSettings()
    {
        if (!Yii::$app->hasModule('payment')) {
            return $settings = PaypalSettings::find()->one();
        }
        if (!Yii::$app->getModule('payment')->enableSettingsCache) {
            return $settings = PaypalSettings::find()->one();
        }
        if (!Yii::$app->has('cache')) {
            return $settings = PaypalSettings::find()->one();
        }
        $cacheName = PaypalSettings::className().'settings';
        if (Yii::$app->cache->exists($cacheName)) {
            return Yii::$app->cache->get($cacheName);
        }
        $settings = PaypalSettings::find()->one();
        Yii::$app->cache->set($cacheName, $settings);
        return $settings;
    }
}
