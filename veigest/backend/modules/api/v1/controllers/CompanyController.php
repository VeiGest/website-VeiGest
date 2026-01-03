<?php

namespace backend\modules\api\v1\controllers;

use backend\modules\api\v1\models\Company;
use yii\data\ActiveDataProvider;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\ContentNegotiator;
use yii\filters\Cors;
use yii\rest\ActiveController;
use yii\web\Response;

/**
 * Company API Controller
 * 
 * Provides CRUD operations for companies
 * 
 * @author VeiGest Team
 */
class CompanyController extends ActiveController
{
    public $modelClass = 'backend\modules\api\v1\models\Company';

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

        // Simple Bearer authentication with custom 401 JSON
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
        ];

        return $behaviors;
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        $actions = parent::actions();

        unset($actions['index'], $actions['create']);

        return $actions;
    }

    /**
     * Lists all companies with their vehicle count
     * 
     * @return array
     */
    public function actionIndex()
    {
        // Require valid bearer token
        $auth = \Yii::$app->request->headers->get('Authorization');
        if (!$auth || !preg_match('/^Bearer\s+(.*?)$/', $auth, $m)) {
            \Yii::$app->response->statusCode = 401;
            return ['success' => false, 'message' => 'Authentication required'];
        }
        $token = $m[1];
        $user = \common\models\User::findIdentityByAccessToken($token);
        if (!$user) {
            \Yii::$app->response->statusCode = 401;
            return ['success' => false, 'message' => 'Authentication required'];
        }

        $companies = Company::find()->with(['vehicles'])->asArray()->all();

        return [
            'success' => true,
            'message' => 'Companies fetched successfully',
            'data' => $companies,
        ];
    }

    /**
     * Get company with vehicles
     * 
     * @param integer $id
     * @return Company
     */
    public function actionView($id)
    {
        return Company::find()
            ->with(['vehicles', 'users'])
            ->where(['id' => $id])
            ->one();
    }

    /**
     * Create company with simplified payload
     */
    public function actionCreate()
    {
        // Require auth (ActiveController create bypasses auth otherwise)
        $auth = \Yii::$app->request->headers->get('Authorization');
        if (!$auth || !preg_match('/^Bearer\s+(.*?)$/', $auth, $m)) {
            \Yii::$app->response->statusCode = 401;
            return ['success' => false, 'message' => 'Authentication required'];
        }
        $token = $m[1];
        $user = \common\models\User::findIdentityByAccessToken($token);
        if (!$user) {
            \Yii::$app->response->statusCode = 401;
            return ['success' => false, 'message' => 'Authentication required'];
        }

        $body = \Yii::$app->request->bodyParams;
        $model = new Company();
        $model->name = $body['name'] ?? $body['nome'] ?? null;
        $model->email = $body['email'] ?? null;
        $model->phone = $body['phone'] ?? ($body['telefone'] ?? null);
        $model->tax_id = $body['cnpj'] ?? ($body['nif'] ?? null);
        $model->status = 'active';

        if (!$model->save()) {
            \Yii::$app->response->statusCode = 422;
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $model->errors,
            ];
        }

        \Yii::$app->response->statusCode = 201;
        return [
            'success' => true,
            'message' => 'Company created successfully',
            'data' => $model,
        ];
    }

    /**
     * Get company vehicles
     * 
     * @param integer $id Company ID
     * @return ActiveDataProvider
     */
    public function actionVehicles($id)
    {
        $company = Company::findOne($id);
        if (!$company) {
            throw new \yii\web\NotFoundHttpException('Company not found');
        }

        return new ActiveDataProvider([
            'query' => $company->getVehicles()->with(['maintenances', 'fuelLogs']),
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);
    }

    /**
     * Get company users/drivers
     * 
     * @param integer $id Company ID
     * @return ActiveDataProvider
     */
    public function actionUsers($id)
    {
        $company = Company::findOne($id);
        if (!$company) {
            throw new \yii\web\NotFoundHttpException('Company not found');
        }

        return new ActiveDataProvider([
            'query' => $company->getUsers(),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
    }

    /**
     * Get company statistics
     * 
     * @param integer $id Company ID
     * @return array
     */
    public function actionStats($id)
    {
        $company = Company::findOne($id);
        if (!$company) {
            throw new \yii\web\NotFoundHttpException('Company not found');
        }

        return [
            'company' => $company,
            'vehicles_count' => $company->getVehicles()->count(),
            'users_count' => $company->getUsers()->count(),
            'active_vehicles' => $company->getVehicles()->where(['status' => 'active'])->count(),
            'vehicles_in_maintenance' => $company->getVehicles()->where(['status' => 'maintenance'])->count(),
        ];
    }
}