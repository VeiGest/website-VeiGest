# VeiGest API - Documentação de Endpoints

## Visão Geral

A API VeiGest é uma API RESTful que fornece endpoints para gestão de frotas de veículos. A API utiliza autenticação via Bearer Token (Base64) e implementa multi-tenancy através do `company_id`.

**Base URL:** `http://localhost:8002/api` ou `https://veigestback.dryadlang.org/api`

**Autenticação:** Bearer Token no header `Authorization`

---

## Índice

1. [Autenticação](#1-autenticação)
2. [Empresas](#2-empresas)
3. [Veículos](#3-veículos)
4. [Usuários](#4-usuários)
5. [Manutenções](#5-manutenções)
6. [Abastecimentos](#6-abastecimentos)
7. [Alertas](#7-alertas)
8. [Documentos](#8-documentos)
9. [Arquivos](#9-arquivos)
10. [Rotas](#10-rotas)
11. [Logs de Atividade](#11-logs-de-atividade)

---

## 1. Autenticação

### POST /auth/login

Realiza login do usuário e retorna token de acesso.

**Request:**
```json
{
  "username": "admin",
  "password": "admin"
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "access_token": "eyJ1c2VyX2lkIjoxLC...",
    "token_type": "Bearer",
    "expires_in": 86400,
    "expires_at": 1768169433,
    "user": {
      "id": 1,
      "username": "admin",
      "name": "Administrator",
      "email": "admin@veigest.com",
      "phone": null,
      "status": "active",
      "company_id": 1
    },
    "company": {
      "id": 1,
      "name": "VeiGest - Demo Company",
      "code": 1767970425807,
      "email": "admin@veigest.com"
    },
    "roles": ["admin"],
    "permissions": [
      "alerts.create",
      "alerts.resolve",
      "vehicles.view",
      "..."
    ]
  }
}
```

**Erros:**
- `400 Bad Request`: Username ou password não fornecidos
- `401 Unauthorized`: Credenciais inválidas

---

### POST /auth/register

Registra um novo usuário e retorna um token de acesso.

**Request:**
```json
{
  "username": "novo_usuario",
  "email": "novo@empresa.com",
  "password": "senha123",
  "name": "Nome Completo",
  "company_id": 1,
  "phone": "+351912345678"  // opcional
}
```

**Response (201 Created):**
```json
{
  "success": true,
  "message": "Usuário registrado com sucesso",
  "data": {
    "access_token": "...",
    "token_type": "Bearer",
    "expires_in": 86400,
    "expires_at": 1768255833,
    "user": { /* dados do usuário criado */ },
    "company": { /* dados da empresa */ },
    "roles": ["driver"],
    "permissions": []
  }
}
```

**Erros:**
- `400 Bad Request`: Campos obrigatórios faltando (username, email, password, name, company_id), email inválido, senha muito curta, username/email já existente, company_id inexistente
- `500 Internal Server Error`: Erro interno ao criar usuário


### GET /auth/me

Retorna informações do usuário autenticado.

**Headers:**
```
Authorization: Bearer {access_token}
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "username": "admin",
      "name": "Administrator",
      "email": "admin@veigest.com",
      "phone": null,
      "status": "active",
      "company_id": 1
    },
    "company": {
      "id": 1,
      "name": "VeiGest - Demo Company",
      "code": 1767970425807,
      "email": "admin@veigest.com"
    },
    "roles": ["admin"],
    "permissions": ["..."],
    "token_info": {
      "issued_at": 1768083033,
      "expires_at": 1768169433
    }
  }
}
```

---

### POST /auth/refresh

Renova o token de autenticação.

**Headers:**
```
Authorization: Bearer {access_token}
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "access_token": "eyJ1c2VyX2lkIjoxLC...",
    "token_type": "Bearer",
    "expires_in": 86400,
    "expires_at": 1768255833
  }
}
```

---

### POST /auth/logout

Realiza logout do usuário (invalidação do lado cliente).

**Headers:**
```
Authorization: Bearer {access_token}
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Logout realizado com sucesso"
}
```

---

### GET /auth/info

Retorna informações sobre a API.

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "api_name": "VeiGest REST API",
    "version": "1.0",
    "framework": "Yii2",
    "authentication": "Bearer Token (Base64)",
    "endpoints": {
      "auth": [
        "POST /api/auth/login",
        "GET /api/auth/me",
        "POST /api/auth/refresh",
        "POST /api/auth/logout"
      ]
    },
    "timestamp": "2026-01-10T22:15:00+00:00"
  }
}
```

---

## 2. Empresas

### GET /company/{id}

Visualiza detalhes de uma empresa específica.

**Headers:**
```
Authorization: Bearer {access_token}
```

**Response (200 OK):**
```json
{
  "id": 1,
  "code": 1767970425807,
  "name": "VeiGest - Demo Company",
  "email": "admin@veigest.com",
  "phone": "+351912345678",
  "tax_id": "123456789",
  "status": "active",
  "plan": "basic",
  "settings": null,
  "created_at": "2025-11-21 10:00:00",
  "updated_at": "2025-11-21 10:00:00"
}
```

---

### PUT /company/{id}

Atualiza dados de uma empresa.

**Headers:**
```
Authorization: Bearer {access_token}
Content-Type: application/json
```

**Request:**
```json
{
  "phone": "+351987654321",
  "email": "updated@veigest.com"
}
```

**Response (200 OK):**
```json
{
  "id": 1,
  "code": 1767970425807,
  "name": "VeiGest - Demo Company",
  "email": "updated@veigest.com",
  "phone": "+351987654321",
  "tax_id": "123456789",
  "status": "active",
  "plan": "basic",
  "settings": null,
  "created_at": "2025-11-21 10:00:00",
  "updated_at": "2026-01-10 22:15:00"
}
```

---

### GET /companies/{id}/vehicles

Lista veículos de uma empresa.

**Headers:**
```
Authorization: Bearer {access_token}
```

**Query Parameters:**
- `per-page`: Itens por página (padrão: 20)
- `page`: Número da página
- `status`: Filtrar por status (active, maintenance, inactive)

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "company_id": 1,
      "license_plate": "AA-12-34",
      "brand": "Mercedes-Benz",
      "model": "Sprinter",
      "year": 2020,
      "fuel_type": "diesel",
      "mileage": 125000,
      "status": "active",
      "driver_id": 5,
      "created_at": "2025-11-21 10:00:00"
    }
  ],
  "_meta": {
    "totalCount": 4,
    "pageCount": 1,
    "currentPage": 1,
    "perPage": 20
  }
}
```

---

### GET /companies/{id}/users

Lista usuários de uma empresa.

**Headers:**
```
Authorization: Bearer {access_token}
```

**Query Parameters:**
- `per-page`: Itens por página (padrão: 20)
- `page`: Número da página
- `role`: Filtrar por função (admin, manager, driver)

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "username": "admin",
      "name": "Administrator",
      "email": "admin@veigest.com",
      "phone": null,
      "status": "active",
      "tipo": "admin",
      "company_id": 1
    }
  ],
  "_meta": {
    "totalCount": 5,
    "pageCount": 1,
    "currentPage": 1,
    "perPage": 20
  }
}
```

---

### GET /companies/{id}/stats

Retorna estatísticas da empresa.

**Headers:**
```
Authorization: Bearer {access_token}
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "vehicles_count": 4,
    "active_vehicles": 3,
    "users_count": 5,
    "drivers_count": 3,
    "maintenance_stats": {
      "total_maintenances": 4,
      "pending_maintenances": 1
    },
    "fuel_stats": {
      "total_fuel_logs": 10,
      "total_fuel_cost": 1250.50
    }
  }
}
```

---

## 3. Veículos

### GET /vehicle

Lista todos os veículos da empresa do usuário autenticado.

**Headers:**
```
Authorization: Bearer {access_token}
```

**Query Parameters:**
- `per-page`: Itens por página (padrão: 20)
- `page`: Número da página
- `status`: Filtrar por status (active, maintenance, inactive)
- `brand`: Filtrar por marca
- `fuel_type`: Filtrar por tipo de combustível

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "company_id": 1,
      "license_plate": "AA-12-34",
      "brand": "Mercedes-Benz",
      "model": "Sprinter",
      "year": 2020,
      "fuel_type": "diesel",
      "mileage": 125000,
      "status": "active",
      "driver_id": 5,
      "photo": null,
      "created_at": "2025-11-21 10:00:00",
      "updated_at": "2025-11-21 10:00:00"
    }
  ],
  "_meta": {
    "totalCount": 4,
    "pageCount": 1,
    "currentPage": 1,
    "perPage": 20
  }
}
```

---

### POST /vehicle

Cria um novo veículo.

**Headers:**
```
Authorization: Bearer {access_token}
Content-Type: application/json
```

**Request:**
```json
{
  "license_plate": "ZZ-99-88",
  "brand": "Toyota",
  "model": "Hilux",
  "year": 2023,
  "fuel_type": "diesel",
  "mileage": 0,
  "status": "active",
  "driver_id": null
}
```

**Response (201 Created):**
```json
{
  "success": true,
  "data": {
    "id": 5,
    "company_id": 1,
    "license_plate": "ZZ-99-88",
    "brand": "Toyota",
    "model": "Hilux",
    "year": 2023,
    "fuel_type": "diesel",
    "mileage": 0,
    "status": "active",
    "driver_id": null,
    "created_at": "2026-01-10 22:15:00",
    "updated_at": "2026-01-10 22:15:00"
  },
  "message": "Veículo criado com sucesso"
}
```

**Erros:**
- `400 Bad Request`: Dados inválidos
- `403 Forbidden`: Sem permissão

---

### GET /vehicle/{id}

Visualiza detalhes de um veículo específico.

**Headers:**
```
Authorization: Bearer {access_token}
```

**Response (200 OK):**
```json
{
  "id": 1,
  "company_id": 1,
  "license_plate": "AA-12-34",
  "brand": "Mercedes-Benz",
  "model": "Sprinter",
  "year": 2020,
  "fuel_type": "diesel",
  "mileage": 125000,
  "status": "active",
  "driver_id": 5,
  "photo": null,
  "created_at": "2025-11-21 10:00:00",
  "updated_at": "2025-11-21 10:00:00",
  "company": {
    "id": 1,
    "name": "VeiGest - Demo Company"
  }
}
```

---

### PUT /vehicle/{id}

Atualiza dados de um veículo.

**Headers:**
```
Authorization: Bearer {access_token}
Content-Type: application/json
```

**Request:**
```json
{
  "mileage": 130000,
  "status": "maintenance"
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "mileage": 130000,
    "status": "maintenance",
    "..."
  },
  "message": "Veículo atualizado com sucesso"
}
```

---

### DELETE /vehicle/{id}

Remove um veículo.

**Headers:**
```
Authorization: Bearer {access_token}
```

**Response (204 No Content):** Sucesso sem corpo

**Erros:**
- `404 Not Found`: Veículo não encontrado
- `403 Forbidden`: Sem permissão

---

### GET /vehicles/{id}/maintenances

Lista manutenções de um veículo.

**Headers:**
```
Authorization: Bearer {access_token}
```

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "vehicle_id": 1,
      "type": "Oil Change",
      "description": "Regular oil and filter change",
      "date": "2025-10-15",
      "cost": 85.50,
      "status": "completed"
    }
  ]
}
```

---

### GET /vehicles/{id}/fuel-logs

Lista abastecimentos de um veículo.

**Headers:**
```
Authorization: Bearer {access_token}
```

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "vehicle_id": 1,
      "liters": 50.5,
      "value": 85.75,
      "current_mileage": 125000,
      "date": "2025-10-15",
      "price_per_liter": 1.70
    }
  ]
}
```

---

### GET /vehicles/{id}/stats

Estatísticas do veículo.

**Headers:**
```
Authorization: Bearer {access_token}
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "vehicle_info": {
      "id": 1,
      "license_plate": "AA-12-34",
      "brand": "Mercedes-Benz",
      "model": "Sprinter",
      "status": "active",
      "current_mileage": 125000
    },
    "maintenance_stats": {
      "total_maintenances": 5,
      "pending_maintenances": 1,
      "completed_maintenances": 4,
      "total_maintenance_cost": 850.00
    },
    "fuel_stats": {
      "total_fuel_logs": 15,
      "total_liters": 750.5,
      "total_fuel_cost": 1275.85,
      "average_consumption": 8.5
    }
  }
}
```

---

### GET /vehicles/by-status/{status}

Lista veículos por status.

**Headers:**
```
Authorization: Bearer {access_token}
```

**Parâmetros de URL:**
- `status`: active | maintenance | inactive

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "license_plate": "AA-12-34",
      "status": "active",
      "..."
    }
  ]
}
```

---

## 4. Usuários

### GET /user

Lista usuários da empresa.

**Headers:**
```
Authorization: Bearer {access_token}
```

**Query Parameters:**
- `per-page`: Itens por página (padrão: 20)
- `page`: Número da página
- `role`: Filtrar por função
- `status`: Filtrar por status
- `search`: Buscar por nome ou username

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "username": "admin",
      "name": "Administrator",
      "email": "admin@veigest.com",
      "phone": null,
      "status": "active",
      "tipo": "admin",
      "company_id": 1,
      "license_number": null,
      "license_expiry": null,
      "photo": null,
      "created_at": "2025-11-21 10:00:00"
    }
  ],
  "_meta": {
    "totalCount": 5,
    "pageCount": 1,
    "currentPage": 1,
    "perPage": 20
  }
}
```

