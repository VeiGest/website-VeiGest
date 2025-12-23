<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;

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
        $user = Yii::$app->user->identity;
        $companyId = $user->company_id ?? 1;

        // Veículos
        $totalVehicles = \common\models\Vehicle::find()->where(['company_id' => $companyId])->count();

        // Condutores ativos (role assignment)
        $totalDrivers = (int) Yii::$app->db->createCommand("SELECT COUNT(*) FROM {{%auth_assignment}} a JOIN {{%users}} u ON a.user_id = u.id WHERE a.item_name = :role AND u.company_id = :companyId AND u.estado = 'ativo'")
            ->bindValues([':role' => 'condutor', ':companyId' => $companyId])->queryScalar();

        // Alertas
        $alertStats = \common\models\Alert::getStatsByCompany($companyId);
        $activeAlerts = $alertStats['active'] ?? 0;

        // Custos de manutenção do mês
        $startMonth = date('Y-m-01');
        $endMonth = date('Y-m-t');
        $maintenanceStats = \common\models\Maintenance::getStatsByCompany($companyId, $startMonth, $endMonth);
        $monthlyCost = $maintenanceStats['total_cost'] ?? 0;

        // Consumo mensal (12 meses) para gráfico
        $fuelMonthly = \common\models\FuelLog::getMonthlyConsumption($companyId, 12);
        if (empty($fuelMonthly)) {
            // Gerar últimos 12 meses com zeros
            $fuelMonthly = [];
            for ($i = 11; $i >= 0; $i--) {
                $m = date('Y-m', strtotime("-{$i} months"));
                $label = date('M/Y', strtotime("-{$i} months"));
                $fuelMonthly[] = ['month' => $m, 'month_label' => $label, 'total_liters' => 0, 'total_value' => 0, 'count' => 0];
            }
        }

        // Estado da frota (usa chaves do DB: active, maintenance, inactive)
        $stateRows = Yii::$app->db->createCommand("SELECT status, COUNT(*) as cnt FROM {{%vehicles}} WHERE company_id = :companyId GROUP BY status")->bindValue(':companyId', $companyId)->queryAll();
        $fleetState = ['active' => 0, 'maintenance' => 0, 'inactive' => 0];
        foreach ($stateRows as $r) {
            $key = $r['status'];
            if (!isset($fleetState[$key])) {
                $fleetState[$key] = 0;
            }
            $fleetState[$key] = (int)$r['cnt'];
        }

        // Alertas recentes
        $recentAlerts = \common\models\Alert::getRecent($companyId, 5);

        return $this->render('index', [
            'totalVehicles' => (int)$totalVehicles,
            'totalDrivers' => (int)$totalDrivers,
            'activeAlerts' => (int)$activeAlerts,
            'monthlyCost' => (float)$monthlyCost,
            'fuelMonthly' => $fuelMonthly,
            'fleetState' => $fleetState,
            'recentAlerts' => $recentAlerts,
        ]);
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
     * Redireciona para o DocumentController que implementa CRUD completo.
     *
     * @return \yii\web\Response
     */
    public function actionDocuments()
    {
        return $this->redirect(['document/index']);
    }

    /**
     * Displays drivers page.
     *
     * @return string
     */
    public function actionDrivers()
    {
        return $this->render('drivers');
    }

    /**
     * Displays maintenance page.
     *
     * @return string
     */
    public function actionMaintenance()
    {
        return $this->render('maintenance');
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
        return $this->render('vehicles');
    }
}