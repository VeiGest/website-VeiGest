# 📋 Changelog - API VeiGest - 03 de Janeiro de 2026

## 🎯 Resumo das Alterações

Este documento descreve todas as correções e melhorias realizadas na API VeiGest em 03/01/2026.

---

## 🔧 Correções Realizadas

### 1. URL Base da API

**Problema:** Os testes estavam configurados para usar `localhost:8002` em vez do domínio de produção.

**Solução:** Atualizada a URL base para `https://veigestback.dryadlang.org`

**Arquivos alterados:**
- `veigest/backend/modules/api-tests/utils/http-client.js`
- `veigest/backend/modules/api-tests/test-connection.js`
- `veigest/backend/modules/api-tests/test-connectivity-complete.js`

```javascript
// Antes
const API_BASE_URL = 'http://localhost:8002/api';

// Depois
const API_BASE_URL = 'https://veigestback.dryadlang.org/api';
```

---

### 2. Credenciais de Teste

**Problema:** Os testes usavam credenciais incorretas (`apiadmin/password`).

**Solução:** Atualizadas as credenciais conforme a migration consolidada.

**Credenciais Corretas (conforme migration `m251121_000000_veigest_consolidated_migration.php`):**

| Usuário   | Username  | Password    | Role     |
|-----------|-----------|-------------|----------|
| Admin     | `admin`   | `admin`     | admin    |
| Manager   | `gestor`  | `manager123`| gestor   |
| Driver 1  | `driver1` | `driver123` | condutor |
| Driver 2  | `driver2` | `driver123` | condutor |

**Arquivos alterados:**
- `veigest/backend/modules/api-tests/tests/test-auth.js`
- `veigest/backend/modules/api-tests/run-all-tests.js`
- `veigest/backend/modules/api-tests/test-connectivity-complete.js`

---

### 3. Campo de Token

**Problema:** Os testes buscavam `data.token` mas a API retorna `data.access_token`.

**Solução:** Corrigido o acesso ao campo de token em todos os arquivos de teste.

```javascript
// Antes
const token = loginResult.response.body.data.token;

// Depois
const token = loginResult.response.body.data.access_token;
```

**Arquivos alterados:**
- `veigest/backend/modules/api-tests/tests/test-auth.js`
- `veigest/backend/modules/api-tests/tests/test-vehicles.js`
- `veigest/backend/modules/api-tests/tests/test-users.js`
- `veigest/backend/modules/api-tests/tests/test-maintenance.js`
- `veigest/backend/modules/api-tests/tests/test-fuel-logs.js`
- `veigest/backend/modules/api-tests/tests/test-companies.js`

---

### 4. Rotas da API (URL Manager)

**Problema:** Inconsistência nas rotas - endpoints REST usavam singular enquanto endpoints custom usavam plural.

**Solução:** Padronizado para usar plural em todos os endpoints REST.

**Arquivo alterado:** `veigest/backend/config/main.php`

```php
// Antes (singular, sem pluralização)
['class' => 'yii\\rest\\UrlRule', 'controller' => ['api/vehicle'], 'pluralize' => false],

// Depois (plural, com pluralização automática)
['class' => 'yii\\rest\\UrlRule', 'controller' => ['api/vehicle' => 'api/vehicle'], 'pluralize' => true],
```

---

## ✨ Novas Funcionalidades

### 5. Endpoint para Vincular Usuário à Empresa

**Novo endpoint criado:** `PUT /api/users/{id}/link-company`

**Descrição:** Permite que um administrador vincule um usuário a uma empresa diferente.

**Arquivo:** `veigest/backend/modules/api/controllers/UserController.php`

#### Especificação do Endpoint

```
PUT /api/users/{id}/link-company
```

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Body:**
```json
{
    "company_id": 2
}
```

**Response (200 OK):**
```json
{
    "success": true,
    "message": "Usuário vinculado à empresa com sucesso",
    "data": {
        "user": {
            "id": 5,
            "username": "driver1",
            "name": "Maria Santos",
            "email": "driver1@veigest.com",
            "company_id": 2
        },
        "company": {
            "id": 2,
            "name": "Nova Empresa",
            "email": "nova@empresa.com",
            "status": "active"
        },
        "previous_company_id": 1
    },
    "timestamp": "2026-01-03T14:00:00+00:00"
}
```

**Erros possíveis:**

| Código | Mensagem |
|--------|----------|
| 400    | Campo company_id é obrigatório |
| 400    | Não é possível vincular usuário a uma empresa inativa |
| 403    | Apenas administradores podem vincular usuários a empresas |
| 404    | Usuário não encontrado |
| 404    | Empresa não encontrada |

**Permissões:** Apenas usuários com role `admin` podem usar este endpoint.

---

### 6. Endpoint para Desvincular Usuário (informativo)

**Novo endpoint criado:** `DELETE /api/users/{id}/unlink-company`

**Nota:** Este endpoint retorna erro informando que `company_id` é obrigatório no sistema e sugere usar `link-company` para transferir o usuário.

