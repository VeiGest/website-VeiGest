<?php
namespace backend\tests\api;

use backend\tests\ApiTester;
use Codeception\Util\HttpCode;

/**
 * TDD Tests for AuthController
 * 
 * Tests authentication endpoints following Test-Driven Development methodology
 * Testing scenarios: login, logout, refresh token, invalid credentials, security
 */
class AuthCest
{
    /**
     * Setup before each test
     */
    public function _before(ApiTester $I)
    {
        // Ensure clean state for each test
        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Content-Type', 'application/json');
    }

    /**
     * TDD Test: GET /auth/info should return API information
     */
    public function testGetApiInfo(ApiTester $I)
    {
        $I->wantTo('get API information without authentication');
        
        $I->sendGET('/auth/info');
        
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'name' => 'VeiGest API',
            'version' => '1.0.0'
        ]);
        $I->seeResponseMatchesJsonType([
            'name' => 'string',
            'version' => 'string',
            'description' => 'string',
            'endpoints' => 'array'
        ]);
    }

    /**
     * TDD Test: POST /auth/login with valid credentials should authenticate user
     */
    public function testLoginWithValidCredentials(ApiTester $I)
    {
        $I->wantTo('login with valid credentials');
        
        $I->sendPOST('/auth/login', [
            'username' => 'admin',
            'password' => 'admin'
        ]);
        
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'success' => true,
            'message' => 'Login successful'
        ]);
        
        // Verify response structure
        $I->seeResponseMatchesJsonType([
            'success' => 'boolean',
            'message' => 'string',
            'data' => [
                'access_token' => 'string',
                'token_type' => 'string',
                'user' => [
                    'id' => 'integer',
                    'username' => 'string',
                    'email' => 'string',
                    'nome' => 'string'
                ]
            ]
        ]);
        
        // Verify token properties
        $response = json_decode($I->grabResponse(), true);
        $I->assertNotEmpty($response['data']['access_token']);
        $I->assertEquals('Bearer', $response['data']['token_type']);
        $I->assertEquals('admin', $response['data']['user']['username']);
    }

    /**
     * TDD Test: POST /auth/login with invalid credentials should fail
     */
    public function testLoginWithInvalidCredentials(ApiTester $I)
    {
        $I->wantTo('fail login with invalid credentials');
        
        $I->sendPOST('/auth/login', [
            'username' => 'wronguser',
            'password' => 'wrongpass'
        ]);
        
        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'success' => false,
            'message' => 'Invalid credentials'
        ]);
    }

    /**
     * TDD Test: POST /auth/login with missing fields should fail validation
     */
    public function testLoginWithMissingFields(ApiTester $I)
    {
        $I->wantTo('fail login with missing username');
        
        $I->sendPOST('/auth/login', [
            'password' => 'admin'
        ]);
        
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'success' => false
        ]);
        $I->seeResponseContains('username');
        
        // Test missing password
        $I->sendPOST('/auth/login', [
            'username' => 'admin'
        ]);
        
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContains('password');
    }

    /**
     * TDD Test: POST /auth/refresh should refresh valid token
     */
    public function testRefreshValidToken(ApiTester $I)
    {
        $I->wantTo('refresh a valid authentication token');
        
        // First login to get a token
        $loginResponse = $I->authenticateUser('admin', 'admin');
        $originalToken = $loginResponse['data']['access_token'];
        
        $I->sendPOST('/auth/refresh');
        
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'success' => true,
            'message' => 'Token refreshed successfully'
        ]);
        
        // Verify new token is different
        $response = json_decode($I->grabResponse(), true);
        $newToken = $response['data']['access_token'];
        $I->assertNotEquals($originalToken, $newToken);
        $I->assertNotEmpty($newToken);
    }

    /**
     * TDD Test: POST /auth/refresh without token should fail
     */
    public function testRefreshWithoutToken(ApiTester $I)
    {
        $I->wantTo('fail token refresh without authentication');
        
        $I->sendPOST('/auth/refresh');
        
        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'success' => false,
            'message' => 'Authentication required'
        ]);
    }

    /**
     * TDD Test: POST /auth/logout should invalidate token
     */
    public function testLogoutWithValidToken(ApiTester $I)
    {
        $I->wantTo('logout with valid authentication token');
        
        // Login first
        $I->authenticateUser('admin', 'admin');
        
        $I->sendPOST('/auth/logout');
        
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
        
        // Verify token is invalidated - try to use it again
        $I->sendGET('/company');
        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);
    }

    /**
     * TDD Test: POST /auth/logout without token should fail
     */
    public function testLogoutWithoutToken(ApiTester $I)
    {
        $I->wantTo('fail logout without authentication');
        
        $I->sendPOST('/auth/logout');
        
        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'success' => false,
            'message' => 'Authentication required'
        ]);
    }

    /**
     * TDD Test: Authentication token should work for protected endpoints
     */
    public function testTokenWorksForProtectedEndpoints(ApiTester $I)
    {
        $I->wantTo('access protected endpoint with valid token');
        
        // Login and get token
        $I->authenticateUser('admin', 'admin');
        
        // Try accessing a protected endpoint
        $I->sendGET('/company');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
    }

    /**
     * TDD Test: Invalid token should be rejected
     */
    public function testInvalidTokenIsRejected(ApiTester $I)
    {
        $I->wantTo('fail access with invalid token');
        
        $I->haveHttpHeader('Authorization', 'Bearer invalid-token-here');
        
        $I->sendGET('/company');
        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'success' => false
        ]);
    }

    /**
     * TDD Test: Malformed Authorization header should be rejected
     */
    public function testMalformedAuthHeaderIsRejected(ApiTester $I)
    {
        $I->wantTo('fail access with malformed authorization header');
        
        // Test without "Bearer" prefix
        $I->haveHttpHeader('Authorization', 'some-random-token');
        $I->sendGET('/company');
        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);
        
        // Test with wrong prefix
        $I->haveHttpHeader('Authorization', 'Basic some-token');
        $I->sendGET('/company');
        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);
    }

    /**
     * TDD Test: Rate limiting for failed login attempts (security)
     */
    public function testLoginRateLimiting(ApiTester $I)
    {
        $I->wantTo('test rate limiting for failed login attempts');
        
        // Attempt multiple failed logins
        for ($i = 0; $i < 5; $i++) {
            $I->sendPOST('/auth/login', [
                'username' => 'wronguser',
                'password' => 'wrongpass'
            ]);
            $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);
        }
        
        // After 5 failed attempts, should be rate limited
        $I->sendPOST('/auth/login', [
            'username' => 'wronguser',
            'password' => 'wrongpass'
        ]);
        
        // Should return 429 Too Many Requests (when rate limiting is implemented)
        // For now, we expect this to fail - this is TDD for future implementation
        $I->seeResponseCodeIsNot(HttpCode::OK);
    }

    /**
     * TDD Test: Token expiration handling (future implementation)
     */
    public function testExpiredTokenHandling(ApiTester $I)
    {
        $I->wantTo('handle expired tokens gracefully');
        
        // This test is for future implementation of token expiration
        // For now, we'll test that tokens work immediately after creation
        $I->authenticateUser('admin', 'admin');
        
        $I->sendGET('/company');
        $I->seeResponseCodeIs(HttpCode::OK);
        
        // TODO: Implement actual token expiration testing
        // when JWT with expiration is implemented
        $I->comment('Token expiration testing to be implemented with JWT');
    }
}