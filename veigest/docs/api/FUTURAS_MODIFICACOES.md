# 🚀 Futuras Modificações e Melhorias

## 📋 Visão Geral do Plano de Desenvolvimento

Este documento descreve as melhorias planejadas para a API VeiGest, organizadas por prioridade e impacto. As modificações foram categorizadas para facilitar o planejamento e implementação.

## 🎯 Prioridade Alta (Próximas 2-4 semanas)

### 1. Expansão dos Endpoints da API

#### Veículos - Funcionalidades Avançadas
```php
// Novo: Busca avançada com filtros múltiplos
GET /api/vehicles/search?brand=Toyota&year=2020&status=active&sort=created_at&order=desc

// Novo: Estatísticas de veículos
GET /api/vehicles/stats
// Retorna: total, ativos, inativos, por marca, por ano, etc.

// Novo: Upload de fotos
POST /api/vehicles/{id}/photos
Content-Type: multipart/form-data
// Campos: photos[] (array de arquivos)

// Novo: Histórico de manutenções por veículo
GET /api/vehicles/{id}/maintenance-history
```

#### Usuários - Gestão Avançada
```php
// Novo: Reset de senha
POST /api/users/{id}/reset-password
// Envia email com link de reset

// Novo: Perfis de usuário
GET /api/users/{id}/profile
PUT /api/users/{id}/profile
// Campos adicionais: avatar, bio, preferences

// Novo: Logs de atividade
GET /api/users/{id}/activity-logs?limit=50&offset=0
```

#### Manutenção - Módulo Completo
```php
// CRUD completo para manutenções
GET /api/maintenance
POST /api/maintenance
GET /api/maintenance/{id}
PUT /api/maintenance/{id}
DELETE /api/maintenance/{id}

// Novo: Agendamento de manutenções
POST /api/maintenance/{id}/schedule
// Campos: scheduled_date, priority, assigned_technician

// Novo: Relatórios de manutenção
GET /api/maintenance/reports/monthly?year=2024&month=01
GET /api/maintenance/reports/costs?start_date=2024-01-01&end_date=2024-12-31
```

#### Abastecimento - Módulo Completo
```php
// CRUD para registros de abastecimento
GET /api/fuel
POST /api/fuel
GET /api/fuel/{id}
PUT /api/fuel/{id}
DELETE /api/fuel/{id}

// Novo: Estatísticas de consumo
GET /api/fuel/stats?vehicle_id=123&period=monthly
// Retorna: consumo médio, custo total, eficiência

// Novo: Alertas de baixo combustível
GET /api/fuel/alerts
// Veículos com tanque baixo
```

### 2. Melhorias de Segurança

#### Rate Limiting
```php
// Implementar limite de requisições
'rateLimiter' => [
    'class' => \yii\filters\RateLimiter::class,
    'enableRateLimitHeaders' => true,
    'user' => function ($request, $response) {
        return \Yii::$app->user->identity ?? new RateLimitUser();
    },
],
```

#### Two-Factor Authentication (2FA)
```php
// Novo endpoint para 2FA
POST /api/auth/2fa/setup
POST /api/auth/2fa/verify
POST /api/auth/2fa/disable

// Middleware para verificar 2FA
class TwoFactorAuthMiddleware extends \yii\base\ActionFilter
{
    public function beforeAction($action)
    {
        $user = \Yii::$app->user->identity;
        if ($user && $user->two_factor_enabled && !$user->two_factor_verified) {
            throw new \yii\web\ForbiddenHttpException('2FA verification required');
        }
        return parent::beforeAction($action);
    }
}
```

#### Encryption at Rest
```php
// Criptografar dados sensíveis no banco
class EncryptedFieldBehavior extends \yii\base\Behavior
{
    public $attributes = [];

    public function encrypt($value)
    {
        return \Yii::$app->security->encryptByKey($value, \Yii::$app->params['encryption_key']);
    }

    public function decrypt($value)
    {
        return \Yii::$app->security->decryptByKey($value, \Yii::$app->params['encryption_key']);
    }
}
```

### 3. Validação e Sanitização Aprimorada

