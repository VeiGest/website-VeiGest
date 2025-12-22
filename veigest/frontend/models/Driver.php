<?php

namespace frontend\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "users" for drivers.
 *
 * @property int $id
 * @property int $company_id
 * @property string $nome
 * @property string $email
 * @property string $password_hash
 * @property string|null $telefone
 * @property string|null $numero_carta
 * @property string|null $validade_carta
 * @property string|null $photo
 * @property string $estado
 * @property string $created_at
 * @property string|null $updated_at
 */
class Driver extends ActiveRecord
{
    const STATUS_ACTIVE = 'ativo';
    const STATUS_INACTIVE = 'inativo';

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
            [['nome', 'email'], 'required'],
            [['email'], 'email'],
            [['email'], 'string', 'max' => 150],
            [['nome'], 'string', 'max' => 150],
            [['telefone'], 'string', 'max' => 20],
            [['numero_carta'], 'string', 'max' => 50],
            [['validade_carta'], 'safe'],
            [['photo'], 'string'],
            [['estado'], 'in', 'range' => ['ativo', 'inativo']],
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
            'nome' => 'Nome',
            'email' => 'Email',
            'password' => 'Palavra-passe',
            'password_hash' => 'Palavra-passe (Hash)',
            'telefone' => 'Telefone',
            'numero_carta' => 'Número da Carta',
            'validade_carta' => 'Validade da Carta',
            'photo' => 'Foto',
            'estado' => 'Estado',
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
            'ativo' => 'Ativo',
            'inativo' => 'Inativo',
        ];
    }

    /**
     * Display status
     */
    public function displayStatus()
    {
        return self::optsStatus()[$this->estado] ?? '-';
    }

    /**
     * Relation to auth_assignment (para RBAC)
     */
    // Alias getters for legacy fields
    public function getName() { return $this->nome; }
    public function setName($v) { $this->nome = $v; }
    public function getPhone() { return $this->telefone; }
    public function setPhone($v) { $this->telefone = $v; }
    public function getLicense_number() { return $this->numero_carta; }
    public function setLicense_number($v) { $this->numero_carta = $v; }
    public function getLicense_expiry() { return $this->validade_carta; }
    public function setLicense_expiry($v) { $this->validade_carta = $v; }
    public function getStatus() { return $this->estado; }
    public function setStatus($v) { $this->estado = $v; }
}
