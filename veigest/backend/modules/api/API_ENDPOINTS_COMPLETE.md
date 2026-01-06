# 📚 VeiGest API - Documentação Completa dos Endpoints

## 📋 Visão Geral

A API VeiGest agora inclui **7 módulos principais** completos com endpoints CRUD e funcionalidades avançadas:

- **🔐 Autenticação** - Login, tokens, segurança
- **🏢 Empresas** - Gestão de empresas e estatísticas
- **🚗 Veículos** - CRUD de veículos e relatórios
- **👥 Usuários** - Gestão de usuários e condutores
- **🔧 Manutenções** - Registros e agendamento de manutenções
- **⛽ Abastecimentos** - Controle de combustível e eficiência
- **🚨 Alertas + MQTT** - Sistema de alertas com messaging em tempo real

## 🔐 Autenticação

### Endpoints Básicos
```
POST   /api/auth/login          # Login do usuário
POST   /api/auth/logout         # Logout do usuário
GET    /api/auth/me             # Perfil do usuário autenticado
POST   /api/auth/refresh        # Renovar token
GET    /api/auth/info           # Informações da API
```

**Exemplo de Login:**
```bash
curl -X POST http://localhost:21080/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin"}'
```

## 🏢 Empresas (Companies)

### Endpoints CRUD
```
GET    /api/company             # Listar empresas (admin only)
GET    /api/company/{id}        # Visualizar empresa
POST   /api/company             # Criar empresa (admin only)
PUT    /api/company/{id}        # Atualizar empresa
DELETE /api/company/{id}        # Deletar empresa (admin only)
```

### Endpoints Personalizados
```
GET    /api/companies/{id}/vehicles       # Veículos da empresa
GET    /api/companies/{id}/users          # Usuários da empresa
GET    /api/companies/{id}/stats          # Estatísticas da empresa
```

## 🔧 Manutenções (Maintenance)

### Endpoints principais
```
GET    /api/maintenance                     # Listar manutenções (filtros: vehicle_id, tipo, estado, date range)
GET    /api/maintenance/{id}                # Ver detalhe da manutenção
POST   /api/maintenance                     # Criar nova manutenção
PUT    /api/maintenance/{id}                # Atualizar manutenção
DELETE /api/maintenance/{id}                # Remover manutenção
```

### Endpoints adicionais
```
GET    /api/maintenance/by-vehicle/{vehicle_id}   # Manutenções de um veículo
GET    /api/maintenance/by-status/{estado}        # Filtrar por estado (ex: scheduled, completed)
POST   /api/maintenance/{id}/schedule             # Agendar/atualizar agendamento de manutenção
GET    /api/maintenance/reports/monthly           # Relatório mensal de manutenções
GET    /api/maintenance/reports/costs             # Relatório de custos por tipo/veículo
```

## ⛽ Abastecimentos (FuelLog)

### Endpoints principais
```
GET    /api/fuel-log                  # Listar abastecimentos (filtros: vehicle_id, date range)
GET    /api/fuel-log/{id}             # Detalhe de um abastecimento
POST   /api/fuel-log                  # Criar registro de abastecimento
PUT    /api/fuel-log/{id}             # Atualizar abastecimento
DELETE /api/fuel-log/{id}             # Remover registro
```

### Relatórios e utilitários
```
GET    /api/fuel-log/by-vehicle/{vehicle_id}     # Abastecimentos por veículo
GET    /api/fuel-log/stats                        # Estatísticas/consumo por período
GET    /api/fuel-log/alerts                       # Alertas relacionados a consumo/eficiência
GET    /api/fuel-log/efficiency-report            # Relatório de eficiência (km/l, custo por km)
```

## 🚗 Veículos (Vehicle) — Endpoints Avançados

```
GET    /api/vehicles                   # Listar veículos (filtros e paginação)
GET    /api/vehicles/{id}              # Detalhe do veículo
POST   /api/vehicles                   # Criar veículo
PUT    /api/vehicles/{id}              # Atualizar veículo
DELETE /api/vehicles/{id}              # Remover veículo
GET    /api/vehicles/{id}/maintenances # Manutenções do veículo
GET    /api/vehicles/{id}/fuel-logs    # Abastecimentos do veículo
GET    /api/vehicles/{id}/stats        # Estatísticas por veículo (consumo, custos, manutenção)
GET    /api/vehicles/by-status/{status}# Filtrar por status (active, maintenance, inactive)
```

