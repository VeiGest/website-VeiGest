<?php

namespace common\tests\unit\models;

use Yii;
use common\models\User;
use common\fixtures\UserFixture;
use common\fixtures\CompanyFixture;

/**
 * Testes Unitários - User Model
 * 
 * Testa validação, password hashing, e métodos do modelo User.
 * Segue padrão Active Record: Create, Read, Update, Delete
 * 
 * @group unit
 * @group models
 * @group user
 */
class UserTest extends \Codeception\Test\Unit
{
    /**
     * @var \common\tests\UnitTester
     */
    protected $tester;

    /**
     * @return array
     */
    public function _fixtures()
    {
        return [
            'company' => [
                'class' => CompanyFixture::class,
                'dataFile' => codecept_data_dir() . 'company.php'
            ],
            'user' => [
                'class' => UserFixture::class,
                'dataFile' => codecept_data_dir() . 'user.php'
            ],
        ];
    }

    /**
     * Teste 1: Validação de campos obrigatórios
     * Verifica se username, name, email e company_id são obrigatórios
     */
    public function testValidationRequired()
    {
        $user = new User();
        $user->scenario = 'create';

        verify('User sem dados não deve validar', $user->validate())->false();
        verify('Username deve ser obrigatório', $user->errors)->arrayHasKey('username');
        verify('Name deve ser obrigatório', $user->errors)->arrayHasKey('name');
        verify('Email deve ser obrigatório', $user->errors)->arrayHasKey('email');
        verify('Company_id deve ser obrigatório', $user->errors)->arrayHasKey('company_id');
    }

    /**
     * Teste 2: Validação de formato de email
     * Verifica se emails inválidos são rejeitados
     */
    public function testValidationEmail()
    {
        $user = new User([
            'username' => 'test_email_user',
            'name' => 'Test Email User',
            'company_id' => 1,
            'email' => 'email_invalido',
        ]);

        verify('Email inválido não deve validar', $user->validate(['email']))->false();
        verify('Deve ter erro no email', $user->errors)->arrayHasKey('email');

        $user->email = 'email_valido@veigest.pt';
        verify('Email válido deve passar validação', $user->validate(['email']))->true();
    }

    /**
     * Teste 3: Validação de username único
     * Verifica se usernames duplicados são rejeitados
     */
    public function testValidationUniqueUsername()
    {
        // Criar primeiro user
        $user1 = new User([
            'username' => 'unique_test_user',
            'name' => 'Unique Test User',
            'email' => 'unique1@veigest.test',
            'company_id' => 1,
            'password' => 'TestPassword123',
        ]);
        $user1->scenario = 'create';
        $user1->save();

        // Tentar criar segundo user com mesmo username
        $user2 = new User([
            'username' => 'unique_test_user', // mesmo username
            'name' => 'Another User',
            'email' => 'unique2@veigest.test',
            'company_id' => 1,
            'password' => 'TestPassword123',
        ]);
        $user2->scenario = 'create';

        verify('Username duplicado não deve validar', $user2->validate())->false();
        verify('Deve ter erro no username', $user2->errors)->arrayHasKey('username');

        // Cleanup
        $user1->delete();
    }

    /**
     * Teste 4: Password hash é gerado corretamente
     * Verifica se a password é hashada antes de salvar
     */
    public function testPasswordHashing()
    {
        $plainPassword = 'MinhaPasswordSegura123';
        
        $user = new User([
            'username' => 'password_test_user',
            'name' => 'Password Test User',
            'email' => 'password_test@veigest.test',
            'company_id' => 1,
            'password' => $plainPassword,
        ]);
        $user->scenario = 'create';

        verify('User deve salvar', $user->save())->true();
        verify('Password hash deve existir', $user->password_hash)->notNull();
        verify('Password hash não deve ser igual à password plain', $user->password_hash)->notEquals($plainPassword);
        verify('ValidatePassword deve retornar true para password correta', $user->validatePassword($plainPassword))->true();
        verify('ValidatePassword deve retornar false para password errada', $user->validatePassword('PasswordErrada'))->false();

        // Cleanup
        $user->delete();
    }

