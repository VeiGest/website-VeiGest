<?php

namespace backend\modules\api\models;

use yii\db\ActiveRecord;

/**
 * Ticket API model
 *
 * @property integer $id
 * @property integer $company_id
 * @property integer $route_id
 * @property string $passenger_name
 * @property string $passenger_phone
 * @property string $status
 * @property string $created_at
 * @property string $updated_at
 */
class Ticket extends ActiveRecord
{
    // Status
    const STATUS_ACTIVE = 'active';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_COMPLETED = 'completed';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%tickets}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['company_id', 'route_id'], 'required'],
            [['company_id', 'route_id'], 'integer'],
            [['passenger_name'], 'string', 'max' => 150],
            [['passenger_phone'], 'string', 'max' => 20],
            [['status'], 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_CANCELLED, self::STATUS_COMPLETED]],
            [['status'], 'default', 'value' => self::STATUS_ACTIVE],
            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => Company::class, 'targetAttribute' => ['company_id' => 'id']],
            [['route_id'], 'exist', 'skipOnError' => true, 'targetClass' => Route::class, 'targetAttribute' => ['route_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'company_id' => 'Empresa',
            'route_id' => 'Rota',
            'passenger_name' => 'Nome do Passageiro',
            'passenger_phone' => 'Telefone do Passageiro',
            'status' => 'Estado',
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
            'company_id',
            'route_id',
            'passenger_name',
            'passenger_phone',
            'status',
            'status_label' => function ($model) {
                return $this->getStatusLabel($model->status);
            },
            'created_at',
            'updated_at',
        ];
    }

    /**
     * Extra fields
     */
    public function extraFields()
    {
        return [
            'company',
            'route',
        ];
    }

    /**
     * Get company relationship
     */
    public function getCompany()
    {
        return $this->hasOne(Company::class, ['id' => 'company_id']);
    }

    /**
     * Get route relationship
     */
    public function getRoute()
    {
        return $this->hasOne(Route::class, ['id' => 'route_id']);
    }

    /**
     * Get status label
     */
    public function getStatusLabel($status)
    {
        $labels = [
            self::STATUS_ACTIVE => 'Ativo',
            self::STATUS_CANCELLED => 'Cancelado',
            self::STATUS_COMPLETED => 'Concluído',
        ];
        return $labels[$status] ?? $status;
    }

    /**
     * Cancel ticket
     */
    public function cancel()
    {
        $this->status = self::STATUS_CANCELLED;
        return $this->save(false);
    }

    /**
     * Complete ticket
     */
    public function complete()
    {
        $this->status = self::STATUS_COMPLETED;
        return $this->save(false);
    }

    /**
     * Get all status options
     */
    public static function getStatusOptions()
    {
        return [
            self::STATUS_ACTIVE => 'Ativo',
            self::STATUS_CANCELLED => 'Cancelado',
            self::STATUS_COMPLETED => 'Concluído',
        ];
    }
}
