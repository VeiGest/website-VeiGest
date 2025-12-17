# Guia de Requisitos para Desenvolvimento da API REST - VeiGest

## 1. Introdução e Fundamentos REST

### 1.1 Princípios Fundamentais
- **Arquitetura RESTful**: Representational State Transfer
- **Stateless**: Serviços não devem manter estado dos clientes
- **Interface Uniforme**: Uso padronizado dos verbos HTTP
- **Baseado em Recursos**: Cada URL representa um recurso específico
- **Multi-tenancy**: Isolamento de dados por empresa (company_id)
- **RBAC**: Controle de acesso baseado em papéis

### 1.2 Verbos HTTP e Operações CRUD

| Verbo HTTP | Operação CRUD | Descrição | Exemplo |
|------------|---------------|-----------|---------|
| **GET** | Read | Obter recurso(s) | `GET /api/v1/vehicles` |
| **POST** | Create | Criar novo recurso | `POST /api/v1/vehicles` |
| **PUT** | Update | Atualizar recurso completo | `PUT /api/v1/vehicles/123` |
| **DELETE** | Delete | Remover recurso | `DELETE /api/v1/vehicles/123` |

### 1.3 Códigos de Status HTTP Obrigatórios

| Código | Descrição | Quando Usar |
|--------|-----------|-------------|
| **200** | OK | Operação bem-sucedida |
| **201** | Created | Recurso criado com sucesso |
| **204** | No Content | Atualização/deleção sem retorno |
| **400** | Bad Request | Dados inválidos do cliente |
| **401** | Unauthorized | Autenticação necessária |
| **403** | Forbidden | Sem permissão para a operação |
| **404** | Not Found | Recurso não encontrado |
| **500** | Internal Server Error | Erro interno do servidor |

## 2. Estrutura da API no Projeto VeiGest

### 2.1 Organização em Módulos (Yii2) - IMPLEMENTADA ✅
```
backend/modules/api/
├── Module.php                    # Módulo principal da API
├── components/
│   └── ApiAuthenticator.php     # Autenticação Bearer Token personalizada
├── controllers/
│   ├── BaseApiController.php    # Controlador base com comportamentos comuns
│   ├── AuthController.php       # Endpoints de autenticação
│   ├── VehicleController.php    # CRUD de veículos com multi-tenancy
│   └── UserController.php       # CRUD de usuários com RBAC
└── models/
    ├── Company.php              # Modelo de empresa
    ├── Vehicle.php              # Modelo de veículo  
    ├── Maintenance.php          # Modelo de manutenção
    └── FuelLog.php              # Modelo de abastecimento
```

### 2.2 Configuração Base do Módulo API

#### Em `backend/modules/api/ModuleAPI.php`:
```php
public function init()
{
    parent::init();
    \Yii::$app->user->enableSession = false; // Desabilitar sessões
}
```

#### Em `backend/config/main.php`:
```php
'modules' => [
    'api' => [
        'class' => 'backend\modules\api\ModuleAPI',
    ],
],
'urlManager' => [
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    'rules' => [
        ['class' => 'yii\rest\UrlRule', 'controller' => 'api/vehicle'],
        ['class' => 'yii\rest\UrlRule', 'controller' => 'api/user'],
        ['class' => 'yii\rest\UrlRule', 'controller' => 'api/message'],
    ],
],
```

## 3. Padrões de Controladores REST

### 3.1 Estrutura Base do Controlador
```php
<?php
namespace backend\modules\api\controllers;

use yii\rest\ActiveController;

class VehicleController extends ActiveController
{
    public $modelClass = 'common\models\Vehicle';
    
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        // Configurações de autenticação aqui
        return $behaviors;
    }
    
    public function checkAccess($action, $model = null, $params = [])
    {
        // Lógica de autorização aqui
    }
}
```

### 3.2 URLs Padrão Geradas Automaticamente

