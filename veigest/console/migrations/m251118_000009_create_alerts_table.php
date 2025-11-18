<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%alerts}}`.
 */
class m251118_000009_create_alerts_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%alerts}}', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer()->notNull(),
            'tipo' => "ENUM('manutencao','documento','combustivel','outro') NOT NULL",
            'titulo' => $this->string(200)->notNull(),
            'descricao' => $this->text(),
            'prioridade' => "ENUM('baixa','media','alta','critica') NOT NULL DEFAULT 'media'",
            'status' => "ENUM('ativo','resolvido','ignorado') NOT NULL DEFAULT 'ativo'",
            'detalhes' => $this->json()->comment('vehicle_id, document_id, user_id, etc.'),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'resolvido_em' => $this->dateTime(),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Criar índices
        $this->createIndex('idx_status', '{{%alerts}}', 'status');
        $this->createIndex('idx_company_id', '{{%alerts}}', 'company_id');
        $this->createIndex('idx_tipo', '{{%alerts}}', 'tipo');

        // Adicionar chave estrangeira
        $this->addForeignKey(
            'fk_alerts_company',
            '{{%alerts}}',
            'company_id',
            '{{%companies}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_alerts_company', '{{%alerts}}');
        $this->dropTable('{{%alerts}}');
    }
}