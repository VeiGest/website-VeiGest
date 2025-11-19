-- VeiGest - Schema Ultra-Simplificado com RBAC Yii2
-- Última atualização: 2025-11-06 (Revisão 3 - Sem GPS)
-- ULTRA-LEAN: 9 tabelas principais + 4 RBAC + 3 views
-- Removido: Sistema de rastreamento GPS e rotas
SET FOREIGN_KEY_CHECKS = 0;

DROP DATABASE IF EXISTS veigest;
CREATE DATABASE veigest CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE veigest;

-- ============================================================================
-- 1. EMPRESAS
-- ============================================================================
CREATE TABLE companies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(200) NOT NULL,
    nif VARCHAR(20) NOT NULL UNIQUE,
    email VARCHAR(150),
    telefone VARCHAR(20),
    estado ENUM('ativa','suspensa','inativa') NOT NULL DEFAULT 'ativa',
    plano ENUM('basico','profissional','enterprise') NOT NULL DEFAULT 'basico',
    configuracoes JSON COMMENT 'Configurações específicas da empresa',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_nif (nif),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 2. RBAC YII2 (Sistema de Controlo de Acesso)
-- ============================================================================
CREATE TABLE auth_rule (
    name VARCHAR(64) NOT NULL PRIMARY KEY,
    data BLOB,
    created_at INT,
    updated_at INT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE auth_item (
    name VARCHAR(64) NOT NULL PRIMARY KEY,
    type TINYINT NOT NULL COMMENT '1=role, 2=permission',
    description TEXT,
    rule_name VARCHAR(64),
    data BLOB,
    created_at INT,
    updated_at INT,
    INDEX idx_type (type),
    CONSTRAINT fk_auth_item_rule FOREIGN KEY (rule_name) 
        REFERENCES auth_rule(name) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE auth_item_child (
    parent VARCHAR(64) NOT NULL,
    child VARCHAR(64) NOT NULL,
    PRIMARY KEY (parent, child),
    CONSTRAINT fk_auth_item_child_parent FOREIGN KEY (parent) 
        REFERENCES auth_item(name) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_auth_item_child_child FOREIGN KEY (child) 
        REFERENCES auth_item(name) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE auth_assignment (
    item_name VARCHAR(64) NOT NULL,
    user_id VARCHAR(64) NOT NULL,
    created_at INT,
    PRIMARY KEY (item_name, user_id),
    INDEX idx_user_id (user_id),
    CONSTRAINT fk_auth_assignment_item FOREIGN KEY (item_name) 
        REFERENCES auth_item(name) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 3. UTILIZADORES (com perfil condutor integrado)
-- ============================================================================
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    company_id INT NOT NULL,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    telefone VARCHAR(20),
    estado ENUM('ativo','inativo') NOT NULL DEFAULT 'ativo',
    auth_key VARCHAR(32) COMMENT 'Para autenticação Yii2',
    password_reset_token VARCHAR(255),
    -- Campos de condutor (apenas preenchidos se for condutor)
    numero_carta VARCHAR(50),
    validade_carta DATE,
    foto VARCHAR(255),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_email_company (email, company_id),
    INDEX idx_company_id (company_id),
    INDEX idx_estado (estado),
    INDEX idx_validade_carta (validade_carta),
    INDEX idx_password_reset_token (password_reset_token),
    CONSTRAINT fk_users_company FOREIGN KEY (company_id) 
        REFERENCES companies(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 4. FICHEIROS (simplificado)
-- ============================================================================
CREATE TABLE files (
    id INT PRIMARY KEY AUTO_INCREMENT,
    company_id INT NOT NULL,
    nome_original VARCHAR(255) NOT NULL,
    tamanho BIGINT NOT NULL,
    caminho VARCHAR(500) NOT NULL COMMENT 'Caminho completo do ficheiro',
    uploaded_by INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_company_id (company_id),
    INDEX idx_uploaded_by (uploaded_by),
    CONSTRAINT fk_files_company FOREIGN KEY (company_id) 
        REFERENCES companies(id) ON DELETE CASCADE,
    CONSTRAINT fk_files_uploaded_by FOREIGN KEY (uploaded_by) 
        REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 5. VEÍCULOS
-- ============================================================================
CREATE TABLE vehicles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    company_id INT NOT NULL,
    matricula VARCHAR(20) NOT NULL,
    marca VARCHAR(100),
    modelo VARCHAR(100),
    ano INT,
    tipo_combustivel ENUM('gasolina','diesel','eletrico','hibrido','outro'),
    quilometragem INT NOT NULL DEFAULT 0,
    estado ENUM('ativo','manutencao','inativo') NOT NULL DEFAULT 'ativo',
    condutor_id INT COMMENT 'Condutor atualmente atribuído',
    foto VARCHAR(255),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_matricula_company (matricula, company_id),
    INDEX idx_company_id (company_id),
    INDEX idx_estado (estado),
    INDEX idx_condutor_id (condutor_id),
    CONSTRAINT fk_vehicles_company FOREIGN KEY (company_id) 
        REFERENCES companies(id) ON DELETE CASCADE,
    CONSTRAINT fk_vehicles_condutor FOREIGN KEY (condutor_id) 
        REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE maintenances (
    id INT PRIMARY KEY AUTO_INCREMENT,
    company_id INT NOT NULL,
    vehicle_id INT NOT NULL,
    tipo VARCHAR(100) NOT NULL,
    descricao TEXT,
    data DATE NOT NULL,
    custo DECIMAL(10,2) DEFAULT 0.00,
    km_registro INT,
    proxima_data DATE,
    oficina VARCHAR(200),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_vehicle_id (vehicle_id),
    INDEX idx_data (data),
    INDEX idx_proxima_data (proxima_data),
    CONSTRAINT fk_maintenances_company FOREIGN KEY (company_id) 
        REFERENCES companies(id) ON DELETE CASCADE,
    CONSTRAINT fk_maintenances_vehicle FOREIGN KEY (vehicle_id) 
        REFERENCES vehicles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 5. DOCUMENTOS (simplificado - ficheiros ligados a veículos/condutores)
-- ============================================================================
CREATE TABLE documents (
    id INT PRIMARY KEY AUTO_INCREMENT,
    company_id INT NOT NULL,
    file_id INT NOT NULL,
    vehicle_id INT,
    driver_id INT,
    tipo ENUM('dua','seguro','inspecao','carta_conducao','outro') NOT NULL,
    data_validade DATE,
    status ENUM('valido','expirado') NOT NULL DEFAULT 'valido',
    notas TEXT COMMENT 'Informações adicionais sobre o documento',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_file_id (file_id),
    INDEX idx_vehicle_id (vehicle_id),
    INDEX idx_driver_id (driver_id),
    INDEX idx_data_validade (data_validade),
    CONSTRAINT fk_documents_company FOREIGN KEY (company_id) 
        REFERENCES companies(id) ON DELETE CASCADE,
    CONSTRAINT fk_documents_file FOREIGN KEY (file_id) 
        REFERENCES files(id) ON DELETE CASCADE,
    CONSTRAINT fk_documents_vehicle FOREIGN KEY (vehicle_id) 
        REFERENCES vehicles(id) ON DELETE CASCADE,
    CONSTRAINT fk_documents_driver FOREIGN KEY (driver_id) 
        REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT chk_documents_entity CHECK (vehicle_id IS NOT NULL OR driver_id IS NOT NULL)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 6. COMBUSTÍVEL (simplificado)
-- ============================================================================
CREATE TABLE fuel_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    company_id INT NOT NULL,
    vehicle_id INT NOT NULL,
    driver_id INT,
    data DATE NOT NULL,
    litros DECIMAL(10,2) NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    preco_litro DECIMAL(8,4) AS (valor / litros) STORED,
    km_atual INT,
    notas VARCHAR(255),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_vehicle_id (vehicle_id),
    INDEX idx_data (data),
    CONSTRAINT fk_fuel_logs_company FOREIGN KEY (company_id) 
        REFERENCES companies(id) ON DELETE CASCADE,
    CONSTRAINT fk_fuel_logs_vehicle FOREIGN KEY (vehicle_id) 
        REFERENCES vehicles(id) ON DELETE CASCADE,
    CONSTRAINT fk_fuel_logs_driver FOREIGN KEY (driver_id) 
        REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 7. ALERTAS (simplificado com detalhes JSON)
-- ============================================================================
CREATE TABLE alerts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    company_id INT NOT NULL,
    tipo ENUM('manutencao','documento','combustivel','outro') NOT NULL,
    titulo VARCHAR(200) NOT NULL,
    descricao TEXT,
    prioridade ENUM('baixa','media','alta','critica') NOT NULL DEFAULT 'media',
    status ENUM('ativo','resolvido','ignorado') NOT NULL DEFAULT 'ativo',
    detalhes JSON COMMENT 'vehicle_id, document_id, user_id, etc.',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    resolvido_em DATETIME,
    INDEX idx_status (status),
    INDEX idx_company_id (company_id),
    INDEX idx_tipo (tipo),
    CONSTRAINT fk_alerts_company FOREIGN KEY (company_id) 
        REFERENCES companies(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 8. LOGS DE ATIVIDADE
-- ============================================================================
CREATE TABLE activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    company_id INT NOT NULL,
    user_id INT,
    acao VARCHAR(255) NOT NULL,
    entidade VARCHAR(100) NOT NULL COMMENT 'Ex: vehicle, document, user',
    entidade_id INT,
    detalhes JSON,
    ip VARCHAR(45),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_created_at (created_at),
    INDEX idx_entidade (entidade, entidade_id),
    INDEX idx_user_id (user_id),
    CONSTRAINT fk_activity_logs_company FOREIGN KEY (company_id) 
        REFERENCES companies(id) ON DELETE CASCADE,
    CONSTRAINT fk_activity_logs_user FOREIGN KEY (user_id) 
        REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 9. VIEWS ÚTEIS
-- ============================================================================
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
FROM documents d
INNER JOIN files f ON d.file_id = f.id
LEFT JOIN vehicles v ON d.vehicle_id = v.id
LEFT JOIN users u ON d.driver_id = u.id
WHERE d.data_validade IS NOT NULL
  AND d.status = 'valido'
  AND DATEDIFF(d.data_validade, CURDATE()) <= 30
ORDER BY d.data_validade ASC;

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
FROM companies c
LEFT JOIN users u ON c.id = u.company_id AND u.estado = 'ativo'
LEFT JOIN vehicles v ON c.id = v.company_id AND v.estado != 'inativo'
LEFT JOIN files f ON c.id = f.company_id
GROUP BY c.id;

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
FROM vehicles v
LEFT JOIN maintenances m ON v.id = m.vehicle_id
LEFT JOIN fuel_logs fl ON v.id = fl.vehicle_id
GROUP BY v.id;

-- ============================================================================
-- 10. DADOS INICIAIS
-- ============================================================================

-- Empresa padrão
INSERT INTO companies (nome, nif, email, estado, plano, configuracoes)
VALUES (
    'VeiGest - Empresa Demo',
    '999999990',
    'admin@veigest.com',
    'ativa',
    'enterprise',
    JSON_OBJECT(
        'moeda', 'EUR',
        'timezone', 'Europe/Lisbon',
        'idioma', 'pt',
        'alertas_email', true,
        'dias_alerta_documentos', 30
    )
);

-- Utilizador administrador
INSERT INTO users (company_id, nome, email, password_hash, estado, auth_key)
VALUES (
    1,
    'admin',
    'admin@veigest.com',
    '$2a$12$/piK/Am/.6Wau7PpIzvO5ergX4AG17Xzk5RicS1Yom6YSsE5sSlgG',
    'ativo',
    MD5(CONCAT('admin@veigest.com', NOW()))
);

-- ============================================================================
-- RBAC: ROLES (auth_item com type=1)
-- ============================================================================
INSERT INTO auth_item (name, type, description, created_at, updated_at) VALUES
('super-admin', 1, 'Super Administrador - Acesso Total', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('admin', 1, 'Administrador', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('gestor', 1, 'Gestor de Frota', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('gestor-manutencao', 1, 'Gestor de Manutenção', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('condutor-senior', 1, 'Condutor Senior', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('condutor', 1, 'Condutor', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- ============================================================================
-- RBAC: PERMISSIONS (auth_item com type=2)
-- ============================================================================
INSERT INTO auth_item (name, type, description, created_at, updated_at) VALUES
-- Companies
('companies.view', 2, 'Ver empresas', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('companies.manage', 2, 'Gerir empresas', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),

-- Users
('users.view', 2, 'Ver utilizadores', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('users.create', 2, 'Criar utilizadores', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('users.update', 2, 'Editar utilizadores', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('users.delete', 2, 'Eliminar utilizadores', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('users.manage-roles', 2, 'Gerir roles de utilizadores', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),

-- Vehicles
('vehicles.view', 2, 'Ver veículos', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('vehicles.create', 2, 'Criar veículos', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('vehicles.update', 2, 'Editar veículos', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('vehicles.delete', 2, 'Eliminar veículos', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('vehicles.assign', 2, 'Atribuir veículos a condutores', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),

-- Drivers
('drivers.view', 2, 'Ver perfis de condutores', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('drivers.create', 2, 'Criar perfis de condutores', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('drivers.update', 2, 'Editar perfis de condutores', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('drivers.delete', 2, 'Eliminar perfis de condutores', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),

-- Files
('files.view', 2, 'Ver ficheiros', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('files.upload', 2, 'Upload de ficheiros', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('files.delete', 2, 'Eliminar ficheiros', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),

-- Maintenances
('maintenances.view', 2, 'Ver manutenções', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('maintenances.create', 2, 'Criar manutenções', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('maintenances.update', 2, 'Editar manutenções', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('maintenances.delete', 2, 'Eliminar manutenções', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('maintenances.schedule', 2, 'Agendar manutenções', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),

-- Documents
('documents.view', 2, 'Ver documentos', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('documents.create', 2, 'Criar documentos', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('documents.update', 2, 'Editar documentos', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('documents.delete', 2, 'Eliminar documentos', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),

-- Fuel
('fuel.view', 2, 'Ver registos de combustível', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('fuel.create', 2, 'Registar combustível', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('fuel.update', 2, 'Editar registos de combustível', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('fuel.delete', 2, 'Eliminar registos de combustível', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),

-- Alerts
('alerts.view', 2, 'Ver alertas', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('alerts.create', 2, 'Criar alertas', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('alerts.resolve', 2, 'Resolver alertas', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),

-- Reports
('reports.view', 2, 'Ver relatórios', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('reports.create', 2, 'Gerar relatórios', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('reports.export', 2, 'Exportar relatórios', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('reports.advanced', 2, 'Relatórios avançados', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),

-- System
('system.config', 2, 'Configurações do sistema', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('system.logs', 2, 'Ver logs do sistema', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),

-- Dashboard
('dashboard.view', 2, 'Ver dashboard', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('dashboard.advanced', 2, 'Dashboard avançado', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- ============================================================================
-- RBAC: ASSOCIAR PERMISSIONS AOS ROLES
-- ============================================================================

-- Super Admin: Todas as permissões
INSERT INTO auth_item_child (parent, child)
SELECT 'super-admin', name FROM auth_item WHERE type = 2;

-- Admin: Todas exceto configurações críticas
INSERT INTO auth_item_child (parent, child)
SELECT 'admin', name FROM auth_item 
WHERE type = 2 AND name NOT IN ('system.config');

-- Gestor de Frota
INSERT INTO auth_item_child (parent, child)
SELECT 'gestor', name FROM auth_item WHERE type = 2 AND name IN (
    'companies.view',
    'users.view', 'users.create', 'users.update',
    'vehicles.view', 'vehicles.create', 'vehicles.update', 'vehicles.assign',
    'drivers.view', 'drivers.create', 'drivers.update',
    'files.view', 'files.upload',
    'fuel.view', 'fuel.update',
    'alerts.view', 'alerts.resolve',
    'reports.view', 'reports.create', 'reports.export', 'reports.advanced',
    'dashboard.view', 'dashboard.advanced'
);

-- Gestor de Manutenção
INSERT INTO auth_item_child (parent, child)
SELECT 'gestor-manutencao', name FROM auth_item WHERE type = 2 AND name IN (
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
);

-- Condutor Senior
INSERT INTO auth_item_child (parent, child)
SELECT 'condutor-senior', name FROM auth_item WHERE type = 2 AND name IN (
    'vehicles.view',
    'drivers.view',
    'files.view',
    'fuel.view', 'fuel.create',
    'documents.view',
    'alerts.view',
    'reports.view',
    'dashboard.view'
);

-- Condutor
INSERT INTO auth_item_child (parent, child)
SELECT 'condutor', name FROM auth_item WHERE type = 2 AND name IN (
    'vehicles.view',
    'files.view',
    'fuel.view', 'fuel.create',
    'documents.view',
    'alerts.view',
    'dashboard.view'
);

-- Atribuir role 'super-admin' ao utilizador admin (user_id = 1)
INSERT INTO auth_assignment (item_name, user_id, created_at)
VALUES ('super-admin', '1', UNIX_TIMESTAMP());

SET FOREIGN_KEY_CHECKS = 1;
