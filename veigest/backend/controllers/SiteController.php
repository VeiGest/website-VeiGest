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
        // Clear session and identity
        Yii::$app->user->logout();
        
        // Get cookie domain for cross-subdomain clearing
        $serverHost = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
        $isLocalhost = strpos($serverHost, 'localhost') !== false || strpos($serverHost, '127.0.0.1') !== false;
        $cookieDomain = $isLocalhost ? '' : '.dryadlang.org';
        
        // Clear identity cookie
        $cookies = Yii::$app->response->cookies;
        $cookies->remove('_identity-frontend');
        
        // Also expire the cookie directly in case remove doesn't work across domains
        if ($cookieDomain) {
            setcookie('_identity-frontend', '', time() - 3600, '/', $cookieDomain);
            setcookie('PHPSESSID', '', time() - 3600, '/', $cookieDomain);
            setcookie('_csrf-frontend', '', time() - 3600, '/', $cookieDomain);
            setcookie('_csrf-backend', '', time() - 3600, '/', $cookieDomain);
        } else {
            setcookie('_identity-frontend', '', time() - 3600, '/');
            setcookie('PHPSESSID', '', time() - 3600, '/');
            setcookie('_csrf-frontend', '', time() - 3600, '/');
            setcookie('_csrf-backend', '', time() - 3600, '/');
        }
        
        // Destroy session completely
        Yii::$app->session->destroy();

        return $this->redirect(['site/login']);
    }
}
