<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;

class RbacController extends Controller
{
    public $enableCsrfValidation = false;

    public function actionInit()
    {
        $auth = Yii::$app->authManager;

        // Limpar tudo
        $auth->removeAll();

        // ROLES
        $admin    = $auth->createRole('admin');
        $gestor   = $auth->createRole('gestor');
        $condutor = $auth->createRole('condutor');

        $auth->add($admin);
        $auth->add($gestor);
        $auth->add($condutor);

        //permissoes
        $acessoBackend = $auth->createPermission('acessoBackend');
        $acessoBackend->description = 'Permite aceder ao backend';
        $auth->add($acessoBackend);

        $auth->addChild($admin, $acessoBackend);

        $auth->assign($admin, 1);

        echo "<h3>RBAC criado com sucesso!</h3>";
        exit;
    }
}
