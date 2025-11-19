<?php

use yii\db\Migration;

/**
 * Creates database views for VeiGest system.
 */
class m251118_000011_create_views extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // View para documentos a expirar
        $this->execute("
            CREATE VIEW v_documents_expiring AS
            SELECT
                d.id,
                d.company_id,
                d.tipo,
                d.data_validade,
                d.status,
                f.nome_original,
                DATEDIFF(d.data_validade, CURDATE()) AS dias_para_vencimento,
                COALESCE(v.matricula, CONCAT('Condutor: ', u.nome)) AS entidade
            FROM {{%documents}} d
            INNER JOIN {{%files}} f ON d.file_id = f.id
            LEFT JOIN {{%vehicles}} v ON d.vehicle_id = v.id
            LEFT JOIN {{%users}} u ON d.driver_id = u.id
            WHERE d.data_validade IS NOT NULL
              AND d.status = 'valido'
              AND DATEDIFF(d.data_validade, CURDATE()) <= 30
            ORDER BY d.data_validade ASC
        ");

        // View para estatísticas das empresas
        $this->execute("
            CREATE VIEW v_company_stats AS
            SELECT
                c.id,
                c.nome,
                c.plano,
                c.estado,
                COUNT(DISTINCT u.id) AS total_users,
                COUNT(DISTINCT v.id) AS total_vehicles,
                COUNT(DISTINCT CASE WHEN u.numero_carta IS NOT NULL THEN u.id END) AS total_drivers,
                COALESCE(SUM(f.tamanho), 0) AS total_storage_bytes
            FROM {{%companies}} c
            LEFT JOIN {{%users}} u ON c.id = u.company_id AND u.estado = 'ativo'
            LEFT JOIN {{%vehicles}} v ON c.id = v.company_id AND v.estado != 'inativo'
            LEFT JOIN {{%files}} f ON c.id = f.company_id
            GROUP BY c.id
        ");

        // View para custos dos veículos
        $this->execute("
            CREATE VIEW v_vehicle_costs AS
            SELECT
                v.id AS vehicle_id,
                v.company_id,
                v.matricula,
                v.marca,
                v.modelo,
                COALESCE(SUM(m.custo), 0) AS total_maintenance,
                COALESCE(SUM(fl.valor), 0) AS total_fuel,
                COALESCE(SUM(m.custo), 0) + COALESCE(SUM(fl.valor), 0) AS total_costs
            FROM {{%vehicles}} v
            LEFT JOIN {{%maintenances}} m ON v.id = m.vehicle_id
            LEFT JOIN {{%fuel_logs}} fl ON v.id = fl.vehicle_id
            GROUP BY v.id
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute("DROP VIEW IF EXISTS v_vehicle_costs");
        $this->execute("DROP VIEW IF EXISTS v_company_stats");
        $this->execute("DROP VIEW IF EXISTS v_documents_expiring");
    }
}