---

### POST /user

Cria um novo usuário.

**Headers:**
```
Authorization: Bearer {access_token}
Content-Type: application/json
```

**Request:**
```json
{
  "username": "newdriver",
  "password": "password123",
  "name": "João Silva",
  "email": "joao@veigest.com",
  "phone": "+351912345678",
  "tipo": "driver",
  "license_number": "PT123456789",
  "license_expiry": "2027-12-31"
}
```

**Response (201 Created):**
```json
{
  "id": 10,
  "username": "newdriver",
  "name": "João Silva",
  "email": "joao@veigest.com",
  "phone": "+351912345678",
  "status": "active",
  "tipo": "driver",
  "company_id": 1,
  "license_number": "PT123456789",
  "license_expiry": "2027-12-31",
  "created_at": "2026-01-10 22:15:00"
}
```

---

### PUT /user/{id}

Atualiza dados de um usuário.

**Headers:**
```
Authorization: Bearer {access_token}
Content-Type: application/json
```

**Request:**
```json
{
  "name": "João Silva Santos",
  "phone": "+351999888777"
}
```

**Response (200 OK):**
```json
{
  "id": 10,
  "username": "newdriver",
  "name": "João Silva Santos",
  "phone": "+351999888777",
  "..."
}
```

---

### DELETE /user/{id}

