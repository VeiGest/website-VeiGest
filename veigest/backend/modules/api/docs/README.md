# 📚 VeiGest API - Documentação Completa

## 📖 Visão Geral

Esta documentação completa da API RESTful VeiGest fornece guias detalhados sobre arquitetura, implementação, configuração e manutenção do sistema.

**URL de Produção:** `https://veigestback.dryadlang.org/api`

## 📁 Estrutura da Documentação

### 🏗️ Arquitetura e Design
- **[ARQUITETURA.md](ARQUITETURA.md)** - Visão geral da arquitetura da API
- **[ESTRUTURA_CODIGO.md](ESTRUTURA_CODIGO.md)** - Como o código está organizado
- **[PADROES_DESIGN.md](PADROES_DESIGN.md)** - Padrões de design implementados

### 🔧 Configuração e Setup
- **[CONFIGURACAO_AMBIENTE.md](CONFIGURACAO_AMBIENTE.md)** - Como configurar o ambiente de desenvolvimento

### 🔄 Desenvolvimento e Manutenção
- **[FUTURAS_MODIFICACOES.md](FUTURAS_MODIFICACOES.md)** - Plano de melhorias e expansões

### 📋 Changelogs
- **[CHANGELOG-2026-01-03.md](CHANGELOG-2026-01-03.md)** - Correções de URL, credenciais, rotas e novo endpoint link-company

### 🚨 Troubleshooting
- **[TROUBLESHOOTING.md](TROUBLESHOOTING.md)** - Solução de problemas comuns

## 🚀 Início Rápido

### Pré-requisitos
- Docker e Docker Compose
- Node.js 18+ (para testes)
- PHP 8.1+ com Yii2

### Setup Básico
```bash
# 1. Clonar repositório
git clone <repository-url>
cd website-VeiGest

# 2. Iniciar containers
docker-compose up -d

# 3. Executar migrações
cd veigest
php yii migrate

# 4. Testar API
cd backend/modules/api-tests
node run-all-tests.js
```

### Primeiro Teste
```bash
# Login de teste (produção)
curl -X POST https://veigestback.dryadlang.org/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username": "admin", "password": "admin"}'
```

## 📊 Status da API

| Componente | Status | Versão |
|------------|--------|---------|
| Autenticação JWT | ✅ Completo | v1.0 |
| Multi-tenancy | ✅ Completo | v1.0 |
| RBAC Básico | ✅ Completo | v1.0 |
| CRUD Veículos | ✅ Básico | v1.0 |
| CRUD Usuários | ✅ Básico | v1.0 |
| Testes Automatizados | ✅ Completo | v1.0 |
| Documentação | ✅ Completo | v1.0 |
| Docker Setup | ✅ Completo | v1.0 |

### 🎯 Funcionalidades Implementadas
- ✅ Autenticação Bearer Token com JWT
- ✅ Isolamento de dados por empresa (multi-tenancy)
- ✅ Controle básico de permissões
- ✅ Endpoints RESTful para veículos e usuários
- ✅ Validação de dados e tratamento de erros
- ✅ Testes automatizados com JavaScript
- ✅ Configuração completa com Docker
- ✅ Documentação abrangente

### 🚧 Funcionalidades Planejadas
- 🔄 Módulo completo de manutenção
- 🔄 Módulo completo de abastecimento
- 🔄 Sistema de notificações
- 🔄 Two-Factor Authentication (2FA)
- 🔄 Analytics e relatórios avançados

## 🎯 Endpoints Principais

### Autenticação
- `POST /api/auth/login` - Login
- `GET /api/auth/me` - Perfil do usuário
- `POST /api/auth/refresh` - Renovar token
- `POST /api/auth/logout` - Logout

### Recursos
- `GET /api/vehicles` - Listar veículos
- `POST /api/vehicles` - Criar veículo
- `GET /api/users` - Listar usuários
- `POST /api/users` - Criar usuário

## 🔐 Credenciais de Teste

| Usuário | Username | Password | Papel |
|---------|----------|----------|-------|
| Admin | `admin` | `admin123` | Administrador |
| Gestor | `gestor` | `gestor123` | Gestor |
| Condutor | `driver1` | `driver123` | Condutor |

## 📞 Suporte

Para dúvidas ou problemas:
1. Consulte primeiro a seção **[TROUBLESHOOTING.md](TROUBLESHOOTING.md)**
2. Verifique os logs do Docker: `docker-compose logs backend`
3. Execute os testes: `cd api-tests && npm test`
4. Verifique a **[CONFIGURACAO_AMBIENTE.md](CONFIGURACAO_AMBIENTE.md)** para setup correto

## 📝 Convenções da Documentação

### Ícones Utilizados
- 📖 Documentação geral
- 🏗️ Arquitetura e estrutura
- 🔧 Configuração e setup
- 📋 Funcionalidades específicas
- 🔄 Desenvolvimento
- 🚨 Problemas e soluções
- ✅ Status positivo
- ❌ Status negativo
- ⚠️ Atenção necessária

### Formatação de Código
- **Arquivos**: `backend/modules/api/Module.php`
- **Classes**: `ApiAuthenticator`
- **Métodos**: `actionLogin()`
- **Propriedades**: `$modelClass`
- **Comandos**: `docker-compose up -d`

---

**Última atualização:** Dezembro 2024
**Versão da API:** 1.0
**Framework:** Yii2 Advanced + Docker + MySQL
