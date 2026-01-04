# VeiGest API REST - Backend

Esta é a implementação oficial da API RESTful para o sistema VeiGest, seguindo rigorosamente os padrões REST e as melhores práticas de desenvolvimento de APIs.

## 🏗️ Arquitetura da API

A API foi reestruturada para seguir um padrão arquitetural sólido:

### Estrutura de Diretórios
```
backend/modules/api/
├── Module.php                     # Módulo principal da API
├── components/
│   └── ApiAuthenticator.php      # Autenticação personalizada Bearer Token
├── controllers/
│   ├── BaseApiController.php     # Controlador base com comportamentos comuns
│   ├── AuthController.php        # Endpoints de autenticação
│   ├── VehicleController.php     # CRUD de veículos
│   └── UserController.php        # CRUD de usuários/condutores
└── models/
    ├── Company.php               # Modelo de empresa
    ├── Vehicle.php               # Modelo de veículo
    ├── Maintenance.php           # Modelo de manutenção
    └── FuelLog.php               # Modelo de abastecimento
```

### Controladores Principais

  - Configurações CORS automáticas
  - Autenticação Bearer Token
  - Respostas padronizadas (success/error)
  - Verificações de multi-tenancy
  - Content negotiation (JSON/XML)

  - `POST /api/auth/login` — Login com username/password
  - `GET /api/auth/me` — Informações do usuário autenticado
  - `POST /api/auth/refresh` — Renovação de token
  - `POST /api/auth/logout` — Logout
  - `GET /api/auth/info` — Informações da API

  - CRUD completo com filtragem por empresa
  - Endpoints personalizados para manutenções e abastecimentos
  - Estatísticas de consumo e custos

### Novos Módulos / Endpoints (resumo)

- **MaintenanceController** — CRUD de manutenções e endpoints de relatórios:
  - `GET /api/maintenance`, `POST /api/maintenance`, `PUT /api/maintenance/{id}`, `DELETE /api/maintenance/{id}`
  - `GET /api/maintenance/by-vehicle/{vehicle_id}`
  - `GET /api/maintenance/by-status/{estado}`
  - `POST /api/maintenance/{id}/schedule`
  - Relatórios: `GET /api/maintenance/reports/monthly`, `GET /api/maintenance/reports/costs`

- **FuelLogController** — Gestão de abastecimentos, estatísticas e relatórios de eficiência:
  - `GET /api/fuel-log`, `POST /api/fuel-log`, `PUT /api/fuel-log/{id}`
  - `GET /api/fuel-log/stats`, `GET /api/fuel-log/efficiency-report`, `GET /api/fuel-log/alerts`

- **CompanyController** — Endpoints avançados de empresa e estatísticas por empresa:
  - `GET /api/company/{id}/vehicles`, `GET /api/company/{id}/users`, `GET /api/company/{id}/stats`

- **DocumentController / FileController** — Upload, listagem e download de ficheiros/documentos com multi-tenancy.

Consulte `API_ENDPOINTS_COMPLETE.md` para a lista completa e exemplos de requests/response.

- **UserController** — Gestão de usuários:
  - CRUD com controle de permissões RBAC
  - Filtragem por empresa (multi-tenancy)
  - Perfil do usuário e gestão de condutores

As rotas REST são configuradas automaticamente em `backend/config/main.php`.

## 🚀 Endpoints Principais

### Autenticação
- `POST /api/auth/login` — Login de usuário
- `GET /api/auth/me` — Perfil do usuário autenticado  
- `POST /api/auth/refresh` — Renovar token
- `POST /api/auth/logout` — Logout
- `GET /api/auth/info` — Informações da API

### Veículos
- `GET /api/vehicles` — Listar veículos da empresa
- `POST /api/vehicles` — Criar novo veículo
- `GET /api/vehicles/{id}` — Detalhes do veículo
- `PUT /api/vehicles/{id}` — Atualizar veículo
- `DELETE /api/vehicles/{id}` — Deletar veículo
- `GET /api/vehicles/{id}/maintenances` — Manutenções do veículo
- `GET /api/vehicles/{id}/fuel-logs` — Abastecimentos do veículo
- `GET /api/vehicles/{id}/stats` — Estatísticas do veículo
- `GET /api/vehicles/by-status/{status}` — Filtrar por status

