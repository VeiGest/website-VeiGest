<?php

use yii\db\Migration;

/**
 * Migração inicial do VeiGest - Schema completo
 * DISABLED: This migration conflicts with the standard Yii2 user table approach
 */
class m241201_000001_initial_veigest_schema extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // This migration is intentionally disabled to avoid conflicts
        echo "This migration has been disabled to prevent table conflicts.\\n";
        return true;
        // Desabilitar verificação de chaves estrangeiras temporariamente
        $this->execute('SET FOREIGN_KEY_CHECKS = 0');

        // ============================================================================
        // 1. EMPRESAS
        // ============================================================================
        $this->createTable('companies', [
            'id' => $this->primaryKey(),
            'nome' => $this->string(200)->notNull(),
            'nif' => $this->string(20)->notNull()->unique(),
            'email' => $this->string(150),
            'telefone' => $this->string(20),
            'estado' => "ENUM('ativa','suspensa','inativa') NOT NULL DEFAULT 'ativa'",
            'plano' => "ENUM('basico','profissional','enterprise') NOT NULL DEFAULT 'basico'",
            'configuracoes' => 'JSON COMMENT \'Configurações específicas da empresa\'',
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        $this->createIndex('idx_nif', 'companies', 'nif');
        $this->createIndex('idx_estado', 'companies', 'estado');

        // ============================================================================
        // 2. RBAC YII2 (Sistema de Controlo de Acesso)
        // ============================================================================
        $this->createTable('auth_rule', [
            'name' => $this->string(64)->notNull(),
            'data' => $this->binary(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        $this->addPrimaryKey('pk_auth_rule', 'auth_rule', 'name');

        $this->createTable('auth_item', [
            'name' => $this->string(64)->notNull(),
            'type' => $this->tinyInteger()->notNull()->comment('1=role, 2=permission'),
            'description' => $this->text(),
            'rule_name' => $this->string(64),
            'data' => $this->binary(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        $this->addPrimaryKey('pk_auth_item', 'auth_item', 'name');
        $this->createIndex('idx_type', 'auth_item', 'type');

        $this->createTable('auth_item_child', [
            'parent' => $this->string(64)->notNull(),
            'child' => $this->string(64)->notNull(),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        $this->addPrimaryKey('pk_auth_item_child', 'auth_item_child', ['parent', 'child']);

        $this->createTable('auth_assignment', [
            'item_name' => $this->string(64)->notNull(),
            'user_id' => $this->string(64)->notNull(),
            'created_at' => $this->integer(),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        $this->addPrimaryKey('pk_auth_assignment', 'auth_assignment', ['item_name', 'user_id']);
        $this->createIndex('idx_user_id', 'auth_assignment', 'user_id');

        // ============================================================================
        // 3. UTILIZADORES
        // ============================================================================
        $this->createTable('users', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer()->notNull(),
            'nome' => $this->string(150)->notNull(),
            'email' => $this->string(150)->notNull(),
            'password_hash' => $this->string(255)->notNull(),
            'telefone' => $this->string(20),
            'estado' => "ENUM('ativo','inativo') NOT NULL DEFAULT 'ativo'",
            'auth_key' => $this->string(32)->comment('Para autenticação Yii2'),
            'password_reset_token' => $this->string(255),
            'numero_carta' => $this->string(50),
            'validade_carta' => $this->date(),
            'foto' => $this->string(255),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        $this->createIndex('uk_email_company', 'users', ['email', 'company_id'], true);
        $this->createIndex('idx_company_id', 'users', 'company_id');
        $this->createIndex('idx_estado', 'users', 'estado');
        $this->createIndex('idx_validade_carta', 'users', 'validade_carta');
        $this->createIndex('idx_password_reset_token', 'users', 'password_reset_token');

        // ============================================================================
        // 4. FICHEIROS
        // ============================================================================
        $this->createTable('files', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer()->notNull(),
            'nome_original' => $this->string(255)->notNull(),
            'tamanho' => $this->bigInteger()->notNull(),
            'caminho' => $this->string(500)->notNull()->comment('Caminho completo do ficheiro'),
            'uploaded_by' => $this->integer()->notNull(),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        $this->createIndex('idx_company_id', 'files', 'company_id');
        $this->createIndex('idx_uploaded_by', 'files', 'uploaded_by');

        // ============================================================================
        // 5. VEÍCULOS
        // ============================================================================
        $this->createTable('vehicles', [
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

        $this->createIndex('uk_matricula_company', 'vehicles', ['matricula', 'company_id'], true);
        $this->createIndex('idx_company_id', 'vehicles', 'company_id');
        $this->createIndex('idx_estado', 'vehicles', 'estado');
        $this->createIndex('idx_condutor_id', 'vehicles', 'condutor_id');

        // ============================================================================
        // 6. MANUTENÇÕES
        // ============================================================================
        $this->createTable('maintenances', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer()->notNull(),
            'vehicle_id' => $this->integer()->notNull(),
            'tipo' => $this->string(100)->notNull(),
            'descricao' => $this->text(),
            'data' => $this->date()->notNull(),
            'custo' => $this->decimal(10, 2)->defaultValue(0.00),
            'km_registro' => $this->integer(),
            'proxima_data' => $this->date(),
            'oficina' => $this->string(200),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        $this->createIndex('idx_vehicle_id', 'maintenances', 'vehicle_id');
        $this->createIndex('idx_data', 'maintenances', 'data');
        $this->createIndex('idx_proxima_data', 'maintenances', 'proxima_data');

        // ============================================================================
        // 7. DOCUMENTOS
        // ============================================================================
        $this->createTable('documents', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer()->notNull(),
            'file_id' => $this->integer()->notNull(),
            'vehicle_id' => $this->integer(),
            'driver_id' => $this->integer(),
            'tipo' => "ENUM('dua','seguro','inspecao','carta_conducao','outro') NOT NULL",
            'data_validade' => $this->date(),
            'status' => "ENUM('valido','expirado') NOT NULL DEFAULT 'valido'",
            'notas' => $this->text()->comment('Informações adicionais sobre o documento'),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        $this->createIndex('idx_file_id', 'documents', 'file_id');
        $this->createIndex('idx_vehicle_id', 'documents', 'vehicle_id');
        $this->createIndex('idx_driver_id', 'documents', 'driver_id');
        $this->createIndex('idx_data_validade', 'documents', 'data_validade');

        // ============================================================================
        // 8. COMBUSTÍVEL
        // ============================================================================
        $this->createTable('fuel_logs', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer()->notNull(),
            'vehicle_id' => $this->integer()->notNull(),
            'driver_id' => $this->integer(),
            'data' => $this->date()->notNull(),
            'litros' => $this->decimal(10, 2)->notNull(),
            'valor' => $this->decimal(10, 2)->notNull(),
            'preco_litro' => 'DECIMAL(8,4) AS (valor / litros) STORED',
            'km_atual' => $this->integer(),
            'notas' => $this->string(255),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        $this->createIndex('idx_vehicle_id', 'fuel_logs', 'vehicle_id');
        $this->createIndex('idx_data', 'fuel_logs', 'data');

        // ============================================================================
        // 9. ALERTAS
        // ============================================================================
        $this->createTable('alerts', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer()->notNull(),
            'tipo' => "ENUM('manutencao','documento','combustivel','outro') NOT NULL",
            'titulo' => $this->string(200)->notNull(),
            'descricao' => $this->text(),
            'prioridade' => "ENUM('baixa','media','alta','critica') NOT NULL DEFAULT 'media'",
            'status' => "ENUM('ativo','resolvido','ignorado') NOT NULL DEFAULT 'ativo'",
            'detalhes' => 'JSON COMMENT \'vehicle_id, document_id, user_id, etc.\'',
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'resolvido_em' => $this->dateTime(),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        $this->createIndex('idx_status', 'alerts', 'status');
        $this->createIndex('idx_company_id', 'alerts', 'company_id');
        $this->createIndex('idx_tipo', 'alerts', 'tipo');

        // ============================================================================
        // 10. LOGS DE ATIVIDADE
        // ============================================================================
        $this->createTable('activity_logs', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer()->notNull(),
            'user_id' => $this->integer(),
            'acao' => $this->string(255)->notNull(),
            'entidade' => $this->string(100)->notNull()->comment('Ex: vehicle, document, user'),
            'entidade_id' => $this->integer(),
            'detalhes' => 'JSON',
            'ip' => $this->string(45),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        $this->createIndex('idx_created_at', 'activity_logs', 'created_at');
        $this->createIndex('idx_entidade', 'activity_logs', ['entidade', 'entidade_id']);
        $this->createIndex('idx_user_id', 'activity_logs', 'user_id');

        // ============================================================================
        // CHAVES ESTRANGEIRAS
        // ============================================================================
        
        // auth_item
        $this->addForeignKey('fk_auth_item_rule', 'auth_item', 'rule_name', 'auth_rule', 'name', 'SET NULL', 'CASCADE');
        
        // auth_item_child
        $this->addForeignKey('fk_auth_item_child_parent', 'auth_item_child', 'parent', 'auth_item', 'name', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_auth_item_child_child', 'auth_item_child', 'child', 'auth_item', 'name', 'CASCADE', 'CASCADE');
        
        // auth_assignment
        $this->addForeignKey('fk_auth_assignment_item', 'auth_assignment', 'item_name', 'auth_item', 'name', 'CASCADE', 'CASCADE');
        
        // users
        $this->addForeignKey('fk_users_company', 'users', 'company_id', 'companies', 'id', 'CASCADE');
        
        // files
        $this->addForeignKey('fk_files_company', 'files', 'company_id', 'companies', 'id', 'CASCADE');
        $this->addForeignKey('fk_files_uploaded_by', 'files', 'uploaded_by', 'users', 'id', 'RESTRICT');
        
        // vehicles
        $this->addForeignKey('fk_vehicles_company', 'vehicles', 'company_id', 'companies', 'id', 'CASCADE');
        $this->addForeignKey('fk_vehicles_condutor', 'vehicles', 'condutor_id', 'users', 'id', 'SET NULL');
        
        // maintenances
        $this->addForeignKey('fk_maintenances_company', 'maintenances', 'company_id', 'companies', 'id', 'CASCADE');
        $this->addForeignKey('fk_maintenances_vehicle', 'maintenances', 'vehicle_id', 'vehicles', 'id', 'CASCADE');
        
        // documents
        $this->addForeignKey('fk_documents_company', 'documents', 'company_id', 'companies', 'id', 'CASCADE');
        $this->addForeignKey('fk_documents_file', 'documents', 'file_id', 'files', 'id', 'CASCADE');
        $this->addForeignKey('fk_documents_vehicle', 'documents', 'vehicle_id', 'vehicles', 'id', 'CASCADE');
        $this->addForeignKey('fk_documents_driver', 'documents', 'driver_id', 'users', 'id', 'CASCADE');
        
        // fuel_logs
        $this->addForeignKey('fk_fuel_logs_company', 'fuel_logs', 'company_id', 'companies', 'id', 'CASCADE');
        $this->addForeignKey('fk_fuel_logs_vehicle', 'fuel_logs', 'vehicle_id', 'vehicles', 'id', 'CASCADE');
        $this->addForeignKey('fk_fuel_logs_driver', 'fuel_logs', 'driver_id', 'users', 'id', 'SET NULL');
        
        // alerts
        $this->addForeignKey('fk_alerts_company', 'alerts', 'company_id', 'companies', 'id', 'CASCADE');
        
        // activity_logs
        $this->addForeignKey('fk_activity_logs_company', 'activity_logs', 'company_id', 'companies', 'id', 'CASCADE');
        $this->addForeignKey('fk_activity_logs_user', 'activity_logs', 'user_id', 'users', 'id', 'SET NULL');

        // Adicionar constraint para documents
        $this->execute('ALTER TABLE documents ADD CONSTRAINT chk_documents_entity CHECK (vehicle_id IS NOT NULL OR driver_id IS NOT NULL)');

        // Reabilitar verificação de chaves estrangeiras
        $this->execute('SET FOREIGN_KEY_CHECKS = 1');

        echo "Schema inicial do VeiGest criado com sucesso!\n";
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // This migration is intentionally disabled - nothing to rollback
        echo "This migration has been disabled - nothing to rollback.\\n";
        return true;
    }
}
