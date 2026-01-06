<?php

/**
 * Dados de fixture para testes de User
 * Estes dados são inseridos na tabela 'users' durante os testes
 */
return [
    // Admin user para testes
    [
        'id' => 100,
        'company_id' => 1,
        'username' => 'test_admin',
        'name' => 'Test Admin',
        'email' => 'test_admin@veigest.test',
        'auth_key' => 'test_admin_auth_key_123456',
        // password: admin123
        'password_hash' => '$2y$13$EjaPFBnZOQsHdGuHI.xvhuDp1fHpo8hKRSk6yshqa9c5EG8s3C3lO',
        'status' => 'active',
        'created_at' => '2025-01-01 00:00:00',
        'updated_at' => '2025-01-01 00:00:00',
    ],
    // Manager user para testes
    [
        'id' => 101,
        'company_id' => 1,
        'username' => 'test_manager',
        'name' => 'Test Manager',
        'email' => 'test_manager@veigest.test',
        'auth_key' => 'test_manager_auth_key_123456',
        // password: manager123
        'password_hash' => '$2y$13$EjaPFBnZOQsHdGuHI.xvhuDp1fHpo8hKRSk6yshqa9c5EG8s3C3lO',
        'status' => 'active',
        'created_at' => '2025-01-01 00:00:00',
        'updated_at' => '2025-01-01 00:00:00',
    ],
    // Driver user para testes
    [
        'id' => 102,
        'company_id' => 1,
        'username' => 'test_driver',
        'name' => 'Test Driver',
        'email' => 'test_driver@veigest.test',
        'auth_key' => 'test_driver_auth_key_123456',
        // password: driver123
        'password_hash' => '$2y$13$EjaPFBnZOQsHdGuHI.xvhuDp1fHpo8hKRSk6yshqa9c5EG8s3C3lO',
        'license_number' => 'DRV-TEST-001',
        'license_expiry' => '2030-12-31',
        'status' => 'active',
        'created_at' => '2025-01-01 00:00:00',
        'updated_at' => '2025-01-01 00:00:00',
    ],
    // Inactive user para testes
    [
        'id' => 103,
        'company_id' => 1,
        'username' => 'test_inactive',
        'name' => 'Test Inactive User',
        'email' => 'test_inactive@veigest.test',
        'auth_key' => 'test_inactive_auth_key_123456',
        'password_hash' => '$2y$13$EjaPFBnZOQsHdGuHI.xvhuDp1fHpo8hKRSk6yshqa9c5EG8s3C3lO',
        'status' => 'inactive',
        'created_at' => '2025-01-01 00:00:00',
        'updated_at' => '2025-01-01 00:00:00',
    ],
];