Remove um usuário.

**Headers:**
```
Authorization: Bearer {access_token}
```

**Response (204 No Content):** Sucesso

---

### GET /users/drivers

Lista apenas condutores.

**Headers:**
```
Authorization: Bearer {access_token}
```

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 5,
      "username": "driver1",
      "name": "Maria Santos",
      "tipo": "driver",
      "license_number": "PT987654321",
      "license_expiry": "2027-06-15"
    }
  ]
}
```

---

### GET /users/profile

Retorna perfil do usuário autenticado.

**Headers:**
```
Authorization: Bearer {access_token}
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "username": "admin",
    "name": "Administrator",
    "email": "admin@veigest.com",
    "company": {
      "id": 1,
      "name": "VeiGest - Demo Company"
    },
    "roles": ["admin"],
    "permissions": ["..."]
  }
}
```

---

## 5. Manutenções

### GET /maintenance

Lista manutenções da empresa.

**Headers:**
```
Authorization: Bearer {access_token}
```

**Query Parameters:**
- `per-page`: Itens por página
- `page`: Número da página
- `vehicle_id`: Filtrar por veículo
- `status`: Filtrar por status (scheduled, in_progress, completed)
- `type`: Filtrar por tipo de manutenção
- `start_date`: Data inicial
- `end_date`: Data final

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "company_id": 1,
      "vehicle_id": 1,
      "type": "Oil Change",
      "description": "Regular oil and filter change",
      "date": "2025-10-15",
      "cost": 85.50,
      "mileage_record": 120000,
      "next_date": "2026-01-15",
      "workshop": "AutoCenter Lisbon",
      "status": "completed",
      "created_at": "2025-10-15 10:00:00",
      "vehicle": {
        "id": 1,
        "license_plate": "AA-12-34"
      }
    }
  ],
  "_meta": {
    "totalCount": 4,
    "pageCount": 1,
    "currentPage": 1,
    "perPage": 20
  }
}
```

