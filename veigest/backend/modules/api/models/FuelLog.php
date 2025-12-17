<?php

namespace backend\modules\api\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

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
 * @property double $preco_por_litro
 * @property string $observacoes
 * @property string $created_at
 * @property string $updated_at
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
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['vehicle_id', 'litros', 'custo_total'], 'required'],
            [['vehicle_id', 'quilometragem'], 'integer'],
            [['litros', 'custo_total', 'preco_por_litro'], 'number', 'min' => 0],
            [['data_abastecimento'], 'date', 'format' => 'php:Y-m-d H:i:s'],
            [['data_abastecimento'], 'default', 'value' => function() {
                return date('Y-m-d H:i:s');
            }],
            [['local'], 'string', 'max' => 200],
            [['observacoes'], 'string'],
            [['quilometragem'], 'integer', 'min' => 0],
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
            'preco_por_litro' => 'Preço por Litro',
            'observacoes' => 'Observações',
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
            'vehicle_id',
            'litros',
            'custo_total',
            'quilometragem',
            'data_abastecimento',
            'local',
            'preco_por_litro' => function ($model) {
                return $model->litros > 0 ? round($model->custo_total / $model->litros, 3) : 0;
            },
            'observacoes',
            'consumption_since_last' => function ($model) {
                return $this->getConsumptionSinceLast();
            },
            'cost_per_km' => function ($model) {
                $consumption = $this->getConsumptionSinceLast();
                return $consumption > 0 ? round($model->custo_total / $consumption, 3) : 0;
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
            'vehicle',
        ];
    }

    /**
     * Get vehicle relationship
     */
    public function getVehicle()
    {
        return $this->hasOne(Vehicle::class, ['id' => 'vehicle_id']);
    }

    /**
     * Before save event
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Calcular preço por litro automaticamente se não fornecido
            if ($this->litros > 0 && $this->custo_total > 0) {
                $this->preco_por_litro = round($this->custo_total / $this->litros, 3);
            }
            
            return true;
        }
        return false;
    }

    /**
     * Get consumption since last fuel log
     * 
     * @return float Distance in km
     */
    public function getConsumptionSinceLast()
    {
        if (!$this->quilometragem) {
            return 0;
        }

        $lastFuelLog = static::find()
            ->where(['vehicle_id' => $this->vehicle_id])
            ->andWhere(['<', 'quilometragem', $this->quilometragem])
            ->orderBy(['quilometragem' => SORT_DESC])
            ->one();

        if (!$lastFuelLog || !$lastFuelLog->quilometragem) {
            return 0;
        }

        return $this->quilometragem - $lastFuelLog->quilometragem;
    }

    /**
     * Calculate fuel efficiency (km/l)
     * 
     * @return float
     */
    public function getFuelEfficiency()
    {
        $distance = $this->getConsumptionSinceLast();
        return ($distance > 0 && $this->litros > 0) ? round($distance / $this->litros, 2) : 0;
    }

    /**
     * Get average fuel price per liter for this vehicle
     * 
     * @return float
     */
    public function getAverageFuelPrice()
    {
        $avgPrice = static::find()
            ->where(['vehicle_id' => $this->vehicle_id])
            ->andWhere(['>', 'litros', 0])
            ->andWhere(['>', 'custo_total', 0])
            ->average('(custo_total / litros)');

        return $avgPrice ? round($avgPrice, 3) : 0;
    }

    /**
     * Check if this is an expensive fill-up compared to average
     * 
     * @return boolean
     */
    public function isExpensive()
    {
        $currentPrice = $this->litros > 0 ? ($this->custo_total / $this->litros) : 0;
        $averagePrice = $this->getAverageFuelPrice();

        return $currentPrice > 0 && $averagePrice > 0 && $currentPrice > ($averagePrice * 1.1); // 10% above average
    }
}