| Método HTTP | URL | Ação | Descrição |
|-------------|-----|------|-----------|
| GET | `/api/vehicles` | index | Listar todos os veículos |
| GET | `/api/vehicles/123` | view | Visualizar veículo específico |
| POST | `/api/vehicles` | create | Criar novo veículo |
| PUT | `/api/vehicles/123` | update | Atualizar veículo |
| DELETE | `/api/vehicles/123` | delete | Deletar veículo |

## 4. Ações Personalizadas (Custom Actions)

### 4.1 Configuração de Rotas Customizadas
```php
'urlManager' => [
    'rules' => [
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => 'api/vehicle',
            'extraPatterns' => [
                'GET count' => 'count',
                'GET active' => 'active-vehicles',
                'GET {id}/maintenance' => 'maintenance-history',
                'POST {id}/schedule-maintenance' => 'schedule-maintenance',
            ],
            'tokens' => [
                '{id}' => '<id:\\d+>',
            ],
        ],
    ],
],
```

### 4.2 Implementação de Ações Customizadas
```php
public function actionCount()
{
    $model = new $this->modelClass;
    $count = $model::find()->count();
    return ['count' => $count];
}

public function actionActiveVehicles()
{
    $model = new $this->modelClass;
    $vehicles = $model::find()->where(['status' => 'active'])->all();
    return $vehicles;
}

public function actionMaintenanceHistory($id)
{
    $model = new $this->modelClass;
    $vehicle = $model::findOne($id);
    if (!$vehicle) {
        throw new \yii\web\NotFoundHttpException("Veículo não encontrado");
    }
    return $vehicle->maintenanceRecords;
}
```

## 5. Sistema de Autenticação

### 5.1 Autenticação HTTP Basic Auth
```php
use yii\filters\auth\HttpBasicAuth;

public function behaviors()
{
    $behaviors = parent::behaviors();
    $behaviors['authenticator'] = [
        'class' => HttpBasicAuth::className(),
        'except' => ['index', 'view'], // Excluir GETs públicos
        'auth' => [$this, 'auth']
    ];
    return $behaviors;
}

public function auth($username, $password)
{
    $user = \common\models\User::findByUsername($username);
    if ($user && $user->validatePassword($password)) {
        return $user;
    }
    throw new \yii\web\ForbiddenHttpException('Autenticação inválida');
}
```

### 5.2 Autenticação por Token (Query Parameter)
```php
use yii\filters\auth\QueryParamAuth;

public function behaviors()
{
    $behaviors = parent::behaviors();
    $behaviors['authenticator'] = [
        'class' => QueryParamAuth::className(),
    ];
    return $behaviors;
}
```

**Uso**: `GET /api/vehicles?access-token=TOKEN_AQUI`

## 6. Sistema de Autorização

### 6.1 Implementação do checkAccess
```php
public function checkAccess($action, $model = null, $params = [])
{
    if ($this->user) {
        // Verificar permissões baseadas no usuário
        if ($this->user->role === 'admin') {
            return; // Admin tem acesso total
        }
        
        if ($action === 'delete' && $this->user->role !== 'manager') {
            throw new \yii\web\ForbiddenHttpException('Sem permissão para deletar');
        }
        
        if ($action === 'update' && $model && $model->user_id !== $this->user->id) {
            throw new \yii\web\ForbiddenHttpException('Só pode editar próprios recursos');
        }
    }
}
```

## 7. Requisitos Específicos do Projeto - Sistema de Mensagens

### 7.1 API de Mensagens (Adenda do Projeto)
O sistema deve implementar as seguintes operações para mensagens:

#### Endpoints Obrigatórios:
```
POST /api/messages                    # Enviar mensagem
GET /api/messages/user/{user_id}/count # Contar mensagens do usuário
GET /api/messages/user/{user_id}       # Obter mensagens do usuário
PUT /api/messages/{id}/read           # Marcar mensagem como lida
```

#### Estrutura da Mensagem:
```json
{
    "id": 123,
    "recipient_id": 456,
    "sender_id": 1,
    "subject": "Assunto da mensagem",
    "body": "Corpo da mensagem",
    "read": false,
    "created_at": "2025-12-17T10:30:00Z"
}
```

