-- Adicionar permissões de manutenção ao RBAC
USE veigest;

-- Criar permissões
INSERT IGNORE INTO `auth_item` (`name`, `type`, `description`, `created_at`, `updated_at`) VALUES
('maintenances.view', 2, 'Ver manutenções', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('maintenances.create', 2, 'Criar manutenções', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('maintenances.update', 2, 'Atualizar manutenções', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('maintenances.delete', 2, 'Eliminar manutenções', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- Atribuir permissões ao role 'gestor'
INSERT IGNORE INTO `auth_item_child` (`parent`, `child`) VALUES
('gestor', 'maintenances.view'),
('gestor', 'maintenances.create'),
('gestor', 'maintenances.update'),
('gestor', 'maintenances.delete');

-- Atribuir permissões ao role 'admin' (se existir)
INSERT IGNORE INTO `auth_item_child` (`parent`, `child`) VALUES
('admin', 'maintenances.view'),
('admin', 'maintenances.create'),
('admin', 'maintenances.update'),
('admin', 'maintenances.delete');

-- Verificar permissões criadas
SELECT * FROM auth_item WHERE name LIKE 'maintenances.%';
SELECT * FROM auth_item_child WHERE child LIKE 'maintenances.%';
