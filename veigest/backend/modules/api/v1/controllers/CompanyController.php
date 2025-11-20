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

        // Authentication - temporarily disabled for testing
        // $behaviors['authenticator'] = [
        //     'class' => HttpBearerAuth::class,
        // ];

        return $behaviors;
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        $actions = parent::actions();

        // Customize the data provider for the index action
        $actions['index']['dataFilter'] = [
            'class' => 'yii\data\ActiveDataFilter',
            'searchModel' => 'backend\modules\api\v1\models\Company',
        ];

        return $actions;
    }

    /**
     * Lists all companies with their vehicle count
     * 
     * @return ActiveDataProvider
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Company::find()->with(['vehicles']),
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'defaultOrder' => ['id' => SORT_ASC]
            ],
        ]);

        return $dataProvider;
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
            'active_vehicles' => $company->getVehicles()->where(['estado' => 'ativo'])->count(),
            'vehicles_in_maintenance' => $company->getVehicles()->where(['estado' => 'manutencao'])->count(),
        ];
    }
}