<?php
namespace backend\tests\_support\Helper;

use Codeception\Module;
use Codeception\TestInterface;
use yii\web\User;
use common\models\User as UserModel;

/**
 * Helper class for API testing
 * Provides authentication, data setup and common assertions for API tests
 */
class Api extends Module
{
    /**
     * @var string Current authentication token
     */
    protected $authToken;

    /**
     * @var UserModel Current authenticated user
     */
    protected $currentUser;

    /**
     * HOOK executed before each test
     */
    public function _before(TestInterface $test)
    {
        parent::_before($test);
        
        // Clear authentication state
        $this->authToken = null;
        $this->currentUser = null;
    }

    /**
     * Authenticate user and store token for subsequent requests
     * 
     * @param string $username
     * @param string $password
     * @return array Authentication response
     */
    public function authenticateUser($username = 'admin', $password = 'admin')
    {
        $I = $this->getModule('REST');
        
        $I->sendPOST('/auth/login', [
            'username' => $username,
            'password' => $password
        ]);
        
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['success' => true]);
        
        $response = json_decode($I->grabResponse(), true);
        $this->authToken = $response['data']['access_token'];
        
        // Set authorization header for subsequent requests
        $I->haveHttpHeader('Authorization', 'Bearer ' . $this->authToken);
        
        return $response;
    }

    /**
     * Get current authentication token
     * 
     * @return string|null
     */
    public function getAuthToken()
    {
        return $this->authToken;
    }

    /**
     * Set authentication token manually
     * 
     * @param string $token
     */
    public function setAuthToken($token)
    {
        $this->authToken = $token;
        $I = $this->getModule('REST');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $token);
    }

    /**
     * Clear authentication
     */
    public function clearAuth()
    {
        $this->authToken = null;
        $this->currentUser = null;
        $I = $this->getModule('REST');
        $I->deleteHeader('Authorization');
    }

    /**
     * Assert API response structure
     */
    public function seeResponseMatchesApiStructure($expectedStructure)
    {
        $I = $this->getModule('REST');
        
        foreach ($expectedStructure as $field => $type) {
            if (is_array($type)) {
                $I->seeResponseContainsJson([$field => []]);
            } else {
                $I->seeResponseJsonMatchesJsonPath('$.' . $field);
            }
        }
    }

    /**
     * Assert pagination headers
     */
    public function seePaginationHeaders()
    {
        $I = $this->getModule('REST');
        $I->seeHttpHeader('X-Pagination-Total-Count');
        $I->seeHttpHeader('X-Pagination-Page-Count');
        $I->seeHttpHeader('X-Pagination-Current-Page');
        $I->seeHttpHeader('X-Pagination-Per-Page');
    }

    /**
     * Create test company
     */
    public function createTestCompany($attributes = [])
    {
        $defaultData = [
            'nome' => 'Test Company ' . uniqid(),
            'nif' => '123456789',
            'email' => 'test' . uniqid() . '@company.com',
            'telefone' => '123456789',
            'endereco' => 'Test Address 123',
            'cidade' => 'Lisboa',
            'codigo_postal' => '1000-001',
            'pais' => 'Portugal',
            'ativa' => 1
        ];

        $data = array_merge($defaultData, $attributes);
        
        $I = $this->getModule('REST');
        $I->sendPOST('/company', $data);
        $I->seeResponseCodeIs(201);
        
        $response = json_decode($I->grabResponse(), true);
        return $response['data'] ?? $response;
    }

    /**
     * Create test vehicle
     */
    public function createTestVehicle($companyId, $attributes = [])
    {
        $defaultData = [
            'company_id' => $companyId,
            'matricula' => 'AA-' . rand(10, 99) . '-BB',
            'marca' => 'Toyota',
            'modelo' => 'Corolla',
            'ano' => 2020,
            'combustivel' => 'gasolina',
            'quilometragem' => 10000,
            'cor' => 'branco',
            'estado' => 'ativo'
        ];

        $data = array_merge($defaultData, $attributes);
        
        $I = $this->getModule('REST');
        $I->sendPOST('/vehicle', $data);
        $I->seeResponseCodeIs(201);
        
        $response = json_decode($I->grabResponse(), true);
        return $response['data'] ?? $response;
    }

    /**
     * Create test maintenance
     */
    public function createTestMaintenance($vehicleId, $attributes = [])
    {
        $defaultData = [
            'vehicle_id' => $vehicleId,
            'tipo' => 'preventiva',
            'descricao' => 'Test maintenance ' . uniqid(),
            'custo' => 100.00,
            'data_manutencao' => date('Y-m-d'),
            'fornecedor' => 'Test Provider',
            'estado' => 'agendada'
        ];

        $data = array_merge($defaultData, $attributes);
        
        $I = $this->getModule('REST');
        $I->sendPOST('/maintenance', $data);
        $I->seeResponseCodeIs(201);
        
        $response = json_decode($I->grabResponse(), true);
        return $response['data'] ?? $response;
    }

    /**
     * Create test user
     */
    public function createTestUser($companyId, $attributes = [])
    {
        $defaultData = [
            'company_id' => $companyId,
            'username' => 'testuser' . uniqid(),
            'email' => 'testuser' . uniqid() . '@test.com',
            'nome' => 'Test User',
            'password' => 'testpass123',
            'status' => 10
        ];

        $data = array_merge($defaultData, $attributes);
        
        $I = $this->getModule('REST');
        $I->sendPOST('/user', $data);
        $I->seeResponseCodeIs(201);
        
        $response = json_decode($I->grabResponse(), true);
        return $response['data'] ?? $response;
    }

    /**
     * Assert error response structure
     */
    public function seeErrorResponse($expectedCode = 400, $expectedMessage = null)
    {
        $I = $this->getModule('REST');
        $I->seeResponseCodeIs($expectedCode);
        $I->seeResponseContainsJson(['success' => false]);
        
        if ($expectedMessage) {
            $I->seeResponseContains($expectedMessage);
        }
    }

    /**
     * Assert successful response structure
     */
    public function seeSuccessResponse($expectedCode = 200)
    {
        $I = $this->getModule('REST');
        $I->seeResponseCodeIs($expectedCode);
        $I->seeResponseContainsJson(['success' => true]);
    }
}