```json
{
    "success": false,
    "message": "Não é possível desvincular usuário. O campo company_id é obrigatório no sistema. Use link-company para transferir para outra empresa.",
    "errors": {
        "info": "Use PUT /api/users/{id}/link-company para transferir o usuário para outra empresa"
    }
}
```

---

## 📌 Rotas da API - Referência Completa

### Autenticação

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| POST | `/api/auth/login` | Login do usuário |
| POST | `/api/auth/logout` | Logout do usuário |
| GET | `/api/auth/me` | Perfil do usuário autenticado |
| POST | `/api/auth/refresh` | Renovar token |
| GET | `/api/auth/info` | Informações da API |

### Veículos

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/api/vehicles` | Listar veículos |
| POST | `/api/vehicles` | Criar veículo |
| GET | `/api/vehicles/{id}` | Visualizar veículo |
| PUT | `/api/vehicles/{id}` | Atualizar veículo |
| DELETE | `/api/vehicles/{id}` | Excluir veículo |
| GET | `/api/vehicles/{id}/maintenances` | Manutenções do veículo |
| GET | `/api/vehicles/{id}/fuel-logs` | Abastecimentos do veículo |
| GET | `/api/vehicles/{id}/stats` | Estatísticas do veículo |
| GET | `/api/vehicles/by-status/{status}` | Veículos por status |

### Usuários

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/api/users` | Listar usuários |
| POST | `/api/users` | Criar usuário |
| GET | `/api/users/{id}` | Visualizar usuário |
| PUT | `/api/users/{id}` | Atualizar usuário |
| DELETE | `/api/users/{id}` | Excluir usuário |
| GET | `/api/users/drivers` | Listar condutores |
| GET | `/api/users/profile` | Perfil do usuário autenticado |
| PUT | `/api/users/{id}/link-company` | **NOVO** - Vincular a empresa |
| DELETE | `/api/users/{id}/unlink-company` | **NOVO** - Info sobre desvincular |
| POST | `/api/users/{id}/update-photo` | Atualizar foto |

### Empresas

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/api/companies` | Listar empresas |
| GET | `/api/companies/{id}` | Visualizar empresa |
| PUT | `/api/companies/{id}` | Atualizar empresa |
| GET | `/api/companies/{id}/vehicles` | Veículos da empresa |
| GET | `/api/companies/{id}/users` | Usuários da empresa |
| GET | `/api/companies/{id}/stats` | Estatísticas da empresa |

### Manutenções

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/api/maintenances` | Listar manutenções |
| POST | `/api/maintenances` | Criar manutenção |
| GET | `/api/maintenances/{id}` | Visualizar manutenção |
| PUT | `/api/maintenances/{id}` | Atualizar manutenção |
| DELETE | `/api/maintenances/{id}` | Excluir manutenção |
| GET | `/api/maintenances/by-vehicle/{id}` | Por veículo |
| GET | `/api/maintenances/by-status/{status}` | Por status |
| GET | `/api/maintenances/scheduled` | Agendadas |
| GET | `/api/maintenances/stats` | Estatísticas |
| GET | `/api/maintenances/reports/monthly` | Relatório mensal |
| GET | `/api/maintenances/reports/costs` | Relatório de custos |
| POST | `/api/maintenances/{id}/schedule` | Agendar |

### Abastecimentos (Fuel Logs)

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/api/fuel-logs` | Listar abastecimentos |
| POST | `/api/fuel-logs` | Criar abastecimento |
| GET | `/api/fuel-logs/{id}` | Visualizar abastecimento |
| PUT | `/api/fuel-logs/{id}` | Atualizar abastecimento |
| DELETE | `/api/fuel-logs/{id}` | Excluir abastecimento |
| GET | `/api/fuel-logs/by-vehicle/{id}` | Por veículo |
| GET | `/api/fuel-logs/stats` | Estatísticas |
| GET | `/api/fuel-logs/alerts` | Alertas de combustível |
| GET | `/api/fuel-logs/efficiency-report` | Relatório de eficiência |

### Documentos

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/api/documents` | Listar documentos |
| POST | `/api/documents` | Criar documento |
| GET | `/api/documents/{id}` | Visualizar documento |
| PUT | `/api/documents/{id}` | Atualizar documento |
| DELETE | `/api/documents/{id}` | Excluir documento |
| GET | `/api/documents/by-vehicle/{id}` | Por veículo |
| GET | `/api/documents/by-driver/{id}` | Por condutor |
| GET | `/api/documents/expiring` | Expirando |
| GET | `/api/documents/expired` | Expirados |
| GET | `/api/documents/stats` | Estatísticas |
| GET | `/api/documents/types` | Tipos disponíveis |

