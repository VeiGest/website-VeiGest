# 📡 VeiGest API - Sistema de Messaging MQTT

## 📋 Visão Geral

O sistema de messaging da API VeiGest utiliza o protocolo **MQTT** através do broker **Mosquitto** para fornecer atualizações em tempo real aos clientes (aplicações Android, dashboards web, etc.).

A implementação segue o padrão **Publish/Subscribe**, onde:
- A **API** atua como **Publisher** - publica alertas quando são criados, resolvidos ou ignorados
- Os **Clientes** atuam como **Subscribers** - subscrevem os canais relevantes para receber atualizações

## 🐋 Configuração do Broker

O Mosquitto está configurado no Docker:

```yaml
services:
  mosquitto:
    image: eclipse-mosquitto
    container_name: mosquitto
    ports:
      - "1883:1883"
    volumes:
      - ./config:/mosquitto/config
      - ./data:/mosquitto/data
      - ./log:/mosquitto/log
    restart: unless-stopped
```

### Ficheiro de Configuração (`config/mosquitto.conf`)

```conf
listener 1883
allow_anonymous true
persistence true
persistence_location /mosquitto/data/
log_dest file /mosquitto/log/mosquitto.log
```

## 📡 Canais (Topics) MQTT

### Estrutura Base

```
veigest/alerts/{company_id}
```

Onde `{company_id}` é o ID da empresa do utilizador autenticado.

### Canais Disponíveis

| Canal | Descrição | Eventos |
|-------|-----------|---------|
| `veigest/alerts/{company_id}` | Canal principal - todos os alertas | new, resolved, ignored, updated |
| `veigest/alerts/{company_id}/new` | Novos alertas criados | new |
| `veigest/alerts/{company_id}/resolved` | Alertas resolvidos | resolved |
| `veigest/alerts/{company_id}/ignored` | Alertas ignorados | ignored |
| `veigest/alerts/{company_id}/critical` | Alertas de prioridade crítica | new (apenas críticos) |
| `veigest/alerts/{company_id}/high` | Alertas de alta prioridade | new (apenas high) |

### Exemplo de Canais para Empresa ID 1

```
veigest/alerts/1           # Todos os alertas
veigest/alerts/1/new       # Novos alertas
veigest/alerts/1/resolved  # Alertas resolvidos
veigest/alerts/1/critical  # Alertas críticos
veigest/alerts/1/high      # Alertas de alta prioridade
```

## 📦 Formato das Mensagens (Payload)

Todas as mensagens são enviadas em formato **JSON** com a seguinte estrutura:

```json
{
    "event": "new",
    "timestamp": "2026-01-06T10:30:00+00:00",
    "data": {
        "id": 1,
        "company_id": 1,
        "type": "maintenance",
        "type_label": "Manutenção",
        "title": "Veículo necessita revisão",
        "description": "O veículo 00-AA-00 atingiu 50.000 km",
        "priority": "high",
        "priority_label": "Alta",
        "priority_level": 3,
        "status": "active",
        "status_label": "Ativo",
        "details": {
            "vehicle_id": 5,
            "license_plate": "00-AA-00",
            "mileage": 50000
        },
        "created_at": "2026-01-06 10:30:00",
        "resolved_at": null,
        "age": "5 minutos"
    }
}
```

### Campos do Payload

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `event` | string | Tipo de evento: `new`, `resolved`, `ignored`, `updated` |
| `timestamp` | string | Data/hora do evento em formato ISO 8601 |
| `data` | object | Dados completos do alerta |

### Tipos de Eventos

| Evento | Descrição | Quando é disparado |
|--------|-----------|-------------------|
| `new` | Novo alerta criado | Após inserção de novo alerta na BD |
| `resolved` | Alerta resolvido | Após chamar endpoint `POST /alerts/{id}/resolve` |
| `ignored` | Alerta ignorado | Após chamar endpoint `POST /alerts/{id}/ignore` |
| `updated` | Alerta atualizado | Após broadcast manual |

## 🔌 Endpoints da API para MQTT

### Obter Informação dos Canais MQTT

```bash
GET /api/alerts/mqtt-info
Authorization: Bearer {token}
```

**Resposta:**
```json
{
    "success": true,
    "data": {
        "broker": {
            "host": "mosquitto",
            "port": 1883,
            "protocol": "mqtt"
        },
        "channels": [
            {
                "topic": "veigest/alerts/1",
                "description": "Todos os alertas da empresa",
                "events": ["new", "resolved", "ignored", "updated"]
            },
            {
                "topic": "veigest/alerts/1/new",
                "description": "Novos alertas criados"
            }
        ],
        "payload_format": {...},
        "example_payload": {...}
    }
}
```

### Broadcast Manual de Alerta

```bash
POST /api/alerts/{id}/broadcast
Authorization: Bearer {token}
```

**Resposta:**
```json
{
    "success": true,
    "message": "Alerta publicado via MQTT com sucesso",
    "data": {
        "alert_id": 1,
        "event": "new",
        "topics": [
            "veigest/alerts/1",
            "veigest/alerts/1/new"
        ]
    }
}
```

## 📱 Integração com Clientes

### Android (Kotlin) - Exemplo com HiveMQ Client

