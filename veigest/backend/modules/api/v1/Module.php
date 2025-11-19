<?php

namespace backend\modules\api\v1;

use yii\base\Module as BaseModule;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\ContentNegotiator;
use yii\web\Response;

/**
 * VeiGest API v1 Module
 * 
 * RESTful API for VeiGest vehicle management system
 * Provides CRUD operations and custom endpoints for mobile application
 */
class Module extends BaseModule
{
    public $controllerNamespace = 'backend\modules\api\v1\controllers';
    
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        
        // Set JSON as default response format
        \Yii::$app->response->format = Response::FORMAT_JSON;
        
        // Configure API behaviors
        \Yii::$app->user->enableSession = false;
        \Yii::$app->user->loginUrl = null;
    }
}
