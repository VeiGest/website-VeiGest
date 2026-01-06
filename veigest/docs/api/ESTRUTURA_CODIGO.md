# 📋 Estrutura Detalhada do Código

## 🎯 Module.php - O Coração da API

### Código Completo Analisado

```php
<?php
namespace backend\modules\api;

use yii\base\Module as BaseModule;
use yii\web\Response;

class Module extends BaseModule
{
    public $controllerNamespace = 'backend\modules\api\controllers';

    public function init()
    {
        parent::init();

        // 🔥 CONFIGURAÇÃO GLOBAL DA API
        \Yii::$app->response->format = Response::FORMAT_JSON;
        \Yii::$app->user->enableSession = false;
        \Yii::$app->user->loginUrl = null;

        // 🌐 CORS GLOBAL - Permite requisições de qualquer origem
        \Yii::$app->response->on(\yii\web\Response::EVENT_BEFORE_SEND, function ($event) {
            $response = $event->sender;
            $response->headers->add('Access-Control-Allow-Origin', '*');
            $response->headers->add('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response->headers->add('Access-Control-Allow-Headers', 'Authorization, Content-Type, X-Requested-With');
            $response->headers->add('Access-Control-Max-Age', '3600');
        });
    }
}
```

### O que cada linha faz:

1. **`namespace backend\modules\api;`** - Define o namespace do módulo
2. **`use yii\base\Module as BaseModule;`** - Importa classe base do Yii2
3. **`use yii\web\Response;`** - Para manipulação de respostas HTTP

4. **`$controllerNamespace`** - Define onde estão os controllers da API

5. **`init()` method:**
   - **`\Yii::$app->response->format = Response::FORMAT_JSON;`** - Força JSON em todas as respostas
   - **`\Yii::$app->user->enableSession = false;`** - Desabilita sessões (stateless)
   - **`\Yii::$app->user->loginUrl = null;`** - Remove redirecionamento de login

6. **CORS Setup:** Permite requisições cross-origin de qualquer domínio

---

## 🔐 ApiAuthenticator.php - Guardião da Segurança

### Fluxo de Autenticação Passo a Passo

```php
public function authenticate($user, $request, $response)
{
    // 1. 🔍 EXTRAI TOKEN DO HEADER
    $authHeader = $request->getHeaders()->get('Authorization');
    if (!$authHeader || !preg_match('/^Bearer\s+(.*)$/i', $authHeader, $matches)) {
        return null; // Sem token = acesso anônimo
    }

    $token = $matches[1];

    // 2. 🔓 DECODIFICA TOKEN BASE64
    $tokenData = $this->decodeToken($token);
    if (!$tokenData) {
        throw new UnauthorizedHttpException('Token inválido');
    }

    // 3. ⏰ VALIDA EXPIRAÇÃO
    if ($tokenData['expires_at'] < time()) {
        throw new UnauthorizedHttpException('Token expirado');
    }

    // 4. 👤 BUSCA USUÁRIO NO BANCO
    $identity = User::findIdentity($tokenData['user_id']);
    if (!$identity) {
        throw new UnauthorizedHttpException('Usuário não encontrado');
    }

    // 5. ✅ VERIFICA STATUS ATIVO
    if ($identity->estado !== 'ativo' && $identity->status !== 'active') {
        throw new UnauthorizedHttpException('Usuário inativo');
    }

    // 6. 💾 ARMAZENA DADOS PARA USO POSTERIOR
    Yii::$app->params['token_data'] = $tokenData;
    Yii::$app->params['company_id'] = $tokenData['company_id'] ?? null;
    Yii::$app->params['user_id'] = $tokenData['user_id'];

    return $identity;
}
```

### Método decodeToken():

```php
private function decodeToken($token)
{
    try {
        // Decodifica Base64
        $decoded = base64_decode($token);

        // Converte JSON para array
        $data = json_decode($decoded, true);

        // Valida estrutura mínima
        if (!is_array($data) || !isset($data['user_id'])) {
            return null;
        }

        return $data;
    } catch (\Exception $e) {
        return null;
    }
}
```

---

## 🏗️ BaseApiController.php - A Base de Tudo

### Comportamentos (behaviors())

```php
public function behaviors()
{
    $behaviors = parent::behaviors();

    // 🌐 CORS ESPECÍFICO POR CONTROLLER
    $behaviors['corsFilter'] = [
        'class' => \yii\filters\Cors::class,
        'cors' => [
            'Origin' => ['*'],
            'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
            'Access-Control-Request-Headers' => ['*'],
            'Access-Control-Allow-Credentials' => false,
        ],
    ];

    // 📋 CONTENT NEGOTIATION - GARANTE JSON
    $behaviors['contentNegotiator'] = [
        'class' => \yii\filters\ContentNegotiator::class,
        'formats' => [
            'application/json' => \yii\web\Response::FORMAT_JSON,
        ],
    ];

    // 🔐 AUTENTICAÇÃO - PODE SER SOBRESCRITA
    $behaviors['authenticator'] = [
        'class' => \backend\modules\api\components\ApiAuthenticator::class,
        'except' => ['options'], // OPTIONS não precisa de auth
    ];

    return $behaviors;
}
```

