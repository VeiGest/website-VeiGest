<?php

/**
 * Dados de fixture para testes de Company
 * Nota: O campo 'code' é do tipo bigint na base de dados
 */
return [
    [
        'id' => 1,
        'code' => 1001,
        'name' => 'VeiGest Test Company',
        'tax_id' => '123456789',
        'email' => 'test@veigest.test',
        'phone' => '+351912345678',
        'status' => 'active',
        'plan' => 'professional',
        'created_at' => '2025-01-01 00:00:00',
        'updated_at' => '2025-01-01 00:00:00',
    ],
    [
        'id' => 2,
        'code' => 1002,
        'name' => 'Second Test Company',
        'tax_id' => '987654321',
        'email' => 'company2@veigest.test',
        'phone' => '+351987654321',
        'status' => 'active',
        'plan' => 'basic',
        'created_at' => '2025-01-01 00:00:00',
        'updated_at' => '2025-01-01 00:00:00',
    ],
];
