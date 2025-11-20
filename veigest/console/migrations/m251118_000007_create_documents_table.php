<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%documents}}`.
 */
class m251118_000007_create_documents_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%documents}}', [
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

        // Criar índices
        $this->createIndex('idx_file_id', '{{%documents}}', 'file_id');
        $this->createIndex('idx_vehicle_id', '{{%documents}}', 'vehicle_id');
        $this->createIndex('idx_driver_id', '{{%documents}}', 'driver_id');
        $this->createIndex('idx_data_validade', '{{%documents}}', 'data_validade');

        // Adicionar chaves estrangeiras
        $this->addForeignKey(
            'fk_documents_company',
            '{{%documents}}',
            'company_id',
            '{{%companies}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_documents_file',
            '{{%documents}}',
            'file_id',
            '{{%files}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_documents_vehicle',
            '{{%documents}}',
            'vehicle_id',
            '{{%vehicles}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_documents_driver',
            '{{%documents}}',
            'driver_id',
            '{{%users}}',
            'id',
            'CASCADE'
        );

        // Adicionar constraint check (vehicle_id OR driver_id deve ser NOT NULL)
        $this->execute('ALTER TABLE {{%documents}} ADD CONSTRAINT chk_documents_entity CHECK (vehicle_id IS NOT NULL OR driver_id IS NOT NULL)');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Só tenta remover o CHECK se for MySQL >= 8.0.16
        $version = $this->db->createCommand('SELECT VERSION()')->queryScalar();
        $isMySQL8 = false;
        if (preg_match('/^(\\d+)\\.(\\d+)\\.(\\d+)/', $version, $matches)) {
            $major = (int)$matches[1];
            $minor = (int)$matches[2];
            $patch = (int)$matches[3];
            if ($major > 8 || ($major === 8 && $minor === 0 && $patch >= 16)) {
                $isMySQL8 = true;
            }
        }
        if ($isMySQL8) {
            $this->execute('ALTER TABLE {{%documents}} DROP CHECK chk_documents_entity');
        } else {
            // Em MySQL < 8.0.16, o comando não existe e o CHECK é ignorado
            echo "[info] Ignorando DROP CHECK em MySQL < 8.0.16\n";
        }
        $this->dropForeignKey('fk_documents_driver', '{{%documents}}');
        $this->dropForeignKey('fk_documents_vehicle', '{{%documents}}');
        $this->dropForeignKey('fk_documents_file', '{{%documents}}');
        $this->dropForeignKey('fk_documents_company', '{{%documents}}');
        $this->dropTable('{{%documents}}');
    }
}