## � Alertas + Messaging MQTT (Alert)

### Endpoints CRUD
```
GET    /api/alerts                      # Listar alertas (filtros: type, status, priority)
GET    /api/alerts/{id}                 # Ver detalhe do alerta
POST   /api/alerts                      # Criar novo alerta (publica MQTT automaticamente)
PUT    /api/alerts/{id}                 # Atualizar alerta
DELETE /api/alerts/{id}                 # Remover alerta
```

### Endpoints de Gestão
```
POST   /api/alerts/{id}/resolve         # Resolver alerta (publica MQTT)
POST   /api/alerts/{id}/ignore          # Ignorar alerta (publica MQTT)
POST   /api/alerts/bulk-resolve         # Resolver múltiplos alertas
```

### Endpoints de Consulta
```
GET    /api/alerts/by-type/{type}       # Filtrar por tipo (maintenance, document, fuel, other)
GET    /api/alerts/by-priority/{priority} # Filtrar por prioridade (low, medium, high, critical)
GET    /api/alerts/count                # Contagem de alertas ativos
GET    /api/alerts/stats                # Estatísticas completas de alertas
GET    /api/alerts/types                # Lista de tipos disponíveis
GET    /api/alerts/priorities           # Lista de prioridades disponíveis
```

### Endpoints MQTT (Messaging)
```
POST   /api/alerts/{id}/broadcast       # Broadcast manual de alerta via MQTT
GET    /api/alerts/mqtt-info            # Informação sobre canais MQTT disponíveis
```

### Canais MQTT Disponíveis

| Canal | Descrição |
|-------|-----------|
| `veigest/alerts/{company_id}` | Todos os alertas da empresa |
| `veigest/alerts/{company_id}/new` | Novos alertas criados |
| `veigest/alerts/{company_id}/resolved` | Alertas resolvidos |
| `veigest/alerts/{company_id}/critical` | Alertas de prioridade crítica |
| `veigest/alerts/{company_id}/high` | Alertas de alta prioridade |

**Exemplo - Criar Alerta (dispara MQTT automaticamente):**
```bash
curl -X POST http://localhost:21080/api/alerts \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "type": "maintenance",
    "title": "Revisão Obrigatória",
    "description": "Veículo atingiu 50.000 km",
    "priority": "high"
  }'
```

**Exemplo - Subscrever MQTT (mosquitto_sub):**
```bash
mosquitto_sub -h localhost -p 1883 -t "veigest/alerts/1/#" -v
```

## �📄 Documentos e Ficheiros (Document / File)

```
GET    /api/documents                   # Listar documentos (filtros: vehicle_id, driver_id, status)
POST   /api/documents                   # Criar/associar documento (com upload de ficheiro)
GET    /api/files                       # Listar ficheiros
POST   /api/files                       # Upload de ficheiros
GET    /api/files/{id}/download         # Download de ficheiro
```

## 🧭 Rotas

```
GET    /api/routes                      # Listar rotas
POST   /api/routes                      # Criar rota
GET    /api/routes/{id}                 # Ver rota
PUT    /api/routes/{id}                 # Atualizar rota
DELETE /api/routes/{id}                 # Excluir rota
POST   /api/routes/{id}/complete        # Concluir rota
GET    /api/routes/active               # Rotas ativas
GET    /api/routes/scheduled            # Rotas agendadas
GET    /api/routes/stats                # Estatísticas de rotas
```

## 🔐 Autenticação / Token
A API continua a devolver um token Base64 com `user_id`, `company_code` e `expires_at`. Incluir no header:

```
Authorization: Bearer {token}
```

---
Observação: Muitos controllers implementam filtragem automática por `company_id` e validação RBAC; consulte as controllers específicas em `backend/modules/api/controllers/` para parâmetros de query e exemplos de payload.


