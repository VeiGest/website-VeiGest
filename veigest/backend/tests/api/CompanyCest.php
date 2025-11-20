<?php
namespace backend\tests\api;

use backend\tests\ApiTester;
use Codeception\Util\HttpCode;

class CompanyCest
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

    public function testGetCompaniesList(ApiTester $I)
    {
        $I->wantTo('get list of companies');
        
        $I->sendGET('/companies');
        
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'success' => 'boolean',
            'data' => 'array',
            'message' => 'string'
        ]);
    }

    public function testCreateCompany(ApiTester $I)
    {
        $I->wantTo('create a new company');
        
        $companyData = [
            'name' => 'Test Company Ltd',
            'cnpj' => '12.345.678/0001-90',
            'email' => 'contact@testcompany.com',
            'phone' => '(11) 1234-5678'
        ];
        
        $I->sendPOST('/companies', $companyData);
        
        $I->seeResponseCodeIs(HttpCode::CREATED);
        $I->seeResponseIsJson();
        $I->seeResponseContains('"success":true');
        $I->seeResponseContains('Test Company Ltd');
    }
}