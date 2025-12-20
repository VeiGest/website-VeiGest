<?php

namespace frontend\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "users" for drivers.
 *
 * @property int $id
 * @property int $company_id
 * @property string $name
 * @property string $email
 * @property string $password_hash
 * @property string|null $phone
 * @property string|null $license_number
 * @property string|null $license_expiry
 * @property string|null $photo
 * @property string $status
 * @property string $created_at
 * @property string|null $updated_at
 */
class Driver extends ActiveRecord
{
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    public $password; // Campo temporário para password

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'users';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'email'], 'required'],
            [['email'], 'email'],
            [['email'], 'string', 'max' => 150],
            [['name'], 'string', 'max' => 150],
            [['phone'], 'string', 'max' => 20],
            [['license_number'], 'string', 'max' => 50],
            [['license_expiry'], 'safe'],
            [['photo'], 'string'],
            [['status'], 'in', 'range' => ['active', 'inactive', 'suspended']],
            // Verificar unicidade do email por empresa
            [
                ['email'],
                'unique',
                'targetAttribute' => ['email', 'company_id'],
                'message' => 'Este email já está registado na sua empresa.'
            ],
            // Password - obrigatória ao criar, opcional ao editar
            [['password'], 'string', 'min' => 6],
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
            'name' => 'Nome',
            'email' => 'Email',
            'password' => 'Palavra-passe',
            'password_hash' => 'Palavra-passe (Hash)',
            'phone' => 'Telefone',
            'license_number' => 'Número da Carta',
            'license_expiry' => 'Validade da Carta',
            'photo' => 'Foto',
            'status' => 'Estado',
            'created_at' => 'Criado em',
            'updated_at' => 'Atualizado em',
        ];
    }

    /**
     * Hash a password
     */
    public function setPassword($password)
    {
        if (!empty($password)) {
            $this->password_hash = Yii::$app->security->generatePasswordHash($password);
        }
    }

    /**
     * Options for status
     */
    public static function optsStatus()
    {
        return [
            'active' => 'Ativo',
            'inactive' => 'Inativo',
            'suspended' => 'Suspenso',
        ];
    }

    /**
     * Display status
     */
    public function displayStatus()
    {
        return self::optsStatus()[$this->status] ?? '-';
    }

    /**
     * Relation to auth_assignment (para RBAC)
     */
    public function getAuthAssignments()
    {
        return $this->hasMany('yii\\rbac\\Assignment', ['user_id' => 'id']);
    }
}
