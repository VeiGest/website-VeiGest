<?php

namespace backend\controllers;

use Yii; 
use common\models\User;
use backend\models\UserSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

class UserController extends Controller
{
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::class,
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lista utilizadores
     */
    public function actionIndex()
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Ver utilizador
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Criar utilizador
     */
    public function actionCreate()
    {
        
        $model = new User(['scenario' => 'adminCreate']);

        $roles = [
            'admin'    => 'Admin',
            'gestor'   => 'Gestor',
            'condutor' => 'Condutor',
        ];

        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            $role = $post['User']['role'] ?? null;
            unset($post['User']['role']);
            $model->role = $role;

            if ($model->load($post) && $model->save()) {

                // RBAC: atribuir role 
                $auth = Yii::$app->authManager;

                // remover roles antigas 
                $auth->revokeAll($model->id);

                // atribuir nova role
                if ($role) {
                    $roleObj = $auth->getRole($role);
                    if ($roleObj) {
                        $auth->assign($roleObj, $model->id);
                    }
                }

                return $this->redirect(['index']);
            }
        }

        return $this->render('create', [
            'model' => $model,
            'roles' => $roles,
        ]);
    }

    /**
     * Atualizar utilizador
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->scenario = 'update';

        $roles = [
            'admin'    => 'Admin',
            'gestor'   => 'Gestor',
            'condutor' => 'Condutor',
        ];

        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            $role = $post['User']['role'] ?? null;
            unset($post['User']['role']);
            $model->role = $role;

            if ($model->load($post) && $model->save()) {

                //RBAC: atualizar role 
                $auth = Yii::$app->authManager;

                // remover roles antigas
                $auth->revokeAll($model->id);

                // atribuir nova role
                if ($role) {
                    $roleObj = $auth->getRole($role);
                    if ($roleObj) {
                        $auth->assign($roleObj, $model->id);
                    }
                }

                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', [
            'model' => $model,
            'roles' => $roles,
        ]);
    }

    /**
     * Apagar utilizador
     */
    public function actionDelete($id)
    {
        $user = $this->findModel($id);

        Yii::$app->authManager->revokeAll($id);

        $user->delete();

        return $this->redirect(['index']);
    }

    /**
     * Encontra modelo User pelo ID
     */
    protected function findModel($id)
    {
        if (($model = User::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('O utilizador não existe.');
    }
}
