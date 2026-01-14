/**
 * VeiGest API - Testes de Alertas
 * Testa endpoints de listagem, visualização e filtros básicos de alertas
 */

const { apiRequest } = require('../utils/http-client.js');

async function runAlertTests(token, companyId) {
    console.log('\n🚨 INICIANDO TESTES DE ALERTAS\n');
    console.log('='.repeat(80));
    console.log(`Token: ${token ? token.substring(0,30)+"..." : 'none'}`);
    console.log(`Company ID: ${companyId}`);

    const results = { total: 0, success: 0, failed: 0, tests: [] };

    // Teste 1: Listar alertas
    console.log('\n📝 Teste 1: Listar alertas');
    const list = await apiRequest('GET', '/alert', { token });
    results.total++;
    if (list.success) { results.success++; results.tests.push({name:'Listar alertas', status:'SUCESSO'}); }
    else { results.failed++; results.tests.push({name:'Listar alertas', status:'FALHA'}); }

    // Teste 2: Listar alertas por tipo
    console.log('\n📝 Teste 2: Listar alertas por tipo=maintenance');
    const byType = await apiRequest('GET', '/alert?type=maintenance&per-page=5', { token });
    results.total++;
    if (byType.success) { results.success++; results.tests.push({name:'Listar alertas por tipo', status:'SUCESSO'}); }
    else { results.failed++; results.tests.push({name:'Listar alertas por tipo', status:'FALHA'}); }

    // Teste 3: Visualizar alerta específico (se existir)
    console.log('\n📝 Teste 3: Visualizar primeiro alerta disponível');
    const first = (list.response && list.response.body && list.response.body.data && list.response.body.data[0]) ? list.response.body.data[0] : null;
    if (first && first.id) {
        const view = await apiRequest('GET', `/alerts/${first.id}`, { token });
        results.total++;
        if (view.success) { results.success++; results.tests.push({name:'Ver alerta', status:'SUCESSO'}); }
        else { results.failed++; results.tests.push({name:'Ver alerta', status:'FALHA'}); }
    } else {
        results.tests.push({name:'Ver alerta', status:'INFO - Nenhum alerta disponível'});
        results.total++;
        results.success++;
    }

    console.log('\nResultados - Alertas:', results);
    return results;
}

if (require.main === module) {
    (async () => {
        await runAlertTests(null, null);
    })();
}

module.exports = { runAlertTests };