---

### POST /maintenance

Cria uma nova manutenção.

**Headers:**
```
Authorization: Bearer {access_token}
Content-Type: application/json
```

**Request:**
```json
{
  "vehicle_id": 1,
  "type": "Tire Replacement",
  "description": "Replace all 4 tires",
  "date": "2026-01-15",
  "cost": 450.00,
  "mileage_record": 125000,
  "next_date": "2027-01-15",
  "workshop": "Pneus & Rodas",
  "status": "scheduled"
}
```

**Response (201 Created):**
```json
{
  "id": 5,
  "company_id": 1,
  "vehicle_id": 1,
  "type": "Tire Replacement",
  "description": "Replace all 4 tires",
  "date": "2026-01-15",
  "cost": 450.00,
  "status": "scheduled",
  "created_at": "2026-01-10 22:15:00"
}
```

---

### PUT /maintenance/{id}

Atualiza uma manutenção.

**Headers:**
```
Authorization: Bearer {access_token}
Content-Type: application/json
```

**Request:**
```json
{
  "status": "completed",
  "cost": 475.00
}
```

---

### GET /maintenances/by-vehicle/{vehicle_id}

Lista manutenções de um veículo específico.

**Headers:**
```
Authorization: Bearer {access_token}
```

---

### GET /maintenances/by-status/{status}

