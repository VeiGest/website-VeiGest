# Configuração das Rotas da API VeiGest

Para integrar a API reestruturada no projeto, adicione as seguintes configurações no arquivo `backend/config/main.php`:

## Registrar o Módulo API

```php
'modules' => [
    'api' => [
        'class' => 'backend\modules\api\Module',
    ],
    // ... outros módulos existentes
],
```

## Configurar URL Manager

```php
'urlManager' => [
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    'rules' => [
        // Rotas da API REST
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => 'api/auth',
            'pluralize' => false,
            'extraPatterns' => [
                'POST login' => 'login',
                'GET me' => 'me',
                'POST refresh' => 'refresh',
                'POST logout' => 'logout',
                'GET info' => 'info',
            ],
        ],
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => 'api/vehicle',
            'pluralize' => true,
            'extraPatterns' => [
                'GET {id}/maintenances' => 'maintenances',
                'GET {id}/fuel-logs' => 'fuel-logs',
                'GET {id}/stats' => 'stats',
                'GET by-status/{status}' => 'by-status',
            ],
        ],
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => 'api/user',
            'pluralize' => true,
            'extraPatterns' => [
                'GET drivers' => 'drivers',
                'GET profile' => 'profile',
                'PUT {id}/photo' => 'update-photo',
                'GET by-company/{company_id}' => 'by-company',
            ],
        ],
        
        // ... outras rotas existentes do projeto
    ],
],
```

## Configurações de Componentes (Opcional)

Para melhorar a performance e segurança, adicione:

```php
'components' => [
    // ... componentes existentes
    
    'response' => [
        'class' => 'yii\web\Response',
        'on beforeSend' => function ($event) {
            $response = $event->sender;
            if (strpos(Yii::$app->request->url, '/api/') === 0) {
                $response->headers->add('X-API-Version', '1.0');
                $response->headers->add('X-Powered-By', 'VeiGest-API');
            }
        },
    ],
    
    'user' => [
        'identityClass' => 'common\models\User',
        'enableAutoLogin' => false, // Importante para API
        'enableSession' => false,   // Importante para API
        'loginUrl' => null,         // Importante para API
    ],
],
```

## Schema da Base de Dados

Certifique-se de que as seguintes tabelas existem:

```sql
-- Tabela de empresas
CREATE TABLE companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(10),
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telefone VARCHAR(20),
    nif VARCHAR(50),
    morada VARCHAR(200),
    cidade VARCHAR(100),
    codigo_postal VARCHAR(20),
    pais VARCHAR(100),
    estado ENUM('ativo', 'inativo') DEFAULT 'ativo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de veículos
CREATE TABLE vehicles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    license_plate VARCHAR(20) NOT NULL,
    brand VARCHAR(100) NOT NULL,
    model VARCHAR(100) NOT NULL,
    year INT,
    fuel_type ENUM('gasoline', 'diesel', 'electric', 'hybrid', 'other') DEFAULT 'gasoline',
    mileage INT DEFAULT 0,
    status ENUM('active', 'maintenance', 'inactive') DEFAULT 'active',
    driver_id INT,
    photo VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id),
    FOREIGN KEY (driver_id) REFERENCES user(id)
);

-- Tabela de manutenções
CREATE TABLE maintenances (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vehicle_id INT NOT NULL,
    tipo ENUM('preventiva', 'corretiva', 'revisao', 'inspecao') NOT NULL,
    descricao TEXT NOT NULL,
    custo DECIMAL(10,2) DEFAULT 0,
    data_manutencao DATE,
    quilometragem INT,
    fornecedor VARCHAR(150),
    estado ENUM('agendada', 'em_andamento', 'concluida', 'cancelada') DEFAULT 'agendada',
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE
);

-- Tabela de abastecimentos
CREATE TABLE fuel_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vehicle_id INT NOT NULL,
    litros DECIMAL(8,3) NOT NULL,
    custo_total DECIMAL(8,2) NOT NULL,
    quilometragem INT,
    data_abastecimento DATETIME DEFAULT CURRENT_TIMESTAMP,
    local VARCHAR(200),
    preco_por_litro DECIMAL(6,3),
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE
);
```

## Atualizar Tabela de Usuários

Adicione as seguintes colunas à tabela `user` se não existirem:

```sql
ALTER TABLE user 
ADD COLUMN company_id INT,
ADD COLUMN phone VARCHAR(20),
ADD COLUMN tipo ENUM('admin', 'manager', 'condutor', 'user') DEFAULT 'user',
ADD COLUMN license_number VARCHAR(50),
ADD COLUMN license_expiry DATE,
ADD COLUMN photo VARCHAR(255),
ADD FOREIGN KEY (company_id) REFERENCES companies(id);
```

## Testar a Configuração

Após aplicar as configurações, teste a API:

```bash
# Teste de conectividade
curl http://localhost/backend/web/api/auth/info

# Teste de login
curl -X POST http://localhost/backend/web/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin123"}'
```

Se tudo estiver configurado corretamente, você receberá respostas JSON válidas.
