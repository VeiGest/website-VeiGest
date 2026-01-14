<?php

namespace backend\modules\api\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * Company API model
 *
 * @property integer $id
 * @property integer $code
 * @property string $name
 * @property string $email
 * @property string $phone
 * @property string $tax_id
 * @property string $status
 * @property string $plan
 * @property string $settings
 * @property string $created_at
 * @property string $updated_at
 */
class Company extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%companies}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'value' => function() { return date('Y-m-d H:i:s'); },
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'tax_id'], 'required'],
            [['name'], 'string', 'max' => 200],
            [['email'], 'email'],
            [['email'], 'string', 'max' => 150],
            [['phone'], 'string', 'max' => 20],
            [['tax_id'], 'string', 'max' => 20],
            [['code'], 'integer'],
            [['status'], 'in', 'range' => ['active', 'suspended', 'inactive']],
            [['status'], 'default', 'value' => 'active'],
            [['plan'], 'in', 'range' => ['basic', 'professional', 'enterprise']],
            [['plan'], 'default', 'value' => 'basic'],
            [['settings'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Código',
            'name' => 'Nome',
            'email' => 'Email',
            'phone' => 'Telefone',
            'tax_id' => 'NIF/Tax ID',
            'status' => 'Estado',
            'plan' => 'Plano',
            'settings' => 'Configurações',
            'created_at' => 'Criado em',
            'updated_at' => 'Atualizado em',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function fields()
    {
        return [
            'id',
            'code',
            'name',
            'email',
            'phone',
            'tax_id',
            'status',
            'plan',
            'settings',
            'created_at',
            'updated_at',
        ];
    }

    /**
     * Get vehicles relationship
     */
    public function getVehicles()
    {
        return $this->hasMany(\backend\modules\api\models\Vehicle::class, ['company_id' => 'id']);
    }

    /**
     * Get users relationship
     */
    public function getUsers()
    {
        return $this->hasMany(\common\models\User::class, ['company_id' => 'id']);
    }

    /**
     * Get active vehicles count
     */
    public function getActiveVehiclesCount()
    {
        return $this->getVehicles()
            ->where(['status' => 'active'])
            ->count();
    }

    /**
     * Get total users count
     */
    public function getTotalUsersCount()
    {
        return $this->getUsers()
            ->where(['status' => 'active'])
            ->count();
    }
}