Lista manutenções por status.

**Parâmetros:**
- `status`: scheduled | in_progress | completed

---

### POST /maintenances/{id}/schedule

Agenda uma manutenção.

**Headers:**
```
Authorization: Bearer {access_token}
Content-Type: application/json
```

**Request:**
```json
{
  "next_date": "2026-03-15",
  "notes": "Agendar troca de óleo"
}
```

---

### GET /maintenances/reports/monthly

Relatório mensal de manutenções.

**Headers:**
```
Authorization: Bearer {access_token}
```

**Query Parameters:**
- `year`: Ano (padrão: atual)
- `month`: Mês (padrão: atual)

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "period": "2026-01",
    "summary": {
      "total_maintenances": 5,
      "total_cost": 1500.00,
      "scheduled": 2,
      "completed": 3
    },
    "by_vehicle": [
      {
        "vehicle_id": 1,
        "license_plate": "AA-12-34",
        "maintenance_count": 2,
        "total_cost": 600.00
      }
    ],
    "by_type": [
      {
        "type": "Oil Change",
        "count": 3,
        "total_cost": 250.00
      }
    ]
  }
}
```

---

## 6. Abastecimentos

### GET /fuel-log

Lista abastecimentos da empresa.

**Headers:**
```
Authorization: Bearer {access_token}
```

**Query Parameters:**
- `per-page`: Itens por página
- `page`: Número da página
- `vehicle_id`: Filtrar por veículo
- `start_date`: Data inicial (YYYY-MM-DD)
- `end_date`: Data final (YYYY-MM-DD)
- `search`: Buscar em notas

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "vehicle_id": 1,
      "liters": 50.5,
      "value": 85.75,
      "current_mileage": 125000,
      "date": "2025-10-15",
      "price_per_liter": 1.70,
      "notes": "Abastecimento completo",
      "created_at": "2025-10-15 10:00:00",
      "vehicle": {
        "id": 1,
        "license_plate": "AA-12-34"
      }
    }
  ],
  "_meta": {
    "totalCount": 10,
    "pageCount": 1,
    "currentPage": 1,
    "perPage": 20
  }
}
```

---

### POST /fuel-log

Registra um novo abastecimento.

**Headers:**
```
Authorization: Bearer {access_token}
Content-Type: application/json
```

**Request:**
```json
{
  "vehicle_id": 1,
  "liters": 55.0,
  "value": 93.50,
  "current_mileage": 130000,
  "date": "2026-01-10",
  "notes": "Posto Shell"
}
```

**Response (201 Created):**
```json
{
  "id": 11,
  "vehicle_id": 1,
  "liters": 55.0,
  "value": 93.50,
  "price_per_liter": 1.70,
  "current_mileage": 130000,
  "date": "2026-01-10",
  "notes": "Posto Shell",
  "created_at": "2026-01-10 22:15:00"
}
```

---

### GET /fuel-logs/by-vehicle/{vehicle_id}

Lista abastecimentos de um veículo.

---

### GET /fuel-logs/stats

Estatísticas de combustível.

**Headers:**
```
Authorization: Bearer {access_token}
```

