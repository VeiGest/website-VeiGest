<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;

class CondutorController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        // só condutor
                        'allow' => true,
                        'roles' => ['condutor'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Dashboard do condutor (simples)
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionVeiculo()
    {
        return $this->render('veiculo');
    }

    public function actionDocumentos()
    {
        return $this->render('documentos');
    }

    public function actionAlertas()
    {
        return $this->render('alertas');
    }
}
