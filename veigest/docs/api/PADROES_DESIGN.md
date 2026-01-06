# 🎨 Padrões de Design Implementados

## 📋 Visão Geral dos Padrões

A API VeiGest implementa vários padrões de design para garantir código limpo, manutenível e escalável. Aqui estão os principais padrões utilizados:

## 🏗️ 1. MVC (Model-View-Controller) Adaptado

### Estrutura Implementada
```
Models/          # 📊 Camada de Dados
├── Vehicle.php
├── User.php
├── Company.php
└── ...

Views/           # 🎨 Não usado (API retorna JSON)
└── (empty)

Controllers/     # 🎯 Camada de Controle
├── BaseApiController.php
├── AuthController.php
├── VehicleController.php
└── UserController.php
```

### Adaptação para API REST
- **Views eliminadas:** APIs retornam dados estruturados (JSON), não HTML
- **Controllers especializados:** Cada controller gerencia um recurso específico
- **Models ricos:** Contêm lógica de negócio e relacionamentos

## 🔗 2. Template Method Pattern

### Implementação no BaseApiController

```php
abstract class BaseApiController extends ActiveController
{
    // 🏗️ TEMPLATE METHOD - Estrutura fixa
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // 1. CORS sempre aplicado
        $behaviors['corsFilter'] = $this->getCorsConfig();

        // 2. Content negotiation sempre JSON
        $behaviors['contentNegotiator'] = $this->getContentNegotiatorConfig();

        // 3. Autenticação customizável
        $behaviors['authenticator'] = $this->getAuthenticatorConfig();

        return $behaviors;
    }

    // 🎯 HOOK METHODS - Customizáveis pelas subclasses
    protected function getCorsConfig() { /* ... */ }
    protected function getContentNegotiatorConfig() { /* ... */ }
    protected function getAuthenticatorConfig() { /* ... */ }
}
```

### Controllers Concretos
```php
class VehicleController extends BaseApiController
{
    // Customiza apenas o necessário
    protected function getAuthenticatorConfig()
    {
        return [
            'class' => ApiAuthenticator::class,
            'except' => ['options', 'public-endpoints'],
        ];
    }
}
```

## 🏭 3. Factory Method Pattern

### Geração de Tokens

```php
class AuthController extends BaseApiController
{
    // 🏭 FACTORY METHOD - Cria tokens de diferentes tipos
    private function generateToken($user, $company = null)
    {
        $issuedAt = time();
        $expiresAt = $issuedAt + 86400; // 24 horas

        return [
            'user_id' => $user->id,
            'username' => $user->username,
            'company_id' => $user->company_id,
            'company_code' => $company ? $company->code : null,
            'roles' => $this->getUserRoles($user->id),        // 🎯 Polimorfismo
            'permissions' => $this->getUserPermissions($user->id), // 🎯 Polimorfismo
            'expires_at' => $expiresAt,
            'issued_at' => $issuedAt,
        ];
    }

    // 📤 FACTORY METHOD - Cria diferentes tipos de resposta
    private function createAuthResponse($user, $token, $company = null)
    {
        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => 86400,
            'user' => $this->createUserData($user),          // 🏭 Sub-factory
            'company' => $company ? $this->createCompanyData($company) : null, // 🏭 Sub-factory
            'roles' => $this->getUserRoles($user->id),
            'permissions' => $this->getUserPermissions($user->id),
        ];
    }
}
```

## 🎭 4. Strategy Pattern

### Autenticação Customizável

```php
// 🎭 STRATEGY INTERFACE
interface AuthenticationStrategy
{
    public function authenticate($user, $request, $response);
}

// 🎭 CONCRETE STRATEGY - Bearer Token
class ApiAuthenticator extends \yii\filters\auth\AuthMethod implements AuthenticationStrategy
{
    public function authenticate($user, $request, $response)
    {
        // Estratégia específica para Bearer Token
        $authHeader = $request->getHeaders()->get('Authorization');

        if (!$authHeader || !preg_match('/^Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return null;
        }

        return $this->processBearerToken($matches[1]);
    }
}

// 🎭 CONCRETE STRATEGY - API Key (futuro)
class ApiKeyAuthenticator extends \yii\filters\auth\AuthMethod implements AuthenticationStrategy
{
    public function authenticate($user, $request, $response)
    {
        // Estratégia específica para API Key
        $apiKey = $request->getHeaders()->get('X-API-Key');
        return $this->processApiKey($apiKey);
    }
}
```