**Exemplo - Estatísticas da Empresa:**
```bash
curl -X GET http://localhost:21080/api/companies/1/stats \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Resposta:**
```json
{
  "company": {
    "id": 1,
    "nome": "VeiGest Empresa",
    "status": "active"
  },
  "vehicles_count": 15,
  "active_vehicles": 12,
  "users_count": 8,
  "drivers_count": 4,
  "maintenance_stats": {
    "total_maintenances": 25,
    "pending_maintenances": 3
  },
  "fuel_stats": {
    "total_fuel_logs": 85,
    "total_fuel_cost": 12750.50
  }
}
```

## 🚗 Veículos (Vehicles)

### Endpoints CRUD
```
GET    /api/vehicles            # Listar veículos
GET    /api/vehicles/{id}       # Visualizar veículo
POST   /api/vehicles            # Criar veículo
PUT    /api/vehicles/{id}       # Atualizar veículo
DELETE /api/vehicles/{id}       # Deletar veículo
```

### Endpoints Personalizados
```
GET    /api/vehicles/{id}/maintenances     # Manutenções do veículo
GET    /api/vehicles/{id}/fuel-logs        # Abastecimentos do veículo
GET    /api/vehicles/{id}/stats            # Estatísticas do veículo
GET    /api/vehicles/by-status/{status}    # Filtrar por status
```

**Exemplo - Criar Veículo:**
```bash
curl -X POST http://localhost:21080/api/vehicles \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "license_plate": "AB-123-CD",
    "brand": "Toyota",
    "model": "Corolla",
    "year": 2023,
    "fuel_type": "gasolina",
    "mileage": 15000,
    "status": "active"
  }'
```

## 👥 Usuários (Users)

### Endpoints CRUD
```
GET    /api/users              # Listar usuários
GET    /api/users/{id}         # Visualizar usuário
POST   /api/users              # Criar usuário
PUT    /api/users/{id}         # Atualizar usuário
DELETE /api/users/{id}         # Deletar usuário
```

### Endpoints Personalizados
```
GET    /api/users/drivers                      # Listar condutores
GET    /api/users/profile                      # Perfil atual
GET    /api/users/by-company/{company_id}      # Usuários por empresa
POST   /api/users/{id}/update-photo            # Atualizar foto
```

## 🔧 Manutenções (Maintenance)

### Endpoints CRUD
```
GET    /api/maintenance         # Listar manutenções
GET    /api/maintenance/{id}    # Visualizar manutenção
POST   /api/maintenance         # Criar manutenção
PUT    /api/maintenance/{id}    # Atualizar manutenção
DELETE /api/maintenance/{id}    # Deletar manutenção
```

### Endpoints Personalizados
```
GET    /api/maintenance/by-vehicle/{vehicle_id}    # Manutenções por veículo
GET    /api/maintenance/by-status/{estado}         # Manutenções por estado
POST   /api/maintenance/{id}/schedule              # Agendar manutenção
GET    /api/maintenance/reports/monthly            # Relatório mensal
GET    /api/maintenance/reports/costs              # Relatório de custos
GET    /api/maintenance/stats                      # Estatísticas gerais
```

**Exemplo - Criar Manutenção:**
```bash
curl -X POST http://localhost:21080/api/maintenance \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "vehicle_id": 1,
    "tipo": "preventiva",
    "descricao": "Troca de óleo e filtros",
    "custo": 150.00,
    "data_manutencao": "2024-12-25",
    "quilometragem": 45000,
    "fornecedor": "Oficina Central",
    "estado": "agendada"
  }'
```

**Exemplo - Agendar Manutenção:**
```bash
curl -X POST http://localhost:21080/api/maintenance/1/schedule \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "scheduled_date": "2024-12-30",
    "priority": "alta",
    "assigned_technician": "João Silva"
  }'
