<?php

use backend\tests\ApiTester;

class BasicApiCest
{
    public function testApiInfo(ApiTester $I)
    {
        $I->wantTo('test basic API info endpoint');
        $I->sendGET('/auth/info');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('VeiGest API');
    }

    public function testApiLogin(ApiTester $I)
    {
        $I->wantTo('test API login');
        $I->sendPOST('/auth/login', [
            'username' => 'admin',
            'password' => 'admin'
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('Login successful');
        $I->seeResponseContains('access_token');
    }
}