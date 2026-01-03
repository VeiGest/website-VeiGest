<?php

namespace backend\modules\api\v1\models;

use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * Company API model
 *
 * @property integer $id
 * @property string $name
 * @property string $email
 * @property string $phone
 * @property string $tax_id
 * @property string $status
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
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 200],
            [['email'], 'email'],
            [['phone'], 'string', 'max' => 20],
            [['tax_id'], 'string', 'max' => 20],
            [['status'], 'in', 'range' => ['active', 'suspended', 'inactive']],
            [['status'], 'default', 'value' => 'active'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'email' => 'Email',
            'phone' => 'Phone',
            'tax_id' => 'Tax ID',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function fields()
    {
        return [
            'id',
            'name',
            'email',
            'phone',
            'tax_id',
            'status',
            'created_at',
            'updated_at',
        ];
    }

    /**
     * Get vehicles relationship
     */
    public function getVehicles()
    {
        return $this->hasMany(Vehicle::class, ['company_id' => 'id']);
    }

    /**
     * Get users relationship
     */
    public function getUsers()
    {
        return $this->hasMany(\common\models\User::class, ['company_id' => 'id']);
    }
}