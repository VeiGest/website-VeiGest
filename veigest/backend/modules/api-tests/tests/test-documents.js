/**
 * VeiGest API - Testes de Documentos
 * Testa listagem, filtros por veículo/condutor e visualização de documentos
 */

const { apiRequest } = require('../utils/http-client.js');

async function runDocumentTests(token, companyId) {
    console.log('\n📄 INICIANDO TESTES DE DOCUMENTOS\n');
    console.log('='.repeat(80));
    console.log(`Token: ${token ? token.substring(0,30)+"..." : 'none'}`);
    console.log(`Company ID: ${companyId}`);

    const results = { total: 0, success: 0, failed: 0, tests: [] };

    // Teste 1: Listar documentos
    console.log('\n📝 Teste 1: Listar documentos');
    const list = await apiRequest('GET', '/document', { token });
    results.total++;
    if (list.success) { results.success++; results.tests.push({name:'Listar documentos', status:'SUCESSO'}); }
    else { results.failed++; results.tests.push({name:'Listar documentos', status:'FALHA'}); }

    // Teste 2: Filtrar por veículo (se houver veículos)
    console.log('\n📝 Teste 2: Filtrar por vehicle_id');
    const vehicles = await apiRequest('GET', '/vehicle?per-page=1', { token });
    results.total++;
    if (vehicles.success && vehicles.response.body.data && vehicles.response.body.data.length>0) {
        const vid = vehicles.response.body.data[0].id;
        const byVehicle = await apiRequest('GET', `/document?vehicle_id=${vid}`, { token });
        if (byVehicle.success) { results.success++; results.tests.push({name:'Filtrar por veículo', status:'SUCESSO'}); }
        else { results.failed++; results.tests.push({name:'Filtrar por veículo', status:'FALHA'}); }
    } else {
        results.success++; results.tests.push({name:'Filtrar por veículo', status:'INFO - Nenhum veículo disponível'});
    }

    // Teste 3: Visualizar documento (se existir)
    const first = (list.response && list.response.body && list.response.body.data && list.response.body.data[0]) ? list.response.body.data[0] : null;
    results.total++;
    if (first && first.id) {
        const view = await apiRequest('GET', `/documents/${first.id}`, { token });
        if (view.success) { results.success++; results.tests.push({name:'Ver documento', status:'SUCESSO'}); }
        else { results.failed++; results.tests.push({name:'Ver documento', status:'FALHA'}); }
    } else {
        results.success++; results.tests.push({name:'Ver documento', status:'INFO - Nenhum documento disponível'});
    }

    console.log('\nResultados - Documentos:', results);
    return results;
}

if (require.main === module) {
    (async () => {
        await runDocumentTests(null, null);
    })();
}

module.exports = { runDocumentTests };