### Uso no Controller
```php
class SomeController extends BaseApiController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // 🔄 ESTRATÉGIA PLUGÁVEL
        $behaviors['authenticator'] = [
            'class' => ApiAuthenticator::class, // Ou ApiKeyAuthenticator::class
        ];

        return $behaviors;
    }
}
```

## 🔄 5. Decorator Pattern

### Enriquecimento de Respostas

```php
// 🔄 DECORATOR BASE
class ResponseDecorator
{
    protected $response;

    public function __construct($response)
    {
        $this->response = $response;
    }

    public function getDecoratedResponse()
    {
        return $this->response;
    }
}

// 🔄 CONCRETE DECORATOR - Adiciona timestamps
class TimestampDecorator extends ResponseDecorator
{
    public function getDecoratedResponse()
    {
        $response = parent::getDecoratedResponse();
        $response['timestamp'] = time();
        return $response;
    }
}

// 🔄 CONCRETE DECORATOR - Adiciona metadados de paginação
class PaginationDecorator extends ResponseDecorator
{
    public function getDecoratedResponse()
    {
        $response = parent::getDecoratedResponse();

        if (isset($response['_meta'])) {
            $response['_meta']['processed_at'] = date('c');
            $response['_meta']['api_version'] = '1.0';
        }

        return $response;
    }
}
```

### Uso nos Controllers
```php
class VehicleController extends BaseApiController
{
    public function actionIndex()
    {
        $dataProvider = $this->getDataProvider();

        // 🔄 APLICA DECORATORS
        $decorator = new PaginationDecorator(
            new TimestampDecorator($dataProvider)
        );

        return $decorator->getDecoratedResponse();
    }
}
```

## 🎯 6. Command Pattern

### Processamento de Ações

```php
// 🎯 COMMAND INTERFACE
interface ApiCommand
{
    public function execute();
    public function undo();
}

// 🎯 CONCRETE COMMAND - Criar Veículo
class CreateVehicleCommand implements ApiCommand
{
    private $vehicleData;
    private $companyId;

    public function __construct($vehicleData, $companyId)
    {
        $this->vehicleData = $vehicleData;
        $this->companyId = $companyId;
    }

    public function execute()
    {
        $vehicle = new Vehicle();
        $vehicle->attributes = $this->vehicleData;
        $vehicle->company_id = $this->companyId;

        if ($vehicle->save()) {
            $this->vehicleId = $vehicle->id;
            return $vehicle;
        }

        throw new \Exception('Erro ao criar veículo');
    }

    public function undo()
    {
        if ($this->vehicleId) {
            Vehicle::findOne($this->vehicleId)->delete();
        }
    }
}

// 🎯 COMMAND INVOKER
class VehicleController extends BaseApiController
{
    public function actionCreate()
    {
        $command = new CreateVehicleCommand(
            Yii::$app->request->bodyParams,
            $this->getCompanyId()
        );

        try {
            $vehicle = $command->execute();
            return $this->successResponse($vehicle, 'Veículo criado com sucesso', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }
}
```

## 🏢 7. Repository Pattern

### Abstração da Camada de Dados

```php
// 🏢 REPOSITORY INTERFACE
interface VehicleRepositoryInterface
{
    public function findById($id);
    public function findByCompany($companyId, $filters = []);
    public function save(Vehicle $vehicle);
    public function delete($id);
}

// 🏢 CONCRETE REPOSITORY
class VehicleRepository implements VehicleRepositoryInterface
{
    public function findById($id)
    {
        return Vehicle::findOne($id);
    }

    public function findByCompany($companyId, $filters = [])
    {
        $query = Vehicle::find()->where(['company_id' => $companyId]);

        if (isset($filters['status'])) {
            $query->andWhere(['status' => $filters['status']]);
        }

        if (isset($filters['search'])) {
            $query->andWhere([
                'or',
                ['like', 'license_plate', $filters['search']],
                ['like', 'brand', $filters['search']],
            ]);
        }

        return $query->all();
    }

    public function save(Vehicle $vehicle)
    {
        return $vehicle->save();
    }

    public function delete($id)
    {
        $vehicle = $this->findById($id);
        return $vehicle ? $vehicle->delete() : false;
    }
}
```

