<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%files}}`.
 */
class m251118_000004_create_files_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%files}}', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer()->notNull(),
            'nome_original' => $this->string(255)->notNull(),
            'tamanho' => $this->bigInteger()->notNull(),
            'caminho' => $this->string(500)->notNull()->comment('Caminho completo do ficheiro'),
            'uploaded_by' => $this->integer()->notNull(),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Criar índices
        $this->createIndex('idx_company_id', '{{%files}}', 'company_id');
        $this->createIndex('idx_uploaded_by', '{{%files}}', 'uploaded_by');

        // Adicionar chaves estrangeiras
        $this->addForeignKey(
            'fk_files_company',
            '{{%files}}',
            'company_id',
            '{{%companies}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_files_uploaded_by',
            '{{%files}}',
            'uploaded_by',
            '{{%users}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_files_uploaded_by', '{{%files}}');
        $this->dropForeignKey('fk_files_company', '{{%files}}');
        $this->dropTable('{{%files}}');
    }
}