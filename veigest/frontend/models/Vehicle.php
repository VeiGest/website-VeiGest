<?php

namespace frontend\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "vehicles".
 *
 * @property int $id
 * @property int $company_id
 * @property string $license_plate
 * @property string|null $brand
 * @property string|null $model
 * @property int|null $year
 * @property string|null $fuel_type
 * @property int $mileage
 * @property string $status
 * @property int|null $driver_id
 * @property string|null $photo
 * @property string $created_at
 * @property string|null $updated_at
 */
class Vehicle extends ActiveRecord
{
    // Fuel types
    const FUEL_TYPE_GASOLINE = 'gasoline';
    const FUEL_TYPE_DIESEL   = 'diesel';
    const FUEL_TYPE_ELECTRIC = 'electric';
    const FUEL_TYPE_HYBRID   = 'hybrid';
    const FUEL_TYPE_OTHER    = 'other';

    // Status
    const STATUS_ACTIVE      = 'active';
    const STATUS_MAINTENANCE = 'maintenance';
    const STATUS_INACTIVE    = 'inactive';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vehicles';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['license_plate', 'brand', 'model', 'status'], 'required'],
            [['year', 'mileage', 'company_id'], 'integer'],
            [['license_plate'], 'string', 'max' => 20],
            [['brand', 'model'], 'string', 'max' => 100],
            [['status'], 'in', 'range' => ['active', 'maintenance', 'inactive']],
            [['fuel_type'], 'in', 'range' => ['gasoline', 'diesel', 'electric', 'hybrid', 'other']],
            [['photo'], 'string'],
            // Verificar unicidade da matrícula por empresa
            [
                ['license_plate'],
                'unique',
                'targetAttribute' => ['license_plate', 'company_id'],
                'message' => 'Esta matrícula já está registada na sua empresa.'
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'            => 'ID',
            'company_id'    => 'Empresa',
            'license_plate' => 'Matrícula',
            'brand'         => 'Marca',
            'model'         => 'Modelo',
            'year'          => 'Ano',
            'fuel_type'     => 'Tipo de Combustível',
            'mileage'       => 'Quilometragem',
            'status'        => 'Estado',
            'driver_id'     => 'Condutor',
            'photo'         => 'Foto',
            'created_at'    => 'Criado em',
            'updated_at'    => 'Atualizado em',
        ];
    }

    /* =========================
     * ENUM HELPERS
     * ========================= */

    public static function optsFuelType()
    {
        return [
            self::FUEL_TYPE_GASOLINE => 'Gasolina',
            self::FUEL_TYPE_DIESEL   => 'Diesel',
            self::FUEL_TYPE_ELECTRIC => 'Elétrico',
            self::FUEL_TYPE_HYBRID   => 'Híbrido',
            self::FUEL_TYPE_OTHER    => 'Outro',
        ];
    }

    public static function optsStatus()
    {
        return [
            self::STATUS_ACTIVE      => 'Ativo',
            self::STATUS_MAINTENANCE => 'Em Manutenção',
            self::STATUS_INACTIVE    => 'Inativo',
        ];
    }

    public function displayFuelType()
    {
        return self::optsFuelType()[$this->fuel_type] ?? '-';
    }

    public function displayStatus()
    {
        return self::optsStatus()[$this->status] ?? '-';
    }
}
