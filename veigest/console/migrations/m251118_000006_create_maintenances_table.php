<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%maintenances}}`.
 */
class m251118_000006_create_maintenances_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%maintenances}}', [
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

        // Criar índices
        $this->createIndex('idx_vehicle_id', '{{%maintenances}}', 'vehicle_id');
        $this->createIndex('idx_data', '{{%maintenances}}', 'data');
        $this->createIndex('idx_proxima_data', '{{%maintenances}}', 'proxima_data');

        // Adicionar chaves estrangeiras
        $this->addForeignKey(
            'fk_maintenances_company',
            '{{%maintenances}}',
            'company_id',
            '{{%companies}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_maintenances_vehicle',
            '{{%maintenances}}',
            'vehicle_id',
            '{{%vehicles}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_maintenances_vehicle', '{{%maintenances}}');
        $this->dropForeignKey('fk_maintenances_company', '{{%maintenances}}');
        $this->dropTable('{{%maintenances}}');
    }
}