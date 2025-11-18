<?php

use yii\db\Migration;

/**
 * Inserts RBAC roles and permissions data.
 */
class m251118_000012_insert_rbac_data extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $time = time();

        // Inserir ROLES (auth_item com type=1)
        $this->batchInsert('{{%auth_item}}', 
            ['name', 'type', 'description', 'created_at', 'updated_at'],
            [
                ['super-admin', 1, 'Super Administrador - Acesso Total', $time, $time],
                ['admin', 1, 'Administrador', $time, $time],
                ['gestor', 1, 'Gestor de Frota', $time, $time],
                ['gestor-manutencao', 1, 'Gestor de Manutenção', $time, $time],
                ['condutor-senior', 1, 'Condutor Senior', $time, $time],
                ['condutor', 1, 'Condutor', $time, $time],
            ]
        );

        // Inserir PERMISSIONS (auth_item com type=2)
        $permissions = [
            // Companies
            ['companies.view', 2, 'Ver empresas', $time, $time],
            ['companies.manage', 2, 'Gerir empresas', $time, $time],
            
            // Users
            ['users.view', 2, 'Ver utilizadores', $time, $time],
            ['users.create', 2, 'Criar utilizadores', $time, $time],
            ['users.update', 2, 'Editar utilizadores', $time, $time],
            ['users.delete', 2, 'Eliminar utilizadores', $time, $time],
            ['users.manage-roles', 2, 'Gerir roles de utilizadores', $time, $time],
            
            // Vehicles
            ['vehicles.view', 2, 'Ver veículos', $time, $time],
            ['vehicles.create', 2, 'Criar veículos', $time, $time],
            ['vehicles.update', 2, 'Editar veículos', $time, $time],
            ['vehicles.delete', 2, 'Eliminar veículos', $time, $time],
            ['vehicles.assign', 2, 'Atribuir veículos a condutores', $time, $time],
            
            // Drivers
            ['drivers.view', 2, 'Ver perfis de condutores', $time, $time],
            ['drivers.create', 2, 'Criar perfis de condutores', $time, $time],
            ['drivers.update', 2, 'Editar perfis de condutores', $time, $time],
            ['drivers.delete', 2, 'Eliminar perfis de condutores', $time, $time],
            
            // Files
            ['files.view', 2, 'Ver ficheiros', $time, $time],
            ['files.upload', 2, 'Upload de ficheiros', $time, $time],
            ['files.delete', 2, 'Eliminar ficheiros', $time, $time],
            
            // Maintenances
            ['maintenances.view', 2, 'Ver manutenções', $time, $time],
            ['maintenances.create', 2, 'Criar manutenções', $time, $time],
            ['maintenances.update', 2, 'Editar manutenções', $time, $time],
            ['maintenances.delete', 2, 'Eliminar manutenções', $time, $time],
            ['maintenances.schedule', 2, 'Agendar manutenções', $time, $time],
            
            // Documents
            ['documents.view', 2, 'Ver documentos', $time, $time],
            ['documents.create', 2, 'Criar documentos', $time, $time],
            ['documents.update', 2, 'Editar documentos', $time, $time],
            ['documents.delete', 2, 'Eliminar documentos', $time, $time],
            
            // Fuel
            ['fuel.view', 2, 'Ver registos de combustível', $time, $time],
            ['fuel.create', 2, 'Registar combustível', $time, $time],
            ['fuel.update', 2, 'Editar registos de combustível', $time, $time],
            ['fuel.delete', 2, 'Eliminar registos de combustível', $time, $time],
            
            // Alerts
            ['alerts.view', 2, 'Ver alertas', $time, $time],
            ['alerts.create', 2, 'Criar alertas', $time, $time],
            ['alerts.resolve', 2, 'Resolver alertas', $time, $time],
            
            // Reports
            ['reports.view', 2, 'Ver relatórios', $time, $time],
            ['reports.create', 2, 'Gerar relatórios', $time, $time],
            ['reports.export', 2, 'Exportar relatórios', $time, $time],
            ['reports.advanced', 2, 'Relatórios avançados', $time, $time],
            
            // System
            ['system.config', 2, 'Configurações do sistema', $time, $time],
            ['system.logs', 2, 'Ver logs do sistema', $time, $time],
            
            // Dashboard
            ['dashboard.view', 2, 'Ver dashboard', $time, $time],
            ['dashboard.advanced', 2, 'Dashboard avançado', $time, $time],
        ];

        $this->batchInsert('{{%auth_item}}', 
            ['name', 'type', 'description', 'created_at', 'updated_at'],
            $permissions
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('{{%auth_item}}', ['type' => 2]); // Remove permissions
        $this->delete('{{%auth_item}}', ['type' => 1]); // Remove roles
    }
}