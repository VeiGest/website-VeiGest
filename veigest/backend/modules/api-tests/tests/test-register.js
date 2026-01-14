/**
 * VeiGest API - Testes de Registro de Usuários
 * Testa o endpoint de registro (signup) da API
 */

const { apiRequest, formatTestResult } = require('../utils/http-client.js');

// Dados de teste para registro
const generateTestUser = () => {
    const timestamp = Date.now();
    return {
        username: `testuser_${timestamp}`,
        email: `testuser_${timestamp}@teste.com`,
        password: 'test123',
        name: `Usuário Teste ${timestamp}`,
        company_id: 1,
        phone: '+351912345678'
    };
};

/**
 * Executa todos os testes de registro
 */
async function runRegisterTests() {
    console.log('\n📝 INICIANDO TESTES DE REGISTRO\n');
    console.log('='.repeat(80));

    const results = {
        total: 0,
        success: 0,
        failed: 0,
        tests: []
    };

    // ============================================
    // TESTE 1: Registro com sucesso
    // ============================================
    console.log('\n✅ Teste 1: Registro com dados válidos');
    const validUser = generateTestUser();
    
    const registerResult = await apiRequest('POST', '/auth/register', {
        body: validUser
    });
    
    results.total++;
    if (registerResult.success && registerResult.response.status === 201) {
        results.success++;
        console.log('   ✓ Registro realizado com sucesso');
        console.log(`   ✓ Status: ${registerResult.response.status}`);
        console.log(`   ✓ Token recebido: ${registerResult.response.body?.data?.access_token ? 'Sim' : 'Não'}`);
        console.log(`   ✓ User ID: ${registerResult.response.body?.data?.user?.id}`);
        console.log(`   ✓ Username: ${registerResult.response.body?.data?.user?.username}`);
        console.log(`   ✓ Email: ${registerResult.response.body?.data?.user?.email}`);
        console.log(`   ✓ Roles: ${JSON.stringify(registerResult.response.body?.data?.roles)}`);
        results.tests.push({ name: 'Registro com dados válidos', status: 'PASS' });
    } else {
        results.failed++;
        console.log('   ✗ Falha no registro');
        console.log(`   ✗ Status: ${registerResult.response?.status}`);
        console.log(`   ✗ Erro: ${registerResult.response?.body?.message || registerResult.error}`);
        results.tests.push({ name: 'Registro com dados válidos', status: 'FAIL' });
    }

    // ============================================
    // TESTE 2: Login com usuário recém-registrado
    // ============================================
    console.log('\n🔑 Teste 2: Login com usuário recém-registrado');
    
    const loginResult = await apiRequest('POST', '/auth/login', {
        body: {
            username: validUser.username,
            password: validUser.password
        }
    });
    
    results.total++;
    if (loginResult.success && loginResult.response.status === 200) {
        results.success++;
        console.log('   ✓ Login realizado com sucesso');
        console.log(`   ✓ Token válido: ${loginResult.response.body?.data?.access_token ? 'Sim' : 'Não'}`);
        results.tests.push({ name: 'Login com usuário recém-registrado', status: 'PASS' });
    } else {
        results.failed++;
        console.log('   ✗ Falha no login');
        console.log(`   ✗ Erro: ${loginResult.response?.body?.message || loginResult.error}`);
        results.tests.push({ name: 'Login com usuário recém-registrado', status: 'FAIL' });
    }

    // ============================================
    // TESTE 3: Registro sem username
    // ============================================
    console.log('\n❌ Teste 3: Registro sem username (deve falhar)');
    
    const noUsernameResult = await apiRequest('POST', '/auth/register', {
        body: {
            email: 'test@teste.com',
            password: 'test123',
            name: 'Teste',
            company_id: 1
        }
    });
    
    results.total++;
    if (!noUsernameResult.success && noUsernameResult.response.status === 400) {
        results.success++;
        console.log('   ✓ Erro esperado retornado');
        console.log(`   ✓ Status: ${noUsernameResult.response.status}`);
        console.log(`   ✓ Mensagem: ${noUsernameResult.response.body?.message}`);
        results.tests.push({ name: 'Registro sem username', status: 'PASS' });
    } else {
        results.failed++;
        console.log('   ✗ Deveria ter retornado erro 400');
        console.log(`   ✗ Status recebido: ${noUsernameResult.response?.status}`);
        results.tests.push({ name: 'Registro sem username', status: 'FAIL' });
    }

    // ============================================
    // TESTE 4: Registro sem email
    // ============================================
    console.log('\n❌ Teste 4: Registro sem email (deve falhar)');
    
    const noEmailResult = await apiRequest('POST', '/auth/register', {
        body: {
            username: 'testuser_noemail',
            password: 'test123',
            name: 'Teste',
            company_id: 1
        }
    });
    
    results.total++;
    if (!noEmailResult.success && noEmailResult.response.status === 400) {
        results.success++;
        console.log('   ✓ Erro esperado retornado');
        console.log(`   ✓ Status: ${noEmailResult.response.status}`);
        console.log(`   ✓ Mensagem: ${noEmailResult.response.body?.message}`);
        results.tests.push({ name: 'Registro sem email', status: 'PASS' });
    } else {
        results.failed++;
        console.log('   ✗ Deveria ter retornado erro 400');
        console.log(`   ✗ Status recebido: ${noEmailResult.response?.status}`);
        results.tests.push({ name: 'Registro sem email', status: 'FAIL' });
    }

    // ============================================
    // TESTE 5: Registro sem password
    // ============================================
    console.log('\n❌ Teste 5: Registro sem password (deve falhar)');
    
    const noPasswordResult = await apiRequest('POST', '/auth/register', {
        body: {
            username: 'testuser_nopass',
            email: 'nopass@teste.com',
            name: 'Teste',
            company_id: 1
        }
    });
    
    results.total++;
    if (!noPasswordResult.success && noPasswordResult.response.status === 400) {
        results.success++;
        console.log('   ✓ Erro esperado retornado');
        console.log(`   ✓ Status: ${noPasswordResult.response.status}`);
        console.log(`   ✓ Mensagem: ${noPasswordResult.response.body?.message}`);
        results.tests.push({ name: 'Registro sem password', status: 'PASS' });
    } else {
        results.failed++;
        console.log('   ✗ Deveria ter retornado erro 400');
        console.log(`   ✗ Status recebido: ${noPasswordResult.response?.status}`);
        results.tests.push({ name: 'Registro sem password', status: 'FAIL' });
    }

    // ============================================
    // TESTE 6: Registro sem company_id
    // ============================================
    console.log('\n❌ Teste 6: Registro sem company_id (deve falhar)');
    
    const noCompanyResult = await apiRequest('POST', '/auth/register', {
        body: {
            username: 'testuser_nocompany',
            email: 'nocompany@teste.com',
            password: 'test123',
            name: 'Teste'
        }
    });
    
    results.total++;
    if (!noCompanyResult.success && noCompanyResult.response.status === 400) {
        results.success++;
        console.log('   ✓ Erro esperado retornado');
        console.log(`   ✓ Status: ${noCompanyResult.response.status}`);
        console.log(`   ✓ Mensagem: ${noCompanyResult.response.body?.message}`);
        results.tests.push({ name: 'Registro sem company_id', status: 'PASS' });
    } else {
        results.failed++;
        console.log('   ✗ Deveria ter retornado erro 400');
        console.log(`   ✗ Status recebido: ${noCompanyResult.response?.status}`);
        results.tests.push({ name: 'Registro sem company_id', status: 'FAIL' });
    }

    // ============================================
    // TESTE 7: Registro com username duplicado
    // ============================================
    console.log('\n❌ Teste 7: Registro com username duplicado (deve falhar)');
    
    const duplicateUsernameResult = await apiRequest('POST', '/auth/register', {
        body: {
            username: validUser.username, // Mesmo username do teste 1
            email: 'different@teste.com',
            password: 'test123',
            name: 'Outro Usuário',
            company_id: 1
        }
    });
    
    results.total++;
    if (!duplicateUsernameResult.success && duplicateUsernameResult.response.status === 400) {
        results.success++;
        console.log('   ✓ Erro esperado retornado');
        console.log(`   ✓ Status: ${duplicateUsernameResult.response.status}`);
        console.log(`   ✓ Mensagem: ${duplicateUsernameResult.response.body?.message}`);
        results.tests.push({ name: 'Registro com username duplicado', status: 'PASS' });
    } else {
        results.failed++;
        console.log('   ✗ Deveria ter retornado erro 400');
        console.log(`   ✗ Status recebido: ${duplicateUsernameResult.response?.status}`);
        results.tests.push({ name: 'Registro com username duplicado', status: 'FAIL' });
    }

    // ============================================
    // TESTE 8: Registro com email duplicado
    // ============================================
    console.log('\n❌ Teste 8: Registro com email duplicado (deve falhar)');
    
    const duplicateEmailResult = await apiRequest('POST', '/auth/register', {
        body: {
            username: 'differentuser_' + Date.now(),
            email: validUser.email, // Mesmo email do teste 1
            password: 'test123',
            name: 'Outro Usuário',
            company_id: 1
        }
    });
    
    results.total++;
    if (!duplicateEmailResult.success && duplicateEmailResult.response.status === 400) {
        results.success++;
        console.log('   ✓ Erro esperado retornado');
        console.log(`   ✓ Status: ${duplicateEmailResult.response.status}`);
        console.log(`   ✓ Mensagem: ${duplicateEmailResult.response.body?.message}`);
        results.tests.push({ name: 'Registro com email duplicado', status: 'PASS' });
    } else {
        results.failed++;
        console.log('   ✗ Deveria ter retornado erro 400');
        console.log(`   ✗ Status recebido: ${duplicateEmailResult.response?.status}`);
        results.tests.push({ name: 'Registro com email duplicado', status: 'FAIL' });
    }

    // ============================================
    // TESTE 9: Registro com email inválido
    // ============================================
    console.log('\n❌ Teste 9: Registro com email inválido (deve falhar)');
    
    const invalidEmailResult = await apiRequest('POST', '/auth/register', {
        body: {
            username: 'testuser_bademail_' + Date.now(),
            email: 'not-an-email',
            password: 'test123',
            name: 'Teste',
            company_id: 1
        }
    });
    
    results.total++;
    if (!invalidEmailResult.success && invalidEmailResult.response.status === 400) {
        results.success++;
        console.log('   ✓ Erro esperado retornado');
        console.log(`   ✓ Status: ${invalidEmailResult.response.status}`);
        console.log(`   ✓ Mensagem: ${invalidEmailResult.response.body?.message}`);
        results.tests.push({ name: 'Registro com email inválido', status: 'PASS' });
    } else {
        results.failed++;
        console.log('   ✗ Deveria ter retornado erro 400');
        console.log(`   ✗ Status recebido: ${invalidEmailResult.response?.status}`);
        results.tests.push({ name: 'Registro com email inválido', status: 'FAIL' });
    }

    // ============================================
    // TESTE 10: Registro com senha muito curta
    // ============================================
    console.log('\n❌ Teste 10: Registro com senha muito curta (deve falhar)');
    
    const shortPasswordResult = await apiRequest('POST', '/auth/register', {
        body: {
            username: 'testuser_shortpass_' + Date.now(),
            email: 'shortpass' + Date.now() + '@teste.com',
            password: 'ab', // Menos de 3 caracteres
            name: 'Teste',
            company_id: 1
        }
    });
    
    results.total++;
    if (!shortPasswordResult.success && shortPasswordResult.response.status === 400) {
        results.success++;
        console.log('   ✓ Erro esperado retornado');
        console.log(`   ✓ Status: ${shortPasswordResult.response.status}`);
        console.log(`   ✓ Mensagem: ${shortPasswordResult.response.body?.message}`);
        results.tests.push({ name: 'Registro com senha muito curta', status: 'PASS' });
    } else {
        results.failed++;
        console.log('   ✗ Deveria ter retornado erro 400');
        console.log(`   ✗ Status recebido: ${shortPasswordResult.response?.status}`);
        results.tests.push({ name: 'Registro com senha muito curta', status: 'FAIL' });
    }

    // ============================================
    // TESTE 11: Registro com company_id inválido
    // ============================================
    console.log('\n❌ Teste 11: Registro com company_id inválido (deve falhar)');
    
    const invalidCompanyResult = await apiRequest('POST', '/auth/register', {
        body: {
            username: 'testuser_badcompany_' + Date.now(),
            email: 'badcompany' + Date.now() + '@teste.com',
            password: 'test123',
            name: 'Teste',
            company_id: 99999 // ID inexistente
        }
    });
    
    results.total++;
    if (!invalidCompanyResult.success && invalidCompanyResult.response.status === 400) {
        results.success++;
        console.log('   ✓ Erro esperado retornado');
        console.log(`   ✓ Status: ${invalidCompanyResult.response.status}`);
        console.log(`   ✓ Mensagem: ${invalidCompanyResult.response.body?.message}`);
        results.tests.push({ name: 'Registro com company_id inválido', status: 'PASS' });
    } else {
        results.failed++;
        console.log('   ✗ Deveria ter retornado erro 400');
        console.log(`   ✗ Status recebido: ${invalidCompanyResult.response?.status}`);
        results.tests.push({ name: 'Registro com company_id inválido', status: 'FAIL' });
    }

    // ============================================
    // TESTE 12: Verificar token retornado no registro
    // ============================================
    console.log('\n🔐 Teste 12: Verificar estrutura do token retornado');
    
    const newUser = generateTestUser();
    const tokenTestResult = await apiRequest('POST', '/auth/register', {
        body: newUser
    });
    
    results.total++;
    if (tokenTestResult.success && tokenTestResult.response.body?.data?.access_token) {
        const tokenData = tokenTestResult.response.body.data;
        const hasAllFields = 
            tokenData.access_token &&
            tokenData.token_type === 'Bearer' &&
            tokenData.expires_in > 0 &&
            tokenData.expires_at > 0 &&
            tokenData.user &&
            tokenData.company;
        
        if (hasAllFields) {
            results.success++;
            console.log('   ✓ Token possui todos os campos esperados');
            console.log(`   ✓ token_type: ${tokenData.token_type}`);
            console.log(`   ✓ expires_in: ${tokenData.expires_in} segundos`);
            console.log(`   ✓ user: ${JSON.stringify(tokenData.user)}`);
            console.log(`   ✓ company: ${JSON.stringify(tokenData.company)}`);
            results.tests.push({ name: 'Estrutura do token', status: 'PASS' });
        } else {
            results.failed++;
            console.log('   ✗ Token não possui todos os campos esperados');
            results.tests.push({ name: 'Estrutura do token', status: 'FAIL' });
        }
    } else {
        results.failed++;
        console.log('   ✗ Falha ao obter token');
        results.tests.push({ name: 'Estrutura do token', status: 'FAIL' });
    }

    // ============================================
    // TESTE 13: Usar token do registro para acessar /auth/me
    // ============================================
    console.log('\n🔐 Teste 13: Usar token do registro para acessar /auth/me');
    
    if (tokenTestResult.success && tokenTestResult.response.body?.data?.access_token) {
        const newToken = tokenTestResult.response.body.data.access_token;
        
        const meResult = await apiRequest('GET', '/auth/me', {
            token: newToken
        });
        
        results.total++;
        if (meResult.success && meResult.response.status === 200) {
            results.success++;
            console.log('   ✓ Token do registro funciona corretamente');
            console.log(`   ✓ Usuário autenticado: ${meResult.response.body?.data?.user?.username}`);
            results.tests.push({ name: 'Token do registro funciona', status: 'PASS' });
        } else {
            results.failed++;
            console.log('   ✗ Token do registro não funciona');
            console.log(`   ✗ Erro: ${meResult.response?.body?.message || meResult.error}`);
            results.tests.push({ name: 'Token do registro funciona', status: 'FAIL' });
        }
    } else {
        results.total++;
        results.failed++;
        console.log('   ✗ Não foi possível testar - token não disponível');
        results.tests.push({ name: 'Token do registro funciona', status: 'SKIP' });
    }

    // ============================================
    // TESTE 14: Registro via método GET (deve falhar)
    // ============================================
    console.log('\n❌ Teste 14: Registro via método GET (deve falhar)');
    
    const getRegisterResult = await apiRequest('GET', '/auth/register');
    
    results.total++;
    if (!getRegisterResult.success && getRegisterResult.response.status === 405) {
        results.success++;
        console.log('   ✓ Método GET não permitido');
        console.log(`   ✓ Status: ${getRegisterResult.response.status}`);
        results.tests.push({ name: 'Método GET não permitido', status: 'PASS' });
    } else {
        results.failed++;
        console.log('   ✗ Deveria retornar 405 Method Not Allowed');
        console.log(`   ✗ Status recebido: ${getRegisterResult.response?.status}`);
        results.tests.push({ name: 'Método GET não permitido', status: 'FAIL' });
    }

    // ============================================
    // RELATÓRIO FINAL
    // ============================================
    console.log('\n' + '='.repeat(80));
    console.log('📊 RELATÓRIO FINAL - TESTES DE REGISTRO');
    console.log('='.repeat(80));
    console.log(`Total de testes: ${results.total}`);
    console.log(`✅ Sucesso: ${results.success}`);
    console.log(`❌ Falhas: ${results.failed}`);
    console.log(`📈 Taxa de sucesso: ${((results.success / results.total) * 100).toFixed(1)}%`);
    
    console.log('\nDetalhes:');
    results.tests.forEach((test, index) => {
        const icon = test.status === 'PASS' ? '✅' : test.status === 'FAIL' ? '❌' : '⏭️';
        console.log(`  ${index + 1}. ${icon} ${test.name}: ${test.status}`);
    });

    return results;
}

// Executar testes se chamado diretamente
if (require.main === module) {
    runRegisterTests()
        .then(results => {
            console.log('\n✅ Testes de registro concluídos!');
            process.exit(results.failed > 0 ? 1 : 0);
        })
        .catch(error => {
            console.error('\n❌ Erro ao executar testes:', error);
            process.exit(1);
        });
}

module.exports = { runRegisterTests };
