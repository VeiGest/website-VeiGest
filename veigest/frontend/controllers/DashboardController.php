<?php

namespace frontend\controllers;

use Yii;
use frontend\models\Vehicle;
use frontend\models\Driver;
use frontend\models\Maintenance;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\data\ActiveDataProvider;

/**
 * Dashboard controller
 */
class DashboardController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'], // Apenas usuários logados
                    ],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        $this->layout = 'dashboard';
    }

    /**
     * Displays dashboard homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        // Aqui você pode adicionar lógica para buscar dados do dashboard
        // Por exemplo: estatísticas, alertas recentes, etc.

        return $this->render('index');
    }

    /**
     * Displays alerts page.
     *
     * @return string
     */
    public function actionAlerts()
    {
        return $this->render('alerts');
    }

    /**
     * Displays documents page.
     *
     * @return string
     */
    public function actionDocuments()
    {
        return $this->render('documents');
    }

    /**
     * Displays drivers page.
     *
     * @return string
     */
    public function actionDrivers()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Driver::find()
                ->innerJoin('auth_assignment', 'auth_assignment.user_id = users.id')
                ->where([
                    'auth_assignment.item_name' => 'driver',
                    'users.company_id' => Yii::$app->user->identity->company_id
                ]),
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);

        return $this->render('drivers', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays maintenance page.
     *
     * @return string
     */
    public function actionMaintenance($status = 'scheduled')
    {
        $companyId = Yii::$app->user->identity->company_id;
        
        // Build query based on status filter
        $query = Maintenance::find()
            ->where(['company_id' => $companyId]);
        
        if ($status === 'scheduled') {
            // Agendadas: status='scheduled' E data futura
            $query->andWhere(['status' => 'scheduled'])
                ->andWhere(['>=', 'data', date('Y-m-d')])
                ->orderBy(['data' => SORT_ASC]);
        } elseif ($status === 'completed') {
            // Concluídas
            $query->andWhere(['status' => 'completed'])
                ->orderBy(['data' => SORT_DESC]);
        } elseif ($status === 'overdue') {
            // Atrasadas: status='scheduled' MAS data já passou
            $query->andWhere(['status' => 'scheduled'])
                ->andWhere(['<', 'data', date('Y-m-d')])
                ->orderBy(['data' => SORT_ASC]);
        }
        
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        // Calculate stats
        $allMaintenances = Maintenance::find()
            ->where(['company_id' => $companyId])
            ->all();

        $stats = [
            'scheduled' => 0,
            'completed' => 0,
            'overdue' => 0,
            'totalCost' => 0,
        ];

        foreach ($allMaintenances as $maintenance) {
            if ($maintenance->status === 'scheduled') {
                if ($maintenance->data && strtotime($maintenance->data) < strtotime(date('Y-m-d'))) {
                    $stats['overdue']++;
                } else {
                    $stats['scheduled']++;
                }
            } elseif ($maintenance->status === 'completed') {
                $stats['completed']++;
            }
        }
        
        // Calculate total cost only for the filtered status
        $filteredMaintenances = $dataProvider->query->all();
        foreach ($filteredMaintenances as $maintenance) {
            $stats['totalCost'] += (float)$maintenance->custo;
        }

        return $this->render('maintenance', [
            'dataProvider' => $dataProvider,
            'stats' => $stats,
            'status' => $status,
        ]);
    }

    /**
     * Displays reports page.
     *
     * @return string
     */
    public function actionReports()
    {
        return $this->render('reports');
    }

    /**
     * Displays vehicles page.
     *
     * @return string
     */
    public function actionVehicles()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Vehicle::find()
                ->where(['company_id' => Yii::$app->user->identity->company_id]),
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC],
            ],
        ]);

        return $this->render('vehicles', [
            'dataProvider' => $dataProvider,
        ]);
    }
}