### Alertas

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/api/alerts` | Listar alertas |
| POST | `/api/alerts` | Criar alerta |
| GET | `/api/alerts/{id}` | Visualizar alerta |
| PUT | `/api/alerts/{id}` | Atualizar alerta |
| DELETE | `/api/alerts/{id}` | Excluir alerta |
| POST | `/api/alerts/{id}/resolve` | Resolver alerta |
| POST | `/api/alerts/{id}/ignore` | Ignorar alerta |
| GET | `/api/alerts/by-type/{type}` | Por tipo |
| GET | `/api/alerts/by-priority/{priority}` | Por prioridade |
| GET | `/api/alerts/count` | Contagem |
| GET | `/api/alerts/stats` | Estatísticas |
| POST | `/api/alerts/bulk-resolve` | Resolver em lote |

### Rotas

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/api/routes` | Listar rotas |
| POST | `/api/routes` | Criar rota |
| GET | `/api/routes/{id}` | Visualizar rota |
| PUT | `/api/routes/{id}` | Atualizar rota |
| DELETE | `/api/routes/{id}` | Excluir rota |
| POST | `/api/routes/{id}/complete` | Completar rota |
| GET | `/api/routes/by-vehicle/{id}` | Por veículo |
| GET | `/api/routes/by-driver/{id}` | Por condutor |
| GET | `/api/routes/active` | Ativas |
| GET | `/api/routes/scheduled` | Agendadas |
| GET | `/api/routes/stats` | Estatísticas |
| GET | `/api/routes/{id}/tickets` | Bilhetes da rota |

### Bilhetes (Tickets)

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/api/tickets` | Listar bilhetes |
| POST | `/api/tickets` | Criar bilhete |
| GET | `/api/tickets/{id}` | Visualizar bilhete |
| PUT | `/api/tickets/{id}` | Atualizar bilhete |
| DELETE | `/api/tickets/{id}` | Excluir bilhete |
| POST | `/api/tickets/{id}/cancel` | Cancelar bilhete |
| POST | `/api/tickets/{id}/complete` | Completar bilhete |
| GET | `/api/tickets/by-route/{id}` | Por rota |
| GET | `/api/tickets/by-status/{status}` | Por status |
| GET | `/api/tickets/stats` | Estatísticas |
| GET | `/api/tickets/statuses` | Status disponíveis |
| POST | `/api/tickets/bulk-cancel` | Cancelar em lote |
| POST | `/api/tickets/bulk-complete` | Completar em lote |

### Arquivos

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/api/files` | Listar arquivos |
| POST | `/api/files` | Upload de arquivo |
| GET | `/api/files/{id}` | Visualizar arquivo |
| DELETE | `/api/files/{id}` | Excluir arquivo |
| POST | `/api/files/upload` | Upload multipart |
| GET | `/api/files/stats` | Estatísticas |

### Logs de Atividade

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/api/activity-logs` | Listar logs |
| GET | `/api/activity-logs/{id}` | Visualizar log |
| GET | `/api/activity-logs/by-user/{id}` | Por usuário |
| GET | `/api/activity-logs/by-entity/{entity}/{id}` | Por entidade |
| GET | `/api/activity-logs/recent` | Recentes |
| GET | `/api/activity-logs/stats` | Estatísticas |
| GET | `/api/activity-logs/actions` | Ações disponíveis |
| GET | `/api/activity-logs/entities` | Entidades disponíveis |

---

## 🗄️ Estrutura do Banco de Dados (Referência)

### Tabela `users`

```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    company_id INT NOT NULL,              -- FK para companies
    name VARCHAR(150) NOT NULL,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    status ENUM('active','inactive') DEFAULT 'active',
    estado ENUM('ativo','inativo','suspenso') DEFAULT 'ativo',
    auth_key VARCHAR(32),
    password_reset_token VARCHAR(255),
    verification_token VARCHAR(255),
    license_number VARCHAR(50),           -- Para condutores
    license_expiry DATE,                  -- Para condutores
    photo VARCHAR(255),
    roles VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
);
```

### Tabela `companies`

```sql
CREATE TABLE companies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code BIGINT NOT NULL UNIQUE,          -- Código numérico único
    name VARCHAR(200) NOT NULL,
    tax_id VARCHAR(20) NOT NULL,
    email VARCHAR(150),
    phone VARCHAR(20),
    status ENUM('active','suspended','inactive') DEFAULT 'active',
    plan ENUM('basic','professional','enterprise') DEFAULT 'basic',
    settings JSON,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

---

## 🚀 Deploy

**IMPORTANTE:** As alterações no arquivo `backend/config/main.php` precisam ser aplicadas no servidor de produção para que as rotas funcionem corretamente com nomes pluralizados.

### Arquivos que precisam ser sincronizados:

1. `veigest/backend/config/main.php` - Configuração de rotas
2. `veigest/backend/modules/api/controllers/UserController.php` - Novo endpoint

### Comando de deploy (exemplo):
```bash
git add .
git commit -m "fix: correções API e novo endpoint link-company"
git push origin main
```

---

## 📝 Testes

Para executar os testes após o deploy:

```bash
cd veigest/backend/modules/api-tests
node run-all-tests.js
```

Testes individuais:
```bash
node tests/test-auth.js
node tests/test-vehicles.js
node tests/test-users.js
node tests/test-maintenance.js
node tests/test-fuel-logs.js
node tests/test-companies.js
```

---

## 👨‍💻 Autor

VeiGest Team - Atualização 03/01/2026