### Uso no Controller
```php
class VehicleController extends BaseApiController
{
    private $vehicleRepository;

    public function __construct($id, $module, VehicleRepository $repository, $config = [])
    {
        $this->vehicleRepository = $repository;
        parent::__construct($id, $module, $config);
    }

    public function actionIndex()
    {
        $filters = Yii::$app->request->queryParams;
        $vehicles = $this->vehicleRepository->findByCompany(
            $this->getCompanyId(),
            $filters
        );

        return $vehicles;
    }
}
```

## 🔗 8. Observer Pattern

### Logging e Auditoria

```php
// 🔗 SUBJECT (Observable)
class Vehicle extends ActiveRecord
{
    private $observers = [];

    public function attach(Observer $observer)
    {
        $this->observers[] = $observer;
    }

    public function detach(Observer $observer)
    {
        $this->observers = array_filter($this->observers, function($obs) use ($observer) {
            return $obs !== $observer;
        });
    }

    protected function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        // 🔗 NOTIFICA OBSERVERS
        foreach ($this->observers as $observer) {
            $observer->update($this, $insert ? 'created' : 'updated', $changedAttributes);
        }
    }
}

// 🔗 OBSERVER INTERFACE
interface Observer
{
    public function update($subject, $event, $data = null);
}

// 🔗 CONCRETE OBSERVER - Logger
class VehicleAuditLogger implements Observer
{
    public function update($subject, $event, $data = null)
    {
        $logData = [
            'vehicle_id' => $subject->id,
            'event' => $event,
            'user_id' => Yii::$app->params['user_id'] ?? null,
            'company_id' => Yii::$app->params['company_id'] ?? null,
            'changes' => $data,
            'timestamp' => time(),
        ];

        // 💾 SALVA LOG DE AUDITORIA
        $this->saveAuditLog($logData);
    }
}
```

### Configuração Global
```php
// Em Module.php ou bootstrap
$vehicleLogger = new VehicleAuditLogger();
Vehicle::attach($vehicleLogger);
```

## 🛡️ 9. Chain of Responsibility Pattern

### Validações em Sequência

```php
// 🛡️ HANDLER BASE
abstract class ValidationHandler
{
    protected $nextHandler;

    public function setNext(ValidationHandler $handler)
    {
        $this->nextHandler = $handler;
        return $handler; // Permite encadeamento fluido
    }

    public function handle($data)
    {
        if ($this->canHandle($data)) {
            $result = $this->process($data);

            if ($result !== null) {
                return $result; // Interrompe a cadeia se processado
            }
        }

        // 🔗 PASSA PARA O PRÓXIMO HANDLER
        if ($this->nextHandler) {
            return $this->nextHandler->handle($data);
        }

        return null; // Fim da cadeia
    }

    abstract protected function canHandle($data);
    abstract protected function process($data);
}

// 🛡️ CONCRETE HANDLER - Validação de Empresa
class CompanyValidationHandler extends ValidationHandler
{
    protected function canHandle($data)
    {
        return isset($data['company_id']);
    }

    protected function process($data)
    {
        $company = Company::findOne($data['company_id']);

        if (!$company) {
            return ['error' => 'Empresa não encontrada', 'code' => 404];
        }

        if ($company->status !== 'active') {
            return ['error' => 'Empresa inativa', 'code' => 403];
        }

        return null; // Continua para próximo handler
    }
}

// 🛡️ CONCRETE HANDLER - Validação de Usuário
class UserValidationHandler extends ValidationHandler
{
    protected function canHandle($data)
    {
        return isset($data['user_id']);
    }

    protected function process($data)
    {
        $user = User::findOne($data['user_id']);

        if (!$user) {
            return ['error' => 'Usuário não encontrado', 'code' => 404];
        }

        if ($user->estado !== 'ativo') {
            return ['error' => 'Usuário inativo', 'code' => 403];
        }

        return null;
    }
}
```

