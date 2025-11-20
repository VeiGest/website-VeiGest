<?php

use yii\db\Migration;

/**
 * Handles the modification of table `{{%users}}` to add VeiGest fields.
 * Extends the existing Yii2 users table with driver and company fields.
 */
class m251118_000003_create_users_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Adicionar campos VeiGest à tabela users existente do Yii2
        // Verificar se as colunas já existem antes de adicionar
        
        $schema = $this->db->schema->getTableSchema('{{%users}}');
        if ($schema === null) {
            throw new \yii\db\Exception('Table {{%users}} does not exist. Please run the init migration first.');
        }
        
        if (!isset($schema->columns['company_id'])) {
            $this->addColumn('{{%users}}', 'company_id', $this->integer()->notNull()->defaultValue(1));
        }
        
        if (!isset($schema->columns['nome'])) {
            $this->addColumn('{{%users}}', 'nome', $this->string(150)->notNull()->defaultValue(''));
        }
        
        if (!isset($schema->columns['telefone'])) {
            $this->addColumn('{{%users}}', 'telefone', $this->string(20));
        }
        
        if (!isset($schema->columns['estado'])) {
            $this->addColumn('{{%users}}', 'estado', "ENUM('ativo','inativo') NOT NULL DEFAULT 'ativo'");
        }
        
        // Campos de condutor (apenas preenchidos se for condutor)
        if (!isset($schema->columns['numero_carta'])) {
            $this->addColumn('{{%users}}', 'numero_carta', $this->string(50));
        }
        
        if (!isset($schema->columns['validade_carta'])) {
            $this->addColumn('{{%users}}', 'validade_carta', $this->date());
        }
        
        if (!isset($schema->columns['foto'])) {
            $this->addColumn('{{%users}}', 'foto', $this->string(255));
        }

        // Criar índices e chaves únicas
        // Verificar se o índice de email existe antes de removê-lo
        try {
            $this->execute('ALTER TABLE {{%users}} DROP INDEX email');
        } catch (\Exception $e) {
            echo "Index 'email' does not exist or already dropped. Continuing...\n";
        }
        
        // Criar índices apenas se não existirem
        $allIndexes = $this->db->createCommand("SHOW INDEX FROM {{%users}}")->queryAll();
        $indexNames = array_column($allIndexes, 'Key_name');

        if (!in_array('uk_email_company', $indexNames)) {
            $this->createIndex('uk_email_company', '{{%users}}', ['email', 'company_id'], true);
        } else {
            echo "Index 'uk_email_company' já existe. Pulando criação...\n";
        }
        if (!in_array('idx_company_id', $indexNames)) {
            $this->createIndex('idx_company_id', '{{%users}}', 'company_id');
        } else {
            echo "Index 'idx_company_id' já existe. Pulando criação...\n";
        }
        if (!in_array('idx_estado', $indexNames)) {
            $this->createIndex('idx_estado', '{{%users}}', 'estado');
        } else {
            echo "Index 'idx_estado' já existe. Pulando criação...\n";
        }
        if (!in_array('idx_validade_carta', $indexNames)) {
            $this->createIndex('idx_validade_carta', '{{%users}}', 'validade_carta');
        } else {
            echo "Index 'idx_validade_carta' já existe. Pulando criação...\n";
        }

        // Adicionar chave estrangeira
        // Criar foreign key apenas se não existir
        $fkExists = false;
        $tableName = $this->db->getTableSchema('{{%users}}')->fullName;
        $fks = $this->db->createCommand("SHOW CREATE TABLE {{%users}}")
            ->queryOne();
        if ($fks && isset($fks['Create Table']) && strpos($fks['Create Table'], 'CONSTRAINT `fk_users_company`') !== false) {
            $fkExists = true;
        }
        if (!$fkExists) {
            $this->addForeignKey(
                'fk_users_company',
                '{{%users}}',
                'company_id',
                '{{%companies}}',
                'id',
                'CASCADE'
            );
        } else {
            echo "Foreign key 'fk_users_company' já existe. Pulando criação...\n";
        }

        // Garante que o usuário admin sempre existe e tem senha conhecida (apenas campos padrão do Yii2)
        $adminUsername = 'veigest_admin';
        $adminEmail = 'veigest_admin@veigest.com';
        $adminPasswordHash = '$2a$12$hrDfGF6ZCfeGFaahX1SVDudw918BcGdvv1BHTDkVWnFHQhI44yCQK'; // senha: 12345
        $now = time();
        $authKey = Yii::$app->security->generateRandomString();

        $user = (new \yii\db\Query())
            ->from('{{%users}}')
            ->where(['username' => $adminUsername])
            ->one();

        if ($user) {
            $this->update('{{%users}}', [
                'email' => $adminEmail,
                'password_hash' => $adminPasswordHash,
                'auth_key' => $authKey,
                'status' => 10,
                'updated_at' => $now,
            ], ['id' => $user['id']]);
            $adminId = $user['id'];
        } else {
            $this->insert('{{%users}}', [
                'username' => $adminUsername,
                'email' => $adminEmail,
                'password_hash' => $adminPasswordHash,
                'auth_key' => $authKey,
                'status' => 10,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $adminId = $this->db->getLastInsertID();
        }

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_users_company', '{{%users}}');
        $this->dropColumn('{{%users}}', 'company_id');
        $this->dropColumn('{{%users}}', 'nome');
        $this->dropColumn('{{%users}}', 'telefone');
        $this->dropColumn('{{%users}}', 'estado');
        $this->dropColumn('{{%users}}', 'numero_carta');
        $this->dropColumn('{{%users}}', 'validade_carta');
        $this->dropColumn('{{%users}}', 'foto');
    }
}