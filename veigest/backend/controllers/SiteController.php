<?php

namespace backend\controllers;

use common\models\LoginForm;
use Yii;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */public function behaviors()
{
    return [
        'access' => [
            'class' => AccessControl::class,
            'only' => ['login', 'logout', 'index', 'test-session'], // <- adicionamos aqui
            'rules' => [

                // Liberado para TODOS (sem login)
                [
                    'actions' => ['login', 'error', 'test-session'], // <- adicionamos aqui
                    'allow' => true,
                    'roles' => ['?'], 
                ],

                // Apenas ADMIN pode ver o backend
                [
                    'actions' => ['index', 'logout'],
                    'allow' => true,
                    'roles' => ['admin'], 
                ],
            ],
        ],

        'verbs' => [
            'class' => VerbFilter::class,
            'actions' => [
                'logout' => ['post'],
            ],
        ],
    ];
}




    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => \yii\web\ErrorAction::class,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return string|Response
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
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

 public function actionTestSession()
{
    echo "<pre>";

    echo "SESSION ID (PHP): " . session_id() . "\n\n";

    echo "RAW PHP SESSION:\n";
    print_r($_SESSION);

    echo "\nYii Session (keys):\n";
    print_r(Yii::$app->session->getAllFlashes()); // apenas para ver algo

    echo "\nUSER IDENTITY:\n";
    print_r(Yii::$app->user->identity);

    echo "</pre>";
    exit;
}


}
