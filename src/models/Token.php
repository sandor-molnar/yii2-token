<?php

namespace sanyisasha\yii2token\models;

use Yii;
use yii\db\Expression;
use yii\helpers\StringHelper;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "token".
 *
 */
class Token extends \sanyisasha\yii2token\models\bases\BaseToken
{
    public const STATUS_UNUSED = 1;
    public const STATUS_USED = 2;

    public const TYPE_GLOBAL = 1;
    public const TYPE_USER_CONFIRMATION = 2;
    public const TYPE_USER_PASSWORD_RESET = 3;

    public const ENTITY_TYPE_USER = 1;
    public const ENTITY_TYPE_GLOBAL = 2;

    public const EXPIRE_HOURS = 72;

    public static function getStatusTexts()
    {
        return [
            static::STATUS_UNUSED => Yii::t('token', 'Unused'),
            static::STATUS_USED => Yii::t('token', 'Used'),
        ];
    }

    public static function getStatusText($status)
    {
        $statuses = static::getStatusTexts();
        return $statuses[$status] ?? null;
    }

    public function statusText()
    {
        return static::getStatusText($this->status);
    }

    public static function getTypeTexts()
    {
        return [
            static::TYPE_GLOBAL => Yii::t('token', 'Global'),
            static::TYPE_USER_CONFIRMATION => Yii::t('token', 'User confirmation'),
            static::TYPE_USER_PASSWORD_RESET => Yii::t('token', 'User password reset'),
        ];
    }

    public static function getTypeText($type)
    {
        $types = static::getTypeTexts();
        return $types[$type] ?? null;
    }

    public function typeText()
    {
        return static::getTypeText($this->type);
    }

    /**
     * Finds validation token by token
     * @param string $token
     * @param integer|null $type
     * @return Token|null
     */
    public static function findByToken($token, $type = null)
    {
        $q = static::find()->token($token)->status(static::STATUS_UNUSED);
        if ($type !== null) {
            $q->type($type);
        }
        return $q->one();
    }

    /**
     * Finds validation token by entity
     * @param $entity_type
     * @param $entity_id
     * @param null $type
     * @return array|Token|null
     */
    public static function findByEntity($entity_type, $entity_id, $type = self::TYPE_GLOBAL)
    {
        return static::find()
            ->entity($entity_type, $entity_id)
            ->status(static::STATUS_UNUSED)
            ->type($type ?? static::TYPE_GLOBAL)
            ->one();
    }

    /**
     * Generates a token for the given user.
     * @param $entity_type
     * @param $entity_id
     * @param null $type
     * @return string|null
     * @throws \Exception
     */
    public static function generateToken($entity_type, $entity_id, $type = self::TYPE_GLOBAL)
    {
        /** @var Token $token */
        $token = static::findByEntity($entity_type, $entity_id, $type);

        if ($token && $token->isValid()) {
            return $token->token;
        }

        $token = new static();
        $token->token = Yii::$app->security->generateRandomString(32);
        $token->entity_type = $entity_type;
        $token->entity_id = $entity_id;
        $token->type = $type ?? static::TYPE_GLOBAL;
        $token->status = static::STATUS_UNUSED;
        $token->expire = new Expression("(NOW() + INTERVAL " . static::EXPIRE_HOURS . " HOUR)");

        return $token->save() ? $token->token : null;
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
            return $this->save(false);
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isUsed()
    {
        Yii::error($this->status !== static::STATUS_UNUSED);
        return $this->status !== static::STATUS_UNUSED;
    }

    /**
     * @return bool
     */
    public function isExpired()
    {
        return strtotime($this->expire) < time();
    }

    /**
     * Check if the current token match with the needed type.
     * E.g. can't use password reset token to change email
     * @param $type
     * @return bool
     */
    public function isFor($type = null)
    {
        if (!$type) {
            return true;
        }
        return (int)$this->type === (int)$type;
    }

    /**
     * Check if the token is valid.
     * @param $type
     * @return bool
     */
    public function isValid($type = null)
    {
        if (($type !== null && !$this->isFor($type)) || $this->isUsed() || $this->isExpired()) {
            return false;
        }

        return true;
    }

    /**
     * Get the user
     * @return IdentityInterface
     */
    public function getUserEntity()
    {
        if ($this->entity_type === static::ENTITY_TYPE_USER) {
            $identityClass = Yii::$app->user->identityClass;
            return $identityClass::findOne(['id' => $this->entity_id]);
        }
        return null;
    }
}
