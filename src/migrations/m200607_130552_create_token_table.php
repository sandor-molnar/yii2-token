<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%token}}`.
 */
class m200607_130552_create_token_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%token}}', [
            'id' => $this->primaryKey(11),
            'token' => $this->string(32)->null(),
            'type' => $this->tinyInteger()->unsigned()->notNull(),
            'entity_id' => $this->integer(11)->unsigned()->notNull(),
            'entity_type' => $this->tinyInteger()->unsigned()->notNull(),
            'expire' => $this->timestamp()->null(),
            'status' => $this->tinyInteger()->notNull(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%token}}');
    }
}