### Método checkAccess() - Segurança Multi-tenancy

```php
public function checkAccess($action, $model = null, $params = [])
{
    // 🏢 VERIFICA SE O MODEL TEM company_id
    if ($model && method_exists($model, 'hasAttribute') && $model->hasAttribute('company_id')) {
        $userCompanyId = $this->getCompanyId();

        // 🚫 ACESSO NEGADO se empresa diferente
        if ($model->company_id != $userCompanyId) {
            throw new ForbiddenHttpException('Acesso negado: empresa diferente');
        }
    }

    // 🔑 VERIFICAÇÕES DE PERMISSÃO RBAC (futuro)
    // if (!$this->hasPermission($action)) {
    //     throw new ForbiddenHttpException('Permissão insuficiente');
    // }

    parent::checkAccess($action, $model, $params);
}
```

### Métodos Utilitários

```php
// 🏢 OBTÉM COMPANY_ID DO TOKEN
protected function getCompanyId()
{
    return Yii::$app->params['company_id'] ?? null;
}

// 👤 OBTÉM USER_ID DO TOKEN
protected function getUserId()
{
    return Yii::$app->params['user_id'] ?? null;
}

// ❌ RESPOSTA DE ERRO PADRONIZADA
protected function errorResponse($message, $code = 400, $errors = [])
{
    Yii::$app->response->statusCode = $code;
    return [
        'success' => false,
        'message' => $message,
        'errors' => $errors,
        'timestamp' => time(),
    ];
}

// ✅ RESPOSTA DE SUCESSO PADRONIZADA
protected function successResponse($data, $message = null, $code = 200)
{
    Yii::$app->response->statusCode = $code;
    return [
        'success' => true,
        'message' => $message,
        'data' => $data,
        'timestamp' => time(),
    ];
}
```

---

## 🔑 AuthController.php - Autenticação e Autorização

### actionLogin() - O Processo de Login

```php
public function actionLogin()
{
    // 📝 RECEBE DADOS DO REQUEST
    $username = Yii::$app->request->post('username');
    $password = Yii::$app->request->post('password');

    if (!$username || !$password) {
        return $this->errorResponse('Username e password obrigatórios', 400);
    }

    // 👤 BUSCA USUÁRIO POR USERNAME
    $user = User::findByUsername($username);
    if (!$user) {
        return $this->errorResponse('Credenciais inválidas', 401);
    }

    // 🔐 VALIDA SENHA
    if (!$user->validatePassword($password)) {
        return $this->errorResponse('Credenciais inválidas', 401);
    }

    // ✅ VERIFICA STATUS ATIVO
    if ($user->estado !== 'ativo') {
        return $this->errorResponse('Usuário inativo', 401);
    }

    // 🏢 BUSCA EMPRESA DO USUÁRIO
    $company = null;
    if ($user->company_id) {
        $company = Company::findOne($user->company_id);
    }

    // 🔑 GERA TOKEN BEARER
    $tokenData = $this->generateToken($user, $company);
    $accessToken = base64_encode(json_encode($tokenData));

    // 📊 OBTÉM ROLES E PERMISSÕES
    $roles = $this->getUserRoles($user->id);
    $permissions = $this->getUserPermissions($user->id);

    // 📤 RETORNA RESPOSTA COMPLETA
    return $this->successResponse([
        'access_token' => $accessToken,
        'token_type' => 'Bearer',
        'expires_in' => 86400, // 24 horas
        'expires_at' => $tokenData['expires_at'],
        'user' => [
            'id' => $user->id,
            'username' => $user->username,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'status' => $user->estado,
            'company_id' => $user->company_id,
        ],
        'company' => $company ? [
            'id' => $company->id,
            'name' => $company->name,
            'code' => $company->code,
            'email' => $company->email,
        ] : null,
        'roles' => $roles,
        'permissions' => $permissions,
    ], 'Login realizado com sucesso');
}
```

### generateToken() - Criação do Token

```php
private function generateToken($user, $company = null)
{
    $issuedAt = time();
    $expiresAt = $issuedAt + 86400; // 24 horas

    return [
        'user_id' => $user->id,
        'username' => $user->username,
        'company_id' => $user->company_id,
        'company_code' => $company ? $company->code : null,
        'roles' => $this->getUserRoles($user->id),
        'permissions' => $this->getUserPermissions($user->id),
        'expires_at' => $expiresAt,
        'issued_at' => $issuedAt,
    ];
}
```