### 7.2 Implementação do MessageController
```php
<?php
namespace backend\modules\api\controllers;

use yii\rest\ActiveController;

class MessageController extends ActiveController
{
    public $modelClass = 'common\models\Message';
    
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => HttpBasicAuth::className(),
            'auth' => [$this, 'auth']
        ];
        return $behaviors;
    }
    
    // Enviar mensagem
    public function actionCreate()
    {
        $model = new $this->modelClass;
        $model->load(\Yii::$app->request->post(), '');
        $model->sender_id = $this->user->id;
        $model->created_at = date('Y-m-d H:i:s');
        
        if ($model->save()) {
            \Yii::$app->response->statusCode = 201;
            return $model;
        }
        
        \Yii::$app->response->statusCode = 400;
        return $model->errors;
    }
    
    // Contar mensagens não lidas
    public function actionUserCount($user_id)
    {
        $count = $this->modelClass::find()
            ->where(['recipient_id' => $user_id, 'read' => false])
            ->count();
        return ['unread_count' => $count];
    }
    
    // Obter mensagens do usuário
    public function actionUserMessages($user_id)
    {
        if ($this->user->id != $user_id && $this->user->role !== 'admin') {
            throw new \yii\web\ForbiddenHttpException('Acesso negado');
        }
        
        return $this->modelClass::find()
            ->where(['recipient_id' => $user_id])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();
    }
}
```

## 8. Padrões de Resposta JSON

### 8.1 Sucesso (200/201)
```json
{
    "id": 123,
    "name": "Veículo XYZ",
    "status": "active",
    "created_at": "2025-12-17T10:30:00Z"
}
```

### 8.2 Lista de Recursos (200)
```json
[
    {
        "id": 123,
        "name": "Veículo A"
    },
    {
        "id": 124,
        "name": "Veículo B"
    }
]
```

### 8.3 Erro de Validação (400)
```json
{
    "name": "Validation Error",
    "message": "Dados inválidos",
    "code": 0,
    "status": 400,
    "errors": {
        "name": ["Nome é obrigatório"],
        "email": ["Email deve ser válido"]
    }
}
```

### 8.4 Erro de Autenticação (401)
```json
{
    "name": "Unauthorized",
    "message": "Token de acesso inválido",
    "code": 0,
    "status": 401
}
```

## 9. Testes da API

### 9.1 Usando cURL
```bash
# GET - Listar recursos
curl -X GET "http://localhost/veigest/backend/web/api/vehicles" \
     -H "Authorization: Basic base64(username:password)"

# POST - Criar recurso
curl -X POST "http://localhost/veigest/backend/web/api/vehicles" \
     -H "Content-Type: application/json" \
     -H "Authorization: Basic base64(username:password)" \
     -d '{"name":"Novo Veículo","plate":"ABC-1234"}'

# PUT - Atualizar recurso
curl -X PUT "http://localhost/veigest/backend/web/api/vehicles/123" \
     -H "Content-Type: application/json" \
     -H "Authorization: Basic base64(username:password)" \
     -d '{"name":"Veículo Atualizado"}'

# DELETE - Remover recurso
curl -X DELETE "http://localhost/veigest/backend/web/api/vehicles/123" \
     -H "Authorization: Basic base64(username:password)"
```

### 9.2 Usando JavaScript/AJAX
```javascript
// GET Request
$.ajax({
    type: "GET",
    url: "http://localhost/veigest/backend/web/api/vehicles",
    headers: {
        "Authorization": "Basic " + btoa("username:password")
    },
    dataType: "json",
    success: function(response) {
        console.log("Veículos:", response);
    },
    error: function(xhr, status, error) {
        console.error("Erro:", error);
    }
});

// POST Request
$.ajax({
    type: "POST",
    url: "http://localhost/veigest/backend/web/api/vehicles",
    headers: {
        "Authorization": "Basic " + btoa("username:password"),
        "Content-Type": "application/json"
    },
    data: JSON.stringify({
        "name": "Novo Veículo",
        "plate": "XYZ-5678"
    }),
    dataType: "json",
    success: function(response) {
        console.log("Veículo criado:", response);
    },
    error: function(xhr, status, error) {
        console.error("Erro:", error);
    }
});
```

