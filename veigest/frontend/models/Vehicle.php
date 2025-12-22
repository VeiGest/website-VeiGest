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
    // Tipos de combustível (Português, conforme BD)
    const FUEL_TYPE_GASOLINA  = 'gasolina';
    const FUEL_TYPE_DIESEL    = 'diesel';
    const FUEL_TYPE_ELETRICO  = 'eletrico';
    const FUEL_TYPE_HIBRIDO   = 'hibrido';
    const FUEL_TYPE_OUTRO     = 'outro';

    // Estado (Português, conforme BD)
    const STATUS_ATIVO        = 'ativo';
    const STATUS_MANUTENCAO   = 'manutencao';
    const STATUS_INATIVO      = 'inativo';

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
            [['matricula', 'marca', 'modelo', 'estado'], 'required'],
            [['ano', 'quilometragem', 'company_id'], 'integer'],
            [['matricula'], 'string', 'max' => 20],
            [['marca', 'modelo'], 'string', 'max' => 100],
            [['estado'], 'in', 'range' => [self::STATUS_ATIVO, self::STATUS_MANUTENCAO, self::STATUS_INATIVO]],
            [['tipo_combustivel'], 'in', 'range' => [self::FUEL_TYPE_GASOLINA, self::FUEL_TYPE_DIESEL, self::FUEL_TYPE_ELETRICO, self::FUEL_TYPE_HIBRIDO, self::FUEL_TYPE_OUTRO]],
            [['foto'], 'string'],
            // Verificar unicidade da matrícula por empresa
            [
                ['matricula', 'company_id'],
                'unique',
                'targetAttribute' => ['matricula', 'company_id'],
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
            'id'               => 'ID',
            'company_id'       => 'Empresa',
            // Português (BD)
            'matricula'        => 'Matrícula',
            'marca'            => 'Marca',
            'modelo'           => 'Modelo',
            'ano'              => 'Ano',
            'tipo_combustivel' => 'Tipo de Combustível',
            'quilometragem'    => 'Quilometragem',
            'estado'           => 'Estado',
            'condutor_id'      => 'Condutor',
            'foto'             => 'Foto',
            // Aliases para compatibilidade (views antigas)
            'license_plate'    => 'Matrícula',
            'brand'            => 'Marca',
            'model'            => 'Modelo',
            'year'             => 'Ano',
            'fuel_type'        => 'Tipo de Combustível',
            'mileage'          => 'Quilometragem',
            'status'           => 'Estado',
            'driver_id'        => 'Condutor',
            'photo'            => 'Foto',
            'created_at'       => 'Criado em',
            'updated_at'       => 'Atualizado em',
        ];
    }

    /* =========================
     * ENUM HELPERS
     * ========================= */

    public static function optsFuelType()
    {
        return [
            self::FUEL_TYPE_GASOLINA => 'Gasolina',
            self::FUEL_TYPE_DIESEL   => 'Diesel',
            self::FUEL_TYPE_ELETRICO => 'Elétrico',
            self::FUEL_TYPE_HIBRIDO  => 'Híbrido',
            self::FUEL_TYPE_OUTRO    => 'Outro',
        ];
    }

    public static function optsStatus()
    {
        return [
            self::STATUS_ATIVO       => 'Ativo',
            self::STATUS_MANUTENCAO  => 'Em Manutenção',
            self::STATUS_INATIVO     => 'Inativo',
        ];
    }

    public function displayFuelType()
    {
        return self::optsFuelType()[$this->tipo_combustivel] ?? '-';
    }

    public function displayStatus()
    {
        return self::optsStatus()[$this->estado] ?? '-';
    }

    /* =========================
     * Aliases getters/setters (compat)
     * ========================= */
    public function getLicense_plate() { return $this->matricula; }
    public function setLicense_plate($value) { $this->matricula = $value; }

    public function getBrand() { return $this->marca; }
    public function setBrand($value) { $this->marca = $value; }

    public function getModel() { return $this->modelo; }
    public function setModel($value) { $this->modelo = $value; }

    public function getYear() { return $this->ano; }
    public function setYear($value) { $this->ano = (int)$value; }

    public function getFuel_type() { return $this->tipo_combustivel; }
    public function setFuel_type($value) { $this->tipo_combustivel = $value; }

    public function getMileage() { return $this->quilometragem; }
    public function setMileage($value) { $this->quilometragem = (int)$value; }

    public function getStatus() { return $this->estado; }
    public function setStatus($value) { $this->estado = $value; }

    public function getDriver_id() { return $this->condutor_id; }
    public function setDriver_id($value) { $this->condutor_id = (int)$value; }

    public function getPhoto() { return $this->foto; }
    public function setPhoto($value) { $this->foto = $value; }
}
