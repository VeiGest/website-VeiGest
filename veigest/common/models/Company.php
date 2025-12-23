<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "companies".
 *
 * @property int $id
 * @property string $code
 * @property string $nome
 * @property string $email
 * @property string|null $telefone
 * @property string|null $nif
 * @property string|null $morada
 * @property string|null $cidade
 * @property string|null $codigo_postal
 * @property string|null $pais
 * @property string $estado
 * @property string $created_at
 * @property string|null $updated_at
 *
 * @property User[] $users
 * @property Vehicle[] $vehicles
 * @property Document[] $documents
 * @property File[] $files
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
            [['code'], 'string', 'max' => 10],
            [['estado'], 'in', 'range' => ['ativo', 'inativo']],
            [['estado'], 'default', 'value' => 'ativo'],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Código',
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
     * Gets query for [[Users]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::class, ['company_id' => 'id']);
    }

    /**
     * Gets query for [[Vehicles]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVehicles()
    {
        return $this->hasMany(Vehicle::class, ['company_id' => 'id']);
    }

    /**
     * Gets query for [[Documents]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDocuments()
    {
        return $this->hasMany(Document::class, ['company_id' => 'id']);
    }

    /**
     * Gets query for [[Files]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFiles()
    {
        return $this->hasMany(File::class, ['company_id' => 'id']);
    }
}
