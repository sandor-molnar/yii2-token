<?php

namespace sanyisasha\yii2token\components;

use sanyisasha\yii2token\models\Token;
use yii\base\Component;

class TokenComponent extends Component
{
    public $tokenModel = Token::class;
    public $entityTypeUser = Token::ENTITY_TYPE_USER;

    public function generateUserToken($userId, $type = null)
    {
        $tokenModel = $this->tokenModel;
        return $tokenModel::generateToken($this->entityTypeUser, $userId, $type);
    }

    public function findByToken($token, $type = null)
    {
        $tokenModel = $this->tokenModel;
        return $tokenModel::findByToken($token, $type);
    }
}