**Query Parameters:**
- `vehicle_id`: Filtrar por veículo
- `period`: yearly | monthly | weekly

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "period": "yearly",
    "summary": {
      "total_fuel_logs": 50,
      "total_liters": 2500.5,
      "total_cost": 4250.85,
      "average_price_per_liter": 1.70,
      "fuel_efficiency": 8.2
    },
    "by_vehicle": [
      {
        "vehicle_id": 1,
        "license_plate": "AA-12-34",
        "total_liters": 625.0,
        "total_cost": 1062.50,
        "efficiency": 8.5
      }
    ],
    "monthly_breakdown": [
      {
        "month": "2026-01",
        "liters": 200.0,
        "cost": 340.00
      }
    ]
  }
}
```

---

### GET /fuel-logs/alerts

Alertas de combustível.

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "total_alerts": 2,
    "alerts": [
      {
        "vehicle_id": 1,
        "license_plate": "AA-12-34",
        "message": "Veículo sem abastecimento há 30 dias",
        "priority": "high",
        "days_since_last_fuel": 30
      }
    ]
  }
}
```

---

### GET /fuel-logs/efficiency-report

Relatório de eficiência de combustível.

**Query Parameters:**
- `start_date`: Data inicial
- `end_date`: Data final

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "summary": {
      "total_vehicles": 4,
      "total_fuel_cost": 5000.00,
      "total_liters": 2941.0,
      "fleet_average_efficiency": 8.5
    },
    "vehicle_efficiency": [
      {
        "vehicle_id": 1,
        "license_plate": "AA-12-34",
        "fuel_efficiency": 8.8,
        "cost_per_km": 0.19
      }
    ],
    "recommendations": [
      "Veículo BB-56-78 apresenta eficiência 15% abaixo da média"
    ]
  }
}
```

---

## 7. Alertas

### GET /alert

Lista alertas da empresa.

**Headers:**
```
Authorization: Bearer {access_token}
```

**Query Parameters:**
- `per-page`: Itens por página
- `page`: Número da página
- `type`: maintenance | document | fuel | other
- `priority`: low | medium | high | critical
- `status`: active | resolved | ignored

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "company_id": 1,
      "type": "maintenance",
      "title": "Manutenção programada",
      "description": "Veículo AA-12-34 precisa de troca de óleo",
      "priority": "medium",
      "status": "active",
      "details": {
        "vehicle_id": 1,
        "next_date": "2026-01-15"
      },
      "created_at": "2025-12-15 10:00:00",
      "resolved_at": null
    }
  ],
  "_meta": {
    "totalCount": 5,
    "pageCount": 1,
    "currentPage": 1,
    "perPage": 20
  }
}
```

---

### POST /alert

Cria um novo alerta.

**Request:**
```json
{
  "type": "document",
  "title": "Documento a expirar",
  "description": "Seguro do veículo AA-12-34 expira em 30 dias",
  "priority": "high",
  "details": {
    "vehicle_id": 1,
    "document_type": "insurance",
    "expiry_date": "2026-02-10"
  }
}
```

---

### PUT /alert/{id}

Atualiza um alerta.

---

### POST /alerts/{id}/resolve

Marca um alerta como resolvido.

**Request:**
```json
{
  "resolution_notes": "Seguro renovado"
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "status": "resolved",
    "resolved_at": "2026-01-10 22:15:00"
  },
  "message": "Alerta resolvido com sucesso"
}
```

---

### POST /alerts/{id}/ignore

Marca um alerta como ignorado.

---

## 8. Documentos

### GET /document

Lista documentos da empresa.

**Headers:**
```
Authorization: Bearer {access_token}
```

**Query Parameters:**
- `per-page`: Itens por página
- `page`: Número da página
- `vehicle_id`: Filtrar por veículo
- `driver_id`: Filtrar por condutor
- `type`: registration | insurance | inspection | license | other
- `status`: valid | expired

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "company_id": 1,
      "file_id": 1,
      "vehicle_id": 1,
      "driver_id": null,
      "type": "insurance",
      "expiry_date": "2026-03-15",
      "status": "valid",
      "notes": "Comprehensive insurance coverage",
      "created_at": "2025-11-21 10:00:00",
      "file": {
        "id": 1,
        "original_name": "vehicle_insurance.pdf",
        "size": 2048576,
        "path": "/uploads/documents/vehicle_insurance.pdf"
      },
      "vehicle": {
        "id": 1,
        "license_plate": "AA-12-34"
      }
    }
  ],
  "_meta": {
    "totalCount": 3,
    "pageCount": 1,
    "currentPage": 1,
    "perPage": 20
  }
}
```

---

### POST /document

Cria um novo documento.

**Request:**
```json
{
  "file_id": 4,
  "vehicle_id": 2,
  "type": "insurance",
  "expiry_date": "2027-01-15",
  "notes": "Nova apólice de seguro"
}
```

---

### GET /documents/by-vehicle/{vehicle_id}

Lista documentos de um veículo.

---

### GET /documents/expiring

Lista documentos próximos da expiração.

**Query Parameters:**
- `days`: Dias até expiração (padrão: 30)

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "count": 2,
    "documents": [
      {
        "id": 1,
        "type": "insurance",
        "expiry_date": "2026-02-10",
        "days_until_expiry": 31,
        "vehicle": {
          "license_plate": "AA-12-34"
        }
      }
    ]
  }
}
```

