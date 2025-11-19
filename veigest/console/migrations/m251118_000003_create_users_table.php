<?php

use yii\db\Migration;

/**
 * Handles the modification of table `{{%user}}` to add VeiGest fields.
 * Extends the existing Yii2 user table with driver and company fields.
 */
class m251118_000003_create_users_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Adicionar campos VeiGest à tabela user existente do Yii2
        // Verificar se as colunas já existem antes de adicionar
        
        $schema = $this->db->schema->getTableSchema('{{%user}}');
        if ($schema === null) {
            throw new \yii\db\Exception('Table {{%user}} does not exist. Please run the init migration first.');
        }
        
        if (!isset($schema->columns['company_id'])) {
            $this->addColumn('{{%user}}', 'company_id', $this->integer()->notNull()->defaultValue(1));
        }
        
        if (!isset($schema->columns['nome'])) {
            $this->addColumn('{{%user}}', 'nome', $this->string(150)->notNull()->defaultValue(''));
        }
        
        if (!isset($schema->columns['telefone'])) {
            $this->addColumn('{{%user}}', 'telefone', $this->string(20));
        }
        
        if (!isset($schema->columns['estado'])) {
            $this->addColumn('{{%user}}', 'estado', "ENUM('ativo','inativo') NOT NULL DEFAULT 'ativo'");
        }
        
        // Campos de condutor (apenas preenchidos se for condutor)
        if (!isset($schema->columns['numero_carta'])) {
            $this->addColumn('{{%user}}', 'numero_carta', $this->string(50));
        }
        
        if (!isset($schema->columns['validade_carta'])) {
            $this->addColumn('{{%user}}', 'validade_carta', $this->date());
        }
        
        if (!isset($schema->columns['foto'])) {
            $this->addColumn('{{%user}}', 'foto', $this->string(255));
        }

        // Criar índices e chaves únicas
        $this->createIndex('uk_email_company', '{{%user}}', ['email', 'company_id'], true);
        $this->createIndex('idx_company_id', '{{%user}}', 'company_id');
        $this->createIndex('idx_estado', '{{%user}}', 'estado');
        $this->createIndex('idx_validade_carta', '{{%user}}', 'validade_carta');

        // Adicionar chave estrangeira
        $this->addForeignKey(
            'fk_user_company',
            '{{%user}}',
            'company_id',
            '{{%companies}}',
            'id',
            'CASCADE'
        );

        // Atualizar utilizador admin existente
        $this->update('{{%user}}', [
            'company_id' => 1,
            'nome' => 'Administrator',
            'estado' => 'ativo',
        ], ['email' => 'admin@example.com']);
        
        // Inserir utilizador VeiGest se não existir
        $adminExists = $this->db->createCommand('SELECT COUNT(*) FROM {{%user}} WHERE email = :email')
            ->bindValue(':email', 'admin@veigest.com')
            ->queryScalar();
            
        if (!$adminExists) {
            $this->insert('{{%user}}', [
                'company_id' => 1,
                'nome' => 'VeiGest Admin',
                'username' => 'admin', // Campo obrigatório do Yii2
                'email' => 'admin@veigest.com',
                'password_hash' => '$2a$12$EGpeLy0wPpG4vBGeMlpiGODWhmRgYpZLhtLw.H4x9xGTig8fTfH2a', // 'admin'
                'estado' => 'ativo',
                'auth_key' => 'veigest_admin_key',
                'status' => 10,
                'created_at' => time(),
                'updated_at' => time()
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_user_company', '{{%user}}');
        $this->dropColumn('{{%user}}', 'company_id');
        $this->dropColumn('{{%user}}', 'nome');
        $this->dropColumn('{{%user}}', 'telefone');
        $this->dropColumn('{{%user}}', 'estado');
        $this->dropColumn('{{%user}}', 'numero_carta');
        $this->dropColumn('{{%user}}', 'validade_carta');
        $this->dropColumn('{{%user}}', 'foto');
    }
}