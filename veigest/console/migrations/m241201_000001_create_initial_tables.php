<?php

use yii\db\Migration;

/**
 * Handles the creation of initial tables for VeiGest system.
 */
class m241201_000001_create_initial_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Tabela de usuários
        try {
            if ($this->db->schema->getTableSchema('{{%user}}') === null) {
                $this->createTable('{{%user}}', [
                    'id' => $this->primaryKey(),
                    'username' => $this->string()->notNull()->unique(),
                    'email' => $this->string()->notNull()->unique(),
                    'password_hash' => $this->string()->notNull(),
                    'auth_key' => $this->string(32)->notNull(),
                    'password_reset_token' => $this->string()->unique(),
                    'status' => $this->smallInteger()->notNull()->defaultValue(10),
                    'created_at' => $this->integer()->notNull(),
                    'updated_at' => $this->integer()->notNull(),
                ]);
            } else {
                echo "Table {{%user}} already exists. Skipping.\n";
            }
        } catch (\Throwable $e) {
            echo "Skipping create table {{%user}}: " . $e->getMessage() . "\n";
        }

        // Tabela de veículos
        try {
            if ($this->db->schema->getTableSchema('{{%vehicle}}') === null) {
                $this->createTable('{{%vehicle}}', [
                    'id' => $this->primaryKey(),
                    'user_id' => $this->integer()->notNull(),
                    'brand' => $this->string(100)->notNull(),
                    'model' => $this->string(100)->notNull(),
                    'year' => $this->integer()->notNull(),
                    'license_plate' => $this->string(20)->notNull()->unique(),
                    'color' => $this->string(50),
                    'chassis' => $this->string(100),
                    'engine' => $this->string(100),
                    'fuel_type' => $this->string(20),
                    'status' => $this->smallInteger()->notNull()->defaultValue(1),
                    'created_at' => $this->integer()->notNull(),
                    'updated_at' => $this->integer()->notNull(),
                ]);
            } else {
                echo "Table {{%vehicle}} already exists. Skipping.\n";
            }
        } catch (\Throwable $e) {
            echo "Skipping create table {{%vehicle}}: " . $e->getMessage() . "\n";
        }

        // Tabela de manutenções
        try {
            if ($this->db->schema->getTableSchema('{{%maintenance}}') === null) {
                $this->createTable('{{%maintenance}}', [
                    'id' => $this->primaryKey(),
                    'vehicle_id' => $this->integer()->notNull(),
                    'type' => $this->string(100)->notNull(),
                    'description' => $this->text(),
                    'cost' => $this->decimal(10, 2),
                    'date' => $this->date()->notNull(),
                    'next_maintenance' => $this->date(),
                    'status' => $this->smallInteger()->notNull()->defaultValue(1),
                    'created_at' => $this->integer()->notNull(),
                    'updated_at' => $this->integer()->notNull(),
                ]);
            } else {
                echo "Table {{%maintenance}} already exists. Skipping.\n";
            }
        } catch (\Throwable $e) {
            echo "Skipping create table {{%maintenance}}: " . $e->getMessage() . "\n";
        }

        // Índices e chaves estrangeiras (protegidos para não falhar se já existem)
        try {
            $this->createIndex('idx-vehicle-user_id', '{{%vehicle}}', 'user_id');
            $this->addForeignKey(
                'fk-vehicle-user_id',
                '{{%vehicle}}',
                'user_id',
                '{{%user}}',
                'id',
                'CASCADE'
            );
        } catch (\Throwable $e) {
            echo "Skipping idx/fk vehicle-user: " . $e->getMessage() . "\n";
        }

        try {
            $this->createIndex('idx-maintenance-vehicle_id', '{{%maintenance}}', 'vehicle_id');
            $this->addForeignKey(
                'fk-maintenance-vehicle_id',
                '{{%maintenance}}',
                'vehicle_id',
                '{{%vehicle}}',
                'id',
                'CASCADE'
            );
        } catch (\Throwable $e) {
            echo "Skipping idx/fk maintenance-vehicle: " . $e->getMessage() . "\n";
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Remover maintenance se existir
        if ($this->db->schema->getTableSchema('{{%maintenance}}') !== null) {
            try {
                $this->dropForeignKey('fk-maintenance-vehicle_id', '{{%maintenance}}');
            } catch (\Throwable $e) {
                echo "Ignoring missing fk-maintenance-vehicle_id: " . $e->getMessage() . "\n";
            }
            try {
                $this->dropIndex('idx-maintenance-vehicle_id', '{{%maintenance}}');
            } catch (\Throwable $e) {
                echo "Ignoring missing idx-maintenance-vehicle_id: " . $e->getMessage() . "\n";
            }
            $this->dropTable('{{%maintenance}}');
        } else {
            echo "Table {{%maintenance}} does not exist. Skipping drop.\n";
        }

        // Remover vehicle se existir
        if ($this->db->schema->getTableSchema('{{%vehicle}}') !== null) {
            try {
                $this->dropForeignKey('fk-vehicle-user_id', '{{%vehicle}}');
            } catch (\Throwable $e) {
                echo "Ignoring missing fk-vehicle-user_id: " . $e->getMessage() . "\n";
            }
            try {
                $this->dropIndex('idx-vehicle-user_id', '{{%vehicle}}');
            } catch (\Throwable $e) {
                echo "Ignoring missing idx-vehicle-user_id: " . $e->getMessage() . "\n";
            }
            $this->dropTable('{{%vehicle}}');
        } else {
            echo "Table {{%vehicle}} does not exist. Skipping drop.\n";
        }

        // Remover user se existir
        if ($this->db->schema->getTableSchema('{{%user}}') !== null) {
            $this->dropTable('{{%user}}');
        } else {
            echo "Table {{%user}} does not exist. Skipping drop.\n";
        }
    }
}
