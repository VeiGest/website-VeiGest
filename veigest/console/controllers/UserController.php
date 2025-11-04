<?php

namespace console\controllers;

use yii\console\Controller;
use common\models\User;
use Yii;

class UserController extends Controller
{
    
     
     //Gera auth_key para todos os utilizadores que não têm.
    public function actionGenerateAuthKeys()
    {
        $users = User::find()->where(['auth_key' => null])->orWhere(['auth_key' => ''])->all();

        if (empty($users)) {
            $this->stdout("Todos os utilizadores já têm auth_key.\n");
            return;
        }

        foreach ($users as $user) {
            $user->generateAuthKey();
            if ($user->save(false)) {
                $this->stdout("auth_key gerada para utilizador: {$user->nome}\n");
            } else {
                $this->stderr("Erro ao salvar: {$user->nome}\n");
            }
        }

        $this->stdout("DONE!\n");
    }
}
