<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%companies}}`.
 * Esta migração deve ser executada antes das outras pois companies é referenciada em outras tabelas.
 */
class m251118_000001_create_companies_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%companies}}', [
            'id' => $this->primaryKey(),
            'nome' => $this->string(200)->notNull()->comment('Nome da empresa'),
            'email' => $this->string(150)->comment('Email principal da empresa'),
            'telefone' => $this->string(20)->comment('Telefone principal'),
            'nif' => $this->string(20)->comment('NIF/NIPC da empresa'),
            'morada' => $this->text()->comment('Morada completa'),
            'codigo_postal' => $this->string(20)->comment('Código postal'),
            'localidade' => $this->string(100)->comment('Localidade'),
            'distrito' => $this->string(100)->comment('Distrito'),
            'pais' => $this->string(100)->defaultValue('Portugal')->comment('País'),
            'website' => $this->string(200)->comment('Website da empresa'),
            'logo' => $this->string(300)->comment('Caminho para o logo da empresa'),
            
            // Configurações do sistema
            'plano' => $this->string(50)->defaultValue('basic')->comment('Plano contratado: basic, premium, enterprise'),
            'limite_usuarios' => $this->integer()->defaultValue(5)->comment('Limite de utilizadores'),
            'limite_veiculos' => $this->integer()->defaultValue(10)->comment('Limite de veículos'),
            
            // Estados
            'estado' => $this->string(20)->defaultValue('ativo')->comment('Estado: ativo, suspenso, cancelado'),
            'data_criacao' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->comment('Data de criação'),
            'data_atualizacao' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP')->comment('Data da última atualização'),
            
            // Auditoria
            'criado_por' => $this->integer()->comment('ID do utilizador que criou'),
            'atualizado_por' => $this->integer()->comment('ID do utilizador que atualizou'),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Índices para performance (sem unique inline para evitar problemas de tamanho)
        $this->createIndex('idx_companies_email', '{{%companies}}', 'email', true);
        $this->createIndex('idx_companies_nif', '{{%companies}}', 'nif', true);
        $this->createIndex('idx_companies_estado', '{{%companies}}', 'estado');
        $this->createIndex('idx_companies_plano', '{{%companies}}', 'plano');

        // Inserir empresa padrão para desenvolvimento
        $this->insert('{{%companies}}', [
            'id' => 1,
            'nome' => 'VeiGest Demo',
            'email' => 'demo@veigest.com',
            'telefone' => '+351 123 456 789',
            'nif' => '123456789',
            'morada' => 'Rua Principal, 123',
            'codigo_postal' => '1000-000',
            'localidade' => 'Lisboa',
            'distrito' => 'Lisboa',
            'pais' => 'Portugal',
            'plano' => 'enterprise',
            'limite_usuarios' => 50,
            'limite_veiculos' => 100,
            'estado' => 'ativo'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%companies}}');
    }
}