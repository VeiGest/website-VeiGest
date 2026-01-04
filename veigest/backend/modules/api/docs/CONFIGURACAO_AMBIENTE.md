# ⚙️ Configuração do Ambiente de Desenvolvimento

## 📋 Pré-requisitos do Sistema

### 🐧 Linux (Ubuntu/Debian)
```bash
# Atualizar sistema
sudo apt update && sudo apt upgrade -y

# Instalar Docker e Docker Compose
sudo apt install docker.io docker-compose -y

# Instalar Git
sudo apt install git -y

# Instalar Node.js (para testes)
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs

# Instalar PHP (opcional, para desenvolvimento local)
sudo apt install php8.1 php8.1-cli php8.1-common php8.1-mysql php8.1-zip php8.1-gd php8.1-mbstring php8.1-curl php8.1-xml php8.1-bcmath -y
```

### 🍎 macOS
```bash
# Instalar Homebrew (se não tiver)
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"

# Instalar Docker Desktop
brew install --cask docker

# Instalar Node.js
brew install node

# Instalar Git
brew install git
```

### 🪟 Windows
```powershell
# Instalar Chocolatey (se não tiver)
Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://chocolatey.org/install.ps1'))

# Instalar Docker Desktop
choco install docker-desktop

# Instalar Node.js
choco install nodejs

# Instalar Git
choco install git
```

## 🐳 Configuração do Docker

### 1. Clonagem do Repositório
```bash
# Clonar o projeto
git clone https://github.com/seu-usuario/veigest.git
cd veigest

# Entrar na pasta do backend
cd veigest
```

### 2. Arquivo .env
```bash
# Criar arquivo .env na raiz do projeto
cp .env.example .env
```

Conteúdo do `.env`:
```env
# Database
MYSQL_ROOT_PASSWORD=verysecret
MYSQL_DATABASE=veigest
MYSQL_USER=veigest_user
MYSQL_PASSWORD=veigest_pass

# API
API_BASE_URL=http://localhost:21080/api
JWT_SECRET=your-super-secret-jwt-key-here
JWT_EXPIRE=86400

# Admin User
ADMIN_USERNAME=admin
ADMIN_PASSWORD=admin123
ADMIN_EMAIL=admin@veigest.com

# Gestor User
GESTOR_USERNAME=gestor
GESTOR_PASSWORD=gestor123
GESTOR_EMAIL=gestor@veigest.com
```

### 3. Inicialização dos Containers
```bash
# Construir e iniciar containers
docker-compose up -d --build

# Verificar status dos containers
docker-compose ps

# Ver logs (se necessário)
docker-compose logs -f
```

### 4. Verificação da Instalação
```bash
# Verificar se os containers estão rodando
docker-compose ps

# Testar conexão com banco de dados
docker-compose exec db mysql -u veigest_user -pveigest_pass veigest -e "SHOW TABLES;"

# Testar API
curl -X GET http://localhost:21080/api
```

## 🗄️ Configuração do Banco de Dados

### 1. Importação Inicial
```bash
# Entrar no container do banco
docker-compose exec db bash

# Importar schema inicial
mysql -u root -pverysecret veigest < /docker-entrypoint-initdb.d/database.sql

# Sair do container
exit
```

### 2. Verificação dos Dados
```bash
# Conectar ao banco via container
docker-compose exec db mysql -u veigest_user -pveigest_pass veigest

# Verificar tabelas criadas
SHOW TABLES;

# Verificar usuários
SELECT id, username, name, estado FROM user LIMIT 5;

# Verificar empresas
SELECT id, code, name, status FROM company LIMIT 5;

# Sair
exit;
```

### 3. Acesso via phpMyAdmin
- URL: http://localhost:8080
- Usuário: root
- Senha: verysecret
- Banco: veigest

## 🔧 Configuração da API

### 1. Arquivos de Configuração

#### main-local.php
```php
<?php
return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=db;dbname=veigest',
            'username' => 'veigest_user',
            'password' => 'veigest_pass',
            'charset' => 'utf8',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
    ],
];
```

#### main.php (API Module)
```php
<?php
return [
    'modules' => [
        'api' => [
            'class' => 'app\modules\api\Module',
            'components' => [
                'urlManager' => [
                    'class' => 'yii\web\UrlManager',
                    'enablePrettyUrl' => true,
                    'showScriptName' => false,
                    'rules' => [
                        // Auth endpoints
                        'POST api/auth/login' => 'api/auth/login',
                        'POST api/auth/logout' => 'api/auth/logout',
                        'GET api/auth/me' => 'api/auth/me',
                        'POST api/auth/refresh' => 'api/auth/refresh',

                        // Vehicle endpoints
                        'GET api/vehicles' => 'api/vehicle/index',
                        'POST api/vehicles' => 'api/vehicle/create',
                        'GET api/vehicles/<id:\d+>' => 'api/vehicle/view',
                        'PUT api/vehicles/<id:\d+>' => 'api/vehicle/update',
                        'DELETE api/vehicles/<id:\d+>' => 'api/vehicle/delete',

                        // User endpoints
                        'GET api/users' => 'api/user/index',
                        'POST api/users' => 'api/user/create',
                        'GET api/users/<id:\d+>' => 'api/user/view',
                        'PUT api/users/<id:\d+>' => 'api/user/update',
                        'DELETE api/users/<id:\d+>' => 'api/user/delete',
                    ],
                ],
            ],
        ],
    ],
    'components' => [
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                // Regras globais da API
            ],
        ],
        'request' => [
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        'response' => [
            'format' => 'json',
        ],
    ],
];
```

