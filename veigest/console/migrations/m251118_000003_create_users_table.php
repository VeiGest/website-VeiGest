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
        $this->addColumn('{{%user}}', 'company_id', $this->integer()->notNull()->defaultValue(1));
        $this->addColumn('{{%user}}', 'nome', $this->string(150)->notNull()->defaultValue(''));
        $this->addColumn('{{%user}}', 'telefone', $this->string(20));
        $this->addColumn('{{%user}}', 'estado', "ENUM('ativo','inativo') NOT NULL DEFAULT 'ativo'");
        
        // Campos de condutor (apenas preenchidos se for condutor)
        $this->addColumn('{{%user}}', 'numero_carta', $this->string(50));
        $this->addColumn('{{%user}}', 'validade_carta', $this->date());
        $this->addColumn('{{%user}}', 'foto', $this->string(255));

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
                'password_hash' => '$2a$12$/piK/Am/.6Wau7PpIzvO5ergX4AG17Xzk5RicS1Yom6YSsE5sSlgG', // 'admin'
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