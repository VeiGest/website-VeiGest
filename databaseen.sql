-- VeiGest - Ultra-Simplified Schema with RBAC Yii2
-- Last update: 2025-11-06 (Revision 3 - No GPS)
-- ULTRA-LEAN: 9 main tables + 4 RBAC + 3 views
-- Removed: GPS tracking system and routes
SET FOREIGN_KEY_CHECKS = 0;

DROP DATABASE IF EXISTS vehicle_management;
CREATE DATABASE vehicle_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE vehicle_management;

-- ============================================================================
-- 1. COMPANIES
-- ============================================================================
CREATE TABLE companies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    tax_id VARCHAR(20) NOT NULL UNIQUE,
    email VARCHAR(150),
    phone VARCHAR(20),
    status ENUM('active','suspended','inactive') NOT NULL DEFAULT 'active',
    plan ENUM('basic','professional','enterprise') NOT NULL DEFAULT 'basic',
    settings JSON COMMENT 'Company-specific settings',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tax_id (tax_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 2. RBAC Yii2 (Access Control System)
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
-- 3. USERS (with integrated driver profile)
-- ============================================================================
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    company_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    auth_key VARCHAR(32) COMMENT 'For Yii2 authentication',
    password_reset_token VARCHAR(255),
    -- Driver fields (only filled if user is a driver)
    license_number VARCHAR(50),
    license_expiry DATE,
    photo VARCHAR(255),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_email_company (email, company_id),
    INDEX idx_company_id (company_id),
    INDEX idx_status (status),
    INDEX idx_license_expiry (license_expiry),
    INDEX idx_password_reset_token (password_reset_token),
    CONSTRAINT fk_users_company FOREIGN KEY (company_id) 
        REFERENCES companies(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 4. FILES (simplified)
-- ============================================================================
CREATE TABLE files (
    id INT PRIMARY KEY AUTO_INCREMENT,
    company_id INT NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    size BIGINT NOT NULL,
    path VARCHAR(500) NOT NULL COMMENT 'Full path of the file',
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
-- 5. VEHICLES
-- ============================================================================
CREATE TABLE vehicles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    company_id INT NOT NULL,
    license_plate VARCHAR(20) NOT NULL,
    brand VARCHAR(100),
    model VARCHAR(100),
    year INT,
    fuel_type ENUM('gasoline','diesel','electric','hybrid','other'),
    mileage INT NOT NULL DEFAULT 0,
    status ENUM('active','maintenance','inactive') NOT NULL DEFAULT 'active',
    driver_id INT COMMENT 'Currently assigned driver',
    photo VARCHAR(255),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_license_plate_company (license_plate, company_id),
    INDEX idx_company_id (company_id),
    INDEX idx_status (status),
    INDEX idx_driver_id (driver_id),
    CONSTRAINT fk_vehicles_company FOREIGN KEY (company_id) 
        REFERENCES companies(id) ON DELETE CASCADE,
    CONSTRAINT fk_vehicles_driver FOREIGN KEY (driver_id) 
        REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE maintenances (
    id INT PRIMARY KEY AUTO_INCREMENT,
    company_id INT NOT NULL,
    vehicle_id INT NOT NULL,
    type VARCHAR(100) NOT NULL,
    description TEXT,
    date DATE NOT NULL,
    cost DECIMAL(10,2) DEFAULT 0.00,
    km_recorded INT,
    next_date DATE,
    workshop VARCHAR(200),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_vehicle_id (vehicle_id),
    INDEX idx_date (date),
    INDEX idx_next_date (next_date),
    CONSTRAINT fk_maintenances_company FOREIGN KEY (company_id) 
        REFERENCES companies(id) ON DELETE CASCADE,
    CONSTRAINT fk_maintenances_vehicle FOREIGN KEY (vehicle_id) 
        REFERENCES vehicles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 5. DOCUMENTS (simplified - files linked to vehicles/drivers)
-- ============================================================================
CREATE TABLE documents (
    id INT PRIMARY KEY AUTO_INCREMENT,
    company_id INT NOT NULL,
    file_id INT NOT NULL,
    vehicle_id INT,
    driver_id INT,
    type ENUM('registration','insurance','inspection','drivers_license','other') NOT NULL,
    expiry_date DATE,
    status ENUM('valid','expired') NOT NULL DEFAULT 'valid',
    notes TEXT COMMENT 'Additional information about the document',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_file_id (file_id),
    INDEX idx_vehicle_id (vehicle_id),
    INDEX idx_driver_id (driver_id),
    INDEX idx_expiry_date (expiry_date),
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
-- 6. FUEL (simplified)
-- ============================================================================
CREATE TABLE fuel_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    company_id INT NOT NULL,
    vehicle_id INT NOT NULL,
    driver_id INT,
    date DATE NOT NULL,
    liters DECIMAL(10,2) NOT NULL,
    value DECIMAL(10,2) NOT NULL,
    price_per_liter DECIMAL(8,4) AS (value / liters) STORED,
    current_km INT,
    notes VARCHAR(255),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_vehicle_id (vehicle_id),
    INDEX idx_date (date),
    CONSTRAINT fk_fuel_logs_company FOREIGN KEY (company_id) 
        REFERENCES companies(id) ON DELETE CASCADE,
    CONSTRAINT fk_fuel_logs_vehicle FOREIGN KEY (vehicle_id) 
        REFERENCES vehicles(id) ON DELETE CASCADE,
    CONSTRAINT fk_fuel_logs_driver FOREIGN KEY (driver_id) 
        REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 7. ALERTS (simplified with JSON details)
-- ============================================================================
CREATE TABLE alerts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    company_id INT NOT NULL,
    type ENUM('maintenance','document','fuel','other') NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    priority ENUM('low','medium','high','critical') NOT NULL DEFAULT 'medium',
    status ENUM('active','resolved','ignored') NOT NULL DEFAULT 'active',
    details JSON COMMENT 'vehicle_id, document_id, user_id, etc.',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    resolved_at DATETIME,
    INDEX idx_status (status),
    INDEX idx_company_id (company_id),
    INDEX idx_type (type),
    CONSTRAINT fk_alerts_company FOREIGN KEY (company_id) 
        REFERENCES companies(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 8. ACTIVITY LOGS
-- ============================================================================
CREATE TABLE activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    company_id INT NOT NULL,
    user_id INT,
    action VARCHAR(255) NOT NULL,
    entity VARCHAR(100) NOT NULL COMMENT 'E.g.: vehicle, document, user',
    entity_id INT,
    details JSON,
    ip VARCHAR(45),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_created_at (created_at),
    INDEX idx_entity (entity, entity_id),
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
    d.type,
    d.expiry_date,
    d.status,
    f.original_name,
    DATEDIFF(d.expiry_date, CURDATE()) AS days_to_expiry,
    COALESCE(v.license_plate, CONCAT('Driver: ', u.name)) AS entity
FROM documents d
INNER JOIN files f ON d.file_id = f.id
LEFT JOIN vehicles v ON d.vehicle_id = v.id
LEFT JOIN users u ON d.driver_id = u.id
WHERE d.expiry_date IS NOT NULL
  AND d.status = 'valid'
  AND DATEDIFF(d.expiry_date, CURDATE()) <= 30
ORDER BY d.expiry_date ASC;

CREATE VIEW v_company_stats AS
SELECT
    c.id,
    c.name,
    c.plan,
    c.status,
    COUNT(DISTINCT u.id) AS total_users,
    COUNT(DISTINCT v.id) AS total_vehicles,
    COUNT(DISTINCT CASE WHEN u.license_number IS NOT NULL THEN u.id END) AS total_drivers,
    COALESCE(SUM(f.size), 0) AS total_storage_bytes
FROM companies c
LEFT JOIN users u ON c.id = u.company_id AND u.status = 'active'
LEFT JOIN vehicles v ON c.id = v.company_id AND v.status != 'inactive'
LEFT JOIN files f ON c.id = f.company_id
GROUP BY c.id;

CREATE VIEW v_vehicle_costs AS
SELECT
    v.id AS vehicle_id,
    v.company_id,
    v.license_plate,
    v.brand,
    v.model,
    COALESCE(SUM(m.cost), 0) AS total_maintenance,
    COALESCE(SUM(fl.value), 0) AS total_fuel,
    COALESCE(SUM(m.cost), 0) + COALESCE(SUM(fl.value), 0) AS total_costs
FROM vehicles v
LEFT JOIN maintenances m ON v.id = m.vehicle_id
LEFT JOIN fuel_logs fl ON v.id = fl.vehicle_id
GROUP BY v.id;

-- ============================================================================
-- 10. DADOS INICIAIS
-- ============================================================================

-- Default company
INSERT INTO companies (name, tax_id, email, status, plan, settings)
VALUES (
    'VeiGest - Demo Company',
    '999999990',
    'admin@veigest.com',
    'active',
    'enterprise',
    JSON_OBJECT(
        'currency', 'EUR',
        'timezone', 'Europe/Lisbon',
        'language', 'en',
        'email_alerts', true,
        'days_alert_documents', 30
    )
);

-- Admin user
INSERT INTO users (company_id, name, email, password_hash, status, auth_key)
VALUES (
    1,
    'admin',
    'admin@veigest.com',
    '$2a$12$/piK/Am/.6Wau7PpIzvO5ergX4AG17Xzk5RicS1Yom6YSsE5sSlgG',
    'active',
    MD5(CONCAT('admin@veigest.com', NOW()))
);

-- ============================================================================
-- RBAC: ROLES (auth_item with type=1)
-- ============================================================================
INSERT INTO auth_item (name, type, description, created_at, updated_at) VALUES
('admin', 1, 'Administrator', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('manager', 1, 'Fleet Manager', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('maintenance-manager', 1, 'Maintenance Manager', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('senior-driver', 1, 'Senior Driver', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('driver', 1, 'Driver', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- ============================================================================
-- RBAC: PERMISSIONS (auth_item with type=2)
-- ============================================================================
INSERT INTO auth_item (name, type, description, created_at, updated_at) VALUES
-- Companies
('companies.view', 2, 'View companies', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('companies.manage', 2, 'Manage companies', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),

-- Users
('users.view', 2, 'View users', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('users.create', 2, 'Create users', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('users.update', 2, 'Edit users', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('users.delete', 2, 'Delete users', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('users.manage-roles', 2, 'Manage user roles', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),

-- Vehicles
('vehicles.view', 2, 'View vehicles', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('vehicles.create', 2, 'Create vehicles', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('vehicles.update', 2, 'Edit vehicles', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('vehicles.delete', 2, 'Delete vehicles', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('vehicles.assign', 2, 'Assign vehicles to drivers', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),

-- Drivers
('drivers.view', 2, 'View driver profiles', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('drivers.create', 2, 'Create driver profiles', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('drivers.update', 2, 'Edit driver profiles', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('drivers.delete', 2, 'Delete driver profiles', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),

-- Files
('files.view', 2, 'View files', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('files.upload', 2, 'Upload files', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('files.delete', 2, 'Delete files', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),

-- Maintenances
('maintenances.view', 2, 'View maintenances', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('maintenances.create', 2, 'Create maintenances', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('maintenances.update', 2, 'Edit maintenances', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('maintenances.delete', 2, 'Delete maintenances', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('maintenances.schedule', 2, 'Schedule maintenances', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),

-- Documents
('documents.view', 2, 'View documents', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('documents.create', 2, 'Create documents', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('documents.update', 2, 'Edit documents', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('documents.delete', 2, 'Delete documents', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),

-- Fuel
('fuel.view', 2, 'View fuel logs', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('fuel.create', 2, 'Record fuel', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('fuel.update', 2, 'Edit fuel logs', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('fuel.delete', 2, 'Delete fuel logs', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),

-- Alerts
('alerts.view', 2, 'View alerts', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('alerts.create', 2, 'Create alerts', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('alerts.resolve', 2, 'Resolve alerts', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),

-- Reports
('reports.view', 2, 'View reports', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('reports.create', 2, 'Generate reports', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('reports.export', 2, 'Export reports', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('reports.advanced', 2, 'Advanced reports', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),

-- System
('system.config', 2, 'System settings', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('system.logs', 2, 'View system logs', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),

-- Dashboard
('dashboard.view', 2, 'View dashboard', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('dashboard.advanced', 2, 'Advanced dashboard', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- ============================================================================
-- RBAC: ASSOCIAR PERMISSIONS AOS ROLES
-- ============================================================================

-- NOTE: 'super-admin' top-level role removed. Admin is the highest privileged role by default.

-- Admin: Todas exceto configurações críticas
INSERT INTO auth_item_child (parent, child)
SELECT 'admin', name FROM auth_item 
WHERE type = 2 AND name NOT IN ('system.config');

-- Fleet Manager
INSERT INTO auth_item_child (parent, child)
SELECT 'manager', name FROM auth_item WHERE type = 2 AND name IN (
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

-- Maintenance Manager
INSERT INTO auth_item_child (parent, child)
SELECT 'maintenance-manager', name FROM auth_item WHERE type = 2 AND name IN (
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

-- Senior Driver
INSERT INTO auth_item_child (parent, child)
SELECT 'senior-driver', name FROM auth_item WHERE type = 2 AND name IN (
    'vehicles.view',
    'drivers.view',
    'files.view',
    'fuel.view', 'fuel.create',
    'documents.view',
    'alerts.view',
    'reports.view',
    'dashboard.view'
);

-- Driver
INSERT INTO auth_item_child (parent, child)
SELECT 'driver', name FROM auth_item WHERE type = 2 AND name IN (
    'vehicles.view',
    'files.view',
    'fuel.view', 'fuel.create',
    'documents.view',
    'alerts.view',
    'dashboard.view'
);

-- Assign 'admin' role to user admin (user_id = 1)
INSERT INTO auth_assignment (item_name, user_id, created_at)
VALUES ('admin', '1', UNIX_TIMESTAMP());

SET FOREIGN_KEY_CHECKS = 1;