#### Validação de Dados Complexa
```php
class VehicleUpdateForm extends \yii\base\Model
{
    public $license_plate;
    public $brand;
    public $model;
    public $year;
    public $color;
    public $photos;

    public function rules()
    {
        return [
            [['license_plate', 'brand', 'model'], 'required'],
            ['license_plate', 'unique', 'targetClass' => Vehicle::class, 'filter' => function($query) {
                return $query->andWhere(['company_id' => \Yii::$app->params['company_id']]);
            }],
            ['year', 'integer', 'min' => 1900, 'max' => date('Y') + 1],
            ['photos', 'file', 'extensions' => 'png, jpg, jpeg', 'maxFiles' => 10, 'maxSize' => 5 * 1024 * 1024],
            ['color', 'match', 'pattern' => '/^#[a-f0-9]{6}$/i'],
        ];
    }
}
```

#### Sanitização Automática
```php
class SanitizeBehavior extends \yii\base\Behavior
{
    public $attributes = [];

    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_VALIDATE => 'sanitizeAttributes',
        ];
    }

    public function sanitizeAttributes($event)
    {
        foreach ($this->attributes as $attribute) {
            if ($this->owner->hasAttribute($attribute)) {
                $this->owner->$attribute = $this->sanitize($this->owner->$attribute);
            }
        }
    }

    protected function sanitize($value)
    {
        if (is_string($value)) {
            return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
        }
        return $value;
    }
}
```

## 🎯 Prioridade Média (1-3 meses)

### 4. Sistema de Notificações

#### Notificações em Tempo Real
```php
// WebSocket para notificações
class NotificationServer
{
    public function onVehicleStatusChange($vehicleId, $newStatus)
    {
        $notification = new Notification([
            'user_id' => $this->getVehicleOwner($vehicleId),
            'type' => 'vehicle_status_changed',
            'title' => 'Status do Veículo Alterado',
            'message' => "Veículo #{$vehicleId} mudou para: {$newStatus}",
            'data' => ['vehicle_id' => $vehicleId, 'status' => $newStatus]
        ]);

        $this->broadcastToUser($notification->user_id, $notification);
    }
}
```

#### Templates de Notificação
```php
// Sistema de templates
class NotificationTemplate
{
    const VEHICLE_MAINTENANCE_DUE = 'vehicle_maintenance_due';
    const FUEL_LOW = 'fuel_low';
    const USER_PASSWORD_EXPIRING = 'user_password_expiring';

    public static function getTemplate($type, $data = [])
    {
        $templates = [
            self::VEHICLE_MAINTENANCE_DUE => [
                'title' => 'Manutenção Pendente',
                'message' => 'O veículo {{vehicle_name}} precisa de manutenção: {{maintenance_type}}',
                'channels' => ['email', 'push', 'in_app']
            ],
            // ...
        ];

        return $templates[$type] ?? null;
    }
}
```

### 5. API Gateway e Microserviços

#### API Gateway Básico
```php
class ApiGateway extends \yii\base\Component
{
    public $services = [
        'auth' => 'http://auth-service:8080',
        'vehicles' => 'http://vehicle-service:8081',
        'maintenance' => 'http://maintenance-service:8082',
    ];

    public function route($service, $endpoint, $method = 'GET', $data = null)
    {
        $url = $this->services[$service] . $endpoint;

        return $this->makeRequest($url, $method, $data, [
            'Authorization' => \Yii::$app->request->headers->get('Authorization'),
            'X-Company-ID' => \Yii::$app->params['company_id'],
        ]);
    }
}
```

#### Service Discovery
```php
class ServiceDiscovery
{
    private $services = [];
    private $healthChecks = [];

    public function register($serviceName, $url, $healthEndpoint = '/health')
    {
        $this->services[$serviceName] = $url;
        $this->healthChecks[$serviceName] = $url . $healthEndpoint;
    }

    public function getHealthyService($serviceName)
    {
        if (!$this->isHealthy($serviceName)) {
            throw new \Exception("Service {$serviceName} is not healthy");
        }

        return $this->services[$serviceName];
    }

    private function isHealthy($serviceName)
    {
        // Implementar health check
        return true; // Placeholder
    }
}
```

### 6. Cache Inteligente

#### Cache Hierárquico
```php
class SmartCache
{
    private $redis;
    private $fileCache;

    public function __construct()
    {
        $this->redis = \Yii::$app->redis;
        $this->fileCache = \Yii::$app->cache;
    }

    public function get($key)
    {
        // Tentar Redis primeiro
        $value = $this->redis->get($key);
        if ($value !== false) {
            return json_decode($value, true);
        }

        // Fallback para file cache
        $value = $this->fileCache->get($key);
        if ($value !== false) {
            // Popular Redis para próximas requisições
            $this->redis->setex($key, 3600, json_encode($value));
            return $value;
        }

        return false;
    }

    public function set($key, $value, $ttl = 3600)
    {
        $jsonValue = json_encode($value);
        $this->redis->setex($key, $ttl, $jsonValue);
        $this->fileCache->set($key, $value, $ttl);
    }
}
```

