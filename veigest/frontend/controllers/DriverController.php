<?php

namespace frontend\controllers;

use Yii;
use frontend\models\Driver;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class DriverController extends Controller
{
    public $layout = 'dashboard';

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    // Index action requires drivers.view permission
                    [
                        'allow' => true,
                        'actions' => ['index'],
                        'matchCallback' => function($rule, $action) {
                            return Yii::$app->user->can('drivers.view');
                        },
                    ],
                    // Create action requires drivers.create permission
                    [
                        'allow' => true,
                        'actions' => ['create'],
                        'matchCallback' => function($rule, $action) {
                            return Yii::$app->user->can('drivers.create');
                        },
                    ],
                    // View action requires drivers.view permission
                    [
                        'allow' => true,
                        'actions' => ['view'],
                        'matchCallback' => function($rule, $action) {
                            return Yii::$app->user->can('drivers.view');
                        },
                    ],
                    // Update action requires drivers.update permission
                    [
                        'allow' => true,
                        'actions' => ['update'],
                        'matchCallback' => function($rule, $action) {
                            return Yii::$app->user->can('drivers.update');
                        },
                    ],
                    // Delete action requires drivers.delete permission
                    [
                        'allow' => true,
                        'actions' => ['delete'],
                        'matchCallback' => function($rule, $action) {
                            return Yii::$app->user->can('drivers.delete');
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
     * Lista de condutores
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Driver::find()->where(['company_id' => $this->getCompanyId()]),
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC],
            ],
        ]);
        return $this->render('/dashboard/drivers', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Criar condutor
     */
    public function actionCreate()
    {
        $model = new Driver();
        $model->company_id = $this->getCompanyId();
        $model->estado = Driver::STATUS_ACTIVE;

        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                if (!empty($model->password)) {
                    $model->setPassword($model->password);
                }
                // Auto-gerar username se não existir
                if (empty($model->username)) {
                    $base = !empty($model->email) ? strstr($model->email, '@', true) : null;
                    if (!$base) {
                        $base = !empty($model->nome) ? strtolower(preg_replace('/[^a-z0-9]+/i', '-', $model->nome)) : 'condutor';
                    }
                    $username = $base;
                    $suffix = 1;
                    while (\common\models\User::find()->where(['username' => $username, 'company_id' => $model->company_id])->exists()) {
                        $username = $base . '-' . $suffix++;
                    }
                    $model->username = $username;
                }
                // auth_key para sessão/remember-me
                if (empty($model->auth_key)) {
                    $model->auth_key = Yii::$app->security->generateRandomString();
                }
                // Role espelhada
                $model->role = 'condutor';

                if ($model->save()) {
                    // Atribuir RBAC 'condutor'
                    $auth = Yii::$app->authManager;
                    $role = $auth->getRole('condutor');
                    if ($role && !$auth->getAssignment('condutor', $model->id)) {
                        $auth->assign($role, $model->id);
                    }
                    Yii::$app->session->setFlash('success', 'Condutor criado com sucesso.');
                    return $this->redirect(['dashboard/drivers']);
                } else {
                    Yii::error('Driver save failed: ' . json_encode($model->errors), 'driver-create');
                }
            }
        }

        $this->layout = 'dashboard';
        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Visualizar condutor
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
     * Atualizar condutor
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                if (!empty($model->password)) {
                    $model->setPassword($model->password);
                }
                if ($model->save()) {
                    // Garantir role 'condutor' na coluna
                    if ($model->role !== 'condutor') {
                        $model->role = 'condutor';
                        $model->save(false, ['role']);
                    }
                    // Garantir atribuição RBAC
                    $auth = Yii::$app->authManager;
                    if (!$auth->getAssignment('condutor', $model->id)) {
                        $role = $auth->getRole('condutor');
                        if ($role) { $auth->assign($role, $model->id); }
                    }
                    Yii::$app->session->setFlash('success', 'Condutor atualizado com sucesso.');
                    return $this->redirect(['dashboard/drivers']);
                }
            }
        }

        $this->layout = 'dashboard';
        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Apagar condutor
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', 'Condutor removido.');
        return $this->redirect(['dashboard/drivers']);
    }

    protected function findModel($id)
    {
        $model = Driver::findOne([
            'id' => $id,
            'company_id' => $this->getCompanyId(),
        ]);

        if ($model !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Condutor não encontrado.');
    }

    private function getCompanyId()
    {
        $identity = Yii::$app->user->identity;
        if ($identity instanceof \common\models\User) {
            return $identity->getAttribute('company_id');
        }
        return null;
    }
}
