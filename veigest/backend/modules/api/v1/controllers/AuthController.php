<?php

namespace backend\modules\api\v1\controllers;

use Yii;
use yii\rest\Controller;
use yii\filters\Cors;
use yii\filters\VerbFilter;
use common\models\User;

class AuthController extends Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        // CORS
        $behaviors['corsFilter'] = [
            'class' => Cors::class,
        ];

        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'login' => ['POST'],
            ],
        ];

        return $behaviors;
    }

    // POST api/v1/auth/login
    public function actionLogin()
    {
        $body = Yii::$app->request->bodyParams;
        $username = $body['username'] ?? null;
        $password = $body['password'] ?? null;

        if (!$username || !$password) {
            return $this->asJson(['error' => 'username and password required']);
        }

        $user = User::findByUsername($username);
        if (!$user || !$user->validatePassword($password)) {
            return $this->asJson(['error' => 'invalid credentials']);
        }

        // generate auth_key if missing
        if (empty($user->auth_key)) {
            $user->generateAuthKey();
            $user->save(false);
        }

        return $this->asJson([
            'access_token' => $user->auth_key,
            'user' => [
                'id' => $user->id,
                'nome' => $user->nome,
                'email' => $user->email,
            ],
        ]);
    }
}
