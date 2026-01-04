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
    const STATUS_ACTIVE = 10;
    const STATUS_INACTIVE = 9;
    const STATUS_DELETED = 0;

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
            [['status'], 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE, self::STATUS_DELETED]],
            [['nome', 'telefone', 'numero_carta', 'validade_carta'], 'safe'],
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
            self::STATUS_ACTIVE => 'Ativo',
            self::STATUS_INACTIVE => 'Inativo',
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
    // Alias getters for legacy fields
    public function getNome() { return $this->name; }
    public function setNome($v) { $this->name = $v; }
    public function getTelefone() { return $this->phone; }
    public function setTelefone($v) { $this->phone = $v; }
    public function getNumero_carta() { return $this->license_number; }
    public function setNumero_carta($v) { $this->license_number = $v; }
    public function getValidade_carta() { return $this->license_expiry; }
    public function setValidade_carta($v) { $this->license_expiry = $v; }
    public function getStatus() { return $this->status; }
    public function setStatus($v) { $this->status = $v; }
}