---

## 9. Arquivos

### GET /file

Lista arquivos da empresa.

**Headers:**
```
Authorization: Bearer {access_token}
```

**Query Parameters:**
- `per-page`: Itens por página
- `page`: Número da página

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "company_id": 1,
      "original_name": "vehicle_insurance.pdf",
      "size": 2048576,
      "size_formatted": "2.0 MB",
      "path": "/uploads/documents/vehicle_insurance.pdf",
      "uploaded_by": 1,
      "created_at": "2025-11-21 10:00:00",
      "uploader": {
        "id": 1,
        "username": "admin"
      }
    }
  ],
  "_meta": {
    "totalCount": 3,
    "pageCount": 1,
    "currentPage": 1,
    "perPage": 20
  }
}
```

---

### POST /file

Faz upload de um arquivo.

**Headers:**
```
Authorization: Bearer {access_token}
Content-Type: multipart/form-data
```

**Request (Form Data):**
- `file`: Arquivo binário

**Response (201 Created):**
```json
{
  "id": 4,
  "company_id": 1,
  "original_name": "documento.pdf",
  "size": 1024000,
  "path": "/uploads/documents/documento_1704918900.pdf",
  "uploaded_by": 1,
  "created_at": "2026-01-10 22:15:00"
}
```

---

### DELETE /file/{id}

Remove um arquivo.

**Response (204 No Content):** Sucesso

---

### GET /files/stats

Estatísticas de arquivos.

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "total_files": 3,
    "total_size": 4096576,
    "total_size_formatted": "3.9 MB",
    "by_type": {
      "pdf": {
        "count": 2,
        "size": 3584576
      },
      "jpg": {
        "count": 1,
        "size": 512000
      }
    }
  }
}
```

---

## 10. Rotas

### GET /route

Lista rotas da empresa.

**Headers:**
```
Authorization: Bearer {access_token}
```

**Query Parameters:**
- `per-page`: Itens por página
- `page`: Número da página
- `vehicle_id`: Filtrar por veículo
- `driver_id`: Filtrar por condutor
- `status`: pending | in_progress | completed
- `start_date`: Data inicial
- `end_date`: Data final

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "company_id": 1,
      "vehicle_id": 1,
      "driver_id": 5,
      "start_location": "Lisboa - Armazém Central",
      "end_location": "Porto - Cliente ABC",
      "start_time": "2025-12-15 08:00:00",
      "end_time": "2025-12-15 12:30:00",
      "status": "completed",
      "distance_km": 320,
      "notes": "Entrega regular",
      "created_at": "2025-12-15 07:00:00",
      "vehicle": {
        "id": 1,
        "license_plate": "AA-12-34"
      },
      "driver": {
        "id": 5,
        "name": "Maria Santos"
      }
    }
  ],
  "_meta": {
    "totalCount": 10,
    "pageCount": 1,
    "currentPage": 1,
    "perPage": 20
  }
}
```

---

### POST /route

Cria uma nova rota.

**Request:**
```json
{
  "vehicle_id": 1,
  "driver_id": 5,
  "start_location": "Lisboa - Armazém Central",
  "end_location": "Coimbra - Cliente XYZ",
  "start_time": "2026-01-11 09:00:00",
  "notes": "Entrega urgente"
}
```

---

### PUT /route/{id}

Atualiza uma rota.

---

### DELETE /route/{id}

Remove uma rota.

---

### POST /routes/{id}/complete

Marca uma rota como completada.

**Request:**
```json
{
  "end_time": "2026-01-11 14:30:00",
  "distance_km": 200,
  "notes": "Entrega realizada com sucesso"
}
```

---

### GET /routes/by-vehicle/{vehicle_id}

Lista rotas de um veículo.

---

### GET /routes/by-driver/{driver_id}

Lista rotas de um condutor.

---

## 11. Logs de Atividade

### GET /activity-log

Lista logs de atividade da empresa.

**Headers:**
```
Authorization: Bearer {access_token}
```

**Query Parameters:**
- `per-page`: Itens por página
- `page`: Número da página
- `user_id`: Filtrar por usuário
- `action`: create | update | delete | view | login | logout
- `entity`: user | vehicle | maintenance | document | fuel_log | alert | file | route
- `entity_id`: ID da entidade
- `start_date`: Data inicial
- `end_date`: Data final

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "company_id": 1,
      "user_id": 1,
      "action": "create",
      "entity": "vehicle",
      "entity_id": 1,
      "details": {
        "license_plate": "AA-12-34",
        "brand": "Mercedes-Benz"
      },
      "ip": "192.168.1.100",
      "created_at": "2025-11-21 10:00:00",
      "user": {
        "id": 1,
        "username": "admin"
      }
    }
  ],
  "_meta": {
    "totalCount": 100,
    "pageCount": 5,
    "currentPage": 1,
    "perPage": 20
  }
}
```

