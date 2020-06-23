<?php

\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "token".
 *
 * @property int $id
 * @property string $token
 * @property string $type
 * @property int $user_id
 * @property string $expire
 * @property int $status
 * @property string $created_at
 * @property string $updated_at
 *
 *
 */
class TokenOld extends \yii\db\ActiveRecord
{
    public const STATUS_UNUSED = 1;
    public const STATUS_USED = 2;

    public const TYPE_PASSWORD = 1;
    public const TYPE_ACTIVATE_USER = 2;
    public const TYPE_EMAIL_CHANGE = 3;
    public const TYPE_EMAIL_CHANGE_NEW = 4;
    public const TYPE_DELETE_COMPANY = 5;

    public const EXPIRE_HOURS = 72;


    private $_user;

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
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'status', 'type', 'token'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'token' => 'Token',
            'type' => 'For',
            'user_id' => 'User ID',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public static function getTypeTexts() {
        return [
            static::TYPE_PASSWORD => 'Jelszó emlékeztető',
            static::TYPE_ACTIVATE_USER => 'Felhasználó aktiválás',
            static::TYPE_EMAIL_CHANGE => 'E-mail cím módosítása',
            static::TYPE_EMAIL_CHANGE_NEW => 'E-mail cím módosítása új',
        ];
    }

    public static function getStatusTexts() {
        return [
            static::STATUS_UNUSED => 'Nem felhasznált',
            static::STATUS_USED => 'Felhasznált',
        ];
    }


    public static function getTypeText($type) {
        $types = static::getTypeTexts();
        return $types[$type] ?? null;
    }

    public function typeText() {
        return static::getTypeText($this->type);
    }

    public static function getStatusText($status) {
        $statuses = static::getStatusTexts();
        return $statuses[$status] ?? null;
    }

    public function statusText() {
        return static::getStatusText($this->status);
    }

    /**
     * Finds validation token by token
     * @param $token
     * @return TokenOld|null
     */
    public static function findByToken($token, $type = null)
    {
        $q = static::find()
            ->where(['token' => $token, 'status' => static::STATUS_UNUSED])
            ->with('user');
        if ($type !== null) {
            $q->andWhere(['type' => $type]);
        }
        return $q->one();
    }

    /**
     * Find token by user_id and type
     * @param $user_id
     * @param $type
     * @return TokenOld|null
     */
    public static function findByUserAndType($user_id, $type)
    {
        return static::find()
            ->where(['user_id' => $user_id, 'type' => $type, 'status' => static::STATUS_UNUSED])
            ->orderBy(['created_at' => SORT_DESC])
            ->with('user')
            ->one();
    }

    /**
     * Generates a token for the given user.
     * @param $user_id
     * @param null $type
     * @return string|null
     * @throws \Exception
     */
    public static function generateToken($user_id, $type = null)
    {
        $token = static::findByUserAndType($user_id, $type);
        /** @var TokenOld $token */
        if ($token !== null && !$token->isExpired()) {
            $saved = true;
        } else {
            $token = new static();
            $token->token = random_int(100000000,999999999);
            $token->user_id = $user_id;
            $token->type = $type;
            $token->status = static::STATUS_UNUSED;
            $token->expire = new Expression("(NOW() + INTERVAL " . static::EXPIRE_HOURS . " HOUR)");//date("Y-m-d H:i:s", strtotime("72 hours"));
            $saved = $token->save();
        }

        return $saved && $token ? $token->token : null;
    }

    public function getUser() {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Use the current token if its not expired and not used.
     * Return false if it's can't be use.
     * @return bool
     */
    public function useToken()
    {
        if (!$this->isExpired() && !$this->isUsed()) {
            $this->status = static::STATUS_USED;
            return $this->save();
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isUsed()
    {
        return $this->status !== static::STATUS_UNUSED;
    }

    /**
     * @return bool
     */
    public function isExpired()
    {
        return $this->expire < date('Y-m-d H:i:s');
    }

    /**
     * Check if the current token match with the needed type.
     * E.g. can't use password reset token to change email
     * @param $type
     * @return bool
     */
    public function isFor($type)
    {
        return $this->type !== null && $this->type == $type;
    }

    /**
     * Check if the token is valid.
     * @param $type
     * @return bool
     */
    public function isValid($type = null) {
        if (($type !== null && !$this->isFor($type)) || $this->isUsed() || $this->isExpired()) {
            return false;
        }

        return true;
    }
}
