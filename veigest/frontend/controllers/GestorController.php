<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;

class GestorController extends Controller
{
      public $layout = 'dashboard';   

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function () {
                            return Yii::$app->user->identity->role === 'gestor';
                        }
                    ]
                ]
            ]
        ];
    }

    public function actionIndex()
    {
        $stats = [
            'totalVeiculos' => 42,
            'veiculosAtivos' => 39,
            'veiculosInativos' => 3,
            'manutencoesPendentes' => 5,
            'alertasAtivos' => 12,
            'condutoresAtivos' => 18,
        ];

        return $this->render('index', [
            'stats' => $stats,
        ]);
    }


    /**
     * Frota - lista de veículos
     */
    public function actionFrota()
    {
        return $this->render('frota');
    }

    /**
     * Manutenções
     */
    public function actionManutencoes()
    {
        return $this->render('manutencoes');
    }

    /**
     * Documentos
     */
    public function actionDocumentos()
    {
        return $this->render('documentos');
    }

    /**
     * Alertas
     */
    public function actionAlertas()
    {
        return $this->render('alertas');
    }

    /**
     * Relatórios
     */
    public function actionRelatorios()
    {
        return $this->render('relatorios');
    }
}
