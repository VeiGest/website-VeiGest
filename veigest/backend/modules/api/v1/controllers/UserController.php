<?php

namespace backend\modules\api\v1\controllers;

use backend\modules\api\v1\models\User;
use backend\modules\api\v1\models\Company;
use yii\data\ActiveDataProvider;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\ContentNegotiator;
use yii\filters\Cors;
use yii\rest\ActiveController;
use yii\web\Response;
use yii\web\NotFoundHttpException;

/**
 * User API Controller
 * 
 * Provides CRUD operations for users/drivers
 * 
 * @author VeiGest Team
 */
class UserController extends ActiveController
{
    public $modelClass = 'backend\modules\api\v1\models\User';

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // CORS filter
        $behaviors['corsFilter'] = [
            'class' => Cors::class,
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => false,
                'Access-Control-Max-Age' => 86400,
            ]
        ];

        // Content negotiator
        $behaviors['contentNegotiator'] = [
            'class' => ContentNegotiator::class,
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
            ],
        ];

        // Authentication - temporarily disabled for testing
        // $behaviors['authenticator'] = [
        //     'class' => CompositeAuth::class,
        //     'authMethods' => [
        //         HttpBearerAuth::class,
        //     ],
        // ];

        return $behaviors;
    }

    /**
     * Lists all users
     * 
     * @return ActiveDataProvider
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => User::find()->with(['company']),
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'defaultOrder' => ['id' => SORT_ASC]
            ],
        ]);

        return $dataProvider;
    }

    /**
     * Get user with company information
     * 
     * @param integer $id
     * @return User
     */
    public function actionView($id)
    {
        $user = User::find()
            ->with(['company'])
            ->where(['id' => $id])
            ->one();
            
        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }

        return $user;
    }

    /**
     * Get users by company
     * 
     * @param integer $company_id Company ID
     * @return ActiveDataProvider
     */
    public function actionByCompany($company_id)
    {
        $company = Company::findOne($company_id);
        if (!$company) {
            throw new NotFoundHttpException('Company not found');
        }

        return new ActiveDataProvider([
            'query' => User::find()
                ->where(['company_id' => $company_id])
                ->with(['company']),
            'pagination' => [
                'pageSize' => 15,
            ],
        ]);
    }

    /**
     * Get drivers (users with driving license)
     * 
     * @return ActiveDataProvider
     */
    public function actionDrivers()
    {
        return new ActiveDataProvider([
            'query' => User::find()
                ->where(['not', ['numero_carta_conducao' => null]])
                ->andWhere(['!=', 'numero_carta_conducao', ''])
                ->with(['company']),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
    }

    /**
     * Get user profile (for authenticated user)
     * 
     * @return User
     */
    public function actionProfile()
    {
        // This would typically get the authenticated user
        // For now, return user with ID 1 for testing
        return User::find()->with(['company'])->where(['id' => 1])->one();
    }
}
