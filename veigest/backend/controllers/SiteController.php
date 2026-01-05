<?php

namespace backend\controllers;

use common\models\LoginForm;
use Yii;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\web\ForbiddenHttpException;

/**
 * SiteController - Backend Main Controller
 * 
 * Access Control:
 * - Admin: FULL ACCESS
 * - Manager: NO ACCESS (403 Forbidden)
 * - Driver: NO ACCESS (403 Forbidden)
 */
class SiteController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    // Public actions (login, error)
                    [
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    // Backend access: admin only
                    [
                        'allow' => true,
                        'roles' => ['admin'],
                    ],
                ],
                // Custom deny callback - use blank layout for 403 errors
                'denyCallback' => function ($rule, $action) {
                    $action->controller->layout = 'blank';
                    throw new ForbiddenHttpException(
                        'You do not have permission to access the backend. Only administrators can access this area.'
                    );
                },
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => \yii\web\ErrorAction::class,
                'layout' => 'blank', // Use blank layout for error pages
            ],
        ];
    }

    /**
     * Displays backend homepage.
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Login action.
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $this->layout = 'blank';

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';

        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->redirect(Yii::getAlias('@frontendUrl') . '/site/login');
    }
}
