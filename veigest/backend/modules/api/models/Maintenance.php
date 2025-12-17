<?php

namespace backend\modules\api\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

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
 * @property string $observacoes
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
            [['vehicle_id', 'tipo', 'descricao'], 'required'],
            [['vehicle_id', 'quilometragem'], 'integer'],
            [['custo'], 'number', 'min' => 0],
            [['data_manutencao'], 'date', 'format' => 'php:Y-m-d'],
            [['tipo'], 'in', 'range' => ['preventiva', 'corretiva', 'revisao', 'inspecao']],
            [['descricao', 'observacoes'], 'string'],
            [['fornecedor'], 'string', 'max' => 150],
            [['estado'], 'in', 'range' => ['agendada', 'em_andamento', 'concluida', 'cancelada']],
            [['estado'], 'default', 'value' => 'agendada'],
            [['custo'], 'default', 'value' => 0],
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
            'tipo',
            'tipo_label' => function ($model) {
                return $this->getTipoLabel($model->tipo);
            },
            'descricao',
            'custo',
            'data_manutencao',
            'quilometragem',
            'fornecedor',
            'estado',
            'estado_label' => function ($model) {
                return $this->getEstadoLabel($model->estado);
            },
            'observacoes',
            'days_until_maintenance' => function ($model) {
                if ($model->data_manutencao && $model->estado === 'agendada') {
                    $now = new \DateTime();
                    $maintenanceDate = new \DateTime($model->data_manutencao);
                    return $maintenanceDate->diff($now)->days * ($maintenanceDate > $now ? 1 : -1);
                }
                return null;
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
     * Get tipo label
     * 
     * @param string $tipo
     * @return string
     */
    public function getTipoLabel($tipo)
    {
        $labels = [
            'preventiva' => 'Preventiva',
            'corretiva' => 'Corretiva',
            'revisao' => 'Revisão',
            'inspecao' => 'Inspeção',
        ];

        return $labels[$tipo] ?? $tipo;
    }

    /**
     * Get estado label
     * 
     * @param string $estado
     * @return string
     */
    public function getEstadoLabel($estado)
    {
        $labels = [
            'agendada' => 'Agendada',
            'em_andamento' => 'Em Andamento',
            'concluida' => 'Concluída',
            'cancelada' => 'Cancelada',
        ];

        return $labels[$estado] ?? $estado;
    }

    /**
     * Check if maintenance is overdue
     * 
     * @return boolean
     */
    public function isOverdue()
    {
        if ($this->estado !== 'agendada' || !$this->data_manutencao) {
            return false;
        }

        return strtotime($this->data_manutencao) < time();
    }

    /**
     * Check if maintenance is upcoming (within 7 days)
     * 
     * @return boolean
     */
    public function isUpcoming()
    {
        if ($this->estado !== 'agendada' || !$this->data_manutencao) {
            return false;
        }

        $maintenanceTime = strtotime($this->data_manutencao);
        $now = time();
        $sevenDaysFromNow = $now + (7 * 24 * 60 * 60);

        return $maintenanceTime >= $now && $maintenanceTime <= $sevenDaysFromNow;
    }
}
