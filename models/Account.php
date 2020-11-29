<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "accounts".
 *
 * @property int $id
 * @property int|null $client_uid
 * @property int|null $login
 */
class Account extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'accounts';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['client_uid', 'login'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'client_uid' => 'Client Uid',
            'login' => 'Login',
        ];
    }

    public function getTrades()
    {
        return $this->hasMany(Trade::class, ['login' => 'login']);
    }

    public function getClient()
    {
        return $this->hasOne(Client::class, ['client_uid' => 'client_uid']);
    }
}