### Usuários
- `GET /api/users` — Listar usuários da empresa
- `POST /api/users` — Criar novo usuário
- `GET /api/users/{id}` — Detalhes do usuário
- `PUT /api/users/{id}` — Atualizar usuário
- `DELETE /api/users/{id}` — Deletar usuário
- `GET /api/users/drivers` — Listar apenas condutores
- `GET /api/users/profile` — Perfil completo do usuário
- `PUT /api/users/{id}/photo` — Atualizar foto do usuário

Todos os endpoints (exceto autenticação) requerem header: `Authorization: Bearer <access_token>`

## 🔐 Sistema de Autenticação

A API implementa um sistema robusto de autenticação Bearer Token com Base64 encoding:

### Fluxo de Autenticação
1. **Login**: Cliente envia `POST /api/auth/login` com `username` e `password`
2. **Geração de Token**: Sistema gera token Base64 contendo:
   - `user_id` — ID do usuário
   - `company_id` — ID da empresa (multi-tenancy)
   - `roles` — Papéis RBAC do usuário
   - `permissions` — Permissões específicas
   - `expires_at` — Timestamp de expiração (24h)
3. **Uso**: Cliente inclui `Authorization: Bearer <token>` em requisições
4. **Validação**: Sistema decodifica e valida o token em cada requisição

### Estrutura do Token (Base64)
```json
{
  "user_id": 123,
  "username": "admin",
  "company_id": 1,
  "company_code": "ACME001",
  "roles": ["manager", "user"],
  "permissions": ["manage_vehicles", "view_reports"],
  "expires_at": 1703123456,
  "issued_at": 1703037056
}
```

### Multi-tenancy e RBAC
- **Multi-tenancy**: Cada empresa tem acesso apenas aos seus dados
- **RBAC**: Controle granular de permissões por papel
- **Filtragem Automática**: Todos os recursos são filtrados por `company_id`

### Recursos de Segurança:
- **Tokens com Expiração**: 24 horas de validade
- **Validação de Estado**: Usuários inativos são rejeitados
- **CORS Configurado**: Headers de segurança automáticos  
- **HTTPS Recomendado**: Para ambientes de produção
- **Rate Limiting**: Configurável por controlador

## 📊 Formatos de Resposta

### Resposta de Sucesso
```json
{
  "success": true,
  "data": { ... },
  "message": "Operação realizada com sucesso",
  "timestamp": "2025-12-17T10:30:00Z"
}
```

### Resposta de Erro
```json
{
  "success": false,
  "message": "Descrição do erro",
  "errors": { ... },
  "timestamp": "2025-12-17T10:30:00Z"
}
```

## 🛠️ Configuração e Uso

### Requisitos
- PHP 7.4+
- Yii2 Framework
- Base de dados configurada
- Extensão JSON habilitada

### Configuração em main.php
```php
'modules' => [
    'api' => [
        'class' => 'backend\modules\api\Module',
    ],
],
'urlManager' => [
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    'rules' => [
        ['class' => 'yii\rest\UrlRule', 'controller' => 'api/auth'],
        ['class' => 'yii\rest\UrlRule', 'controller' => 'api/vehicle'],
        ['class' => 'yii\rest\UrlRule', 'controller' => 'api/user'],
    ],
],
```

## 🧪 Testes

A API inclui uma suite completa de testes JavaScript:
- Testes de autenticação
- Testes de CRUD para todos os recursos
- Validação de multi-tenancy
- Verificação de permissões RBAC

Execute os testes:
```bash
cd backend/modules/api/v1/api-tests
node run-all-tests.js
```

## 📚 Documentação Completa

Para documentação detalhada de cada endpoint, consulte:
- `API_ENDPOINTS.md` — Documentação completa da API
- `API_REQUIREMENTS_GUIDE.md` — Guia de desenvolvimento
- `api-tests/README.md` — Guia de testes