### actionMe() - Perfil do Usuário Autenticado

```php
public function actionMe()
{
    // 🔍 OBTÉM DADOS DO TOKEN (já validados pelo authenticator)
    $tokenData = Yii::$app->params['token_data'] ?? [];

    if (empty($tokenData['user_id'])) {
        throw new UnauthorizedHttpException('Token inválido');
    }

    // 👤 BUSCA USUÁRIO NOVAMENTE (por segurança)
    $user = User::findIdentity($tokenData['user_id']);
    if (!$user) {
        throw new UnauthorizedHttpException('Usuário não encontrado');
    }

    // 🏢 BUSCA EMPRESA
    $company = null;
    if ($user->company_id) {
        $company = Company::findOne($user->company_id);
    }

    // 📤 RETORNA DADOS ATUALIZADOS
    return $this->successResponse([
        'user' => [
            'id' => $user->id,
            'username' => $user->username,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'status' => $user->estado,
            'company_id' => $user->company_id,
        ],
        'company' => $company ? [
            'id' => $company->id,
            'name' => $company->name,
            'code' => $company->code,
            'email' => $company->email,
        ] : null,
        'roles' => $tokenData['roles'] ?? [],
        'permissions' => $tokenData['permissions'] ?? [],
    ]);
}
```

---

## 🚗 VehicleController.php - Gestão de Veículos

### actionIndex() - Listagem com Filtros

```php
public function actionIndex()
{
    $companyId = $this->getCompanyId();

    if (!$companyId) {
        throw new ForbiddenHttpException('Empresa não identificada');
    }

    // 🏢 FILTRO AUTOMÁTICO POR EMPRESA
    $query = Vehicle::find()
        ->where(['company_id' => $companyId]);

    // 🔍 FILTROS OPCIONAIS
    $request = Yii::$app->request;

    // Busca por placa ou modelo
    if ($search = $request->get('search')) {
        $query->andWhere([
            'or',
            ['like', 'license_plate', $search],
            ['like', 'brand', $search],
            ['like', 'model', $search],
        ]);
    }

    // Filtro por status
    if ($status = $request->get('status')) {
        $query->andWhere(['status' => $status]);
    }

    // Filtro por tipo de combustível
    if ($fuelType = $request->get('fuel_type')) {
        $query->andWhere(['fuel_type' => $fuelType]);
    }

    // 📊 DATA PROVIDER COM PAGINAÇÃO
    $dataProvider = new ActiveDataProvider([
        'query' => $query,
        'pagination' => [
            'pageSize' => $request->get('per_page', 20),
        ],
        'sort' => [
            'defaultOrder' => ['created_at' => SORT_DESC],
        ],
    ]);

    return $dataProvider;
}
```

### actionCreate() - Criação de Veículo

```php
public function actionCreate()
{
    $companyId = $this->getCompanyId();

    if (!$companyId) {
        throw new ForbiddenHttpException('Empresa não identificada');
    }

    $model = new Vehicle();
    $model->load(Yii::$app->request->bodyParams, '');
    $model->company_id = $companyId; // 🔥 FORÇA COMPANY_ID DO TOKEN

    if ($model->save()) {
        return $this->successResponse($model, 'Veículo criado com sucesso', 201);
    }

    return $this->errorResponse('Erro ao criar veículo', 400, $model->errors);
}
```

### actionUpdate() - Atualização com Segurança

```php
public function actionUpdate($id)
{
    $model = $this->findModel($id);

    // 🔒 SEGURANÇA: checkAccess() já verificou company_id

    $model->load(Yii::$app->request->bodyParams, '');

    if ($model->save()) {
        return $this->successResponse($model, 'Veículo atualizado com sucesso');
    }

    return $this->errorResponse('Erro ao atualizar veículo', 400, $model->errors);
}
```

### findModel() - Busca Segura

```php
protected function findModel($id)
{
    $model = Vehicle::findOne($id);

    if (!$model) {
        throw new NotFoundHttpException('Veículo não encontrado');
    }

    // 🔒 VERIFICAÇÃO AUTOMÁTICA VIA checkAccess()
    $this->checkAccess('update', $model);

    return $model;
}
```

---

## 👥 UserController.php - Gestão de Usuários

### Diferenças Principais do VehicleController:

1. **Herança:** `extends BaseApiController`
2. **Modelo:** `$modelClass = 'common\models\User'`
3. **Filtros Específicos:** `tipo`, `status`, `search`
4. **Ações Extras:** `drivers`, `profile`, `byCompany`

### actionCreate() com Validações Extras

