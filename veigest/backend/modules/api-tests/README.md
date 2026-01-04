# VeiGest API - Scripts de Teste JavaScript

Suite completa de testes para a API RESTful VeiGest, com suporte a autenticação Base64, multi-tenancy e RBAC.

## 📁 Estrutura do Projeto

```
api-tests/
├── run-all-tests.js          # Script principal - executa todos os testes
├── utils/
│   └── http-client.js         # Utilitários HTTP (fetch, formatação)
├── tests/
│   ├── test-auth.js           # Testes de autenticação
│   ├── test-vehicles.js       # Testes de veículos (CRUD)
│   └── test-users.js          # Testes de usuários (CRUD)
└── README.md                  # Este arquivo
```

## 🚀 Pré-requisitos

### 1. Node.js
Certifique-se de ter Node.js instalado (versão 18+ recomendada):

```bash
node --version
```

### 2. Servidor API em Execução

**⚠️ IMPORTANTE:** Certifique-se de que o **BACKEND** está rodando, não o frontend!

#### Opção A: Docker (Recomendado)
```bash
cd /home/pedro/facul/website-VeiGest
docker-compose up -d backend
```
Depois, ajuste a URL em `api-tests/utils/http-client.js` para `http://localhost:21080/api`

#### Opção B: PHP Built-in Server
```bash
cd /home/pedro/facul/website-VeiGest/veigest/backend/web
php -S localhost:8002 -t .
```

#### Verificar se está funcionando:
```bash
curl http://localhost:8002/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin"}'
```

**Resposta esperada:** JSON com token, **NÃO** HTML!

Se receber HTML, consulte `TROUBLESHOOTING.md`

### 3. Banco de Dados Populado
Execute as migrações para criar o schema e dados de teste:

```bash
cd veigest/
php yii migrate
```

## 📝 Executando os Testes

### Executar Todos os Testes

Execute o script principal que roda todas as suites:

```bash
cd api-tests/
node run-all-tests.js
```

### Executar Testes Individuais

**Testes de Autenticação:**
```bash
node tests/test-auth.js
```

**Testes de Empresas:**
```bash
node tests/test-companies.js
```

**Testes de Veículos:**
```bash
node tests/test-vehicles.js
```

**Testes de Usuários:**
```bash
node tests/test-users.js
```

**Testes de Manutenções:**
```bash
node tests/test-maintenance.js
```

**Testes de Abastecimentos:**
```bash
node tests/test-fuel-logs.js
```

## 📊 Entendendo os Resultados

### Formato de Saída

Cada teste exibe:

1. **REQUEST**: Detalhes da requisição HTTP
   - Método (GET, POST, PUT, DELETE)
   - URL completa
   - Headers (incluindo Authorization)
   - Body JSON (quando aplicável)

2. **RESPONSE**: Detalhes da resposta HTTP
   - Status Code (200, 201, 401, 404, etc.)
   - Tempo de resposta
   - Headers
   - Body JSON

3. **RESULTADO**: Sucesso ou falha do teste

### Exemplo de Output

```
================================================================================
📋 TESTE: Login com credenciais válidas (admin)
================================================================================

📤 REQUEST:
--------------------------------------------------------------------------------
Método:  POST
URL:     http://localhost:8002/api/v1/auth/login

Headers:
  Content-Type: application/json
  Accept: application/json

Body:
  {
    "username": "admin",
    "password": "admin"
  }

📥 RESPONSE:
--------------------------------------------------------------------------------
Status:  200 OK
Tempo:   145ms

Body:
  {
    "success": true,
    "data": {
      "token": "eyJ1c2VyX2lkIjoxLCJjb21wYW55X2lkIjox...",
      "expires_at": "2025-12-05T12:00:00Z",
      "user": {
        "id": 1,
        "username": "admin",
        "company_id": 1
      }
    }
  }

--------------------------------------------------------------------------------
✅ RESULTADO: SUCESSO
================================================================================
```

### Resumo Final

Ao final de cada suite, você verá:

```
================================================================================
📊 RESUMO DOS TESTES DE AUTENTICAÇÃO
================================================================================
Total de testes:  7
✅ Sucessos:      7
❌ Falhas:        0
📈 Taxa de êxito: 100.0%
================================================================================

📋 DETALHES DOS TESTES:

✅ 1. Login Admin: SUCESSO
✅ 2. Validação Token: SUCESSO
✅ 3. Refresh Token: SUCESSO
✅ 4. Logout: SUCESSO
✅ 5. Login Inválido: SUCESSO (401 esperado)
✅ 6. Login Manager: SUCESSO
✅ 7. Acesso Sem Token: SUCESSO (401 esperado)
```

