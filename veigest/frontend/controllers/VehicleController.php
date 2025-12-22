<?php

namespace frontend\controllers;

use Yii;
use frontend\models\Vehicle;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class VehicleController extends Controller
{
    public $layout = 'dashboard';

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    // Index action requires vehicles.view permission
                    [
                        'allow' => true,
                        'actions' => ['index'],
                        'matchCallback' => function($rule, $action) {
                            return Yii::$app->user->can('vehicles.view');
                        },
                    ],
                    // Create action requires vehicles.create permission
                    [
                        'allow' => true,
                        'actions' => ['create'],
                        'matchCallback' => function($rule, $action) {
                            return Yii::$app->user->can('vehicles.create');
                        },
                    ],
                    // View action requires vehicles.view permission
                    [
                        'allow' => true,
                        'actions' => ['view'],
                        'matchCallback' => function($rule, $action) {
                            return Yii::$app->user->can('vehicles.view');
                        },
                    ],
                    // Update action requires vehicles.update permission
                    [
                        'allow' => true,
                        'actions' => ['update'],
                        'matchCallback' => function($rule, $action) {
                            return Yii::$app->user->can('vehicles.update');
                        },
                    ],
                    // Delete action requires vehicles.delete permission
                    [
                        'allow' => true,
                        'actions' => ['delete'],
                        'matchCallback' => function($rule, $action) {
                            return Yii::$app->user->can('vehicles.delete');
                        },
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lista de veículos
     */
    public function actionIndex()
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

        return $this->render('/dashboard/vehicles', [
            'dataProvider' => $dataProvider,
            'model' => new Vehicle(),
        ]);
    }

    /**
     * Criar veículo
     */
    public function actionCreate()
    {
        $model = new Vehicle();
        $model->company_id = Yii::$app->user->identity->company_id;

        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                if ($model->save()) {
                    Yii::$app->session->setFlash('success', 'Veículo criado com sucesso.');
                    return $this->redirect(['dashboard/vehicles']);
                } else {
                    // Debug: mostrar erros
                    Yii::error('Vehicle save failed: ' . json_encode($model->errors), 'vehicle-create');
                }
            }
        }

        $this->layout = 'dashboard';
        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Visualizar veículo
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        $this->layout = 'dashboard';
        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Atualizar veículo
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Veículo atualizado com sucesso.');
            return $this->redirect(['dashboard/vehicles']);
        }

        $this->layout = 'dashboard';
        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Apagar veículo
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', 'Veículo removido.');
        return $this->redirect(['dashboard/vehicles']);
    }

    protected function findModel($id)
    {
        $model = Vehicle::findOne([
            'id' => $id,
            'company_id' => Yii::$app->user->identity->company_id,
        ]);

        if ($model !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Veículo não encontrado.');
    }
}