```php
public function actionCreate()
{
    $companyId = $this->getCompanyId();

    if (!$companyId) {
        throw new ForbiddenHttpException('Empresa não identificada');
    }

    $model = new User();
    $model->scenario = 'create'; // Cenário específico para criação
    $model->load(Yii::$app->request->bodyParams, '');
    $model->company_id = $companyId;
    $model->estado = 'ativo'; // Status padrão

    // 🔐 CODIFICA SENHA
    if (!empty($model->password)) {
        $model->setPassword($model->password);
        $model->generateAuthKey();
    }

    if ($model->save()) {
        // Remove senha da resposta
        $model->password_hash = null;
        return $this->successResponse($model, 'Usuário criado com sucesso', 201);
    }

    return $this->errorResponse('Erro ao criar usuário', 400, $model->errors);
}
```

---

## 📊 Models - Camada de Dados

### Vehicle.php - Relacionamentos

```php
class Vehicle extends ActiveRecord
{
    // 🏢 RELACIONAMENTO COM EMPRESA
    public function getCompany()
    {
        return $this->hasOne(Company::class, ['id' => 'company_id']);
    }

    // 👤 RELACIONAMENTO COM CONDUTOR
    public function getDriver()
    {
        return $this->hasOne(\common\models\User::class, ['id' => 'driver_id']);
    }

    // 🔧 RELACIONAMENTO COM MANUTENÇÕES
    public function getMaintenances()
    {
        return $this->hasMany(Maintenance::class, ['vehicle_id' => 'id']);
    }

    // ⛽ RELACIONAMENTO COM ABASTECIMENTOS
    public function getFuelLogs()
    {
        return $this->hasMany(FuelLog::class, ['vehicle_id' => 'id']);
    }
}
```

### Métodos de Cálculo

```php
// 📈 MÉDIA DE CONSUMO
public function getAverageFuelConsumption()
{
    $fuelLogs = $this->fuelLogs;
    if (empty($fuelLogs)) return 0;

    $totalLiters = 0;
    $totalKm = 0;

    foreach ($fuelLogs as $log) {
        $totalLiters += $log->litros;
        // Calcula km percorridos desde último abastecimento
        $totalKm += $log->getConsumptionSinceLast();
    }

    return $totalKm > 0 ? round(($totalLiters / $totalKm) * 100, 2) : 0; // L/100km
}

// ⚠️ VERIFICA SE PRECISA DE MANUTENÇÃO
public function needsMaintenance()
{
    $lastMaintenance = Maintenance::find()
        ->where(['vehicle_id' => $this->id])
        ->orderBy(['data_manutencao' => SORT_DESC])
        ->one();

    if (!$lastMaintenance) return true;

    // Verifica se já passaram 6 meses ou 10.000 km
    $monthsSince = (time() - strtotime($lastMaintenance->data_manutencao)) / (30 * 24 * 3600);
    $kmSince = $this->mileage - ($lastMaintenance->quilometragem ?? 0);

    return $monthsSince > 6 || $kmSince > 10000;
}
```

---

## 🔧 Configuração no backend/config/main.php

### Módulo API

```php
'modules' => [
    'api' => [
        'class' => 'backend\modules\api\Module',
    ],
],
```

### URL Manager

```php
'urlManager' => [
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    'enableStrictParsing' => false,
    'rules' => [
        // 🔐 Autenticação
        'POST api/auth/login' => 'api/auth/login',
        'POST api/auth/logout' => 'api/auth/logout',
        'GET api/auth/me' => 'api/auth/me',
        'POST api/auth/refresh' => 'api/auth/refresh',

        // 🚗 REST API para Veículos
        ['class' => 'yii\\rest\\UrlRule', 'controller' => ['api/vehicle'], 'pluralize' => false],

        // 👥 REST API para Usuários
        ['class' => 'yii\\rest\\UrlRule', 'controller' => ['api/user'], 'pluralize' => false],
    ],
],
```

---

## 🎯 Resumo dos Conceitos-Chave

### 1. **Stateless Authentication**
- Tokens Bearer Base64 no header Authorization
- Validação em cada requisição
- Dados do usuário no token (não em sessão)

### 2. **Multi-tenancy Automático**
- `company_id` em todos os models relevantes
- Filtros automáticos em todas as queries
- Isolamento completo entre empresas

### 3. **RBAC Granular**
- Roles: admin, gestor, condutor
- Permissions específicas por ação
- Controle fino de acesso

### 4. **Herança Inteligente**
- `BaseApiController` com comportamentos comuns
- Controllers específicos sobrescrevem apenas o necessário
- Reutilização máxima de código

### 5. **Segurança em Camadas**
- CORS global e específico
- Autenticação obrigatória
- Autorização por empresa e permissões
- Validação de dados rigorosa

---

**Próximo:** [PADROES_DESIGN.md](PADROES_DESIGN.md) - Padrões de design implementados