## 🔄 Versionamento

A API suporta versionamento através da URL:
- Versão atual: `/api/` (sem versão = v1)
- Futuras versões: `/api/v2/`, `/api/v3/`, etc.

## 📞 Suporte

Para dúvidas ou problemas:
1. Consulte a documentação completa
2. Execute os testes para validar a instalação  
3. Verifique os logs do Yii2 em `runtime/logs/`

---

**VeiGest API v1.0** — Sistema de Gestão de Veículos
- Em produção, não use `auth_key` simples sem expiração. Prefira JWTs (com assinatura e claims) ou uma tabela separada de tokens com `expires_at` e `revoked`.
- Limite origens permitidas no CORS em vez de liberar '*' globalmente.
- Use HTTPS sempre.

## Como modificar / estender a API

### Adicionar um novo controller em v1
1. Criar o arquivo `backend/modules/api/v1/controllers/NomeController.php` com namespace `backend\\modules\\api\\v1\\controllers`.
2. Estender `yii\\rest\\ActiveController` (ou `yii\\rest\\Controller` para endpoints customizados).
3. Se o endpoint deve exigir autenticação, adicione o comportamento de autenticação no método `behaviors()` do controller, por exemplo:

```php
public function behaviors()
{
    $behaviors = parent::behaviors();
    // CORS
    $behaviors['corsFilter'] = [ 'class' => \yii\filters\Cors::class ];
    // Bearer auth
    $behaviors['authenticator'] = [
        'class' => \yii\filters\auth\CompositeAuth::class,
        'authMethods' => [\yii\filters\auth\HttpBearerAuth::class],
    ];
    return $behaviors;
}
```

### Criar uma nova versão (v2)
1. Criar nova pasta `backend/modules/api/v2` e adicionar `Module.php` com `controllerNamespace = 'backend\\modules\\api\\v2\\controllers'`.
2. Criar controllers em `backend/modules/api/v2/controllers`.
3. Registrar o submódulo `v2` no `backend/config/main.php` sob `'modules' => ['api' => ['modules' => ['v2' => ['class' => 'backend\\\\modules\\\\api\\\\v2\\\\Module']]]]`.
4. Adicionar/atualizar regras `yii\\rest\\UrlRule` se quiser rotas diferentes.

### Alterar formato de resposta / comportamento global
- Para alterar behaviors globais (por ex. serialização, autenticação padrão), você pode editar os controllers base ou criar um componente/base controller comum que os controllers da API estendam.

## Exemplos de testes (PowerShell)

1) Login (gera token):

```powershell
$body = @{ username = 'seu_usuario'; password = 'sua_senha' } | ConvertTo-Json
Invoke-RestMethod -Method Post -Uri 'http://localhost:21080/api/v1/auth/login' -Body $body -ContentType 'application/json'
```

2) Acessar lista de usuários com token:

```powershell
$token = 'SEU_TOKEN_AQUI'
Invoke-RestMethod -Method Get -Uri 'http://localhost:21080/api/v1/user' -Headers @{ Authorization = "Bearer $token" }
```

Exemplo com curl:

```bash
curl -X POST http://localhost:21080/api/v1/auth/login -H "Content-Type: application/json" -d '{"username":"user","password":"pass"}'
curl -X GET http://localhost:21080/api/v1/user -H "Authorization: Bearer SEU_TOKEN"
```

## Dicas de debugging
- Verifique logs em `backend/runtime/logs` para erros de execução.
- Se não conseguir acessar rotas, confirme que o servidor está rodando e que o `urlManager` está corretamente configurado (e que o servidor permite reescrita de URL quando necessário).

## Próximos passos sugeridos
- Implementar expiração e revogação de tokens (tabela `access_tokens` ou JWT com expiração).
- Adicionar testes automatizados (Codeception) para os endpoints de autenticação e acesso protegido.
- Restringir CORS e adicionar rate limiting para endpoints sensíveis.
