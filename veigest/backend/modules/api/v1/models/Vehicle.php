<?php

namespace backend\modules\api\v1\models;

use yii\db\ActiveRecord;

/**
 * Vehicle API model
 *
 * @property integer $id
 * @property integer $company_id
 * @property string $license_plate
 * @property string $brand
 * @property string $model
 * @property integer $year
 * @property string $fuel_type
 * @property integer $mileage
 * @property string $color
 * @property string $chassis_number
 * @property string $status
 * @property string $created_at
 * @property string $updated_at
 */
class Vehicle extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%vehicles}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['company_id', 'license_plate', 'brand', 'model'], 'required'],
            [['company_id', 'year', 'mileage'], 'integer'],
            [['license_plate'], 'string', 'max' => 20],
            [['brand', 'model'], 'string', 'max' => 100],
            [['fuel_type'], 'in', 'range' => ['gasoline','diesel','electric','hybrid','other']],
            [['color'], 'string', 'max' => 50],
            [['chassis_number'], 'string', 'max' => 50],
            [['status'], 'in', 'range' => ['active', 'inactive', 'maintenance']],
            [['status'], 'default', 'value' => 'active'],
            [['license_plate'], 'unique'],
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
            'license_plate' => 'Matrícula',
            'brand' => 'Marca',
            'model' => 'Modelo',
            'year' => 'Ano',
            'fuel_type' => 'Combustível',
            'mileage' => 'Quilometragem',
            'color' => 'Cor',
            'chassis_number' => 'Número do Chassis',
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
            'license_plate',
            'brand',
            'model',
            'year',
            'fuel_type',
            'mileage',
            'color',
            'chassis_number',
            'status',
            'created_at',
            'updated_at',
        ];
    }

    // Portuguese alias getters/setters for backward compatibility
    public function getMatricula() { return $this->license_plate; }
    public function setMatricula($v) { $this->license_plate = $v; }
    public function getMarca() { return $this->brand; }
    public function setMarca($v) { $this->brand = $v; }
    public function getModelo() { return $this->model; }
    public function setModelo($v) { $this->model = $v; }
    public function getAno() { return $this->year; }
    public function setAno($v) { $this->year = (int)$v; }
    public function getCombustivel() { return $this->fuel_type; }
    public function setCombustivel($v) { $map = ['gasolina'=>'gasoline','diesel'=>'diesel','eletrico'=>'electric','hibrido'=>'hybrid']; $this->fuel_type = $map[$v] ?? $v; }
    public function getQuilometragem() { return $this->mileage; }
    public function setQuilometragem($v) { $this->mileage = (int)$v; }
    public function getCor() { return $this->color; }
    public function setCor($v) { $this->color = $v; }
    public function getNumero_chassis() { return $this->chassis_number; }
    public function setNumero_chassis($v) { $this->chassis_number = $v; }
    public function getEstado() { return $this->status; }
    public function setEstado($v) { $map = ['ativo'=>'active','inativo'=>'inactive','manutencao'=>'maintenance']; $this->status = $map[$v] ?? $v; }

    /**
     * Get company relationship
     */
    public function getCompany()
    {
        return $this->hasOne(Company::class, ['id' => 'company_id']);
    }

    /**
     * Get maintenances relationship
     */
    public function getMaintenances()
    {
        return $this->hasMany(Maintenance::class, ['vehicle_id' => 'id']);
    }

    /**
     * Get fuel logs relationship
     */
    public function getFuelLogs()
    {
        return $this->hasMany(FuelLog::class, ['vehicle_id' => 'id']);
    }
}