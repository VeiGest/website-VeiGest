<?php

namespace frontend\controllers;

use Yii;
use frontend\models\Vehicle;
use frontend\models\Maintenance;
use frontend\models\FuelLog;
use common\models\Document;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * VehicleController - Gestão de Veículos
 * 
 * Implementa os requisitos:
 * - RF-FO-004: Consulta de Veículos
 * - RF-BO-005: Gestão de Veículos
 */
class VehicleController extends Controller
{
    public $layout = 'dashboard';

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    // RF-FO-004.1: Lista de veículos - vehicles.view
                    [
                        'allow' => true,
                        'actions' => ['index'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->can('vehicles.view');
                        },
                    ],
                    // RF-FO-004.2, RF-FO-004.3: Detalhes e estado - vehicles.view
                    [
                        'allow' => true,
                        'actions' => ['view', 'history', 'documents'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->can('vehicles.view');
                        },
                    ],
                    // RF-BO-005.1: Registo de veículos - vehicles.create
                    [
                        'allow' => true,
                        'actions' => ['create'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->can('vehicles.create');
                        },
                    ],
                    // RF-BO-005.2, RF-BO-005.3: Edição e gestão de estado - vehicles.update
                    [
                        'allow' => true,
                        'actions' => ['update'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->can('vehicles.update');
                        },
                    ],
                    // RF-BO-005.5: Atribuição a condutores - vehicles.assign
                    [
                        'allow' => true,
                        'actions' => ['assign'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->can('vehicles.assign');
                        },
                    ],
                    // Eliminar veículo - vehicles.delete
                    [
                        'allow' => true,
                        'actions' => ['delete'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->can('vehicles.delete');
                        },
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['post'],
                    'assign' => ['post'],
                ],
            ],
        ];
    }

    /**
     * RF-FO-004.1: Lista de veículos
     * Condutores vêem apenas os seus veículos atribuídos
     * Gestores vêem todos os veículos da empresa
     */
    public function actionIndex()
    {
        $companyId = Yii::$app->user->identity->company_id;
        $query = Vehicle::find()->where(['company_id' => $companyId]);

        // Se for condutor, filtrar apenas veículos atribuídos
        if (Yii::$app->user->can('condutor') && !Yii::$app->user->can('vehicles.create')) {
            $query->andWhere(['driver_id' => Yii::$app->user->id]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 10],
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC],
                'attributes' => ['id', 'license_plate', 'brand', 'status', 'created_at'],
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'model' => new Vehicle(),
        ]);
    }

    /**
     * RF-BO-005.1: Registo de veículos
     */
    public function actionCreate()
    {
        $model = new Vehicle();
        $model->company_id = Yii::$app->user->identity->company_id;
        $model->status = Vehicle::STATUS_ATIVO;
        $model->mileage = 0;

        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                Yii::$app->session->setFlash('success', 'Veículo criado com sucesso.');
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                Yii::error('Vehicle save failed: ' . json_encode($model->errors), 'vehicle');
            }
        }

        $drivers = Vehicle::getAvailableDrivers($model->company_id);

        return $this->render('create', [
            'model' => $model,
            'drivers' => $drivers,
        ]);
    }

    /**
     * RF-FO-004.2, RF-FO-004.3: Detalhes técnicos e estado do veículo
     * @param int $id
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        // RF-FO-004.4: Histórico de utilizações (últimas 5 manutenções)
        $maintenancesProvider = new ActiveDataProvider([
            'query' => $model->getMaintenances(),
            'pagination' => ['pageSize' => 5],
        ]);

        // RF-FO-004.5: Documentação associada
        $documentsProvider = new ActiveDataProvider([
            'query' => $model->getDocuments(),
            'pagination' => ['pageSize' => 5],
        ]);

        // Registos de combustível
        $fuelLogsProvider = new ActiveDataProvider([
            'query' => $model->getFuelLogs(),
            'pagination' => ['pageSize' => 5],
        ]);

        // Sumário de custos
        $costSummary = $model->getCostSummary();

        return $this->render('view', [
            'model' => $model,
            'maintenancesProvider' => $maintenancesProvider,
            'documentsProvider' => $documentsProvider,
            'fuelLogsProvider' => $fuelLogsProvider,
            'costSummary' => $costSummary,
        ]);
    }

    /**
     * RF-FO-004.4: Histórico completo de utilizações
     * @param int $id
     */
    public function actionHistory($id)
    {
        $model = $this->findModel($id);

        // Todas as manutenções
        $maintenancesProvider = new ActiveDataProvider([
            'query' => $model->getMaintenances(),
            'pagination' => ['pageSize' => 20],
        ]);

        // Todos os abastecimentos
        $fuelLogsProvider = new ActiveDataProvider([
            'query' => $model->getFuelLogs(),
            'pagination' => ['pageSize' => 20],
        ]);

        // Todas as rotas
        $routesProvider = new ActiveDataProvider([
            'query' => $model->getRoutes(),
            'pagination' => ['pageSize' => 20],
        ]);

        return $this->render('history', [
            'model' => $model,
            'maintenancesProvider' => $maintenancesProvider,
            'fuelLogsProvider' => $fuelLogsProvider,
            'routesProvider' => $routesProvider,
        ]);
    }

    /**
     * RF-FO-004.5: Documentação associada
     * @param int $id
     */
    public function actionDocuments($id)
    {
        $model = $this->findModel($id);

        $documentsProvider = new ActiveDataProvider([
            'query' => $model->getDocuments(),
            'pagination' => ['pageSize' => 20],
        ]);

        return $this->render('documents', [
            'model' => $model,
            'documentsProvider' => $documentsProvider,
        ]);
    }

    /**
     * RF-BO-005.2, RF-BO-005.3: Edição técnica e gestão de estado
     * @param int $id
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Veículo atualizado com sucesso.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        $drivers = Vehicle::getAvailableDrivers($model->company_id);

        return $this->render('update', [
            'model' => $model,
            'drivers' => $drivers,
        ]);
    }

    /**
     * RF-BO-005.5: Atribuição a condutores (via POST)
     * @param int $id
     */
    public function actionAssign($id)
    {
        $model = $this->findModel($id);
        $driverId = Yii::$app->request->post('driver_id');

        if ($driverId !== null) {
            $model->driver_id = $driverId ?: null;
            if ($model->save(false, ['driver_id'])) {
                Yii::$app->session->setFlash('success', 'Condutor atribuído com sucesso.');
            } else {
                Yii::$app->session->setFlash('error', 'Erro ao atribuir condutor.');
            }
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Eliminar veículo
     * @param int $id
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        
        // Verificar se tem manutenções ou documentos
        if ($model->getMaintenances()->count() > 0 || $model->getDocuments()->count() > 0) {
            Yii::$app->session->setFlash('error', 'Não é possível eliminar veículo com manutenções ou documentos associados.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        $model->delete();
        Yii::$app->session->setFlash('success', 'Veículo removido com sucesso.');
        return $this->redirect(['index']);
    }

    /**
     * Encontra o model de veículo pelo ID
     * @param int $id
     * @return Vehicle
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        $companyId = Yii::$app->user->identity->company_id;
        $model = Vehicle::findOne([
            'id' => $id,
            'company_id' => $companyId,
        ]);

        if ($model !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Veículo não encontrado.');
    }
}
