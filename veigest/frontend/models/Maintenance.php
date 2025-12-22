<?php

namespace frontend\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%maintenances}}".
 *
 * @property int $id
 * @property int $company_id Company ID
 * @property int $vehicle_id Vehicle ID
 * @property string $tipo Type of maintenance
 * @property string|null $descricao Description
 * @property string $data Maintenance date (única data)
 * @property string $status Status (scheduled|completed|overdue)
 * @property float|null $custo Cost
 * @property int|null $km_registro Mileage record
 * @property string|null $oficina Workshop name
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Vehicle $vehicle
 */
class Maintenance extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%maintenances}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['company_id', 'vehicle_id', 'tipo', 'data'], 'required'],
            [['company_id', 'vehicle_id', 'km_registro'], 'integer'],
            [['descricao'], 'string'],
            [['data'], 'date', 'format' => 'php:Y-m-d'],
            [['custo'], 'number'],
            [['tipo', 'oficina'], 'string', 'max' => 200],
            [['status'], 'in', 'range' => ['scheduled', 'completed', 'overdue']],
            [['vehicle_id'], 'exist', 'skipOnError' => true, 'targetClass' => Vehicle::class, 'targetAttribute' => ['vehicle_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
            [['company_id', 'vehicle_id', 'tipo'], 'required'],
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'company_id' => 'Empresa',
            'vehicle_id' => 'Veículo',
            'tipo' => 'Tipo de Manutenção',
            'descricao' => 'Descrição',
            'data' => 'Data',
            'status' => 'Estado',
            'custo' => 'Custo (€)',
            'km_registro' => 'Quilometragem',
            'oficina' => 'Oficina',
            'created_at' => 'Criado em',
            'updated_at' => 'Atualizado em',
        ];
    }

    /**
     * Gets query for [[Vehicle]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVehicle()
    {
        return $this->hasOne(Vehicle::class, ['id' => 'vehicle_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new \yii\db\Expression('NOW()'),
            ],
        ];
    }

    /**
     * Get maintenance types available
     */
    public static function getTypes()
    {
        return [
            'Manutenção Preventiva' => 'Manutenção Preventiva',
            'Manutenção Corretiva' => 'Manutenção Corretiva',
            'Inspeção' => 'Inspeção',
            'Reparação' => 'Reparação',
            'Óleo' => 'Óleo',
            'Pneus' => 'Pneus',
            'Freios' => 'Freios',
            'Bateria' => 'Bateria',
            'Filtros' => 'Filtros',
            'Alinhamento' => 'Alinhamento',
            'Outro' => 'Outro',
        ];
    }
}
