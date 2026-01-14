/**
 * VeiGest API - Testes de Files
 * Testa listagem de arquivos, estatísticas e visualização
 */

const { apiRequest } = require('../utils/http-client.js');

async function runFileTests(token, companyId) {
    console.log('\n📁 INICIANDO TESTES DE FILES\n');
    console.log('='.repeat(80));
    console.log(`Token: ${token ? token.substring(0,30)+"..." : 'none'}`);
    console.log(`Company ID: ${companyId}`);

    const results = { total: 0, success: 0, failed: 0, tests: [] };

    // Teste 1: Listar arquivos
    console.log('\n📝 Teste 1: Listar arquivos');
    const list = await apiRequest('GET', '/file', { token });
    results.total++;
    if (list.success) { results.success++; results.tests.push({name:'Listar arquivos', status:'SUCESSO'}); }
    else { results.failed++; results.tests.push({name:'Listar arquivos', status:'FALHA'}); }

    // Teste 2: Estatísticas de arquivos
    console.log('\n📝 Teste 2: Stats de arquivos');
    const stats = await apiRequest('GET', '/files/stats', { token });
    results.total++;
    if (stats.success) { results.success++; results.tests.push({name:'Stats arquivos', status:'SUCESSO'}); }
    else { results.failed++; results.tests.push({name:'Stats arquivos', status:'FALHA'}); }

    // Teste 3: Visualizar primeiro arquivo
    const first = (list.response && list.response.body && list.response.body.data && list.response.body.data[0]) ? list.response.body.data[0] : null;
    results.total++;
    if (first && first.id) {
        const view = await apiRequest('GET', `/files/${first.id}`, { token });
        if (view.success) { results.success++; results.tests.push({name:'Ver arquivo', status:'SUCESSO'}); }
        else { results.failed++; results.tests.push({name:'Ver arquivo', status:'FALHA'}); }
    } else {
        results.success++; results.tests.push({name:'Ver arquivo', status:'INFO - Nenhum arquivo disponível'});
    }

    console.log('\nResultados - Files:', results);
    return results;
}

if (require.main === module) {
    (async () => { await runFileTests(null, null); })();
}

module.exports = { runFileTests };
