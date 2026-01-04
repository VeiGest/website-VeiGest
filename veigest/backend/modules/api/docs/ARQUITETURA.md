# 🏗️ Arquitetura da API VeiGest

## 📋 Visão Geral da Arquitetura

A API VeiGest segue uma arquitetura RESTful moderna baseada em Yii2, implementando princípios de multi-tenancy, RBAC (Role-Based Access Control) e autenticação stateless com tokens Bearer.

## 🗂️ Estrutura de Diretórios

```
backend/modules/api/
├── Module.php                     # 🎯 Módulo principal da API
├── components/
│   └── ApiAuthenticator.php      # 🔐 Autenticação Bearer Token
├── controllers/
│   ├── BaseApiController.php     # 🏗️ Controlador base com comportamentos comuns
│   ├── AuthController.php        # 🔑 Endpoints de autenticação
│   ├── VehicleController.php     # 🚗 CRUD de veículos
│   └── UserController.php        # 👥 CRUD de usuários
├── models/
│   ├── Company.php              # 🏢 Modelo de empresa
│   ├── Vehicle.php              # 🚗 Modelo de veículo
│   ├── User.php                 # 👤 Modelo de usuário (herdado)
│   ├── FuelLog.php              # ⛽ Modelo de abastecimento
│   └── Maintenance.php          # 🔧 Modelo de manutenção
├── docs/                        # 📚 Esta documentação
└── tests/                       # 🧪 Scripts de teste JavaScript
```

## 🎯 Componentes Principais

### 1. Module.php - Núcleo da API

**Responsabilidades:**
- Configuração global da API
- Setup de CORS automático
- Configuração de resposta JSON
- Desabilitação de sessões (stateless)

**Código Principal:**
```php
public function init()
{
    parent::init();

    // Configurações globais da API
    \Yii::$app->response->format = Response::FORMAT_JSON;
    \Yii::$app->user->enableSession = false;
    \Yii::$app->user->loginUrl = null;

    // CORS global
    \Yii::$app->response->on(\yii\web\Response::EVENT_BEFORE_SEND, function ($event) {
        $response = $event->sender;
        $response->headers->add('Access-Control-Allow-Origin', '*');
        // ... outros headers CORS
    });
}
```

### 2. ApiAuthenticator - Autenticação Bearer Token

**Fluxo de Autenticação:**
1. Recebe token no header `Authorization: Bearer {token}`
2. Decodifica token Base64
3. Valida expiração
4. Busca usuário no banco
5. Verifica status ativo
6. Retorna identidade do usuário

**Estrutura do Token:**
```json
{
  "user_id": 1,
  "username": "admin",
  "company_id": 1,
  "company_code": 1765993803275,
  "roles": ["admin"],
  "permissions": ["vehicles.create", "users.view", ...],
  "expires_at": 1766090144,
  "issued_at": 1766003744
}
```

### 3. BaseApiController - Fundamento dos Controllers

**Funcionalidades:**
- Configurações CORS específicas
- Content negotiation (JSON)
- Verificações de multi-tenancy
- Métodos utilitários (`checkAccess`, `getCompanyId`, etc.)

**Método checkAccess():**
```php
public function checkAccess($action, $model = null, $params = [])
{
    // Verificações básicas de multi-tenancy
    if ($model && method_exists($model, 'hasAttribute') && $model->hasAttribute('company_id')) {
        if ($model->company_id != $this->getCompanyId()) {
            throw new ForbiddenHttpException('Acesso negado: empresa diferente');
        }
    }
}
```

## 🔄 Fluxo de Requisição Completo

### 1. Recebimento da Requisição
```
Cliente → Nginx/Apache → Yii2 Application → Module API
```

### 2. Autenticação
```
Module API → ApiAuthenticator → Token Validation → User Identity
```

### 3. Autorização
```
BaseApiController → checkAccess() → RBAC Check → Company Filter
```

### 4. Processamento
```
Controller Específico → Model → Database → Response
```

### 5. Resposta
```
JSON Response ← CORS Headers ← Error Handling
```

## 🏢 Multi-tenancy Implementation

### Isolamento por Empresa
- **Token contém:** `company_id` e `company_code`
- **Filtros automáticos:** Todas as queries incluem `WHERE company_id = :company_id`
- **Validação:** Acesso negado se tentar acessar dados de outra empresa

