# VeiGest - Documentação de Testes

## Índice

1. [Visão Geral](#visão-geral)
2. [Estrutura de Testes](#estrutura-de-testes)
3. [Testes Unitários](#testes-unitários)
4. [Testes Funcionais](#testes-funcionais)
5. [Como Executar os Testes](#como-executar-os-testes)
6. [Fixtures](#fixtures)
7. [Convenções](#convenções)

---

## Visão Geral

O projeto VeiGest utiliza **Codeception** como framework de testes, integrado com o **Yii2 Framework**. A suíte de testes inclui:

- **Testes Unitários**: Validação de modelos e lógica de negócio
- **Testes Funcionais**: Teste de fluxos completos da aplicação

### Requisitos Atendidos

Conforme especificação do projeto:

| Requisito | Implementado | Descrição |
|-----------|--------------|-----------|
| 5 Testes Unitários (mínimo) | ✅ 25+ testes | Modelos User, Vehicle, Maintenance, LoginForm |
| 5 Testes Funcionais (mínimo) | ✅ 20+ testes | Login backend, CRUD Vehicles, Users, Dashboard |
| Teste de Login Back-office | ✅ | LoginCest.php com 8 cenários |
| Testes em Modelos (Model) | ✅ | UserTest, VehicleTest, MaintenanceTest |
| Validação de Parâmetros | ✅ | Todos os modelos testam rules() |
| Integração com BD (Active Record) | ✅ | Testes CRUD completos |

---

## Estrutura de Testes

```
veigest/
├── common/
│   ├── fixtures/                    # Fixtures compartilhadas
│   │   ├── UserFixture.php
│   │   ├── CompanyFixture.php
│   │   ├── VehicleFixture.php
│   │   └── MaintenanceFixture.php
│   └── tests/
│       ├── _data/                   # Dados das fixtures
│       │   ├── user.php
│       │   ├── company.php
│       │   ├── vehicle.php
│       │   └── maintenance.php
│       └── unit/
│           └── models/              # Testes unitários
│               ├── LoginFormTest.php
│               ├── UserTest.php
│               ├── VehicleTest.php
│               └── MaintenanceTest.php
├── backend/
│   └── tests/
│       └── functional/              # Testes funcionais backend
│           ├── LoginCest.php
│           └── UserCest.php
└── frontend/
    └── tests/
        └── functional/              # Testes funcionais frontend
            ├── VehicleCest.php
            ├── DashboardCest.php
            └── MaintenanceCest.php
```

---

## Testes Unitários

### LoginFormTest (5 testes)

Testa o formulário de autenticação.

| Teste | Descrição |
|-------|-----------|
| `testLoginNoUser` | Login com utilizador inexistente falha |
| `testLoginWrongPassword` | Login com password incorreta falha |
| `testValidationRequired` | Campos obrigatórios são validados |
| `testLoginInactiveUser` | Utilizador inativo não consegue login |
| `testRememberMeValidation` | Validação da opção "Lembrar-me" |

### UserTest (10 testes)

Testa o modelo User (Active Record).

| Teste | Descrição |
|-------|-----------|
| `testValidationRequired` | Campos obrigatórios: username, name, email, company_id |
| `testValidationEmail` | Formato de email é validado |
| `testValidationUniqueUsername` | Username duplicado é rejeitado |
| `testPasswordHashing` | Password é hashada corretamente |
| `testAuthKeyGeneration` | AuthKey é gerada automaticamente |
| `testFindByUsername` | Método estático findByUsername funciona |
| `testFindByEmail` | Método estático findByEmail funciona |
| `testValidationRole` | Validação de roles (admin, manager, driver) |
| `testValidationStatus` | Validação de status (active, inactive) |
| `testCRUDOperations` | Ciclo completo: Create, Read, Update, Delete |

### VehicleTest (10 testes)

Testa o modelo Vehicle (Active Record).

| Teste | Descrição |
|-------|-----------|
| `testValidationRequired` | Campos obrigatórios: license_plate, brand, model, status |
| `testValidationStatus` | Status válidos: active, maintenance, inactive |
| `testValidationFuelType` | Tipos de combustível válidos |
| `testConstants` | Constantes de status e fuel_type estão corretas |
| `testOptionsHelpers` | Métodos optsFuelType() e optsStatus() |
| `testDisplayMethods` | Métodos displayFuelType() e displayStatus() |
| `testPTAliases` | Aliases PT-EN funcionam (matricula, marca, modelo) |
| `testUniqueLicensePlatePerCompany` | Matrícula única por empresa |
| `testCRUDOperations` | Ciclo completo: Create, Read, Update, Delete |
| `testNumericValidation` | Validação de campos numéricos (year, mileage) |

### MaintenanceTest (10 testes)

Testa o modelo Maintenance (Active Record).

| Teste | Descrição |
|-------|-----------|
| `testValidationRequired` | Campos obrigatórios: company_id, vehicle_id, type, date |
| `testValidationStatus` | Status válidos: scheduled, completed, cancelled |
| `testConstants` | Constantes de status estão corretas |
| `testGetTypes` | Método getTypes() retorna tipos de manutenção |
| `testValidationDateFormat` | Formato de data Y-m-d é validado |
| `testValidationCost` | Custo aceita apenas valores numéricos |
| `testPTAliases` | Aliases PT-EN funcionam (tipo, data, custo) |
| `testVehicleRelation` | Relação getVehicle() funciona |
| `testVehicleExists` | Validação de vehicle_id existente |
| `testCRUDOperations` | Ciclo completo: Create, Read, Update, Delete |

---

## Testes Funcionais

### Backend - LoginCest (8 testes) ⭐ OBRIGATÓRIO

Testa o processo de login no back-office (painel administrativo).

| Teste | Descrição |
|-------|-----------|
| `testLoginPageIsAccessible` | Página de login carrega corretamente |
| `testLoginWithInvalidCredentials` | Credenciais inválidas são rejeitadas |
| `testLoginWithEmptyFields` | Validação de campos obrigatórios |
| `testAdminLoginSuccessfully` | ✅ Admin consegue autenticar |
| `testManagerLoginSuccessfully` | ✅ Manager consegue autenticar |
| `testDriverCannotAccessBackend` | ❌ Driver é bloqueado no backend |
| `testLogout` | Logout funciona corretamente |
| `testRememberMeCheckboxExists` | Checkbox "Lembrar-me" existe |

### Backend - UserCest (5 testes)

Testa a gestão de utilizadores no backend.

| Teste | Descrição |
|-------|-----------|
| `testUserIndexIsAccessible` | Lista de utilizadores é acessível |
| `testUserListShowsData` | Lista mostra dados dos utilizadores |
| `testUserCreateFormIsAccessible` | Formulário de criação carrega |
| `testUserCreateValidation` | Validação de campos obrigatórios |
| `testUserView` | Visualização de detalhes funciona |

### Frontend - VehicleCest (8 testes)

Testa a gestão de veículos no frontend.

| Teste | Descrição |
|-------|-----------|
| `testVehicleIndexIsAccessible` | Lista de veículos é acessível |
| `testVehicleListShowsData` | Lista mostra dados dos veículos |
| `testVehicleCreateFormIsAccessible` | Formulário de criação carrega |
| `testVehicleCreateValidation` | Validação de campos obrigatórios |
| `testVehicleCreateSuccess` | Criação de veículo com sucesso |
| `testVehicleView` | Visualização de detalhes funciona |
| `testVehicleStatusFilter` | Filtros de status funcionam |
| `testVehicleEditFormIsAccessible` | Formulário de edição carrega |

### Frontend - DashboardCest (3 testes)

Testa o dashboard principal.

| Teste | Descrição |
|-------|-----------|
| `testDashboardIsAccessible` | Dashboard é acessível após login |
| `testDashboardShowsStatistics` | Dashboard mostra estatísticas |
| `testNavigationMenuExists` | Menu de navegação existe |

### Frontend - MaintenanceCest (5 testes)

Testa a gestão de manutenções.

| Teste | Descrição |
|-------|-----------|
| `testMaintenanceIndexIsAccessible` | Lista de manutenções é acessível |
| `testMaintenanceListShowsData` | Lista mostra dados |
| `testMaintenanceCreateFormIsAccessible` | Formulário de criação carrega |
| `testMaintenanceCreateValidation` | Validação de campos obrigatórios |
| `testMaintenanceView` | Visualização de detalhes funciona |

---

## Como Executar os Testes

### Pré-requisitos

1. Ter o projeto configurado com Docker ou ambiente local
2. Base de dados de teste configurada
3. Codeception instalado via Composer

### Comandos

```bash
# Ir para o diretório do projeto
cd veigest

# Executar TODOS os testes
./vendor/bin/codecept run

# Executar apenas testes unitários
./vendor/bin/codecept run common/tests/unit

# Executar apenas testes funcionais do backend
./vendor/bin/codecept run backend/tests/functional

# Executar apenas testes funcionais do frontend
./vendor/bin/codecept run frontend/tests/functional

# Executar um teste específico
./vendor/bin/codecept run common/tests/unit/models/UserTest

# Executar com detalhes (verbose)
./vendor/bin/codecept run --debug

# Executar por grupo
./vendor/bin/codecept run --group login
./vendor/bin/codecept run --group vehicle
./vendor/bin/codecept run --group maintenance
```

### Reconstruir Actors (se necessário)

```bash
./vendor/bin/codecept build
```

---

## Fixtures

As fixtures são dados de teste carregados antes de cada teste.

### Localização

- **Definição**: `common/fixtures/`
- **Dados**: `common/tests/_data/`

### Fixtures Disponíveis

| Fixture | Tabela | Dados |
|---------|--------|-------|
| UserFixture | users | 4 utilizadores (admin, manager, driver, inactive) |
| CompanyFixture | companies | 2 empresas |
| VehicleFixture | vehicles | 4 veículos |
| MaintenanceFixture | maintenances | 3 manutenções |

### Dados de Teste

**Utilizadores:**
- `test_admin` / senha do hash - Admin
- `test_manager` / senha do hash - Manager
- `test_driver` / senha do hash - Driver
- `test_inactive` / senha do hash - Inativo

**Veículos:**
- AA-00-AA - Toyota Corolla (active)
- BB-11-BB - Ford Transit (active)
- CC-22-CC - Renault Kangoo (maintenance)
- DD-33-DD - Mercedes Sprinter (active)

---

## Convenções

### Nomenclatura

- Testes unitários: `*Test.php` com métodos `test*`
- Testes funcionais: `*Cest.php` com métodos `test*`
- Fixtures: `*Fixture.php`

### Grupos (@group)

```php
/**
 * @group unit
 * @group models
 * @group user
 */
```

Grupos disponíveis:
- `unit` - Testes unitários
- `functional` - Testes funcionais
- `backend` - Testes do backend
- `frontend` - Testes do frontend
- `login` - Testes de autenticação
- `vehicle` - Testes de veículos
- `maintenance` - Testes de manutenção
- `user` - Testes de utilizador

### Padrão de Teste Active Record (CRUD)

```php
public function testCRUDOperations()
{
    // CREATE
    $model = new Model([...]);
    verify('CREATE: deve salvar', $model->save())->true();
    
    // READ
    $found = Model::findOne($id);
    verify('READ: deve encontrar', $found)->notNull();
    
    // UPDATE
    $found->attribute = 'new value';
    verify('UPDATE: deve atualizar', $found->save())->true();
    
    // DELETE
    verify('DELETE: deve eliminar', $found->delete())->equals(1);
}
```

---

## Resumo da Implementação

| Categoria | Quantidade | Status |
|-----------|------------|--------|
| Testes Unitários | 35+ | ✅ Implementado |
| Testes Funcionais | 29+ | ✅ Implementado |
| Fixtures | 4 | ✅ Implementado |
| Documentação | 1 | ✅ Implementado |

**Total: 64+ testes implementados**, excedendo significativamente os requisitos mínimos (5 unitários + 5 funcionais).
