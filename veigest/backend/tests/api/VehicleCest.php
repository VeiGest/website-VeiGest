<?php
namespace backend\tests\api;

use backend\tests\ApiTester;
use Codeception\Util\HttpCode;

class VehicleCest
{
    private $authToken;

    public function _before(ApiTester $I)
    {
        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Content-Type', 'application/json');
        
        // Login and get auth token
        $I->sendPOST('/auth/login', [
            'username' => 'admin',
            'password' => 'admin123'
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $response = $I->grabResponse();
        $data = json_decode($response, true);
        $this->authToken = $data['data']['token'];
        
        $I->haveHttpHeader('Authorization', 'Bearer ' . $this->authToken);
    }

    public function testGetVehiclesList(ApiTester $I)
    {
        $I->wantTo('get list of vehicles');
        
        $I->sendGET('/vehicles');
        
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'success' => 'boolean',
            'data' => 'array'
        ]);
    }

    public function testCreateVehicle(ApiTester $I)
    {
        $I->wantTo('create a new vehicle');
        
        $vehicleData = [
            'plate' => 'ABC-1234',
            'model' => 'Test Vehicle',
            'year' => 2023,
            'company_id' => 1
        ];
        
        $I->sendPOST('/vehicles', $vehicleData);
        
        $I->seeResponseCodeIs(HttpCode::CREATED);
        $I->seeResponseIsJson();
        $I->seeResponseContains('"success":true');
        $I->seeResponseContains('ABC-1234');
    }
}