## 🔍 Suites de Teste

### 1. Autenticação (`test-auth.js`)

Testa os endpoints de autenticação:

- ✅ Login com credenciais válidas
- ✅ Validação de token (`/auth/me`)
- ✅ Refresh de token
- ✅ Logout
- ✅ Login com credenciais inválidas (401)
- ✅ Login multi-tenancy (diferentes empresas)
- ✅ Acesso sem token (401)

**Credenciais de Teste:**
```javascript
admin / admin           // Administrador
manager / manager123    // Gestor
driver1 / driver123     // Condutor
```

### 2. Veículos (`test-vehicles.js`)

Testa CRUD de veículos com multi-tenancy:

- ✅ Listar veículos (filtrados por company_id)
- ✅ Criar novo veículo
- ✅ Visualizar veículo específico
- ✅ Atualizar veículo
- ✅ Deletar veículo
- ✅ Validação de multi-tenancy
- ✅ Validação de dados (matrícula duplicada)

### 3. Usuários (`test-users.js`)

Testa CRUD de usuários com multi-tenancy:

- ✅ Listar usuários (filtrados por company_id)
- ✅ Criar novo usuário
- ✅ Visualizar usuário específico
- ✅ Atualizar usuário
- ✅ Listar condutores (filtro por tipo)
- ✅ Buscar por username
- ✅ Validação de dados
- ✅ Deletar usuário

## 🛠️ Personalização

### Alterar URL Base da API

Edite `utils/http-client.js`:

```javascript
const API_BASE_URL = 'http://localhost:8002/api/v1';
```

### Adicionar Novos Testes

1. Crie um novo arquivo em `tests/`:

```javascript
// tests/test-maintenance.js
const { apiRequest, formatTestResult } = require('../utils/http-client.js');

async function runMaintenanceTests(token, companyId) {
    // Seus testes aqui
}

module.exports = { runMaintenanceTests };
```

2. Importe e execute em `run-all-tests.js`:

```javascript
const { runMaintenanceTests } = require('./tests/test-maintenance.js');

// Adicionar na função runAllTests():
const maintenanceResults = await runMaintenanceTests(globalToken, globalCompanyId);
```

### Adicionar Headers Customizados

```javascript
const result = await apiRequest('GET', '/vehicle', {
    token: myToken,
    headers: {
        'X-Custom-Header': 'valor',
        'Accept-Language': 'pt-PT'
    }
});
```

## 🐛 Troubleshooting

### Erro: `ECONNREFUSED`

**Problema:** Servidor não está rodando.

**Solução:**
```bash
# Verificar se o servidor está online
curl http://localhost:8002/api/v1/auth/login

# Iniciar servidor
docker-compose up -d backend
```

### Erro: `Login failed: HTTP 401`

**Problema:** Credenciais inválidas ou banco de dados não populado.

**Solução:**
```bash
# Recriar banco e rodar migrações
cd veigest/
php yii migrate
```

### Erro: `TypeError: fetch is not defined`

**Problema:** Versão antiga do Node.js (< 18).

**Solução:**
```bash
# Atualizar Node.js para versão 18+
node --version

# Ou instalar polyfill:
npm install node-fetch
```

Depois, em `utils/http-client.js`:
```javascript
const fetch = require('node-fetch');
```

### Testes Falhando com 403 Forbidden

**Problema:** Multi-tenancy bloqueando acesso a recursos de outra empresa.

**Solução:** Isso é o comportamento esperado! O sistema está funcionando corretamente. Use tokens da mesma empresa para acessar recursos relacionados.

## 📚 Recursos Adicionais

- **Documentação da API:** `/veigest/backend/views/API_ENDPOINTS.md`
- **Guia de Implementação:** `/veigest/API_IMPLEMENTATION.md`
- **Migrações do Banco:** `/veigest/console/migrations/`

## 🤝 Contribuindo

Para adicionar novos testes:

1. Siga o padrão de estrutura dos testes existentes
2. Use a função `formatTestResult()` para output consistente
3. Documente os novos endpoints testados
4. Atualize este README

## 📄 Licença

Este projeto faz parte do sistema VeiGest. Consulte o arquivo LICENSE no diretório raiz.

---

**Última atualização:** 4 de dezembro de 2025  
**Versão:** 1.0.0
