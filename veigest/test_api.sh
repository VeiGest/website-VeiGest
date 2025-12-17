#!/bin/bash

# VeiGest API Test Script
# Tests the authentication and basic endpoints

BASE_URL="http://localhost:21080/api/v1"

echo "======================================"
echo "VeiGest API - Test Script"
echo "======================================"
echo ""

# Test 1: Login
echo "1. Testing Login Endpoint..."
LOGIN_RESPONSE=$(curl -s -X POST "$BASE_URL/auth/login" \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin"}')

echo "Response: $LOGIN_RESPONSE"
echo ""

# Extract token from response
TOKEN=$(echo $LOGIN_RESPONSE | grep -o '"token":"[^"]*' | sed 's/"token":"//')

if [ -z "$TOKEN" ]; then
    echo "❌ Login failed! No token received."
    exit 1
fi

echo "✓ Login successful! Token received."
echo "Token (first 50 chars): ${TOKEN:0:50}..."
echo ""

# Test 2: Get current user info
echo "2. Testing /auth/me Endpoint..."
ME_RESPONSE=$(curl -s -X GET "$BASE_URL/auth/me" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json")

echo "Response: $ME_RESPONSE"
echo ""

# Test 3: Get vehicles
echo "3. Testing /vehicles Endpoint..."
VEHICLES_RESPONSE=$(curl -s -X GET "$BASE_URL/vehicles" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json")

echo "Response: $VEHICLES_RESPONSE"
echo ""

# Test 4: Get users
echo "4. Testing /users Endpoint..."
USERS_RESPONSE=$(curl -s -X GET "$BASE_URL/users" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json")

echo "Response: $USERS_RESPONSE"
echo ""

# Test 5: Token refresh
echo "5. Testing /auth/refresh Endpoint..."
REFRESH_RESPONSE=$(curl -s -X POST "$BASE_URL/auth/refresh" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json")

echo "Response: $REFRESH_RESPONSE"
echo ""

# Test 6: Logout
echo "6. Testing /auth/logout Endpoint..."
LOGOUT_RESPONSE=$(curl -s -X POST "$BASE_URL/auth/logout" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json")

echo "Response: $LOGOUT_RESPONSE"
echo ""

echo "======================================"
echo "✓ All tests completed!"
echo "======================================"
