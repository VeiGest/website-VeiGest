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

        $this->assertFalse($user->validate(), 'User sem dados não deve validar');
        $this->assertArrayHasKey('username', $user->errors, 'Username deve ser obrigatório');
        $this->assertArrayHasKey('name', $user->errors, 'Name deve ser obrigatório');
        $this->assertArrayHasKey('email', $user->errors, 'Email deve ser obrigatório');
        $this->assertArrayHasKey('company_id', $user->errors, 'Company_id deve ser obrigatório');
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

        $this->assertFalse($user->validate(['email']), 'Email inválido não deve validar');
        $this->assertArrayHasKey('email', $user->errors, 'Deve ter erro no email');

        $user->email = 'email_valido@veigest.pt';
        $this->assertTrue($user->validate(['email']), 'Email válido deve passar validação');
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

        $this->assertFalse($user2->validate(), 'Username duplicado não deve validar');
        $this->assertArrayHasKey('username', $user2->errors, 'Deve ter erro no username');

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

        $this->assertTrue($user->save(), 'User deve salvar');
        $this->assertNotNull($user->password_hash, 'Password hash deve existir');
        $this->assertNotEquals($plainPassword, $user->password_hash, 'Password hash não deve ser igual à password plain');
        $this->assertTrue($user->validatePassword($plainPassword), 'ValidatePassword deve retornar true para password correta');
        $this->assertFalse($user->validatePassword('PasswordErrada'), 'ValidatePassword deve retornar false para password errada');

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

        $this->assertTrue($user->save(), 'User deve salvar');
        $this->assertNotNull($user->auth_key, 'Auth key deve ser gerada');
        $this->assertGreaterThanOrEqual(32, strlen($user->auth_key), 'Auth key deve ter pelo menos 32 caracteres');

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
        
        $this->assertNotNull($user, 'Deve encontrar user existente');
        $this->assertEquals('test_admin', $user->username, 'Username deve corresponder');
        
        // Buscar user inexistente
        $notFound = User::findByUsername('usuario_que_nao_existe');
        $this->assertNull($notFound, 'Não deve encontrar user inexistente');
    }

    /**
     * Teste 7: FindByEmail encontra user correto
     * Verifica se o método estático findByEmail funciona
     */
    public function testFindByEmail()
    {
        $user = User::findByEmail('test_admin@veigest.test');
        
        $this->assertNotNull($user, 'Deve encontrar user por email');
        $this->assertEquals('test_admin@veigest.test', $user->email, 'Email deve corresponder');
    }

    /**
     * Teste 8: Validação de roles
     * Verifica que o campo roles (string) é salvo corretamente
     * Nota: A role real é gerida pelo RBAC (authManager), não pela coluna
     */
    public function testValidationRole()
    {
        $user = new User([
            'username' => 'role_test_user',
            'name' => 'Role Test User',
            'email' => 'role_test@veigest.test',
            'company_id' => 1,
            'roles' => 'manager', // Campo roles na BD
        ]);

        // O campo roles é string e aceita qualquer valor (usado para referência)
        $this->assertTrue($user->validate(['roles']), 'Campo roles deve validar');
        $this->assertEquals('manager', $user->roles, 'Campo roles deve manter o valor');
        
        // Testar atribuição de outros valores
        $user->roles = 'admin';
        $this->assertEquals('admin', $user->roles, 'Campo roles deve aceitar "admin"');
        
        $user->roles = 'driver';
        $this->assertEquals('driver', $user->roles, 'Campo roles deve aceitar "driver"');
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

        $this->assertFalse($user->validate(['status']), 'Status inválido não deve validar');
        
        // Testar status válidos
        foreach (['active', 'inactive'] as $validStatus) {
            $user->status = $validStatus;
            $this->assertTrue($user->validate(['status']), "Status '$validStatus' deve ser válido");
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

        $this->assertTrue($user->save(), 'CREATE: User deve ser salvo');
        $this->assertNotNull($user->id, 'CREATE: ID deve ser atribuído');
        $userId = $user->id;

        // READ
        $foundUser = User::findOne($userId);
        $this->assertNotNull($foundUser, 'READ: User deve ser encontrado');
        $this->assertEquals('crud_test_user', $foundUser->username, 'READ: Username deve corresponder');
        $this->assertEquals('crud_test@veigest.test', $foundUser->email, 'READ: Email deve corresponder');

        // UPDATE
        $foundUser->name = 'CRUD Updated User';
        $foundUser->phone = '+351911111111';
        $this->assertTrue($foundUser->save(), 'UPDATE: User deve ser atualizado');

        $updatedUser = User::findOne($userId);
        $this->assertEquals('CRUD Updated User', $updatedUser->name, 'UPDATE: Nome atualizado deve corresponder');
        $this->assertEquals('+351911111111', $updatedUser->phone, 'UPDATE: Telefone atualizado deve corresponder');

        // DELETE
        $this->assertEquals(1, $updatedUser->delete(), 'DELETE: User deve ser eliminado');
        
        $deletedUser = User::findOne($userId);
        $this->assertNull($deletedUser, 'DELETE: User não deve existir após eliminar');
    }
}