## 10. Configurações de Segurança

### 10.1 CORS (Cross-Origin Resource Sharing)
```php
// Em backend/config/main.php
'components' => [
    'response' => [
        'on beforeSend' => function ($event) {
            $response = $event->sender;
            $response->headers->add('Access-Control-Allow-Origin', '*');
            $response->headers->add('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response->headers->add('Access-Control-Allow-Headers', 'Authorization, Content-Type');
        },
    ],
],
```

### 10.2 Rate Limiting
```php
use yii\filters\RateLimiter;

public function behaviors()
{
    $behaviors = parent::behaviors();
    $behaviors['rateLimiter'] = [
        'class' => RateLimiter::className(),
    ];
    return $behaviors;
}
```

## 11. Integração com MQTT (Sistema de Mensagens)

### 11.1 Configuração do Mosquitto MQTT
```bash
# Instalação no Ubuntu
sudo apt update
sudo apt-get install mosquitto mosquitto-clients

# Configurar autenticação
sudo mosquitto_passwd -c /etc/mosquitto/passwd username

# Arquivo de configuração
# /etc/mosquitto/conf.d/default.conf
allow_anonymous false
password_file /etc/mosquitto/passwd
```

### 11.2 Integração com PHP (Notificações Push)
```php
// Publicar mensagem via MQTT quando nova mensagem é criada
public function afterSave($insert, $changedAttributes)
{
    parent::afterSave($insert, $changedAttributes);
    
    if ($insert) {
        // Publicar notificação MQTT
        $topic = "notifications/user/{$this->recipient_id}";
        $message = json_encode([
            'type' => 'new_message',
            'message_id' => $this->id,
            'subject' => $this->subject
        ]);
        
        // Implementar client MQTT aqui
        $this->publishMqttMessage($topic, $message);
    }
}
```

## 12. Checklist de Desenvolvimento

### 12.1 Para Cada Controlador REST
- [ ] Estende `ActiveController`
- [ ] Define `$modelClass` corretamente
- [ ] Implementa autenticação via `behaviors()`
- [ ] Implementa autorização via `checkAccess()`
- [ ] Trata erros com exceções HTTP apropriadas
- [ ] Documenta ações personalizadas

### 12.2 Para Cada Endpoint
- [ ] Usa verbo HTTP correto
- [ ] Retorna código de status apropriado
- [ ] Valida dados de entrada
- [ ] Trata casos de erro
- [ ] Documenta parâmetros e resposta

### 12.3 Testes Obrigatórios
- [ ] Teste de autenticação válida/inválida
- [ ] Teste de autorização (diferentes roles)
- [ ] Teste de CRUD completo
- [ ] Teste de validação de dados
- [ ] Teste de casos de erro (404, 400, 500)

## 13. Documentação da API

### 13.1 Estrutura de Documentação
Para cada endpoint, documentar:
- **URL**: Endpoint completo
- **Método**: Verbo HTTP
- **Parâmetros**: Query params, path params, body
- **Headers**: Autenticação, content-type
- **Resposta**: Exemplo de sucesso e erro
- **Códigos de Status**: Possíveis retornos

### 13.2 Exemplo de Documentação
```markdown
## GET /api/vehicles/{id}

Obtém detalhes de um veículo específico.

**Parâmetros:**
- `id` (integer, required): ID do veículo

**Headers:**
- `Authorization`: Basic base64(username:password)

**Resposta de Sucesso (200):**
```json
{
    "id": 123,
    "name": "Veículo ABC",
    "plate": "ABC-1234",
    "status": "active"
}
```

**Resposta de Erro (404):**
```json
{
    "name": "Not Found",
    "message": "Veículo não encontrado"
}
```

---

Este guia deve ser seguido rigorosamente para manter consistência e qualidade na API REST do projeto VeiGest.