    /**
     * Teste 5: AuthKey é gerado automaticamente
     * Verifica se auth_key é gerado ao criar novo user
     */
    public function testAuthKeyGeneration()
    {
        $user = new User([
            'username' => 'authkey_test_user',
            'name' => 'AuthKey Test User',
            'email' => 'authkey_test@veigest.test',
            'company_id' => 1,
            'password' => 'TestPassword123',
        ]);
        $user->scenario = 'create';

        verify('User deve salvar', $user->save())->true();
        verify('Auth key deve ser gerada', $user->auth_key)->notNull();
        verify('Auth key deve ter pelo menos 32 caracteres', strlen($user->auth_key))->greaterThanOrEqual(32);

        // Cleanup
        $user->delete();
    }

    /**
     * Teste 6: FindByUsername encontra user correto
     * Verifica se o método estático findByUsername funciona
     */
    public function testFindByUsername()
    {
        // Buscar user existente do fixture
        $user = User::findByUsername('test_admin');
        
        verify('Deve encontrar user existente', $user)->notNull();
        verify('Username deve corresponder', $user->username)->equals('test_admin');
        
        // Buscar user inexistente
        $notFound = User::findByUsername('usuario_que_nao_existe');
        verify('Não deve encontrar user inexistente', $notFound)->null();
    }

    /**
     * Teste 7: FindByEmail encontra user correto
     * Verifica se o método estático findByEmail funciona
     */
    public function testFindByEmail()
    {
        $user = User::findByEmail('test_admin@veigest.test');
        
        verify('Deve encontrar user por email', $user)->notNull();
        verify('Email deve corresponder', $user->email)->equals('test_admin@veigest.test');
    }

    /**
     * Teste 8: Validação de role
     * Verifica se roles inválidas são rejeitadas
     */
    public function testValidationRole()
    {
        $user = new User([
            'username' => 'role_test_user',
            'name' => 'Role Test User',
            'email' => 'role_test@veigest.test',
            'company_id' => 1,
            'role' => 'role_invalida',
        ]);

        verify('Role inválida não deve validar', $user->validate(['role']))->false();
        
        // Testar roles válidas
        foreach (['admin', 'manager', 'driver'] as $validRole) {
            $user->role = $validRole;
            verify("Role '$validRole' deve ser válida", $user->validate(['role']))->true();
        }
    }

    /**
     * Teste 9: Validação de status
     * Verifica se status inválidos são rejeitados
     */
    public function testValidationStatus()
    {
        $user = new User([
            'username' => 'status_test_user',
            'name' => 'Status Test User',
            'email' => 'status_test@veigest.test',
            'company_id' => 1,
            'status' => 'status_invalido',
        ]);

        verify('Status inválido não deve validar', $user->validate(['status']))->false();
        
        // Testar status válidos
        foreach (['active', 'inactive'] as $validStatus) {
            $user->status = $validStatus;
            verify("Status '$validStatus' deve ser válido", $user->validate(['status']))->true();
        }
    }

    /**
     * Teste 10: CRUD completo - Create, Read, Update, Delete
     * Testa integração completa com banco de dados
     */
    public function testCRUDOperations()
    {
        // CREATE
        $user = new User([
            'username' => 'crud_test_user',
            'name' => 'CRUD Test User',
            'email' => 'crud_test@veigest.test',
            'company_id' => 1,
            'password' => 'CrudTestPassword123',
            'status' => 'active',
        ]);
        $user->scenario = 'create';

        verify('CREATE: User deve ser salvo', $user->save())->true();
        verify('CREATE: ID deve ser atribuído', $user->id)->notNull();
        $userId = $user->id;

        // READ
        $foundUser = User::findOne($userId);
        verify('READ: User deve ser encontrado', $foundUser)->notNull();
        verify('READ: Username deve corresponder', $foundUser->username)->equals('crud_test_user');
        verify('READ: Email deve corresponder', $foundUser->email)->equals('crud_test@veigest.test');

        // UPDATE
        $foundUser->name = 'CRUD Updated User';
        $foundUser->phone = '+351911111111';
        verify('UPDATE: User deve ser atualizado', $foundUser->save())->true();

        $updatedUser = User::findOne($userId);
        verify('UPDATE: Nome atualizado deve corresponder', $updatedUser->name)->equals('CRUD Updated User');
        verify('UPDATE: Telefone atualizado deve corresponder', $updatedUser->phone)->equals('+351911111111');

        // DELETE
        verify('DELETE: User deve ser eliminado', $updatedUser->delete())->equals(1);
        
        $deletedUser = User::findOne($userId);
        verify('DELETE: User não deve existir após eliminar', $deletedUser)->null();
    }
}
