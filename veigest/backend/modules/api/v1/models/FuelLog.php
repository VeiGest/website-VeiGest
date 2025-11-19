<?php

namespace backend\modules\api\v1\models;

use yii\db\ActiveRecord;

/**
 * FuelLog API model
 *
 * @property integer $id
 * @property integer $vehicle_id
 * @property double $litros
 * @property double $custo_total
 * @property integer $quilometragem
 * @property string $data_abastecimento
 * @property string $local
 * @property string $created_at
 */
class FuelLog extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%fuel_logs}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['vehicle_id', 'litros', 'custo_total'], 'required'],
            [['vehicle_id', 'quilometragem'], 'integer'],
            [['litros', 'custo_total'], 'number'],
            [['data_abastecimento'], 'date', 'format' => 'php:Y-m-d H:i:s'],
            [['local'], 'string', 'max' => 200],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'vehicle_id' => 'Veículo',
            'litros' => 'Litros',
            'custo_total' => 'Custo Total',
            'quilometragem' => 'Quilometragem',
            'data_abastecimento' => 'Data de Abastecimento',
            'local' => 'Local',
            'created_at' => 'Criado em',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function fields()
    {
        return [
            'id',
            'vehicle_id',
            'litros',
            'custo_total',
            'quilometragem',
            'data_abastecimento',
            'local',
            'preco_por_litro' => function ($model) {
                return $model->litros > 0 ? round($model->custo_total / $model->litros, 3) : 0;
            },
            'created_at',
        ];
    }

    /**
     * Get vehicle relationship
     */
    public function getVehicle()
    {
        return $this->hasOne(Vehicle::class, ['id' => 'vehicle_id']);
    }
}