### Exemplo de Filtro Automático:
```php
public function actionIndex()
{
    $companyId = $this->getCompanyId();

    $query = Vehicle::find()
        ->where(['company_id' => $companyId]) // Filtro automático
        ->andFilterWhere(['like', 'license_plate', $this->request->get('search')]);

    return new ActiveDataProvider([
        'query' => $query,
        'pagination' => ['pageSize' => 20],
    ]);
}
```

## 🔐 Sistema RBAC (Role-Based Access Control)

### Estrutura de Permissões
```
Admin: vehicles.*, users.*, companies.*, system.*
Manager: vehicles.create|update|view, users.view, reports.*
Driver: vehicles.view, fuel.create, profile.update
```

### Verificação de Permissões
```php
private function hasPermission($permission)
{
    $tokenData = Yii::$app->params['token_data'];
    return in_array($permission, $tokenData['permissions'] ?? []);
}
```

## 📊 Models e Relacionamentos

### Company Model
```php
class Company extends ActiveRecord
{
    // Relacionamentos
    public function getVehicles() {
        return $this->hasMany(Vehicle::class, ['company_id' => 'id']);
    }

    public function getUsers() {
        return $this->hasMany(User::class, ['company_id' => 'id']);
    }
}
```

### Vehicle Model
```php
class Vehicle extends ActiveRecord
{
    // Relacionamentos
    public function getCompany() {
        return $this->hasOne(Company::class, ['id' => 'company_id']);
    }

    public function getDriver() {
        return $this->hasOne(User::class, ['id' => 'driver_id']);
    }

    public function getMaintenances() {
        return $this->hasMany(Maintenance::class, ['vehicle_id' => 'id']);
    }
}
```

## 🚀 Escalabilidade e Performance

### Otimizações Implementadas
1. **Stateless Authentication** - Não usa sessões do servidor
2. **Database Indexing** - Índices otimizados para queries frequentes
3. **Lazy Loading** - Relacionamentos carregados sob demanda
4. **Pagination** - Resultados paginados para listas grandes
5. **CORS Global** - Configurado uma vez no Module

### Possíveis Melhorias Futuras
- **Redis Cache** para tokens e dados frequentes
- **Database Sharding** para empresas muito grandes
- **API Rate Limiting** para controle de uso
- **GraphQL** para queries mais flexíveis

## 🔧 Configuração do Ambiente

### Docker Compose Structure
```yaml
services:
  backend:
    build: backend
    ports: ["21080:80"]
    environment:
      - DB_HOST=db
      - DB_NAME=veigest_db
    depends_on: [db]

  db:
    image: mysql:5.7
    environment:
      MYSQL_DATABASE: veigest_db
      MYSQL_USER: veigest_user
      MYSQL_PASSWORD: secret
```

### Yii2 Configuration
```php
// backend/config/main.php
'modules' => [
    'api' => [
        'class' => 'backend\modules\api\Module',
    ],
],
'urlManager' => [
    'enablePrettyUrl' => true,
    'rules' => [
        ['class' => 'yii\\rest\\UrlRule', 'controller' => ['api/vehicle']],
        ['class' => 'yii\\rest\\UrlRule', 'controller' => ['api/user']],
    ],
],
```

## 🎯 Princípios de Design

### SOLID Principles
- **Single Responsibility:** Cada controller tem uma responsabilidade clara
- **Open/Closed:** Extensível através de herança (BaseApiController)
- **Liskov Substitution:** Models compatíveis com ActiveRecord padrão
- **Interface Segregation:** Interfaces específicas por funcionalidade
- **Dependency Inversion:** Injeção de dependências via Yii2 DI

### RESTful Principles
- **Resource-Based URLs:** `/api/vehicles`, `/api/users`
- **HTTP Methods:** GET, POST, PUT, DELETE apropriadamente
- **Stateless:** Não mantém estado entre requisições
- **Content Negotiation:** JSON por padrão
- **HATEOAS:** Links para navegação (futuro)

### Security Principles
- **Defense in Depth:** Múltiplas camadas de segurança
- **Least Privilege:** Permissões mínimas necessárias
- **Fail-Safe Defaults:** Acesso negado por padrão
- **Input Validation:** Validação rigorosa de dados
- **Audit Logging:** Logs de todas as operações (futuro)

---

**Próximo:** [ESTRUTURA_CODIGO.md](ESTRUTURA_CODIGO.md) - Detalhes de implementação de cada arquivo
