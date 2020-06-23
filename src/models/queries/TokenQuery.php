<?php

namespace sanyisasha\yii2token\models\queries;

/**
 * This is the ActiveQuery class for [[\sanyisasha\yii2token\models\Token]].
 *
 * @see \sanyisasha\yii2token\models\Token
 */
class TokenQuery extends \yii\db\ActiveQuery
{
    public function token($token)
    {
        return $this->andWhere(['token' => $token]);
    }

    public function status($status)
    {
        return $this->andWhere(['status' => $status]);
    }

    public function type($type)
    {
        return $this->andWhere(['type' => $type]);
    }

    public function entity($entity_type, $entity_id)
    {
        return $this->andWhere(['entity_type' => $entity_type, 'entity_id' => $entity_id]);
    }


    /**
     * {@inheritdoc}
     * @return \sanyisasha\yii2token\models\Token[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return \sanyisasha\yii2token\models\Token|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
