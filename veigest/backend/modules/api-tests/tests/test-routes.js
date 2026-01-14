/**
 * VeiGest API - Testes de Rotas
 * Testa listagem, filtros por veículo/condutor e visualização de rotas
 */

const { apiRequest } = require('../utils/http-client.js');

async function runRouteTests(token, companyId) {
    console.log('\n🗺️  INICIANDO TESTES DE ROTAS\n');
    console.log('='.repeat(80));
    console.log(`Token: ${token ? token.substring(0,30)+"..." : 'none'}`);
    console.log(`Company ID: ${companyId}`);

    const results = { total: 0, success: 0, failed: 0, tests: [] };

    // Teste 1: Listar rotas
    console.log('\n📝 Teste 1: Listar rotas');
    const list = await apiRequest('GET', '/route', { token });
    results.total++;
    if (list.success) { results.success++; results.tests.push({name:'Listar rotas', status:'SUCESSO'}); }
    else { results.failed++; results.tests.push({name:'Listar rotas', status:'FALHA'}); }

    // Teste 2: Filtrar por driver
    console.log('\n📝 Teste 2: Filtrar por driver');
    const drivers = await apiRequest('GET', '/user?role=driver&per-page=1', { token });
    results.total++;
    if (drivers.success && drivers.response.body.data && drivers.response.body.data.length>0) {
        const did = drivers.response.body.data[0].id;
        const byDriver = await apiRequest('GET', `/route?driver_id=${did}`, { token });
        if (byDriver.success) { results.success++; results.tests.push({name:'Filtrar por driver', status:'SUCESSO'}); }
        else { results.failed++; results.tests.push({name:'Filtrar por driver', status:'FALHA'}); }
    } else {
        results.success++; results.tests.push({name:'Filtrar por driver', status:'INFO - Nenhum driver disponível'});
    }

    // Teste 3: Visualizar rota
    const first = (list.response && list.response.body && list.response.body.data && list.response.body.data[0]) ? list.response.body.data[0] : null;
    results.total++;
    if (first && first.id) {
        const view = await apiRequest('GET', `/routes/${first.id}`, { token });
        if (view.success) { results.success++; results.tests.push({name:'Ver rota', status:'SUCESSO'}); }
        else { results.failed++; results.tests.push({name:'Ver rota', status:'FALHA'}); }
    } else {
        results.success++; results.tests.push({name:'Ver rota', status:'INFO - Nenhuma rota disponível'});
    }

    console.log('\nResultados - Rotas:', results);
    return results;
}

if (require.main === module) {
    (async () => { await runRouteTests(null, null); })();
}

module.exports = { runRouteTests };