#### Cache Invalidation Automática
```php
class CacheInvalidationBehavior extends \yii\base\Behavior
{
    public $cacheKeys = [];

    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_UPDATE => 'invalidateCache',
            ActiveRecord::EVENT_AFTER_DELETE => 'invalidateCache',
        ];
    }

    public function invalidateCache($event)
    {
        $cache = \Yii::$app->cache;

        foreach ($this->cacheKeys as $key) {
            $cacheKey = str_replace('{id}', $this->owner->id, $key);
            $cache->delete($cacheKey);
        }
    }
}
```

## 🎯 Prioridade Baixa (3-6 meses)

### 7. Analytics e Relatórios

#### Dashboard Analytics
```php
class AnalyticsService
{
    public function getDashboardData($companyId, $period = 'month')
    {
        return [
            'vehicles' => [
                'total' => $this->getTotalVehicles($companyId),
                'active' => $this->getActiveVehicles($companyId),
                'maintenance_due' => $this->getVehiclesDueMaintenance($companyId),
            ],
            'maintenance' => [
                'completed_this_month' => $this->getCompletedMaintenance($companyId, $period),
                'pending' => $this->getPendingMaintenance($companyId),
                'costs' => $this->getMaintenanceCosts($companyId, $period),
            ],
            'fuel' => [
                'total_consumption' => $this->getTotalFuelConsumption($companyId, $period),
                'average_efficiency' => $this->getAverageFuelEfficiency($companyId, $period),
                'costs' => $this->getFuelCosts($companyId, $period),
            ],
        ];
    }
}
```

#### Relatórios Exportáveis
```php
class ReportExporter
{
    public function exportToPdf($data, $template, $filename)
    {
        $mpdf = new \Mpdf\Mpdf();
        $html = $this->renderTemplate($template, $data);
        $mpdf->WriteHTML($html);
        $mpdf->Output($filename, 'D');
    }

    public function exportToExcel($data, $filename)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Preencher dados...

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($filename);
    }
}
```

### 8. Integração com APIs Externas

#### Google Maps Integration
```php
class GoogleMapsService
{
    public function geocodeAddress($address)
    {
        $apiKey = \Yii::$app->params['google_maps_api_key'];
        $url = "https://maps.googleapis.com/maps/api/geocode/json?address=" .
               urlencode($address) . "&key={$apiKey}";

        $response = file_get_contents($url);
        $data = json_decode($response, true);

        if ($data['status'] === 'OK') {
            return $data['results'][0]['geometry']['location'];
        }

        return null;
    }

    public function calculateRoute($origin, $destination)
    {
        // Implementar cálculo de rota
    }
}
```

#### WhatsApp Business API
```php
class WhatsAppService
{
    public function sendMaintenanceReminder($phone, $vehicle, $maintenance)
    {
        $message = "Olá! Lembrete: Seu veículo {$vehicle->brand} {$vehicle->model} " .
                  "precisa de {$maintenance->type} em {$maintenance->due_date->format('d/m/Y')}.";

        return $this->sendMessage($phone, $message);
    }

    public function sendFuelAlert($phone, $vehicle)
    {
        $message = "Alerta: O tanque do veículo {$vehicle->license_plate} está baixo! " .
                  "Nível atual: {$vehicle->fuel_level}%";

        return $this->sendMessage($phone, $message);
    }
}
```

### 9. Machine Learning Features

#### Previsão de Manutenção
```php
class MaintenancePredictor
{
    public function predictNextMaintenance($vehicleId)
    {
        $historicalData = $this->getMaintenanceHistory($vehicleId);
        $usageData = $this->getUsageData($vehicleId);

        // Algoritmo de ML para prever manutenção
        $prediction = $this->mlModel->predict([
            'mileage' => $usageData['current_mileage'],
            'age' => $usageData['vehicle_age'],
            'last_maintenance_days' => $usageData['days_since_last_maintenance'],
            'usage_pattern' => $usageData['daily_usage'],
        ]);

        return [
            'recommended_date' => $prediction['date'],
            'confidence' => $prediction['confidence'],
            'maintenance_type' => $prediction['type'],
        ];
    }
}
```

