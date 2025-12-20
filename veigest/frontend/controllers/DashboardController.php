<?php

namespace frontend\controllers;

use Yii;
use frontend\models\Vehicle;
use frontend\models\Driver;
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