```

## ⛽ Abastecimentos (Fuel Logs)

### Endpoints CRUD
```
GET    /api/fuel-logs          # Listar abastecimentos
GET    /api/fuel-logs/{id}     # Visualizar abastecimento
POST   /api/fuel-logs          # Criar abastecimento
PUT    /api/fuel-logs/{id}     # Atualizar abastecimento
DELETE /api/fuel-logs/{id}     # Deletar abastecimento
```

### Endpoints Personalizados
```
GET    /api/fuel-logs/by-vehicle/{vehicle_id}     # Abastecimentos por veículo
GET    /api/fuel-logs/stats                       # Estatísticas de consumo
GET    /api/fuel-logs/alerts                      # Alertas de combustível
GET    /api/fuel-logs/efficiency-report           # Relatório de eficiência
```

**Exemplo - Criar Abastecimento:**
```bash
curl -X POST http://localhost:21080/api/fuel-logs \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "vehicle_id": 1,
    "litros": 45.5,
    "custo_total": 289.75,
    "quilometragem": 47500,
    "data_abastecimento": "2024-12-18",
    "local": "Posto Shell Avenidas",
    "preco_por_litro": 6.37,
    "observacoes": "Tanque completo"
  }'
```

**Exemplo - Relatório de Eficiência:**
```bash
curl -X GET "http://localhost:21080/api/fuel-logs/efficiency-report?start_date=2024-01-01&end_date=2024-12-31" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Resposta:**
```json
{
  "period": {
    "start_date": "2024-01-01",
    "end_date": "2024-12-31"
  },
  "summary": {
    "total_vehicles": 15,
    "total_fuel_cost": 15750.00,
    "total_liters": 2890.5,
    "fleet_average_efficiency": 8.5
  },
  "vehicle_efficiency": [
    {
      "vehicle": {
        "id": 1,
        "license_plate": "AB-123-CD",
        "brand": "Toyota",
        "model": "Corolla"
      },
      "fuel_efficiency": 12.8,
      "cost_per_km": 0.35,
      "total_cost": 1250.00
    }
  ],
  "recommendations": [
    "Veículo XY-789-ZW tem baixa eficiência (6.2 km/l). Considere manutenção.",
    "Frota com boa eficiência geral"
  ]
}
```

## 📊 Filtros e Parâmetros Comuns

### Paginação
```
?page=2&per-page=20
```

### Filtros por Data
```
?start_date=2024-01-01&end_date=2024-12-31
```

### Busca Textual
```
?search=toyota
```

### Filtros Específicos
```
# Veículos por status
?status=active

# Usuários por tipo
?tipo=condutor

# Manutenções por estado
?estado=agendada

# Abastecimentos por veículo
?vehicle_id=123
```

## 🔒 Segurança e Multi-tenancy

### Headers Obrigatórios
```
Authorization: Bearer YOUR_TOKEN
Content-Type: application/json
Accept: application/json
```

### Multi-tenancy Automático
- Todos os dados são automaticamente filtrados por `company_id`
- O token contém informações da empresa do usuário
- Usuários só podem acessar dados da própria empresa
- Admins têm acesso a todas as empresas

### Códigos de Status HTTP
```
200 - OK
201 - Created
204 - No Content
400 - Bad Request
401 - Unauthorized
403 - Forbidden
404 - Not Found
422 - Unprocessable Entity
500 - Internal Server Error
```

## 🧪 Testando a API

Execute a suite completa de testes:

```bash
cd veigest/backend/modules/api-tests/
node run-all-tests.js
```

Ou testes individuais:
```bash
node tests/test-auth.js           # Autenticação
node tests/test-companies.js      # Empresas
node tests/test-vehicles.js       # Veículos
node tests/test-users.js          # Usuários
node tests/test-maintenance.js    # Manutenções
node tests/test-fuel-logs.js      # Abastecimentos
```

## 📈 Métricas de Testes

A API VeiGest possui **6 suites de testes** cobrindo:

- ✅ **120+ testes automatizados**
- ✅ **6 módulos principais**
- ✅ **50+ endpoints REST**
- ✅ **Multi-tenancy validado**
- ✅ **RBAC implementado**
- ✅ **Relatórios e estatísticas**
- ✅ **Alertas automáticos**
- ✅ **Integração completa**

---

**🚀 API VeiGest v1.0 - Sistema Completo de Gestão de Frota**
