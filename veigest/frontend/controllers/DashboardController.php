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