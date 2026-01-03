<?php

namespace backend\modules\api\v1\controllers;

use Yii;
use yii\rest\Controller;
use yii\filters\Cors;
use yii\filters\VerbFilter;
use yii\filters\ContentNegotiator;
use yii\web\Response;
use common\models\User;
use backend\modules\api\v1\models\Company;

/**
 * Auth API Controller
 * 
 * Provides authentication endpoints for VeiGest API
 * 
 * @author VeiGest Team
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
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => false,
                'Access-Control-Max-Age' => 86400,
            ]
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
                'refresh' => ['POST'],
                'logout' => ['POST'],
            ],
        ];

        return $behaviors;
    }

    /**
     * Login user
     * 
     * @return array
     */
    public function actionLogin()
    {
        $body = Yii::$app->request->bodyParams;
        $username = $body['username'] ?? null;
        $password = $body['password'] ?? null;

        if (!$username || !$password) {
            Yii::$app->response->statusCode = 400;
            return [
                'success' => false,
                'message' => 'username and password are required',
                'error_code' => 'MISSING_CREDENTIALS'
            ];
        }

        // Find active user by username
        $user = User::findByUsername($username);

        if (!$user || (int)$user->status !== User::STATUS_ACTIVE || !$user->validatePassword($password)) {
            Yii::$app->response->statusCode = 401;
            return [
                'success' => false,
                'message' => 'Invalid credentials',
                'error_code' => 'INVALID_CREDENTIALS'
            ];
        }

        // Generate new auth key  
        if ($user instanceof User) {
            $user->generateAuthKey();
            $user->save(false);
        }

        // Get company information
        $company = Company::findOne($user->company_id);

        return [
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'access_token' => $user->auth_key,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'nome' => $user->nome,
                    'email' => $user->email,
                    'company_id' => $user->company_id ?? 1,
                    'company' => $company ? [
                        'id' => $company->id,
                        'nome' => $company->nome,
                    ] : null,
                    'estado' => (int)$user->status === User::STATUS_ACTIVE ? 'ativo' : 'inativo',
                ],
            ]
        ];
    }

    /**
     * Refresh authentication token
     * 
     * @return array
     */
    public function actionRefresh()
    {
        $authHeader = Yii::$app->request->headers->get('Authorization');
        
        if (!$authHeader || !preg_match('/^Bearer\s+(.*?)$/', $authHeader, $matches)) {
            Yii::$app->response->statusCode = 401;
            return [
                'success' => false,
                'message' => 'Authentication required'
            ];
        }

        $token = $matches[1];
        $user = User::findIdentityByAccessToken($token);

        if (!$user) {
            Yii::$app->response->statusCode = 401;
            return [
                'success' => false,
                'message' => 'Authentication required'
            ];
        }

        // Generate new token
        if ($user instanceof User) {
            $user->generateAuthKey();
            $user->save(false);
        }

        return [
            'success' => true,
            'message' => 'Token refreshed successfully',
            'data' => [
                'access_token' => $user->auth_key,
                'token_type' => 'Bearer',
            ]
        ];
    }

    /**
     * Logout user
     * 
     * @return array
     */
    public function actionLogout()
    {
        $authHeader = Yii::$app->request->headers->get('Authorization');
        
        if (!$authHeader || !preg_match('/^Bearer\s+(.*?)$/', $authHeader, $matches)) {
            Yii::$app->response->statusCode = 401;
            return [
                'success' => false,
                'message' => 'Authentication required'
            ];
        }

        $token = $matches[1];
        $user = User::findIdentityByAccessToken($token);

        if ($user instanceof User) {
            // Invalidate the current token by generating a new one
            $user->generateAuthKey();
            $user->save(false);
        }

        return [
            'success' => true,
            'message' => 'Logged out successfully'
        ];
    }

    /**
     * Get API info
     * 
     * @return array
     */
    public function actionInfo()
    {
        return [
            'name' => 'VeiGest API',
            'version' => '1.0.0',
            'description' => 'RESTful API for VeiGest vehicle management system',
            'endpoints' => [
                'auth' => '/api/v1/auth',
                'companies' => '/api/v1/company',
                'vehicles' => '/api/v1/vehicle',
                'users' => '/api/v1/user',
                'maintenances' => '/api/v1/maintenance',
            ],
        ];
    }
}