```kotlin
import com.hivemq.client.mqtt.mqtt3.Mqtt3Client

class MqttAlertService(private val companyId: Int) {
    private val client = Mqtt3Client.builder()
        .identifier("veigest-android-${UUID.randomUUID()}")
        .serverHost("api.veigest.com")
        .serverPort(1883)
        .build()

    fun connect() {
        client.toAsync().connectWith().send()
    }

    fun subscribeToAlerts() {
        client.toAsync()
            .subscribeWith()
            .topicFilter("veigest/alerts/$companyId/#")
            .callback { publish ->
                val payload = String(publish.payloadAsBytes)
                val alert = Gson().fromJson(payload, AlertMessage::class.java)
                handleAlert(alert)
            }
            .send()
    }

    private fun handleAlert(alert: AlertMessage) {
        when (alert.event) {
            "new" -> showNotification(alert.data)
            "resolved" -> updateAlertList()
            "critical" -> showUrgentNotification(alert.data)
        }
    }
}
```

### JavaScript (Web Dashboard) - Exemplo com MQTT.js

```javascript
import mqtt from 'mqtt';

class MqttAlertClient {
    constructor(companyId) {
        this.companyId = companyId;
        this.client = mqtt.connect('ws://api.veigest.com:1884');
    }

    connect() {
        this.client.on('connect', () => {
            // Subscrever ao canal principal
            this.client.subscribe(`veigest/alerts/${this.companyId}/#`);
        });

        this.client.on('message', (topic, message) => {
            const alert = JSON.parse(message.toString());
            this.handleAlert(topic, alert);
        });
    }

    handleAlert(topic, alert) {
        switch (alert.event) {
            case 'new':
                this.showToast(`Novo Alerta: ${alert.data.title}`, 'warning');
                this.refreshAlertList();
                break;
            case 'resolved':
                this.showToast(`Alerta Resolvido: ${alert.data.title}`, 'success');
                this.refreshAlertList();
                break;
        }
    }
}
```

## 🧪 Testes com Linha de Comandos

### Subscrever a um Canal (Terminal 1)

```bash
# Usando mosquitto_sub
mosquitto_sub -h localhost -p 1883 -t "veigest/alerts/1/#" -v

# Ou para alertas críticos apenas
mosquitto_sub -h localhost -p 1883 -t "veigest/alerts/1/critical" -v
```

### Criar um Alerta via API (Terminal 2)

```bash
# Login
TOKEN=$(curl -s -X POST http://localhost:21080/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin"}' | jq -r '.data.token')

# Criar alerta (disparará MQTT automaticamente)
curl -X POST http://localhost:21080/api/alerts \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "type": "maintenance",
    "title": "Teste MQTT - Manutenção Urgente",
    "description": "Este é um teste de publicação MQTT",
    "priority": "critical"
  }'
```

### Broadcast Manual

```bash
# Broadcast de alerta existente
curl -X POST http://localhost:21080/api/alerts/1/broadcast \
  -H "Authorization: Bearer $TOKEN"
```

## 🏗️ Arquitetura do Sistema

```
┌─────────────────┐     ┌──────────────┐     ┌─────────────────┐
│   API VeiGest   │────▶│  Mosquitto   │────▶│  App Android    │
│   (Publisher)   │     │   (Broker)   │     │  (Subscriber)   │
└─────────────────┘     └──────────────┘     └─────────────────┘
        │                       │                     │
        │ POST /alerts          │ MQTT Pub            │ MQTT Sub
        │ POST /alerts/resolve  │                     │
        ▼                       ▼                     ▼
┌─────────────────┐     ┌──────────────┐     ┌─────────────────┐
│   Base Dados    │     │    Topics    │     │  Web Dashboard  │
│    (MySQL)      │     │veigest/alerts│     │  (Subscriber)   │
└─────────────────┘     └──────────────┘     └─────────────────┘
```

## 📊 Fluxo de Eventos

1. **Criação de Alerta**
   - Utilizador/Sistema cria alerta via `POST /api/alerts`
   - Alerta é guardado na base de dados
   - `afterSave()` dispara publicação MQTT
   - Clientes subscritos recebem notificação

2. **Resolução de Alerta**
   - Utilizador resolve alerta via `POST /api/alerts/{id}/resolve`
   - Alerta é atualizado na BD
   - `resolve()` dispara publicação MQTT com evento "resolved"
   - Clientes atualizam listas de alertas

3. **Broadcast Manual**
   - Administrador pode re-publicar alerta via `POST /api/alerts/{id}/broadcast`
   - Útil para re-enviar alertas importantes ou testar conectividade

## ⚙️ Componente PHP

O componente `MqttPublisher` está em:
```
backend/modules/api/components/MqttPublisher.php
```

### Métodos Principais

| Método | Descrição |
|--------|-----------|
| `connect()` | Conecta ao broker Mosquitto |
| `disconnect()` | Desconecta do broker |
| `publish($topic, $message)` | Publica mensagem genérica |
| `publishAlert($companyId, $alertData, $event)` | Publica alerta formatado |
| `publishNewAlert($companyId, $alertData)` | Atalho para novo alerta |
| `publishResolvedAlert($companyId, $alertData)` | Atalho para alerta resolvido |

## 🔒 Segurança

- O broker Mosquitto está configurado apenas na rede interna Docker
- Os clientes externos devem autenticar-se na API REST primeiro
- O `company_id` nos tópicos garante isolamento multi-tenant
- Recomenda-se implementar TLS para produção

## 📈 Escalabilidade

Para ambientes de alta carga, considerar:
- Mosquitto Cluster para alta disponibilidade
- WebSocket bridge para clientes web (porta 1884)
- Rate limiting no broker
- Persistência de mensagens para clientes offline
