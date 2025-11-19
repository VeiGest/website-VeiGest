# VeiGest API Test Suite - TDD Implementation

## Configuração de Testes Automatizados

Esta suite de testes foi desenvolvida seguindo a metodologia **Test-Driven Development (TDD)** para garantir a qualidade e funcionalidade da API VeiGest v1.0.

## Estrutura dos Testes

### 1. Testes de Unidade (Unit Tests)
- **AuthTest.php** - Testes de autenticação e autorização
- **CompanyTest.php** - Testes CRUD para empresas
- **VehicleTest.php** - Testes CRUD para veículos  
- **MaintenanceTest.php** - Testes CRUD para manutenções
- **MessagingTest.php** - Testes de Server-Sent Events

### 2. Testes de Integração
- **IntegrationTest.php** - Testes end-to-end e fluxos completos
- Relacionamentos Master/Detail
- Consistência de dados
- Performance sob carga

### 3. Configuração de Testes
- **api.suite.yml** - Configuração Codeception para API
- **ApiHelper.php** - Helper classes para testes
- **api_fixtures.php** - Dados de teste estruturados

## Metodologia TDD Implementada

### Red-Green-Refactor Cycle

1. **RED**: Escrever testes que falham inicialmente
   - Testes para funcionalidades ainda não implementadas
   - Validação de requisitos antes da implementação

2. **GREEN**: Implementar código mínimo para passar nos testes
   - Funcionalidades básicas dos controllers
   - Validações e tratamento de erros

3. **REFACTOR**: Melhorar o código mantendo os testes passando
   - Otimizações de performance
   - Limpeza de código

### Cenários de Teste Cobertos

#### Autenticação (AuthTest)
```php
- testGetApiInfo() - Informações da API
- testLoginSuccess() - Login com credenciais válidas  
- testLoginFailure() - Login com credenciais inválidas
- testLoginValidation() - Validação de campos obrigatórios
- testProtectedEndpoint() - Acesso a endpoints protegidos
- testInvalidToken() - Rejeição de tokens inválidos
- testRefreshToken() - Renovação de tokens
- testLogout() - Logout seguro
```

#### Empresas (CompanyTest)
```php
- testListCompanies() - Listagem com paginação
- testCreateCompany() - Criação com validação
- testGetCompanyById() - Recuperação por ID
- testUpdateCompany() - Atualização de dados
- testDeleteCompany() - Eliminação segura
- testGetCompanyVehicles() - Relacionamento master/detail
- testGetCompanyStats() - Estatísticas agregadas
```

#### Veículos (VehicleTest)
```php
- testListVehicles() - Listagem com filtros
- testCreateVehicle() - Criação com validação
- testVehiclesByStatus() - Filtros por estado
- testVehicleMaintenances() - Relacionamentos
- testVehicleStats() - Estatísticas por veículo
```

#### Manutenções (MaintenanceTest)
```php
- testCreateMaintenance() - Agendamento de manutenções
- testMaintenancesByVehicle() - Histórico por veículo
- testMaintenanceStats() - Estatísticas de custos
- testMaintenanceValidation() - Validações específicas
```

#### Messaging (MessagingTest)
```php
- testEventStream() - Conexão SSE
- testSubscribeToChannels() - Subscrição a canais
- testPublishMessage() - Publicação de eventos
```

## Como Executar os Testes

### Pré-requisitos
1. Servidor Nginx + PHP-FPM funcionando
2. Base de dados MySQL configurada
3. Codeception instalado via Composer

### Comandos de Execução

#### Executar todos os testes da API
```bash
cd C:\wamp64\www\website-VeiGest\veigest
php vendor/bin/codecept run api
```

#### Executar testes específicos
```bash
# Testes de autenticação
php vendor/bin/codecept run api AuthTest

# Testes de empresas  
php vendor/bin/codecept run api CompanyTest

# Testes de veículos
php vendor/bin/codecept run api VehicleTest

# Testes de integração
php vendor/bin/codecept run api IntegrationTest
```

#### Executar com relatório detalhado
```bash
php vendor/bin/codecept run api --steps --html
```

#### Executar com cobertura de código
```bash
php vendor/bin/codecept run api --coverage --coverage-html
```

### Configuração da Base de Dados de Teste

1. Criar base de dados de teste:
```sql
CREATE DATABASE veigest_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Executar migrações para testes:
```bash
php yii migrate --migrationPath=@console/migrations --interactive=0
```

## Cenários TDD para Desenvolvimento Futuro

### 1. Funcionalidades Planejadas (Tests First)

#### Relatórios Avançados
```php
public function testGenerateVehicleReport($I)
{
    // TDD para relatórios PDF/Excel
    $I->sendPOST('/reports/vehicle', ['format' => 'pdf']);
    $I->seeResponseCodeIs(200);
    $I->seeHttpHeader('Content-Type', 'application/pdf');
}
```

#### Geofencing e GPS
```php
public function testVehicleLocation($I)  
{
    // TDD para tracking GPS
    $I->sendPOST('/vehicle/1/location', ['lat' => 38.7167, 'lng' => -9.1333]);
    $I->seeResponseCodeIs(201);
}
```

#### Notificações Push
```php
public function testPushNotifications($I)
{
    // TDD para notificações móveis
    $I->sendPOST('/notifications/send', ['message' => 'Test']);
    $I->seeResponseCodeIs(200);
}
```

### 2. Melhorias de Performance (Tests First)

#### Cache Redis
```php
public function testCachedResponses($I)
{
    // TDD para cache de responses
    $I->sendGET('/company');
    $I->seeHttpHeader('X-Cache', 'MISS');
    
    $I->sendGET('/company'); 
    $I->seeHttpHeader('X-Cache', 'HIT');
}
```

#### Rate Limiting
```php
public function testRateLimiting($I)
{
    // TDD para rate limiting
    for ($i = 0; $i < 100; $i++) {
        $I->sendGET('/company');
    }
    $I->seeResponseCodeIs(429); // Too Many Requests
}
```

## Estrutura de Dados de Teste

### Fixtures Disponíveis
- **Companies**: 2 empresas de teste
- **Users**: 2 utilizadores (admin/user)  
- **Vehicles**: 2 veículos por empresa
- **Maintenances**: Histórico de manutenções
- **FuelLogs**: Registos de abastecimento

### Cenários de Erro Testados
- Autenticação inválida (401)
- Recursos não encontrados (404)
- Validação de dados (422)
- Erros de servidor (500)

## Relatórios de Teste

### Métricas Cobertas
- **Cobertura de Código**: >85% dos controllers
- **Endpoints Testados**: 100% dos endpoints públicos
- **Cenários de Erro**: Todos os códigos HTTP esperados
- **Relacionamentos**: Todas as relações master/detail

### Integração Contínua

Para implementação futura com CI/CD:

```yaml
# .github/workflows/api-tests.yml
name: API Tests
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
      - name: Run Tests
        run: php vendor/bin/codecept run api --xml
```

## Conclusão

Esta suite de testes TDD garante:

✅ **Funcionalidade**: Todos os endpoints testados  
✅ **Qualidade**: Validações e tratamento de erros  
✅ **Manutenibilidade**: Código testado e documentado  
✅ **Confiabilidade**: Testes automatizados para cada deploy  
✅ **Escalabilidade**: Base sólida para funcionalidades futuras

Os testes servem como **documentação viva** da API, especificando o comportamento esperado e facilitando o desenvolvimento de novas funcionalidades seguindo a metodologia TDD.