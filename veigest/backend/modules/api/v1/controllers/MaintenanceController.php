<?php

namespace backend\modules\api\v1\controllers;

use backend\modules\api\v1\models\Maintenance;
use backend\modules\api\v1\models\Vehicle;
use yii\data\ActiveDataProvider;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\ContentNegotiator;
use yii\filters\Cors;
use yii\rest\ActiveController;
use yii\web\Response;
use yii\web\NotFoundHttpException;

/**
 * Maintenance API Controller
 * 
 * Provides CRUD operations for vehicle maintenances
 * 
 * @author VeiGest Team
 */
class MaintenanceController extends ActiveController
{
    public $modelClass = 'backend\modules\api\v1\models\Maintenance';

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

        return $behaviors;
    }

    /**
     * Lists all maintenances
     * 
     * @return ActiveDataProvider
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Maintenance::find()->with(['vehicle']),
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'defaultOrder' => ['data_manutencao' => SORT_DESC]
            ],
        ]);

        return $dataProvider;
    }

    /**
     * Get maintenance with vehicle information
     * 
     * @param integer $id
     * @return Maintenance
     */
    public function actionView($id)
    {
        $maintenance = Maintenance::find()
            ->with(['vehicle', 'vehicle.company'])
            ->where(['id' => $id])
            ->one();
            
        if (!$maintenance) {
            throw new NotFoundHttpException('Maintenance not found');
        }

        return $maintenance;
    }

    /**
     * Get maintenances by vehicle
     * 
     * @param integer $vehicle_id Vehicle ID
     * @return ActiveDataProvider
     */
    public function actionByVehicle($vehicle_id)
    {
        $vehicle = Vehicle::findOne($vehicle_id);
        if (!$vehicle) {
            throw new NotFoundHttpException('Vehicle not found');
        }

        return new ActiveDataProvider([
            'query' => Maintenance::find()
                ->where(['vehicle_id' => $vehicle_id])
                ->with(['vehicle']),
            'pagination' => [
                'pageSize' => 15,
            ],
            'sort' => [
                'defaultOrder' => ['data_manutencao' => SORT_DESC]
            ],
        ]);
    }

    /**
     * Get maintenances by status
     * 
     * @param string $status Maintenance status
     * @return ActiveDataProvider
     */
    public function actionByStatus($status)
    {
        $validStatuses = ['agendada', 'em_andamento', 'concluida', 'cancelada'];
        
        if (!in_array($status, $validStatuses)) {
            throw new \yii\web\BadRequestHttpException('Invalid status. Valid statuses: ' . implode(', ', $validStatuses));
        }

        return new ActiveDataProvider([
            'query' => Maintenance::find()
                ->where(['estado' => $status])
                ->with(['vehicle', 'vehicle.company']),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
    }

    /**
     * Get maintenance statistics
     * 
     * @return array
     */
    public function actionStats()
    {
        $totalMaintenances = Maintenance::find()->count();
        $scheduledCount = Maintenance::find()->where(['estado' => 'agendada'])->count();
        $inProgressCount = Maintenance::find()->where(['estado' => 'em_andamento'])->count();
        $completedCount = Maintenance::find()->where(['estado' => 'concluida'])->count();
        $cancelledCount = Maintenance::find()->where(['estado' => 'cancelada'])->count();
        
        $totalCost = Maintenance::find()->where(['estado' => 'concluida'])->sum('custo') ?? 0;
        
        return [
            'total_maintenances' => $totalMaintenances,
            'scheduled' => $scheduledCount,
            'in_progress' => $inProgressCount,
            'completed' => $completedCount,
            'cancelled' => $cancelledCount,
            'total_cost' => $totalCost,
            'average_cost' => $completedCount > 0 ? round($totalCost / $completedCount, 2) : 0,
        ];
    }
}