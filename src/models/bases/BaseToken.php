<?php

namespace sshpackages\yii2token\models\bases;

use Yii;
use sshpackages\yii2token\models\queries\TokenQuery;

/**
 * THIS IS AND AUTO GENERATED FILE. DO NOT CHANGE!
 *
 * @property int $id
 * @property string $token
 * @property int $type
 * @property int $entity_id
 * @property int $entity_type
 * @property string $expire
 * @property int $status
 * @property string $created_at
 * @property string $updated_at
 */
class BaseToken extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'token';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type', 'entity_id', 'entity_type', 'status'], 'required'],
            [['type', 'entity_id', 'entity_type', 'status'], 'integer'],
            [['expire', 'created_at', 'updated_at'], 'safe'],
            [['token'], 'string', 'max' => 32],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('token', 'ID'),
            'token' => Yii::t('token', 'Token'),
            'type' => Yii::t('token', 'Type'),
            'entity_id' => Yii::t('token', 'Entity ID'),
            'entity_type' => Yii::t('token', 'Entity Type'),
            'expire' => Yii::t('token', 'Expire'),
            'status' => Yii::t('token', 'Status'),
            'created_at' => Yii::t('token', 'Created At'),
            'updated_at' => Yii::t('token', 'Updated At'),
        ];
    }

    /**
     * {@inheritdoc}
     * @return \sshpackages\yii2token\models\queries\TokenQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \sshpackages\yii2token\models\queries\TokenQuery(get_called_class());
    }
}
