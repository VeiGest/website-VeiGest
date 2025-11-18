<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%vehicles}}`.
 */
class m251118_000005_create_vehicles_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%vehicles}}', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer()->notNull(),
            'matricula' => $this->string(20)->notNull(),
            'marca' => $this->string(100),
            'modelo' => $this->string(100),
            'ano' => $this->integer(),
            'tipo_combustivel' => "ENUM('gasolina','diesel','eletrico','hibrido','outro')",
            'quilometragem' => $this->integer()->notNull()->defaultValue(0),
            'estado' => "ENUM('ativo','manutencao','inativo') NOT NULL DEFAULT 'ativo'",
            'condutor_id' => $this->integer()->comment('Condutor atualmente atribuído'),
            'foto' => $this->string(255),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Criar índices e chaves únicas
        $this->createIndex('uk_matricula_company', '{{%vehicles}}', ['matricula', 'company_id'], true);
        $this->createIndex('idx_company_id', '{{%vehicles}}', 'company_id');
        $this->createIndex('idx_estado', '{{%vehicles}}', 'estado');
        $this->createIndex('idx_condutor_id', '{{%vehicles}}', 'condutor_id');

        // Adicionar chaves estrangeiras
        $this->addForeignKey(
            'fk_vehicles_company',
            '{{%vehicles}}',
            'company_id',
            '{{%companies}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_vehicles_condutor',
            '{{%vehicles}}',
            'condutor_id',
            '{{%user}}',
            'id',
            'SET NULL'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_vehicles_condutor', '{{%vehicles}}');
        $this->dropForeignKey('fk_vehicles_company', '{{%vehicles}}');
        $this->dropTable('{{%vehicles}}');
    }
}