### Uso na API
```php
class AuthController extends BaseApiController
{
    private $validationChain;

    public function init()
    {
        // 🛡️ CONFIGURA CADEIA DE VALIDAÇÃO
        $this->validationChain = new CompanyValidationHandler();
        $this->validationChain
            ->setNext(new UserValidationHandler())
            ->setNext(new PermissionValidationHandler());
    }

    public function actionLogin()
    {
        $credentials = Yii::$app->request->bodyParams;

        // 🛡️ EXECUTA CADEIA DE VALIDAÇÃO
        $validationResult = $this->validationChain->handle($credentials);

        if ($validationResult) {
            return $this->errorResponse(
                $validationResult['error'],
                $validationResult['code']
            );
        }

        // Continua com login normal...
    }
}
```

## 📊 10. Data Transfer Object (DTO) Pattern

### Estruturação de Dados de Resposta

```php
// 📊 DTO BASE
class ApiResponseDTO
{
    public $success;
    public $message;
    public $data;
    public $errors;
    public $timestamp;

    public function __construct($success = true, $message = null, $data = null, $errors = [])
    {
        $this->success = $success;
        $this->message = $message;
        $this->data = $data;
        $this->errors = $errors;
        $this->timestamp = time();
    }

    public function toArray()
    {
        return array_filter([
            'success' => $this->success,
            'message' => $this->message,
            'data' => $this->data,
            'errors' => $this->errors,
            'timestamp' => $this->timestamp,
        ], function($value) {
            return $value !== null && $value !== [];
        });
    }
}

// 📊 DTO ESPECÍFICO PARA USUÁRIO
class UserDTO extends ApiResponseDTO
{
    public function __construct($user, $includeSensitive = false)
    {
        $userData = [
            'id' => $user->id,
            'username' => $user->username,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'status' => $user->estado,
            'company_id' => $user->company_id,
        ];

        // 🔒 REMOVE DADOS SENSÍVEIS se necessário
        if (!$includeSensitive) {
            unset($userData['password_hash']);
            unset($userData['auth_key']);
        }

        parent::__construct(true, 'Usuário encontrado', $userData);
    }
}

// 📊 DTO PARA LISTA PAGINADA
class PaginatedResponseDTO extends ApiResponseDTO
{
    public $pagination;

    public function __construct($data, $paginationInfo)
    {
        parent::__construct(true, null, $data);
        $this->pagination = $paginationInfo;
    }

    public function toArray()
    {
        $array = parent::toArray();
        $array['pagination'] = $this->pagination;
        return $array;
    }
}
```

### Uso nos Controllers
```php
class UserController extends BaseApiController
{
    public function actionView($id)
    {
        $user = $this->findModel($id);
        $dto = new UserDTO($user);

        return $dto->toArray();
    }

    public function actionIndex()
    {
        $dataProvider = $this->getDataProvider();

        $dto = new PaginatedResponseDTO(
            $dataProvider->getModels(),
            [
                'total' => $dataProvider->getTotalCount(),
                'page' => $dataProvider->getPagination()->getPage() + 1,
                'per_page' => $dataProvider->getPagination()->getPageSize(),
            ]
        );

        return $dto->toArray();
    }
}
```

## 🎯 Resumo dos Padrões Implementados

| Padrão | Onde Usado | Benefício |
|--------|------------|-----------|
| **Template Method** | BaseApiController | Estrutura consistente, customização pontual |
| **Factory Method** | AuthController (tokens) | Criação flexível de objetos complexos |
| **Strategy** | ApiAuthenticator | Autenticação plugável |
| **Decorator** | Respostas da API | Enriquecimento progressivo de dados |
| **Command** | Operações CRUD | Desfazer ações, logging |
| **Repository** | Models | Abstração da camada de dados |
| **Observer** | Models (auditoria) | Logging automático de mudanças |
| **Chain of Resp.** | Validações | Sequência flexível de validações |
| **DTO** | Respostas da API | Estrutura consistente de dados |

---

**Próximo:** [CONFIGURACAO_AMBIENTE.md](CONFIGURACAO_AMBIENTE.md) - Como configurar o ambiente de desenvolvimento
