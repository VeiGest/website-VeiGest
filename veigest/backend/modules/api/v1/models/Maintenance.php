<?php

namespace backend\modules\api\v1\models;

use yii\db\ActiveRecord;

/**
 * Maintenance API model
 *
 * @property integer $id
 * @property integer $vehicle_id
 * @property string $tipo
 * @property string $descricao
 * @property double $custo
 * @property string $data_manutencao
 * @property integer $quilometragem
 * @property string $fornecedor
 * @property string $estado
 * @property string $created_at
 * @property string $updated_at
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
            [['vehicle_id', 'tipo', 'descricao'], 'required'],
            [['vehicle_id', 'quilometragem'], 'integer'],
            [['custo'], 'number'],
            [['data_manutencao'], 'date', 'format' => 'php:Y-m-d'],
            [['tipo'], 'in', 'range' => ['preventiva', 'corretiva', 'revisao', 'inspecao']],
            [['descricao'], 'string'],
            [['fornecedor'], 'string', 'max' => 150],
            [['estado'], 'in', 'range' => ['agendada', 'em_andamento', 'concluida', 'cancelada']],
            [['estado'], 'default', 'value' => 'agendada'],
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
            'tipo' => 'Tipo',
            'descricao' => 'Descrição',
            'custo' => 'Custo',
            'data_manutencao' => 'Data de Manutenção',
            'quilometragem' => 'Quilometragem',
            'fornecedor' => 'Fornecedor',
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
            'vehicle_id',
            'tipo',
            'descricao',
            'custo',
            'data_manutencao',
            'quilometragem',
            'fornecedor',
            'estado',
            'created_at',
            'updated_at',
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