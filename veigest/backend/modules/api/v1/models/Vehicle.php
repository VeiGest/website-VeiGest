<?php

namespace backend\modules\api\v1\models;

use yii\db\ActiveRecord;

/**
 * Vehicle API model
 *
 * @property integer $id
 * @property integer $company_id
 * @property string $matricula
 * @property string $marca
 * @property string $modelo
 * @property integer $ano
 * @property string $combustivel
 * @property integer $quilometragem
 * @property string $cor
 * @property string $numero_chassis
 * @property string $estado
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
            [['company_id', 'matricula', 'marca', 'modelo'], 'required'],
            [['company_id', 'ano', 'quilometragem'], 'integer'],
            [['matricula'], 'string', 'max' => 20],
            [['marca', 'modelo'], 'string', 'max' => 100],
            [['combustivel'], 'in', 'range' => ['gasolina', 'diesel', 'eletrico', 'hibrido', 'gas']],
            [['cor'], 'string', 'max' => 50],
            [['numero_chassis'], 'string', 'max' => 50],
            [['estado'], 'in', 'range' => ['ativo', 'inativo', 'manutencao']],
            [['estado'], 'default', 'value' => 'ativo'],
            [['matricula'], 'unique'],
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
            'matricula' => 'Matrícula',
            'marca' => 'Marca',
            'modelo' => 'Modelo',
            'ano' => 'Ano',
            'combustivel' => 'Combustível',
            'quilometragem' => 'Quilometragem',
            'cor' => 'Cor',
            'numero_chassis' => 'Número do Chassis',
            'estado' => 'Estado',
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
            'matricula',
            'marca',
            'modelo',
            'ano',
            'combustivel',
            'quilometragem',
            'cor',
            'numero_chassis',
            'estado',
            'created_at',
            'updated_at',
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