### 2. Permissões dos Arquivos
```bash
# Definir permissões corretas
sudo chown -R www-data:www-data veigest/
sudo chmod -R 755 veigest/
sudo chmod -R 777 veigest/runtime/
sudo chmod -R 777 veigest/web/assets/
```

### 3. Instalação de Dependências
```bash
# Entrar no container do backend
docker-compose exec backend bash

# Instalar dependências do Composer
composer install

# Gerar chave de aplicação (se necessário)
php yii key/generate

# Sair
exit
```

## 🧪 Configuração dos Testes

### 1. Dependências de Teste
```bash
# Instalar dependências globais
npm install -g http-server jsonwebtoken

# Instalar dependências do projeto
cd api-tests
npm install
```

### 2. Arquivo de Configuração dos Testes
```javascript
// config/test-config.js
module.exports = {
    baseURL: 'http://localhost:21080/api',
    testUsers: {
        admin: {
            username: 'admin',
            password: 'admin123'
        },
        gestor: {
            username: 'gestor',
            password: 'gestor123'
        }
    },
    timeout: 5000,
    retries: 3
};
```

### 3. Executar Testes
```bash
# Executar todos os testes
cd api-tests
npm test

# Executar testes específicos
npm run test:auth
npm run test:vehicles
npm run test:users

# Executar testes com relatório detalhado
npm run test:verbose
```

## 🔍 Debugging e Desenvolvimento

### 1. Logs da Aplicação
```bash
# Ver logs do backend
docker-compose logs -f backend

# Ver logs do banco
docker-compose logs -f db

# Ver logs de todos os containers
docker-compose logs -f
```

### 2. Acesso aos Containers
```bash
# Bash no backend
docker-compose exec backend bash

# Bash no banco
docker-compose exec db bash

# Bash no frontend
docker-compose exec frontend bash
```

### 3. Desenvolvimento Local (sem Docker)
```bash
# Instalar dependências
composer install

# Configurar banco local
# Editar config/main-local.php com credenciais locais

# Iniciar servidor de desenvolvimento
php yii serve --port=8080

# API disponível em: http://localhost:8080/api
```

## 🌐 Configuração de Rede

### 1. Portas Utilizadas
- **21080**: API Backend (Nginx)
- **3306**: MySQL Database
- **8080**: phpMyAdmin
- **3000**: Frontend (se aplicável)

### 2. Firewall (Ubuntu)
```bash
# Permitir portas necessárias
sudo ufw allow 21080
sudo ufw allow 8080
sudo ufw allow 3306

# Verificar status
sudo ufw status
```

### 3. Hosts File (opcional)
```bash
# Editar /etc/hosts
sudo nano /etc/hosts

# Adicionar linha:
127.0.0.1    api.veigest.local
```

## 🔐 Configurações de Segurança

### 1. JWT Secret
```bash
# Gerar chave JWT segura
openssl rand -base64 32

# Atualizar no .env
JWT_SECRET=chave-gerada-aqui
```

### 2. Senhas do Banco
```env
# Usar senhas fortes
MYSQL_ROOT_PASSWORD=senha-muito-forte-aqui
MYSQL_PASSWORD=outra-senha-forte-aqui
```

### 3. CORS Configuration
```php
// Em components/ApiAuthenticator.php ou config
'corsFilter' => [
    'class' => \yii\filters\Cors::class,
    'cors' => [
        'Origin' => ['http://localhost:3000', 'https://veigest.com'],
        'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
        'Access-Control-Request-Headers' => ['*'],
        'Access-Control-Allow-Credentials' => true,
        'Access-Control-Max-Age' => 86400,
    ],
],
```

## 🚀 Verificação Final

### 1. Checklist de Instalação
- [ ] Docker containers rodando
- [ ] Banco de dados acessível
- [ ] API respondendo
- [ ] Autenticação funcionando
- [ ] Testes passando
- [ ] phpMyAdmin acessível

### 2. Teste de Funcionalidade
```bash
# Teste básico da API
curl -X GET http://localhost:21080/api

# Teste de autenticação
curl -X POST http://localhost:21080/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin123"}'

# Teste de endpoint protegido (usar token do login)
curl -X GET http://localhost:21080/api/auth/me \
  -H "Authorization: Bearer SEU_TOKEN_AQUI"
```

### 3. Monitoramento
```bash
# Status dos containers
docker-compose ps

# Uso de recursos
docker stats

# Logs em tempo real
docker-compose logs -f --tail=100
```

---

**Próximo:** [TROUBLESHOOTING.md](TROUBLESHOOTING.md) - Solução de problemas comuns
