<?php

use yii\db\Migration;

/**
 * Assigns permissions to RBAC roles.
 */
class m251118_000013_assign_rbac_permissions extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Super Admin: Todas as permissões
        $this->assignAllPermissionsToRole('super-admin');

        // Admin: Todas exceto configurações críticas
        $adminPermissions = $this->getAllPermissions(['system.config']);
        $this->assignPermissionsToRole('admin', $adminPermissions);

        // Gestor de Frota
        $gestorPermissions = [
            'companies.view',
            'users.view', 'users.create', 'users.update',
            'vehicles.view', 'vehicles.create', 'vehicles.update', 'vehicles.assign',
            'drivers.view', 'drivers.create', 'drivers.update',
            'files.view', 'files.upload',
            'fuel.view', 'fuel.update',
            'alerts.view', 'alerts.resolve',
            'reports.view', 'reports.create', 'reports.export', 'reports.advanced',
            'dashboard.view', 'dashboard.advanced'
        ];
        $this->assignPermissionsToRole('gestor', $gestorPermissions);

        // Gestor de Manutenção
        $gestorManutencaoPermissions = [
            'companies.view',
            'users.view',
            'vehicles.view',
            'drivers.view',
            'files.view', 'files.upload',
            'maintenances.view', 'maintenances.create', 'maintenances.update', 'maintenances.delete', 'maintenances.schedule',
            'documents.view', 'documents.create', 'documents.update',
            'alerts.view', 'alerts.create', 'alerts.resolve',
            'reports.view',
            'dashboard.view'
        ];
        $this->assignPermissionsToRole('gestor-manutencao', $gestorManutencaoPermissions);

        // Condutor Senior
        $condutorSeniorPermissions = [
            'vehicles.view',
            'drivers.view',
            'files.view',
            'fuel.view', 'fuel.create',
            'documents.view',
            'alerts.view',
            'reports.view',
            'dashboard.view'
        ];
        $this->assignPermissionsToRole('condutor-senior', $condutorSeniorPermissions);

        // Condutor
        $condutorPermissions = [
            'vehicles.view',
            'files.view',
            'fuel.view', 'fuel.create',
            'documents.view',
            'alerts.view',
            'dashboard.view'
        ];
        $this->assignPermissionsToRole('condutor', $condutorPermissions);

        // Atribuir role 'super-admin' ao utilizador admin (user_id = 1)
        $this->insert('{{%auth_assignment}}', [
            'item_name' => 'super-admin',
            'user_id' => '1',
            'created_at' => time()
        ]);
    }

    /**
     * Assign all permissions to a role except excluded ones
     * @param string $role
     * @param array $exclude
     */
    private function assignAllPermissionsToRole($role, $exclude = [])
    {
        $permissions = $this->getAllPermissions($exclude);
        $this->assignPermissionsToRole($role, $permissions);
    }

    /**
     * Get all permissions except excluded ones
     * @param array $exclude
     * @return array
     */
    private function getAllPermissions($exclude = [])
    {
        $query = (new \yii\db\Query())
            ->select('name')
            ->from('{{%auth_item}}')
            ->where(['type' => 2]);

        if (!empty($exclude)) {
            $query->andWhere(['not in', 'name', $exclude]);
        }

        return $query->column();
    }

    /**
     * Assign permissions to role
     * @param string $role
     * @param array $permissions
     */
    private function assignPermissionsToRole($role, $permissions)
    {
        $data = [];
        foreach ($permissions as $permission) {
            $data[] = [$role, $permission];
        }

        if (!empty($data)) {
            $this->batchInsert('{{%auth_item_child}}', ['parent', 'child'], $data);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('{{%auth_assignment}}', ['user_id' => '1']);
        $this->delete('{{%auth_item_child}}', [
            'parent' => ['super-admin', 'admin', 'gestor', 'gestor-manutencao', 'condutor-senior', 'condutor']
        ]);
    }
}