<?php

namespace frontend\controllers;

use frontend\models\ResendVerificationEmailForm;
use frontend\models\VerifyEmailForm;
use Yii;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use frontend\models\ContactForm;
use frontend\models\TicketForm;

/**
 * Site controller
 */
class SiteController extends Controller
{
    public function beforeAction($action)
    {
        // Use error layout for error pages (cleaner 403/404/500 pages)
        if ($action->id === 'error') {
            $this->layout = 'error';
        }
        return parent::beforeAction($action);
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout', 'signup', 'ticket'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['ticket'],
                        'allow' => true,
                        'roles' => ['@'], // Qualquer utilizador autenticado
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
            'captcha' => [
                'class' => \yii\captcha\CaptchaAction::class,
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Logs in a user.
     *
     * @return mixed
     */
    public function actionLogin()
    {
        $this->layout = 'login';

        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new \common\models\LoginForm();

        if ($model->load(Yii::$app->request->post())) {
            Yii::info('Login form loaded: username=' . $model->username, 'login');
            
            try {
                $loginResult = $model->login();
                Yii::info('Login result: ' . ($loginResult ? 'true' : 'false'), 'login');
                
                if ($loginResult) {
                    $user = Yii::$app->user->identity;
                    Yii::info('Login successful for user: ' . $user->id . ' role: ' . $user->role, 'login');

                    // Redirect based on role (using user->role field directly)
                    $role = $user->role;
                    
                    if ($role === 'admin') {
                        Yii::info('User is admin - redirecting to /site/index', 'login');
                        return $this->redirect(['/site/index']);
                    }

                    if ($role === 'manager') {
                        Yii::info('User is manager - redirecting to /dashboard/index', 'login');
                        return $this->redirect(['/dashboard/index']);
                    }

                    if ($role === 'driver') {
                        Yii::info('User is driver - redirecting to /dashboard/index', 'login');
                        return $this->redirect(['/dashboard/index']);
                    }

                    Yii::info('User has unknown role - using goHome()', 'login');
                    return $this->goHome();
                } else {
                    Yii::warning('Login failed - validation errors: ' . json_encode($model->errors), 'login');
                }
            } catch (\Exception $e) {
                Yii::error('Login threw exception: ' . $e->getMessage() . ' | ' . $e->getTraceAsString(), 'login');
                throw $e;
            }
        }

        return $this->render('login', ['model' => $model]);
    }

    /**
     * Logs out the current user.
     *
     * @return mixed
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return mixed
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail(Yii::$app->params['adminEmail'])) {
                Yii::$app->session->setFlash('success', 'Thank you for contacting us. We will respond to you as soon as possible.');
            } else {
                Yii::$app->session->setFlash('error', 'There was an error sending your message.');
            }

            return $this->refresh();
        }

        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return mixed
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post()) && $model->signup()) {
            Yii::$app->session->setFlash('success', 'Thank you for registration. Please check your inbox for verification email.');
            return $this->goHome();
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /**
     * Requests password reset.
     *
     * @return mixed
     */
    public function actionRequestPasswordReset()
    {

        $this->layout = 'login';
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');

                return $this->goHome();
            }

            Yii::$app->session->setFlash('error', 'Sorry, we are unable to reset password for the provided email address.');
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    /**
     * Resets password.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token)
    {

        $this->layout = 'login';
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', 'New password saved.');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }

    /**
     * Verify email address
     *
     * @param string $token
     * @throws BadRequestHttpException
     * @return mixed
     */
    public function actionVerifyEmail($token)
    {
        \Yii::info(['route' => 'verify-email', 'token' => $token], __METHOD__);
        try {
            $model = new VerifyEmailForm($token);
        } catch (InvalidArgumentException $e) {
            \Yii::warning(['verify-email' => 'invalid', 'reason' => $e->getMessage(), 'token' => $token], __METHOD__);
            throw new BadRequestHttpException($e->getMessage());
        }
        if ($model->verifyEmail()) {
            Yii::$app->session->setFlash('success', 'Your email has been confirmed!');
            \Yii::info(['verify-email' => 'success', 'token' => $token], __METHOD__);
            return $this->render('verifyEmailSuccess');
        }

        Yii::$app->session->setFlash('error', 'Sorry, we are unable to verify your account with provided token.');
        \Yii::warning(['verify-email' => 'failed-to-save', 'token' => $token], __METHOD__);
        return $this->goHome();
    }

    /**
     * Resend verification email
     *
     * @return mixed
     */
    public function actionResendVerificationEmail()
    {
        $model = new ResendVerificationEmailForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');
                return $this->goHome();
            }
            Yii::$app->session->setFlash('error', 'Sorry, we are unable to resend verification email for the provided email address.');
        }

        return $this->render('resendVerificationEmail', [
            'model' => $model
        ]);
    }

    /**
     * Displays ticket page.
     *
     * @return mixed
     */
    public function actionTicket()
    {
        $model = new TicketForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail(Yii::$app->params['adminEmail'])) {
                Yii::$app->session->setFlash('success', 'Obrigado por entrar em contato. Responderemos o mais breve possível.');
            } else {
                Yii::$app->session->setFlash('error', 'Ocorreu um erro ao enviar sua mensagem.');
            }

            return $this->refresh();
        }

        return $this->render('ticket', [
            'model' => $model,
        ]);
    }

    /**
     * Displays pricing page.
     *
     * @return mixed
     */
    public function actionPricing()
    {
        return $this->render('pricing');
    }

    /**
     * Displays benefits page.
     *
     * @return mixed
     */
    public function actionBenefits()
    {
        return $this->render('bennefits');
    }

    /**
     * Displays services page.
     *
     * @return mixed
     */
    public function actionServices()
    {
        return $this->render('services');
    }

    /**
     * Redirects to dashboard.
     *
     * @return mixed
     */
    public function actionDashboard()
    {
        return $this->redirect(['dashboard/index']);
    }
}
