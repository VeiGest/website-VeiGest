<?php

namespace backend\modules\api\v1\controllers;

use backend\modules\api\v1\models\Vehicle;
use backend\modules\api\v1\models\Company;
use yii\data\ActiveDataProvider;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\ContentNegotiator;
use yii\filters\Cors;
use yii\rest\ActiveController;
use yii\web\Response;
use yii\web\NotFoundHttpException;

/**
 * Vehicle API Controller
 * 
 * Provides CRUD operations for vehicles
 * 
 * @author VeiGest Team
 */
class VehicleController extends ActiveController
{
    public $modelClass = 'backend\modules\api\v1\models\Vehicle';

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

        // Authentication
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
        ];

        return $behaviors;
    }

    /**
     * Override default ActiveController actions so custom implementations run.
     */
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index'], $actions['create']);
        return $actions;
    }

    /**
     * Lists all vehicles with company information
     * 
     * @return array
     */
    public function actionIndex()
    {
        $vehicles = Vehicle::find()->with(['company'])->asArray()->all();

        return [
            'success' => true,
            'data' => $vehicles,
        ];
    }

    /**
     * Get vehicle with detailed information
     * 
     * @param integer $id
     * @return Vehicle
     */
    public function actionView($id)
    {
        $vehicle = Vehicle::find()
            ->with(['company', 'maintenances', 'fuelLogs'])
            ->where(['id' => $id])
            ->one();
            
        if (!$vehicle) {
            throw new NotFoundHttpException('Vehicle not found');
        }

        return $vehicle;
    }

    /**
     * Create a vehicle from simplified payload.
     */
    public function actionCreate()
    {
        $body = \Yii::$app->request->bodyParams;
        $model = new Vehicle();
        $model->company_id = $body['company_id'] ?? null;
        $model->license_plate = $body['plate'] ?? $body['matricula'] ?? null;
        $model->model = $body['model'] ?? $body['modelo'] ?? null;
        // Use provided brand or reuse model for required brand field
        $model->brand = $body['brand'] ?? $body['marca'] ?? ($model->model ?? '');
        $model->year = $body['year'] ?? $body['ano'] ?? null;
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
            'message' => 'Vehicle created successfully',
            'data' => $model,
        ];
    }

    /**
     * Get vehicle maintenances
     * 
     * @param integer $id Vehicle ID
     * @return ActiveDataProvider
     */
    public function actionMaintenances($id)
    {
        $vehicle = Vehicle::findOne($id);
        if (!$vehicle) {
            throw new NotFoundHttpException('Vehicle not found');
        }

        return new ActiveDataProvider([
            'query' => $vehicle->getMaintenances()->orderBy(['data_manutencao' => SORT_DESC]),
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);
    }

    /**
     * Get vehicle fuel logs
     * 
     * @param integer $id Vehicle ID
     * @return ActiveDataProvider
     */
    public function actionFuelLogs($id)
    {
        $vehicle = Vehicle::findOne($id);
        if (!$vehicle) {
            throw new NotFoundHttpException('Vehicle not found');
        }

        return new ActiveDataProvider([
            'query' => $vehicle->getFuelLogs()->orderBy(['data_abastecimento' => SORT_DESC]),
            'pagination' => [
                'pageSize' => 15,
            ],
        ]);
    }

    /**
     * Get vehicle statistics
     * 
     * @param integer $id Vehicle ID
     * @return array
     */
    public function actionStats($id)
    {
        $vehicle = Vehicle::findOne($id);
        if (!$vehicle) {
            throw new NotFoundHttpException('Vehicle not found');
        }

        $maintenances = $vehicle->getMaintenances()->all();
        $fuelLogs = $vehicle->getFuelLogs()->all();

        $totalMaintenanceCost = array_sum(array_column($maintenances, 'custo'));
        $totalFuelCost = array_sum(array_column($fuelLogs, 'custo_total'));
        $totalLiters = array_sum(array_column($fuelLogs, 'litros'));
        
        return [
            'vehicle' => $vehicle,
            'maintenances_count' => count($maintenances),
            'fuel_logs_count' => count($fuelLogs),
            'total_maintenance_cost' => $totalMaintenanceCost,
            'total_fuel_cost' => $totalFuelCost,
            'total_cost' => $totalMaintenanceCost + $totalFuelCost,
            'total_liters' => $totalLiters,
            'average_fuel_consumption' => $totalLiters > 0 && $vehicle->quilometragem > 0 
                ? round($totalLiters / ($vehicle->quilometragem / 100), 2) : 0,
        ];
    }

    /**
     * Search vehicles by company
     * 
     * @param integer $company_id Company ID
     * @return ActiveDataProvider
     */
    public function actionByCompany($company_id)
    {
        $company = Company::findOne($company_id);
        if (!$company) {
            throw new NotFoundHttpException('Company not found');
        }

        return new ActiveDataProvider([
            'query' => Vehicle::find()
                ->where(['company_id' => $company_id])
                ->with(['company']),
            'pagination' => [
                'pageSize' => 15,
            ],
        ]);
    }

    /**
     * Get vehicles by status
     * 
     * @param string $status Vehicle status
     * @return ActiveDataProvider
     */
    public function actionByStatus($status)
    {
        $validStatuses = ['ativo', 'inativo', 'manutencao'];
        
        if (!in_array($status, $validStatuses)) {
            throw new \yii\web\BadRequestHttpException('Invalid status. Valid statuses: ' . implode(', ', $validStatuses));
        }

        return new ActiveDataProvider([
            'query' => Vehicle::find()
                ->where(['estado' => $status])
                ->with(['company']),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
    }
}