<?php

namespace backend\modules\api\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use backend\modules\api\models\Maintenance;

/**
 * Maintenance API Controller
 * 
 * Fornece operações CRUD para manutenções com multi-tenancy
 * Implementa filtragem automática por company_id
 * 
 * @author VeiGest Team
 */
class MaintenanceController extends BaseApiController
{
    public $modelClass = 'backend\modules\api\models\Maintenance';

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        $actions = parent::actions();

        // Customizar as ações padrão
        unset($actions['index']);
        unset($actions['create']);
        unset($actions['update']);

        return $actions;
    }

    /**
     * Lista todas as manutenções da empresa do usuário autenticado
     * 
     * @return ActiveDataProvider
     */
    public function actionIndex()
    {
        $companyId = $this->getCompanyId();
        
        if (!$companyId) {
            throw new ForbiddenHttpException('Company ID não encontrado no token de autenticação');
        }

        $query = Maintenance::find()
            ->joinWith('vehicle')
            ->where(['{{%vehicles}}.company_id' => $companyId]);

        // Filtros opcionais
        $request = Yii::$app->request;
        
        if ($vehicleId = $request->get('vehicle_id')) {
            $query->andWhere(['vehicle_id' => $vehicleId]);
        }
        
        if ($tipo = $request->get('tipo')) {
            $query->andWhere(['tipo' => $tipo]);
        }
        
        if ($estado = $request->get('estado')) {
            $query->andWhere(['estado' => $estado]);
        }

        if ($search = $request->get('search')) {
            $query->andWhere([
                'or',
                ['like', 'descricao', $search],
                ['like', 'fornecedor', $search],
                ['like', 'observacoes', $search]
            ]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $request->get('per-page', 20),
                'page' => $request->get('page', 1) - 1,
            ],
            'sort' => [
                'defaultOrder' => ['data_manutencao' => SORT_DESC],
                'attributes' => ['id', 'data_manutencao', 'custo', 'tipo', 'estado', 'created_at']
            ],
        ]);

        return $dataProvider;
    }

    /**
     * Cria nova manutenção
     * Automaticamente associa à empresa do usuário autenticado através do veículo
     * 
     * @return Maintenance
     */
    public function actionCreate()
    {
        $companyId = $this->getCompanyId();
        
        if (!$companyId) {
            throw new ForbiddenHttpException('Company ID não encontrado no token de autenticação');
        }

        $model = new Maintenance();
        $model->load(Yii::$app->request->bodyParams, '');

        // Verificar se o veículo pertence à empresa do usuário
        if ($model->vehicle_id) {
            $vehicle = \backend\modules\api\models\Vehicle::findOne($model->vehicle_id);
            if (!$vehicle || $vehicle->company_id != $companyId) {
                return $this->errorResponse('Veículo não encontrado ou não pertence à sua empresa', 403);
            }
        }

        if ($model->save()) {
            Yii::$app->response->statusCode = 201;
            return $model;
        }

        return $this->errorResponse('Erro ao criar manutenção', 422, $model->errors);
    }

    /**
     * Atualiza uma manutenção
     * 
     * @param int $id
     * @return Maintenance
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        
        $model->load(Yii::$app->request->bodyParams, '');

        // Se mudando de veículo, verificar se o novo veículo pertence à empresa
        $companyId = $this->getCompanyId();
        if ($model->vehicle_id) {
            $vehicle = \backend\modules\api\models\Vehicle::findOne($model->vehicle_id);
            if (!$vehicle || $vehicle->company_id != $companyId) {
                return $this->errorResponse('Veículo não encontrado ou não pertence à sua empresa', 403);
            }
        }

        if ($model->save()) {
            return $model;
        }

        return $this->errorResponse('Erro ao atualizar manutenção', 422, $model->errors);
    }

    /**
     * Lista manutenções por veículo
     * 
     * @param int $vehicle_id
     * @return ActiveDataProvider
     */
    public function actionByVehicle($vehicle_id)
    {
        $companyId = $this->getCompanyId();
        
        // Verificar se o veículo pertence à empresa
        $vehicle = \backend\modules\api\models\Vehicle::findOne($vehicle_id);
        if (!$vehicle || $vehicle->company_id != $companyId) {
            throw new NotFoundHttpException('Veículo não encontrado');
        }

        $query = Maintenance::find()->where(['vehicle_id' => $vehicle_id]);
        
        // Filtros opcionais
        $request = Yii::$app->request;
        if ($tipo = $request->get('tipo')) {
            $query->andWhere(['tipo' => $tipo]);
        }
        if ($estado = $request->get('estado')) {
            $query->andWhere(['estado' => $estado]);
        }

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => $request->get('per-page', 20)],
            'sort' => ['defaultOrder' => ['data_manutencao' => SORT_DESC]],
        ]);
    }

    /**
     * Lista manutenções por estado
     * 
     * @param string $estado
     * @return ActiveDataProvider
     */
    public function actionByStatus($estado)
    {
        $companyId = $this->getCompanyId();
        
        if (!$companyId) {
            throw new ForbiddenHttpException('Company ID não encontrado no token de autenticação');
        }

        $query = Maintenance::find()
            ->joinWith('vehicle')
            ->where(['{{%vehicles}}.company_id' => $companyId])
            ->andWhere(['{{%maintenances}}.estado' => $estado]);

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => Yii::$app->request->get('per-page', 20)],
            'sort' => ['defaultOrder' => ['data_manutencao' => SORT_DESC]],
        ]);
    }

    /**
     * Agenda uma manutenção
     * 
     * @param int $id
     * @return Maintenance
     */
    public function actionSchedule($id)
    {
        $model = $this->findModel($id);
        
        $scheduleData = Yii::$app->request->bodyParams;
        
        if (isset($scheduleData['scheduled_date'])) {
            $model->data_manutencao = $scheduleData['scheduled_date'];
        }
        
        if (isset($scheduleData['priority'])) {
            $model->observacoes = ($model->observacoes ? $model->observacoes . '; ' : '') 
                                . 'Prioridade: ' . $scheduleData['priority'];
        }
        
        if (isset($scheduleData['assigned_technician'])) {
            $model->fornecedor = $scheduleData['assigned_technician'];
        }

        $model->estado = 'agendada';

        if ($model->save()) {
            return $model;
        }

        return $this->errorResponse('Erro ao agendar manutenção', 422, $model->errors);
    }

    /**
     * Relatório de manutenções mensais
     * 
     * @return array
     */
    public function actionReportsMonthly()
    {
        $companyId = $this->getCompanyId();
        
        if (!$companyId) {
            throw new ForbiddenHttpException('Company ID não encontrado no token de autenticação');
        }

        $request = Yii::$app->request;
        $year = $request->get('year', date('Y'));
        $month = $request->get('month', date('m'));

        $startDate = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01';
        $endDate = date('Y-m-t', strtotime($startDate));

        $query = Maintenance::find()
            ->joinWith('vehicle')
            ->where(['{{%vehicles}}.company_id' => $companyId])
            ->andWhere(['between', 'data_manutencao', $startDate, $endDate]);

        $maintenances = $query->all();

        return [
            'period' => [
                'year' => $year,
                'month' => $month,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'summary' => [
                'total_maintenances' => count($maintenances),
                'total_cost' => array_sum(array_map(function($m) { return $m->custo; }, $maintenances)),
                'by_type' => $this->groupMaintenancesByType($maintenances),
                'by_status' => $this->groupMaintenancesByStatus($maintenances),
            ],
            'maintenances' => $maintenances,
        ];
    }

    /**
     * Relatório de custos de manutenção
     * 
     * @return array
     */
    public function actionReportsCosts()
    {
        $companyId = $this->getCompanyId();
        
        if (!$companyId) {
            throw new ForbiddenHttpException('Company ID não encontrado no token de autenticação');
        }

        $request = Yii::$app->request;
        $startDate = $request->get('start_date', date('Y-m-01'));
        $endDate = $request->get('end_date', date('Y-m-t'));

        $query = Maintenance::find()
            ->joinWith('vehicle')
            ->where(['{{%vehicles}}.company_id' => $companyId])
            ->andWhere(['between', 'data_manutencao', $startDate, $endDate]);

        $maintenances = $query->all();

        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'costs' => [
                'total_cost' => array_sum(array_map(function($m) { return $m->custo; }, $maintenances)),
                'average_cost' => count($maintenances) > 0 ? array_sum(array_map(function($m) { return $m->custo; }, $maintenances)) / count($maintenances) : 0,
                'by_vehicle' => $this->groupMaintenancesByVehicle($maintenances),
                'by_type' => $this->groupMaintenancesCostByType($maintenances),
            ],
            'total_maintenances' => count($maintenances),
        ];
    }

    /**
     * Estatísticas gerais de manutenções
     * 
     * @return array
     */
    public function actionStats()
    {
        $companyId = $this->getCompanyId();
        
        if (!$companyId) {
            throw new ForbiddenHttpException('Company ID não encontrado no token de autenticação');
        }

        $totalQuery = Maintenance::find()
            ->joinWith('vehicle')
            ->where(['{{%vehicles}}.company_id' => $companyId]);

        $pendingQuery = clone $totalQuery;
        $pendingQuery->andWhere(['estado' => 'agendada']);

        $completedQuery = clone $totalQuery;
        $completedQuery->andWhere(['estado' => 'concluida']);

        return [
            'total_maintenances' => $totalQuery->count(),
            'pending_maintenances' => $pendingQuery->count(),
            'completed_maintenances' => $completedQuery->count(),
            'total_cost' => $totalQuery->sum('custo') ?? 0,
            'average_cost' => $totalQuery->average('custo') ?? 0,
            'maintenances_by_type' => $this->getMaintenancesByType($companyId),
            'recent_maintenances' => $totalQuery
                ->orderBy(['data_manutencao' => SORT_DESC])
                ->limit(10)
                ->all(),
        ];
    }

    /**
     * Busca modelo por ID e verifica acesso
     * 
     * @param int $id
     * @return Maintenance
     * @throws NotFoundHttpException|ForbiddenHttpException
     */
    protected function findModel($id)
    {
        $companyId = $this->getCompanyId();
        
        if (!$companyId) {
            throw new ForbiddenHttpException('Company ID não encontrado no token de autenticação');
        }

        $model = Maintenance::find()
            ->joinWith('vehicle')
            ->where(['{{%maintenances}}.id' => $id])
            ->andWhere(['{{%vehicles}}.company_id' => $companyId])
            ->one();

        if ($model === null) {
            throw new NotFoundHttpException('Manutenção não encontrada');
        }

        return $model;
    }

    /**
     * Agrupa manutenções por tipo
     * 
     * @param array $maintenances
     * @return array
     */
    private function groupMaintenancesByType($maintenances)
    {
        $grouped = [];
        foreach ($maintenances as $maintenance) {
            $type = $maintenance->tipo;
            if (!isset($grouped[$type])) {
                $grouped[$type] = 0;
            }
            $grouped[$type]++;
        }
        return $grouped;
    }

    /**
     * Agrupa manutenções por estado
     * 
     * @param array $maintenances
     * @return array
     */
    private function groupMaintenancesByStatus($maintenances)
    {
        $grouped = [];
        foreach ($maintenances as $maintenance) {
            $status = $maintenance->estado;
            if (!isset($grouped[$status])) {
                $grouped[$status] = 0;
            }
            $grouped[$status]++;
        }
        return $grouped;
    }

    /**
     * Agrupa manutenções por veículo
     * 
     * @param array $maintenances
     * @return array
     */
    private function groupMaintenancesByVehicle($maintenances)
    {
        $grouped = [];
        foreach ($maintenances as $maintenance) {
            $vehicleId = $maintenance->vehicle_id;
            if (!isset($grouped[$vehicleId])) {
                $grouped[$vehicleId] = [
                    'vehicle' => $maintenance->vehicle ? [
                        'id' => $maintenance->vehicle->id,
                        'license_plate' => $maintenance->vehicle->license_plate,
                        'brand' => $maintenance->vehicle->brand,
                        'model' => $maintenance->vehicle->model,
                    ] : null,
                    'total_cost' => 0,
                    'maintenance_count' => 0,
                ];
            }
            $grouped[$vehicleId]['total_cost'] += $maintenance->custo;
            $grouped[$vehicleId]['maintenance_count']++;
        }
        return array_values($grouped);
    }

    /**
     * Agrupa custos de manutenções por tipo
     * 
     * @param array $maintenances
     * @return array
     */
    private function groupMaintenancesCostByType($maintenances)
    {
        $grouped = [];
        foreach ($maintenances as $maintenance) {
            $type = $maintenance->tipo;
            if (!isset($grouped[$type])) {
                $grouped[$type] = [
                    'total_cost' => 0,
                    'count' => 0,
                ];
            }
            $grouped[$type]['total_cost'] += $maintenance->custo;
            $grouped[$type]['count']++;
        }
        
        // Calcular média por tipo
        foreach ($grouped as &$group) {
            $group['average_cost'] = $group['count'] > 0 ? $group['total_cost'] / $group['count'] : 0;
        }
        
        return $grouped;
    }

    /**
     * Obtém estatísticas de manutenções por tipo
     * 
     * @param int $companyId
     * @return array
     */
    private function getMaintenancesByType($companyId)
    {
        $query = "
            SELECT m.tipo, COUNT(*) as count, COALESCE(SUM(m.custo), 0) as total_cost
            FROM {{%maintenances}} m
            JOIN {{%vehicles}} v ON m.vehicle_id = v.id
            WHERE v.company_id = :companyId
            GROUP BY m.tipo
        ";

        return Yii::$app->db->createCommand($query, [':companyId' => $companyId])->queryAll();
    }
}
