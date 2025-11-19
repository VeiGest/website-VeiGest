<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%fuel_logs}}`.
 */
class m251118_000008_create_fuel_logs_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%fuel_logs}}', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer()->notNull(),
            'vehicle_id' => $this->integer()->notNull(),
            'driver_id' => $this->integer(),
            'data' => $this->date()->notNull(),
            'litros' => $this->decimal(10, 2)->notNull(),
            'valor' => $this->decimal(10, 2)->notNull(),
            'km_atual' => $this->integer(),
            'notas' => $this->string(255),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Adicionar coluna calculada para preço por litro
        $this->execute('ALTER TABLE {{%fuel_logs}} ADD preco_litro DECIMAL(8,4) AS (valor / litros) STORED');

        // Criar índices
        $this->createIndex('idx_vehicle_id', '{{%fuel_logs}}', 'vehicle_id');
        $this->createIndex('idx_data', '{{%fuel_logs}}', 'data');

        // Adicionar chaves estrangeiras
        $this->addForeignKey(
            'fk_fuel_logs_company',
            '{{%fuel_logs}}',
            'company_id',
            '{{%companies}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_fuel_logs_vehicle',
            '{{%fuel_logs}}',
            'vehicle_id',
            '{{%vehicles}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_fuel_logs_driver',
            '{{%fuel_logs}}',
            'driver_id',
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
        $this->dropForeignKey('fk_fuel_logs_driver', '{{%fuel_logs}}');
        $this->dropForeignKey('fk_fuel_logs_vehicle', '{{%fuel_logs}}');
        $this->dropForeignKey('fk_fuel_logs_company', '{{%fuel_logs}}');
        $this->dropTable('{{%fuel_logs}}');
    }
}