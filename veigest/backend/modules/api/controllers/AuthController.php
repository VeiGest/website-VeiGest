<?php

namespace backend\modules\api\controllers;

use Yii;
use yii\rest\Controller;
use yii\filters\Cors;
use yii\filters\VerbFilter;
use yii\filters\ContentNegotiator;
use yii\web\Response;
use yii\web\BadRequestHttpException;
use yii\web\UnauthorizedHttpException;
use common\models\User;
use backend\modules\api\components\ApiAuthenticator;

/**
 * Auth API Controller
 * 
 * Fornece endpoints de autenticação para API VeiGest
 * Implementa login, logout, refresh token e informações do usuário
 * 
 * .
 */
class AuthController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // CORS filter
        $behaviors['corsFilter'] = [
            'class' => Cors::class,
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => false,
                'Access-Control-Max-Age' => 86400,
            ],
        ];

        // Content negotiator
        $behaviors['contentNegotiator'] = [
            'class' => ContentNegotiator::class,
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
            ],
        ];

        // Verb filter
        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'login' => ['POST'],
                'register' => ['POST'],
                'logout' => ['POST'],
                'me' => ['GET'],
                'refresh' => ['POST'],
                'info' => ['GET'],
            ],
        ];

        // Autenticação apenas para endpoints protegidos
        $behaviors['authenticator'] = [
            'class' => ApiAuthenticator::class,
            'except' => ['login', 'register', 'options'],
        ];

        return $behaviors;
    }

    /**
     * Login de usuário
     * 
     * @return array
     * @throws BadRequestHttpException
     * @throws UnauthorizedHttpException
     */
    public function actionLogin()
    {
        $body = Yii::$app->request->bodyParams;
        $username = $body['username'] ?? null;
        $password = $body['password'] ?? null;

        // Validar dados obrigatórios
        if (!$username || !$password) {
            throw new BadRequestHttpException('Username e password são obrigatórios');
        }

        // Buscar usuário por username ou email
        $user = User::findByUsername($username);
        if (!$user) {
            $user = User::find()->where(['email' => $username])->one();
        }

        // Validar credenciais
        if (!$user || !$user->validatePassword($password)) {
            throw new UnauthorizedHttpException('Credenciais inválidas');
        }

        // Verificar se usuário está ativo
        if ($user->status !== 'active') {
            throw new UnauthorizedHttpException('Conta de usuário inativa');
        }

        // Obter informações da empresa
        $company = null;
        if ($user->company_id) {
            $company = \backend\modules\api\models\Company::findOne($user->company_id);
        }

        // Obter papéis do usuário (RBAC)
        $roles = [];
        $permissions = [];
        
        if (Yii::$app->authManager) {
            $roles = array_keys(Yii::$app->authManager->getRolesByUser($user->id));
            
            // Obter permissões do usuário
            foreach ($roles as $roleName) {
                $role = Yii::$app->authManager->getRole($roleName);
                if ($role) {
                    $rolePermissions = array_keys(Yii::$app->authManager->getPermissionsByRole($roleName));
                    $permissions = array_merge($permissions, $rolePermissions);
                }
            }
            $permissions = array_unique($permissions);
        }

        // Gerar token Base64 com dados do usuário
        $expiresAt = time() + (24 * 60 * 60); // 24 horas
        $tokenData = [
            'user_id' => $user->id,
            'username' => $user->username,
            'company_id' => $company ? $company->id : null,
            'company_code' => $company ? $company->code ?? null : null,
            'roles' => $roles,
            'permissions' => $permissions,
            'expires_at' => $expiresAt,
            'issued_at' => time(),
        ];
        
        $accessToken = base64_encode(json_encode($tokenData));

        return [
            'success' => true,
            'data' => [
                'access_token' => $accessToken,
                'token_type' => 'Bearer',
                'expires_in' => 86400, // 24 horas em segundos
                'expires_at' => $expiresAt,
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'status' => $user->status,
                    'company_id' => $user->company_id,
                ],
                'company' => $company ? [
                    'id' => $company->id,
                    'name' => $company->nome ?? $company->name,
                    'code' => $company->code ?? null,
                    'email' => $company->email,
                ] : null,
                'roles' => $roles,
                'permissions' => $permissions,
            ]
        ];
    }

    /**
     * Registro de novo usuário
     * 
     * Permite criar uma nova conta de usuário via API.
     * O usuário registrado receberá o papel 'driver' por padrão.
     * 
     * @return array
     * @throws BadRequestHttpException
     */
    public function actionRegister()
    {
        $body = Yii::$app->request->bodyParams;
        
        // Campos obrigatórios
        $username = trim($body['username'] ?? '');
        $email = trim($body['email'] ?? '');
        $password = $body['password'] ?? '';
        $name = trim($body['name'] ?? '');
        $companyId = $body['company_id'] ?? null;
        
        // Campos opcionais
        $phone = trim($body['phone'] ?? '');
        
        // Validar campos obrigatórios
        if (empty($username)) {
            throw new BadRequestHttpException('O campo username é obrigatório');
        }
        
        if (empty($email)) {
            throw new BadRequestHttpException('O campo email é obrigatório');
        }
        
        if (empty($password)) {
            throw new BadRequestHttpException('O campo password é obrigatório');
        }
        
        if (empty($name)) {
            throw new BadRequestHttpException('O campo name é obrigatório');
        }
        
        if (empty($companyId)) {
            throw new BadRequestHttpException('O campo company_id é obrigatório');
        }
        
        // Validar formato do email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new BadRequestHttpException('O formato do email é inválido');
        }
        
        // Validar comprimento da senha
        if (strlen($password) < 3) {
            throw new BadRequestHttpException('A senha deve ter pelo menos 3 caracteres');
        }
        
        // Verificar se username já existe
        $existingUser = User::find()->where(['username' => $username])->one();
        if ($existingUser) {
            throw new BadRequestHttpException('Este nome de utilizador já está em uso');
        }
        
        // Verificar se email já existe
        $existingEmail = User::find()->where(['email' => $email])->one();
        if ($existingEmail) {
            throw new BadRequestHttpException('Este email já está registado no sistema');
        }
        
        // Verificar se empresa existe
        $company = \backend\modules\api\models\Company::findOne($companyId);
        if (!$company) {
            throw new BadRequestHttpException('A empresa especificada não existe');
        }
        
        // Criar novo usuário
        $user = new User();
        $user->scenario = 'signup';
        $user->username = $username;
        $user->email = $email;
        $user->name = $name;
        $user->company_id = $companyId;
        $user->password = $password;
        $user->status = 'active';
        
        if (!empty($phone)) {
            $user->phone = $phone;
        }
        
        // Salvar usuário
        if (!$user->save()) {
            $errors = $user->getErrors();
            $errorMessages = [];
            foreach ($errors as $field => $fieldErrors) {
                foreach ($fieldErrors as $error) {
                    $errorMessages[] = $error;
                }
            }
            throw new BadRequestHttpException('Erro ao criar usuário: ' . implode(', ', $errorMessages));
        }
        
        // Atribuir role 'driver' por padrão (RBAC)
        $defaultRole = 'driver';
        try {
            if (Yii::$app->authManager) {
                $role = Yii::$app->authManager->getRole($defaultRole);
                if ($role) {
                    Yii::$app->authManager->assign($role, $user->id);
                }
            }
        } catch (\Exception $e) {
            // Log do erro mas não bloquear o registro
            Yii::error('Erro ao atribuir role: ' . $e->getMessage());
        }
        
        // Obter roles e permissões
        $roles = [];
        $permissions = [];
        
        if (Yii::$app->authManager) {
            $roles = array_keys(Yii::$app->authManager->getRolesByUser($user->id));
            
            foreach ($roles as $roleName) {
                $role = Yii::$app->authManager->getRole($roleName);
                if ($role) {
                    $rolePermissions = array_keys(Yii::$app->authManager->getPermissionsByRole($roleName));
                    $permissions = array_merge($permissions, $rolePermissions);
                }
            }
            $permissions = array_unique($permissions);
        }
        
        // Gerar token de acesso para o novo usuário
        $expiresAt = time() + (24 * 60 * 60); // 24 horas
        $tokenData = [
            'user_id' => $user->id,
            'username' => $user->username,
            'company_id' => $company->id,
            'company_code' => $company->code ?? null,
            'roles' => $roles,
            'permissions' => $permissions,
            'expires_at' => $expiresAt,
            'issued_at' => time(),
        ];
        
        $accessToken = base64_encode(json_encode($tokenData));
        
        Yii::$app->response->statusCode = 201;
        
        return [
            'success' => true,
            'message' => 'Usuário registrado com sucesso',
            'data' => [
                'access_token' => $accessToken,
                'token_type' => 'Bearer',
                'expires_in' => 86400,
                'expires_at' => $expiresAt,
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'status' => $user->status,
                    'company_id' => $user->company_id,
                ],
                'company' => [
                    'id' => $company->id,
                    'name' => $company->nome ?? $company->name,
                    'code' => $company->code ?? null,
                    'email' => $company->email,
                ],
                'roles' => $roles,
                'permissions' => $permissions,
            ]
        ];
    }

    /**
     * Obter informações do usuário autenticado
     * 
     * @return array
     */
    public function actionMe()
    {
        $tokenData = Yii::$app->params['token_data'] ?? [];
        
        if (empty($tokenData['user_id'])) {
            throw new UnauthorizedHttpException('Token inválido');
        }
        
        $user = User::findIdentity($tokenData['user_id']);
        if (!$user) {
            throw new UnauthorizedHttpException('Usuário não encontrado');
        }
        
        $company = null;
        if ($user->company_id) {
            $company = \backend\modules\api\models\Company::findOne($user->company_id);
        }

        return [
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'status' => $user->status,
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
                'token_info' => [
                    'issued_at' => $tokenData['issued_at'] ?? null,
                    'expires_at' => $tokenData['expires_at'] ?? null,
                ],
            ]
        ];
    }

    /**
     * Refresh do token de autenticação
     * 
     * @return array
     */
    public function actionRefresh()
    {
        $user = Yii::$app->user->identity;
        $oldTokenData = Yii::$app->params['token_data'] ?? [];
        
        // Gerar novo token com mesmos dados mas nova expiração
        $expiresAt = time() + (24 * 60 * 60); // 24 horas
        $tokenData = array_merge($oldTokenData, [
            'expires_at' => $expiresAt,
            'issued_at' => time(),
        ]);
        
        $accessToken = base64_encode(json_encode($tokenData));

        return [
            'success' => true,
            'data' => [
                'access_token' => $accessToken,
                'token_type' => 'Bearer',
                'expires_in' => 86400,
                'expires_at' => $expiresAt,
            ]
        ];
    }

    /**
     * Logout do usuário
     * 
     * @return array
     */
    public function actionLogout()
    {
        // Como usamos tokens stateless, o logout é apenas do lado cliente
        // Aqui podemos registrar o logout para auditoria se necessário
        
        return [
            'success' => true,
            'message' => 'Logout realizado com sucesso',
        ];
    }

    /**
     * Informações sobre a API
     * 
     * @return array
     */
    public function actionInfo()
    {
        return [
            'success' => true,
            'data' => [
                'api_name' => 'VeiGest REST API',
                'version' => '1.0',
                'framework' => 'Yii2',
                'authentication' => 'Bearer Token (Base64)',
                'endpoints' => [
                    'auth' => [
                        'POST /api/auth/login',
                        'POST /api/auth/register',
                        'GET /api/auth/me',
                        'POST /api/auth/refresh',
                        'POST /api/auth/logout',
                        'GET /api/auth/info',
                    ],
                ],
                'timestamp' => date('c'),
            ]
        ];
    }

    /**
     * Ação OPTIONS para CORS preflight
     */
    public function actionOptions()
    {
        Yii::$app->response->statusCode = 204;
        return null;
    }
}
