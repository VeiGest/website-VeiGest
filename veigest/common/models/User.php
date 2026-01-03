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
 * @property string $name
 * @property string $company_id
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $verification_token
 * @property string $email
 * @property string $auth_key
 
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $password write-only password
 */



class User extends ActiveRecord implements IdentityInterface
{
    public const STATUS_DELETED = 0;
    public const STATUS_INACTIVE = 9;
    public const STATUS_ACTIVE = 10;

    // Virtual attribute for password - only used during create/update
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
            [
                'class' => TimestampBehavior::class,
                'value' => function () {
                    return date('Y-m-d H:i:s');
                },
            ],
        ];
    }

    /**
     * Aliases for legacy `nome` usage (maps to `name` column).
     */
    public function getNome()
    {
        return $this->name;
    }

    public function setNome($value)
    {
        $this->name = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username', 'name', 'email', 'company_id'], 'required'],
            ['role', 'required', 'on' => 'adminCreate'],
            ['role', 'safe'], 

            ['password', 'required', 'on' => ['create', 'adminCreate']],

            ['email', 'email'],
            ['username', 'string', 'max' => 64],
            ['username', 'match', 'pattern' => '/^[a-zA-Z0-9._-]{3,64}$/'],
            ['username', 'unique', 'targetAttribute' => ['username', 'company_id']],

            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE, self::STATUS_DELETED]],

            ['role', 'in', 'range' => ['admin', 'gestor', 'condutor']],
        ];
    }


    public function scenarios()
    {
        $scenarios = parent::scenarios();

        // Cenário de criação → password obrigatória
        $scenarios['create'] = [
            'username',
            'name',
            'email',
            'company_id',
            'password',
            'role',
            'status'
        ];

        // Cenário de criação por admin → password obrigatória
        $scenarios['adminCreate'] = [
            'username',
            'name',
            'email',
            'company_id',
            'password',
            'role',
            'status'
        ];

        // Cenário de edição → password opcional
        $scenarios['update'] = [
            'username',
            'name',
            'email',
            'company_id',
            'password',
            'role',
            'status'
        ];

        $scenarios['signup'] = [
            'username',
            'name',
            'email',
            'company_id',
            'password',
            'status'
        ];

        return $scenarios;
    }




    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
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
        return static::findOne(['auth_key' => $token, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by email
     *
     * @param string $email
     * @return static|null
     */
    public static function findByEmail($email)
    {
        return static::findOne(['email' => $email, 'status' => self::STATUS_ACTIVE]);
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
            'status' => self::STATUS_ACTIVE,

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

        if ($this->status === null) {
            $this->status = self::STATUS_ACTIVE;
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

        // Retorna a role principal mapeada para categorias de UI
        $roleNames = array_keys($roles);

        // Prioridade: admin > manager/maintenance-manager > senior-driver/driver
        if (in_array('admin', $roleNames, true)) {
            return 'admin';
        }
        if (in_array('manager', $roleNames, true) || in_array('maintenance-manager', $roleNames, true)) {
            // Mapear para 'gestor' para compatibilidade com UI existente
            return 'gestor';
        }
        if (in_array('senior-driver', $roleNames, true) || in_array('driver', $roleNames, true)) {
            // Mapear para 'condutor' para compatibilidade com UI existente
            return 'condutor';
        }

        // Fallback: primeira role
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
