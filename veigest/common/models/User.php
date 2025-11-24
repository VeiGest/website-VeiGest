<?php

namespace common\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * User model
 *
 * @property integer $id
 * @property string $nome
 * @property string $company_id
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $verification_token
 * @property string $email
 * @property string $auth_key
 * 
 * @property integer $estado
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $password write-only password
 */



class User extends ActiveRecord implements IdentityInterface
{
    public $password;


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%users}}';
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
            [['username', 'nome', 'email', 'company_id'], 'required'],
            ['role', 'required', 'on' => 'adminCreate'],

            ['password', 'required', 'on' => 'create'],

            ['email', 'email'],
            ['username', 'string', 'max' => 255],
            ['username', 'unique'],

            ['role', 'in', 'range' => ['admin', 'gestor', 'condutor']],
            ['estado', 'in', 'range' => ['ativo', 'inativo']],
        ];
    }


    public function scenarios()
    {
        $scenarios = parent::scenarios();

        // Cenário de criação → password obrigatória
        $scenarios['create'] = [
            'username',
            'nome',
            'email',
            'company_id',
            'password',
            'role',
            'estado'
        ];

        // Cenário de edição → password opcional
        $scenarios['update'] = [
            'username',
            'nome',
            'email',
            'company_id',
            'role',
            'estado'
        ];

        $scenarios['signup'] = [
            'username',
            'nome',
            'email',
            'company_id',
            'password'
        ];

        return $scenarios;
    }




    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'estado' => 'ativo']);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        if (empty($token)) {
            return null;
        }

        // assume token is stored in auth_key (simple bearer token)
        return static::findOne(['auth_key' => $token, 'estado' => 'ativo']);
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'estado' => 'ativo']);
    }

    /**
     * Finds user by email
     *
     * @param string $email
     * @return static|null
     */
    public static function findByEmail($email)
    {
        return static::findOne(['email' => $email, 'estado' => 'ativo']);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'estado' => 'ativo',

        ]);
    }

    /**
     * Finds user by verification email token
     *
     * @param string $token verify email token
     * @return static|null
     */
    public static function findByVerificationToken($token)
    {
        return static::findOne([
            'verification_token' => $token,
            'estado' => 'inativo',

        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    public function getUsername()
    {
        return $this->username;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        // Gerar auth_key apenas na criação
        if ($insert) {
            $this->generateAuthKey();
        }

        // Criar hash da password se for preenchida
        if (!empty($this->password)) {
            $this->password_hash = Yii::$app->security->generatePasswordHash($this->password);
        }

        return true;
    }




    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Generates new token for email verification
     */
    public function generateEmailVerificationToken()
    {
        $this->verification_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    /**
     * Get user role from RBAC system
     * @return string|null
     */
    public function getRole()
    {
        $roles = Yii::$app->authManager->getRolesByUser($this->id);
        if (empty($roles)) {
            return null;
        }

        // Retorna a primeira role encontrada (ou a mais importante)
        $roleNames = array_keys($roles);

        // Prioridade: admin > gestor > condutor
        if (in_array('admin', $roleNames)) {
            return 'admin';
        }
        if (in_array('gestor', $roleNames)) {
            return 'gestor';
        }
        if (in_array('condutor', $roleNames)) {
            return 'condutor';
        }

        return $roleNames[0] ?? null;
    }

    /**
     * Check if user has a specific role
     * @param string $roleName
     * @return bool
     */
    public function hasRole($roleName)
    {
        return Yii::$app->authManager->getAssignment($roleName, $this->id) !== null;
    }

    public function attributes()
    {
        return array_merge(parent::attributes(), [
            'password',
        ]);
    }
}
