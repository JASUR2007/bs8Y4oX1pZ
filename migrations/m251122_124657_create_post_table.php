<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%post}}`.
 */
class m251122_124657_create_post_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%post}}', [
            'id'            => $this->primaryKey(),
            'author_name'   => $this->string(15)->notNull(),
            'email'         => $this->string()->notNull(),
            'message'       => $this->text()->notNull(),
            'ip'            => $this->string(45)->notNull(),
            'image'         => $this->string()->null(),
            'created_at'    => $this->integer()->notNull(),
            'deleted_at'    => $this->integer()->null(),
            'token'         => $this->string(64)->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%post}}');
    }
}
