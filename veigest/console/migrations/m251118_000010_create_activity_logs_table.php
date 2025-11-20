<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%activity_logs}}`.
 */
class m251118_000010_create_activity_logs_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%activity_logs}}', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer()->notNull(),
            'user_id' => $this->integer(),
            'acao' => $this->string(255)->notNull(),
            'entidade' => $this->string(100)->notNull()->comment('Ex: vehicle, document, user'),
            'entidade_id' => $this->integer(),
            'detalhes' => $this->json(),
            'ip' => $this->string(45),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Criar índices
        $this->createIndex('idx_created_at', '{{%activity_logs}}', 'created_at');
        $this->createIndex('idx_entidade', '{{%activity_logs}}', ['entidade', 'entidade_id']);
        $this->createIndex('idx_user_id', '{{%activity_logs}}', 'user_id');

        // Adicionar chaves estrangeiras
        $this->addForeignKey(
            'fk_activity_logs_company',
            '{{%activity_logs}}',
            'company_id',
            '{{%companies}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_activity_logs_user',
            '{{%activity_logs}}',
            'user_id',
            '{{%users}}',
            'id',
            'SET NULL'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_activity_logs_user', '{{%activity_logs}}');
        $this->dropForeignKey('fk_activity_logs_company', '{{%activity_logs}}');
        $this->dropTable('{{%activity_logs}}');
    }
}