---

### GET /activity-logs/by-user/{user_id}

Lista logs de um usuário específico.

---

### GET /activity-logs/by-entity/{entity}/{entity_id}

Lista logs de uma entidade específica.

**Exemplo:** `GET /activity-logs/by-entity/vehicle/1`

---

### GET /activity-logs/recent

Lista logs recentes (últimas 24 horas).

---

### GET /activity-logs/stats

Estatísticas de logs de atividade.

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "total_logs": 150,
    "by_action": {
      "create": 45,
      "update": 60,
      "delete": 10,
      "view": 25,
      "login": 10
    },
    "by_entity": {
      "vehicle": 40,
      "maintenance": 35,
      "fuel_log": 30,
      "document": 20,
      "user": 25
    },
    "by_user": [
      {
        "user_id": 1,
        "username": "admin",
        "action_count": 80
      }
    ],
    "recent_activity": {
      "today": 15,
      "this_week": 45,
      "this_month": 150
    }
  }
}
```

---

## Códigos de Status HTTP

| Código | Descrição |
|--------|-----------|
| 200 | OK - Requisição bem-sucedida |
| 201 | Created - Recurso criado com sucesso |
| 204 | No Content - Recurso removido com sucesso |
| 400 | Bad Request - Dados inválidos ou faltando |
| 401 | Unauthorized - Token inválido ou expirado |
| 403 | Forbidden - Sem permissão para acessar |
| 404 | Not Found - Recurso não encontrado |
| 422 | Unprocessable Entity - Erro de validação |
| 500 | Internal Server Error - Erro interno |

---

## Formato de Erro Padrão

```json
{
  "name": "Bad Request",
  "message": "Descrição do erro",
  "code": 0,
  "status": 400,
  "type": "yii\\web\\BadRequestHttpException"
}
```

Ou com erros de validação:

```json
{
  "success": false,
  "message": "Erro ao criar recurso",
  "errors": {
    "field_name": ["Mensagem de erro"]
  }
}
```

---

## Paginação

Todas as listagens suportam paginação via query parameters:

- `per-page`: Número de itens por página (padrão: 20)
- `page`: Número da página (começa em 1)

A resposta inclui metadados de paginação:

```json
{
  "data": [...],
  "_meta": {
    "totalCount": 100,
    "pageCount": 5,
    "currentPage": 1,
    "perPage": 20
  },
  "_links": {
    "self": {"href": "..."},
    "next": {"href": "..."},
    "last": {"href": "..."}
  }
}
```

---

## Autenticação e Multi-Tenancy

### Token de Autenticação

O token é um JSON codificado em Base64 contendo:

```json
{
  "user_id": 1,
  "username": "admin",
  "company_id": 1,
  "company_code": 1767970425807,
  "roles": ["admin"],
  "permissions": ["..."],
  "expires_at": 1768169433,
  "issued_at": 1768083033
}
```

### Multi-Tenancy

Todos os recursos são automaticamente filtrados pelo `company_id` do token de autenticação. Isso garante que cada empresa veja apenas seus próprios dados.

---

## Credenciais de Teste

| Usuário | Senha | Função |
|---------|-------|--------|
| admin | admin | Administrador |
| manager | manager123 | Gestor |
| driver1 | driver123 | Condutor |
| driver2 | driver123 | Condutor |
| driver3 | driver123 | Condutor |

---

## Versão

- **API Version:** 1.0
- **Documento atualizado em:** 10/01/2026
