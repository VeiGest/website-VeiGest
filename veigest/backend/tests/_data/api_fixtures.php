<?php

/**
 * Test data fixtures for VeiGest API tests
 * Contains sample data for all entities used in testing
 */
return [
    // Company test data
    'companies' => [
        [
            'id' => 1,
            'nome' => 'VeiGest Demo',
            'nif' => '123456789',
            'email' => 'demo@veigest.com',
            'telefone' => '213456789',
            'endereco' => 'Rua Principal 123',
            'cidade' => 'Lisboa',
            'codigo_postal' => '1000-001',
            'pais' => 'Portugal',
            'ativa' => 1,
            'created_at' => '2025-01-01 00:00:00',
            'updated_at' => '2025-01-01 00:00:00'
        ],
        [
            'id' => 2,
            'nome' => 'Test Company A',
            'nif' => '987654321',
            'email' => 'testa@company.com',
            'telefone' => '219876543',
            'endereco' => 'Avenida Teste 456',
            'cidade' => 'Porto',
            'codigo_postal' => '4000-001',
            'pais' => 'Portugal',
            'ativa' => 1,
            'created_at' => '2025-01-01 00:00:00',
            'updated_at' => '2025-01-01 00:00:00'
        ]
    ],

    // User test data
    'users' => [
        [
            'id' => 1,
            'company_id' => 1,
            'username' => 'admin',
            'password_hash' => '$2y$13$EjaPFBnZOQsHdGuHI.xvhuDp1fHpo8hKRSk6yshqa9c5EG8s3C3lO', // admin
            'auth_key' => 'test-auth-key-admin',
            'access_token' => 'test-token-admin-123456',
            'email' => 'admin@veigest.com',
            'nome' => 'VeiGest Admin',
            'apelido' => 'System',
            'telefone' => '213456789',
            'data_nascimento' => '1980-01-01',
            'numero_carta' => 'B1234567',
            'validade_carta' => '2030-01-01',
            'status' => 10,
            'created_at' => 1672531200, // 2023-01-01
            'updated_at' => 1672531200,
            'verification_token' => null
        ],
        [
            'id' => 2,
            'company_id' => 1,
            'username' => 'testuser',
            'password_hash' => '$2y$13$EjaPFBnZOQsHdGuHI.xvhuDp1fHpo8hKRSk6yshqa9c5EG8s3C3lO', // admin
            'auth_key' => 'test-auth-key-user',
            'access_token' => 'test-token-user-123456',
            'email' => 'user@veigest.com',
            'nome' => 'Test User',
            'apelido' => 'Tester',
            'telefone' => '213456790',
            'data_nascimento' => '1990-05-15',
            'numero_carta' => 'B7654321',
            'validade_carta' => '2029-05-15',
            'status' => 10,
            'created_at' => 1672531200,
            'updated_at' => 1672531200,
            'verification_token' => null
        ]
    ],

    // Vehicle test data
    'vehicles' => [
        [
            'id' => 1,
            'company_id' => 1,
            'matricula' => 'AA-11-BB',
            'marca' => 'Toyota',
            'modelo' => 'Corolla',
            'ano' => 2020,
            'combustivel' => 'gasolina',
            'cilindrada' => 1600,
            'potencia' => 120,
            'quilometragem' => 25000,
            'cor' => 'branco',
            'numero_chassis' => 'JTDBE40E400123456',
            'seguro_numero' => 'SEG123456789',
            'seguro_validade' => '2025-12-31',
            'inspecao_validade' => '2025-06-30',
            'estado' => 'ativo',
            'observacoes' => 'Vehicle for testing',
            'created_at' => '2025-01-01 00:00:00',
            'updated_at' => '2025-01-01 00:00:00'
        ],
        [
            'id' => 2,
            'company_id' => 1,
            'matricula' => 'CC-22-DD',
            'marca' => 'Ford',
            'modelo' => 'Focus',
            'ano' => 2019,
            'combustivel' => 'diesel',
            'cilindrada' => 1500,
            'potencia' => 95,
            'quilometragem' => 45000,
            'cor' => 'azul',
            'numero_chassis' => 'WF0XXXGCDXKX123456',
            'seguro_numero' => 'SEG987654321',
            'seguro_validade' => '2025-08-15',
            'inspecao_validade' => '2025-03-20',
            'estado' => 'ativo',
            'observacoes' => 'Second test vehicle',
            'created_at' => '2025-01-01 00:00:00',
            'updated_at' => '2025-01-01 00:00:00'
        ]
    ],

    // Maintenance test data
    'maintenances' => [
        [
            'id' => 1,
            'vehicle_id' => 1,
            'tipo' => 'preventiva',
            'descricao' => 'Mudança de óleo e filtros',
            'custo' => 75.50,
            'quilometragem' => 25000,
            'data_manutencao' => '2025-01-15',
            'data_proxima' => '2025-07-15',
            'fornecedor' => 'AutoRepair Lda',
            'numero_fatura' => 'FR2025001',
            'garantia_meses' => 6,
            'estado' => 'concluida',
            'observacoes' => 'Maintenance completed successfully',
            'created_at' => '2025-01-15 10:00:00',
            'updated_at' => '2025-01-15 15:30:00'
        ],
        [
            'id' => 2,
            'vehicle_id' => 1,
            'tipo' => 'corretiva',
            'descricao' => 'Substituição de pastilhas de travão',
            'custo' => 120.00,
            'quilometragem' => 25500,
            'data_manutencao' => '2025-02-01',
            'data_proxima' => null,
            'fornecedor' => 'BrakeService SA',
            'numero_fatura' => 'FR2025002',
            'garantia_meses' => 12,
            'estado' => 'agendada',
            'observacoes' => 'Brake pads replacement needed',
            'created_at' => '2025-01-25 09:00:00',
            'updated_at' => '2025-01-25 09:00:00'
        ]
    ],

    // Fuel log test data
    'fuel_logs' => [
        [
            'id' => 1,
            'vehicle_id' => 1,
            'data_abastecimento' => '2025-01-10',
            'quilometragem' => 24800,
            'litros' => 45.5,
            'custo_litro' => 1.65,
            'custo_total' => 75.08,
            'posto' => 'Galp Energia',
            'endereco_posto' => 'A1 - Área de Serviço Norte',
            'tipo_combustivel' => 'gasolina_95',
            'deposito_cheio' => 1,
            'observacoes' => 'Full tank refill',
            'created_at' => '2025-01-10 14:30:00',
            'updated_at' => '2025-01-10 14:30:00'
        ]
    ],

    // Sample API responses for testing
    'api_responses' => [
        'login_success' => [
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'access_token' => 'sample-jwt-token-here',
                'token_type' => 'Bearer',
                'user' => [
                    'id' => 1,
                    'username' => 'admin',
                    'email' => 'admin@veigest.com',
                    'nome' => 'VeiGest Admin'
                ]
            ]
        ],
        
        'company_created' => [
            'success' => true,
            'message' => 'Company created successfully',
            'data' => [
                'id' => 3,
                'nome' => 'New Test Company',
                'nif' => '555666777',
                'email' => 'newtest@company.com',
                'ativa' => 1,
                'created_at' => '2025-11-19T15:30:00+00:00'
            ]
        ],

        'validation_error' => [
            'success' => false,
            'message' => 'Validation failed',
            'errors' => [
                'nome' => ['Nome is required'],
                'email' => ['Email format is invalid']
            ]
        ],

        'not_found' => [
            'success' => false,
            'message' => 'Resource not found',
            'error' => 'The requested resource could not be found'
        ],

        'unauthorized' => [
            'success' => false,
            'message' => 'Authentication required',
            'error' => 'Valid authentication token is required'
        ]
    ],

    // Test scenarios configuration
    'test_scenarios' => [
        'crud_operations' => [
            'create' => ['POST', 201, 'Resource created successfully'],
            'read' => ['GET', 200, 'Resource retrieved successfully'],
            'update' => ['PUT', 200, 'Resource updated successfully'],
            'delete' => ['DELETE', 204, 'Resource deleted successfully']
        ],
        
        'authentication_flows' => [
            'valid_login' => ['POST', '/auth/login', 200],
            'invalid_login' => ['POST', '/auth/login', 401],
            'token_refresh' => ['POST', '/auth/refresh', 200],
            'logout' => ['POST', '/auth/logout', 200]
        ],

        'pagination_tests' => [
            'default_page' => ['page' => 1, 'per-page' => 20],
            'custom_page' => ['page' => 2, 'per-page' => 10],
            'large_page' => ['page' => 1, 'per-page' => 100]
        ]
    ]
];