<?php

namespace backend\modules\api\v1\models;

use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * Company API model
 *
 * @property integer $id
 * @property string $nome
 * @property string $email
 * @property string $telefone
 * @property string $nif
 * @property string $morada
 * @property string $cidade
 * @property string $codigo_postal
 * @property string $pais
 * @property string $estado
 * @property string $created_at
 * @property string $updated_at
 */
class Company extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%companies}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nome', 'email'], 'required'],
            [['nome'], 'string', 'max' => 150],
            [['email'], 'email'],
            [['telefone'], 'string', 'max' => 20],
            [['nif'], 'string', 'max' => 50],
            [['morada', 'cidade'], 'string', 'max' => 200],
            [['codigo_postal'], 'string', 'max' => 20],
            [['pais'], 'string', 'max' => 100],
            [['estado'], 'in', 'range' => ['ativo', 'inativo']],
            [['estado'], 'default', 'value' => 'ativo'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nome' => 'Nome',
            'email' => 'Email',
            'telefone' => 'Telefone',
            'nif' => 'NIF',
            'morada' => 'Morada',
            'cidade' => 'Cidade',
            'codigo_postal' => 'Código Postal',
            'pais' => 'País',
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
            'nome',
            'email',
            'telefone',
            'nif',
            'morada',
            'cidade',
            'codigo_postal',
            'pais',
            'estado',
            'created_at',
            'updated_at',
        ];
    }

    /**
     * Get vehicles relationship
     */
    public function getVehicles()
    {
        return $this->hasMany(Vehicle::class, ['company_id' => 'id']);
    }

    /**
     * Get users relationship
     */
    public function getUsers()
    {
        return $this->hasMany(\common\models\User::class, ['company_id' => 'id']);
    }
}