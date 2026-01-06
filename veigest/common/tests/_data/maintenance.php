<?php

/**
 * Dados de fixture para testes de Maintenance
 */
return [
    [
        'id' => 1,
        'company_id' => 1,
        'vehicle_id' => 1,
        'type' => 'Óleo',
        'description' => 'Mudança de óleo completa',
        'date' => '2025-01-15',
        'status' => 'scheduled',
        'cost' => 50.00,
        'mileage_record' => 50000,
        'workshop' => 'Oficina Central',
        'created_at' => '2025-01-01 00:00:00',
        'updated_at' => '2025-01-01 00:00:00',
    ],
    [
        'id' => 2,
        'company_id' => 1,
        'vehicle_id' => 2,
        'type' => 'Pneus',
        'description' => 'Troca de pneus dianteiros',
        'date' => '2025-01-10',
        'status' => 'completed',
        'cost' => 250.00,
        'mileage_record' => 80000,
        'workshop' => 'Pneus Express',
        'created_at' => '2025-01-01 00:00:00',
        'updated_at' => '2025-01-10 10:00:00',
    ],
    [
        'id' => 3,
        'company_id' => 1,
        'vehicle_id' => 3,
        'type' => 'Manutenção Corretiva',
        'description' => 'Reparação do motor elétrico',
        'date' => '2025-01-05',
        'status' => 'scheduled',
        'cost' => 500.00,
        'mileage_record' => 30000,
        'workshop' => 'EV Service',
        'created_at' => '2025-01-01 00:00:00',
        'updated_at' => '2025-01-01 00:00:00',
    ],
];
