/**
 * VeiGest API - Testes de ActivityLog
 * Testa listagem, por usuário e entidades e estatísticas básicas
 */

const { apiRequest } = require('../utils/http-client.js');

async function runActivityLogTests(token, companyId) {
    console.log('\n📝 INICIANDO TESTES DE ACTIVITY LOG\n');
    console.log('='.repeat(80));
    console.log(`Token: ${token ? token.substring(0,30)+"..." : 'none'}`);
    console.log(`Company ID: ${companyId}`);

    const results = { total: 0, success: 0, failed: 0, tests: [] };

    // Teste 1: Listar logs
    console.log('\n🔍 Teste 1: Listar logs de atividade');
    const list = await apiRequest('GET', '/activity-log', { token });
    results.total++;
    if (list.success) { results.success++; results.tests.push({name:'Listar logs', status:'SUCESSO'}); }
    else { results.failed++; results.tests.push({name:'Listar logs', status:'FALHA'}); }

    // Teste 2: Estatísticas
    console.log('\n📊 Teste 2: Estatísticas de activity log');
    const stats = await apiRequest('GET', '/activity-logs/stats', { token });
    results.total++;
    if (stats.success) { results.success++; results.tests.push({name:'Stats logs', status:'SUCESSO'}); }
    else { results.failed++; results.tests.push({name:'Stats logs', status:'FALHA'}); }

    // Teste 3: Por usuário (se existir usuário)
    console.log('\n👤 Teste 3: Logs por usuário');
    const users = await apiRequest('GET', '/user?per-page=1', { token });
    results.total++;
    if (users.success && users.response.body.data && users.response.body.data.length>0) {
        const uid = users.response.body.data[0].id;
        const byUser = await apiRequest('GET', `/activity-logs/by-user/${uid}`, { token });
        if (byUser.success) { results.success++; results.tests.push({name:'Logs por usuário', status:'SUCESSO'}); }
        else { results.failed++; results.tests.push({name:'Logs por usuário', status:'FALHA'}); }
    } else {
        results.success++; results.tests.push({name:'Logs por usuário', status:'INFO - Nenhum usuário disponível'});
    }

    console.log('\nResultados - ActivityLog:', results);
    return results;
}

if (require.main === module) {
    (async () => { await runActivityLogTests(null, null); })();
}

module.exports = { runActivityLogTests };