#### Detecção de Anomalias
```php
class AnomalyDetector
{
    public function detectFuelAnomalies($vehicleId, $recentFuelData)
    {
        $normalPattern = $this->getNormalFuelPattern($vehicleId);

        foreach ($recentFuelData as $fuelRecord) {
            $anomalyScore = $this->calculateAnomalyScore($fuelRecord, $normalPattern);

            if ($anomalyScore > $this->threshold) {
                $this->createAnomalyAlert($vehicleId, $fuelRecord, $anomalyScore);
            }
        }
    }
}
```

## 🛠️ Melhorias Técnicas

### 10. Performance e Escalabilidade

#### Database Optimization
```sql
-- Índices para performance
CREATE INDEX idx_vehicle_company_status ON vehicle (company_id, status);
CREATE INDEX idx_vehicle_license_plate ON vehicle (license_plate);
CREATE INDEX idx_maintenance_vehicle_due_date ON maintenance (vehicle_id, due_date);
CREATE INDEX idx_fuel_vehicle_date ON fuel (vehicle_id, created_at);

-- Particionamento para tabelas grandes
ALTER TABLE fuel PARTITION BY RANGE (YEAR(created_at)) (
    PARTITION p2024 VALUES LESS THAN (2025),
    PARTITION p2025 VALUES LESS THAN (2026),
    PARTITION p_future VALUES LESS THAN MAXVALUE
);
```

#### Connection Pooling
```php
// Configuração do pool de conexões
'db' => [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=db;dbname=veigest',
    'username' => 'veigest_user',
    'password' => 'veigest_pass',
    'attributes' => [
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET sql_mode = 'STRICT_TRANS_TABLES'",
    ],
    'poolSize' => 10, // Pool de conexões
    'enableSchemaCache' => true,
    'schemaCacheDuration' => 3600,
],
```

### 11. Monitoramento e Observabilidade

#### Métricas e Monitoring
```php
class MetricsCollector
{
    public function collectRequestMetrics($request, $response, $duration)
    {
        $this->incrementCounter('api_requests_total', [
            'method' => $request->method,
            'endpoint' => $request->pathInfo,
            'status' => $response->statusCode,
        ]);

        $this->observeHistogram('api_request_duration_seconds', $duration, [
            'method' => $request->method,
            'endpoint' => $request->pathInfo,
        ]);
    }

    public function collectDatabaseMetrics($query, $duration)
    {
        $this->observeHistogram('db_query_duration_seconds', $duration, [
            'query_type' => $this->getQueryType($query),
        ]);
    }
}
```

#### Health Checks
```php
class HealthCheckController extends BaseApiController
{
    public function actionIndex()
    {
        return [
            'status' => 'healthy',
            'timestamp' => time(),
            'services' => [
                'database' => $this->checkDatabase(),
                'cache' => $this->checkCache(),
                'external_apis' => $this->checkExternalApis(),
            ],
        ];
    }

    private function checkDatabase()
    {
        try {
            \Yii::$app->db->createCommand('SELECT 1')->execute();
            return ['status' => 'up', 'response_time' => microtime(true) - $start];
        } catch (\Exception $e) {
            return ['status' => 'down', 'error' => $e->getMessage()];
        }
    }
}
```

## 📅 Cronograma Sugerido

| Período | Foco | Entregas Principais |
|---------|------|---------------------|
| **Semanas 1-2** | Expansão Core | Endpoints manutenção, abastecimento, busca avançada |
| **Semanas 3-4** | Segurança | Rate limiting, 2FA, criptografia |
| **Meses 2-3** | Notificações | Sistema de notificações, API Gateway básico |
| **Meses 3-4** | Analytics | Dashboards, relatórios exportáveis |
| **Meses 4-6** | Integrações | Google Maps, WhatsApp, ML básico |
| **Meses 6-12** | Escalabilidade | Microserviços, otimização DB, monitoring avançado |

## 🎯 Critérios de Sucesso

- **Performance**: Tempo de resposta < 200ms para 95% das requisições
- **Disponibilidade**: Uptime > 99.9%
- **Segurança**: Zero vulnerabilidades críticas
- **Usabilidade**: API intuitiva e bem documentada
- **Escalabilidade**: Suporte a 1000+ usuários simultâneos

---

**Fim da Documentação da API VeiGest**

Esta documentação completa fornece uma base sólida para o desenvolvimento, manutenção e expansão futura da API VeiGest. Cada seção foi estruturada para facilitar a implementação gradual das melhorias, mantendo a qualidade e a consistência do código.
