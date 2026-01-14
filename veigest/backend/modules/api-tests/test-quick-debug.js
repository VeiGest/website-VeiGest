const { apiRequest } = require('./utils/http-client.js');

(async () => {
  const login = await apiRequest('POST', '/auth/login', { body: { username: 'admin', password: 'admin' } });
  const token = login.response.body.data.access_token;
  
  console.log('=== TESTANDO STATS ===');
  const stats = await apiRequest('GET', '/companies/1/stats', { token });
  console.log('Status:', stats.response?.statusCode);
  if (!stats.success) {
    console.log('Error:', stats.response?.body?.message);
  } else {
    console.log('Success!', JSON.stringify(stats.response.body, null, 2));
  }
  
  console.log('\n=== TESTANDO UPDATE ===');
  const update = await apiRequest('PUT', '/company/1', { 
    token,
    body: { telefone: '+351987654321', morada: 'Teste', cidade: 'Lisboa' }
  });
  console.log('Status:', update.response?.statusCode);
  if (!update.success) {
    console.log('Error:', update.response?.body?.message);
  } else {
    console.log('Success!');
  }
})();
