# VeiGest - Documentação da API RESTful

## Informações Gerais
- **Framework**: Yii2.0
- **Formato de Resposta**: JSON
- **Autenticação**: Bearer Token (Base64)
- **Base URL**: `http://localhost:21080/api/v1` (desenvolvimento) ou `https://api.veigest.com/v1` (produção)
- **Cabeçalhos Obrigatórios**: 
  - `Content-Type: application/json`
  - `Authorization: Bearer {base64_token}`
  - `Accept: application/json`

---

## Autenticação

A API utiliza autenticação baseada em **Bearer Token** codificado em **Base64**.

### Como Funciona

1. **Login**: O cliente envia `username` e `password` para `/api/v1/auth/login`
2. **Token**: A API retorna um token Base64 que contém:
   - `user_id`: ID do utilizador
   - `username`: Nome de utilizador
   - `name`: Nome completo
   - `email`: Email do utilizador
   - `company_id`: ID da empresa
   - `company_code`: Código único numérico da empresa
   - `company_name`: Nome da empresa
   - `roles`: Roles do utilizador (RBAC)
   - `permissions`: Permissões do utilizador (RBAC)
   - `created_at`: Timestamp de criação do token
   - `expires_at`: Timestamp de expiração do token (24 horas)

3. **Uso**: Incluir o token no cabeçalho `Authorization: Bearer {token}` em todas as requisições
4. **Multi-tenancy**: O `company_code` no token garante que cada empresa acede apenas aos seus dados

### Estrutura do Token

```json
// Conteúdo decodificado do token (Base64)
{
  "user_id": 1,
  "username": "admin",
  "name": "Administrator",
  "email": "admin@veigest.com",
  "company_id": 1,
  "company_code": 1733337418123,
  "company_name": "VeiGest - Demo Company",
  "roles": ["admin"],
  "permissions": ["vehicles.view", "vehicles.create", "users.manage", ...],
  "created_at": 1701388800,
  "expires_at": 1701475200
}
```

### Endpoints de Autenticação

| HTTP Verb | Endpoint | Descrição | Pedido | Resposta |
|-----------|----------|-----------|--------|----------|
| POST | `/auth/login` | Login do utilizador | `{"username": "admin", "password": "admin123"}` | `{"success": true, "data": {"token": "eyJhbG...", "user": {...}, "company": {...}, "roles": [...], "permissions": [...]}}` |
| POST | `/auth/logout` | Logout do utilizador | - | `{"success": true, "message": "Logout successful"}` |
| GET | `/auth/me` | Obter informações do utilizador autenticado | - | `{"success": true, "data": {"user": {...}, "company": {...}, "roles": [...], "permissions": [...]}}` |
| POST | `/auth/refresh` | Renovar token de autenticação | - | `{"success": true, "data": {"token": "new_token...", "expires_at": 1701561600}}` |

### Exemplo de Login

**Request:**
```bash
curl -X POST http://localhost:21080/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username": "admin", "password": "admin"}'
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "eyJ1c2VyX2lkIjoxLCJ1c2VybmFtZSI6ImFkbWluIiwibmFtZSI6IkFkbWluaXN0cmF0b3IiLCJlbWFpbCI6ImFkbWluQHZlaWdlc3QuY29tIiwiY29tcGFueV9pZCI6MSwiY29tcGFueV9jb2RlIjoxNzMzMzM3NDE4MTIzLCJjb21wYW55X25hbWUiOiJWZWlHZXN0IC0gRGVtbyBDb21wYW55Iiwicm9sZXMiOlsiYWRtaW4iXSwicGVybWlzc2lvbnMiOlsidmVoaWNsZXMudmlldyIsInZlaGljbGVzLmNyZWF0ZSJdLCJjcmVhdGVkX2F0IjoxNzAxMzg4ODAwLCJleHBpcmVzX2F0IjoxNzAxNDc1MjAwfQ==",
    "user": {
      "id": 1,
      "username": "admin",
      "name": "Administrator",
      "email": "admin@veigest.com"
    },
    "company": {
      "id": 1,
      "code": 1733337418123,
      "name": "VeiGest - Demo Company"
    },
    "roles": ["admin"],
    "permissions": ["companies.view", "companies.manage", "users.view", "users.create", "vehicles.view", "vehicles.create", ...],
    "expires_at": 1701475200
  }
}
```

### Exemplo de Requisição Autenticada

```bash
curl -X GET http://localhost:21080/api/v1/vehicles \
  -H "Authorization: Bearer eyJ1c2VyX2lkIjoxLCJ1c2VybmFtZSI6ImFkbWluIi..." \
  -H "Accept: application/json"
```

### Controlo de Acesso (RBAC)

A API utiliza o sistema RBAC do Yii2 para controlar permissões:

**Roles Disponíveis:**
- `admin`: Administrador com acesso total
- `manager`: Gestor de frota
- `maintenance-manager`: Gestor de manutenção
- `senior-driver`: Condutor sénior
- `driver`: Condutor

**Permissões por Recurso:**
- `companies.*`: Gestão de empresas
- `users.*`: Gestão de utilizadores
- `vehicles.*`: Gestão de veículos
- `maintenances.*`: Gestão de manutenções
- `documents.*`: Gestão de documentos
- `fuel.*`: Gestão de abastecimentos
- `alerts.*`: Gestão de alertas
- `reports.*`: Relatórios

### Códigos de Erro

| Código | Descrição |
|--------|-----------|
| 200 | OK - Requisição bem sucedida |
| 201 | Created - Recurso criado com sucesso |
| 204 | No Content - Recurso eliminado com sucesso |
| 400 | Bad Request - Dados inválidos |
| 401 | Unauthorized - Token inválido ou expirado |
| 403 | Forbidden - Sem permissão para aceder ao recurso |
| 404 | Not Found - Recurso não encontrado |
| 422 | Unprocessable Entity - Erros de validação |
| 500 | Internal Server Error - Erro no servidor |

---

## 1. EMPRESAS (Companies)

| HTTP Verb | Endpoint | Descrição | Parâmetros | Pedido | Resposta (JSON) |
|-----------|----------|-----------|------------|--------|-----------------|
| GET | `/companies` | Listar todas as empresas | `page`, `limit`, `sort`, `filter` | - | `{"data": [{"id": 1, "nome": "VeiGest Demo", "nif": "999999990", "email": "admin@veigest.com", "estado": "ativa", "plano": "enterprise"}], "pagination": {...}}` |
| GET | `/companies/{id}` | Obter empresa específica | - | - | `{"data": {"id": 1, "nome": "VeiGest Demo", "nif": "999999990", "email": "admin@veigest.com", "estado": "ativa", "plano": "enterprise", "configuracoes": {...}}}` |
| POST | `/companies` | Criar nova empresa | - | `{"nome": "Nova Empresa", "nif": "123456789", "email": "empresa@email.com", "plano": "basico"}` | `{"data": {"id": 2, "nome": "Nova Empresa", "nif": "123456789", "email": "empresa@email.com", "estado": "ativa", "plano": "basico"}}` |
| PUT | `/companies/{id}` | Atualizar empresa completa | - | `{"nome": "Empresa Atualizada", "email": "novo@email.com", "plano": "profissional"}` | `{"data": {"id": 1, "nome": "Empresa Atualizada", "email": "novo@email.com", "plano": "profissional"}}` |
| PATCH | `/companies/{id}` | Atualizar campos específicos | - | `{"estado": "suspensa"}` | `{"data": {"id": 1, "estado": "suspensa"}}` |
| DELETE | `/companies/{id}` | Eliminar empresa | - | - | `{"success": true, "message": "Empresa eliminada com sucesso"}` |

---

## 2. UTILIZADORES (Users)

| HTTP Verb | Endpoint | Descrição | Parâmetros | Pedido | Resposta (JSON) |
|-----------|----------|-----------|------------|--------|-----------------|
| GET | `/users` | Listar utilizadores | `company_id`, `page`, `limit`, `sort`, `filter` | - | `{"data": [{"id": 1, "nome": "João Silva", "email": "joao@empresa.com", "estado": "ativo", "numero_carta": "12345", "validade_carta": "2025-12-31"}]}` |
| GET | `/users/{id}` | Obter utilizador específico | - | - | `{"data": {"id": 1, "company_id": 1, "nome": "João Silva", "email": "joao@empresa.com", "telefone": "912345678", "estado": "ativo", "numero_carta": "12345", "validade_carta": "2025-12-31"}}` |
| POST | `/users` | Criar novo utilizador | - | `{"company_id": 1, "nome": "Maria Santos", "email": "maria@empresa.com", "senha": "password123", "telefone": "913456789", "numero_carta": "54321", "validade_carta": "2026-06-30"}` | `{"data": {"id": 2, "nome": "Maria Santos", "email": "maria@empresa.com", "estado": "ativo"}}` |
| PUT | `/users/{id}` | Atualizar utilizador completo | - | `{"nome": "Maria Santos Silva", "telefone": "914567890", "validade_carta": "2026-12-31"}` | `{"data": {"id": 2, "nome": "Maria Santos Silva", "telefone": "914567890"}}` |
| PATCH | `/users/{id}` | Atualizar campos específicos | - | `{"estado": "inativo"}` | `{"data": {"id": 2, "estado": "inativo"}}` |
| DELETE | `/users/{id}` | Eliminar utilizador | - | - | `{"success": true, "message": "Utilizador eliminado com sucesso"}` |
| POST | `/users/{id}/reset-password` | Reset de password | - | `{"new_password": "newpass123"}` | `{"success": true, "message": "Password alterada com sucesso"}` |

---

## 3. VEÍCULOS (Vehicles)

| HTTP Verb | Endpoint | Descrição | Parâmetros | Pedido | Resposta (JSON) |
|-----------|----------|-----------|------------|--------|-----------------|
| GET | `/vehicles` | Listar veículos | `company_id`, `estado`, `page`, `limit`, `sort` | - | `{"data": [{"id": 1, "matricula": "AA-00-AA", "marca": "Toyota", "modelo": "Corolla", "ano": 2020, "estado": "ativo", "condutor_id": 1}]}` |
| GET | `/vehicles/{id}` | Obter veículo específico | - | - | `{"data": {"id": 1, "company_id": 1, "matricula": "AA-00-AA", "marca": "Toyota", "modelo": "Corolla", "ano": 2020, "tipo_combustivel": "gasolina", "quilometragem": 50000, "estado": "ativo", "condutor_id": 1}}` |
| POST | `/vehicles` | Criar novo veículo | - | `{"company_id": 1, "matricula": "BB-11-BB", "marca": "Ford", "modelo": "Focus", "ano": 2021, "tipo_combustivel": "diesel"}` | `{"data": {"id": 2, "matricula": "BB-11-BB", "marca": "Ford", "modelo": "Focus", "estado": "ativo"}}` |
| PUT | `/vehicles/{id}` | Atualizar veículo completo | - | `{"marca": "Ford", "modelo": "Fiesta", "quilometragem": 55000, "condutor_id": 2}` | `{"data": {"id": 1, "marca": "Ford", "modelo": "Fiesta", "quilometragem": 55000}}` |
| PATCH | `/vehicles/{id}` | Atualizar campos específicos | - | `{"estado": "manutencao", "quilometragem": 56000}` | `{"data": {"id": 1, "estado": "manutencao", "quilometragem": 56000}}` |
| DELETE | `/vehicles/{id}` | Eliminar veículo | - | - | `{"success": true, "message": "Veículo eliminado com sucesso"}` |
| POST | `/vehicles/{id}/assign-driver` | Atribuir condutor | - | `{"condutor_id": 3}` | `{"success": true, "message": "Condutor atribuído com sucesso"}` |
| POST | `/vehicles/{id}/unassign-driver` | Remover condutor | - | - | `{"success": true, "message": "Condutor removido com sucesso"}` |

---

## 4. MANUTENÇÕES (Maintenances)

| HTTP Verb | Endpoint | Descrição | Parâmetros | Pedido | Resposta (JSON) |
|-----------|----------|-----------|------------|--------|-----------------|
| GET | `/maintenances` | Listar manutenções | `vehicle_id`, `data_inicio`, `data_fim`, `page`, `limit` | - | `{"data": [{"id": 1, "vehicle_id": 1, "tipo": "Revisão", "data": "2025-10-15", "custo": 150.00, "oficina": "AutoRep"}]}` |
| GET | `/maintenances/{id}` | Obter manutenção específica | - | - | `{"data": {"id": 1, "company_id": 1, "vehicle_id": 1, "tipo": "Revisão", "descricao": "Troca de óleo e filtros", "data": "2025-10-15", "custo": 150.00, "km_registro": 50000, "proxima_data": "2026-04-15", "oficina": "AutoRep"}}` |
| POST | `/maintenances` | Criar nova manutenção | - | `{"company_id": 1, "vehicle_id": 1, "tipo": "Pneus", "descricao": "Troca de pneus dianteiros", "data": "2025-11-01", "custo": 200.00, "km_registro": 52000, "oficina": "PneuMax"}` | `{"data": {"id": 2, "vehicle_id": 1, "tipo": "Pneus", "data": "2025-11-01", "custo": 200.00}}` |
| PUT | `/maintenances/{id}` | Atualizar manutenção completa | - | `{"custo": 180.00, "proxima_data": "2026-05-01", "oficina": "AutoRep Premium"}` | `{"data": {"id": 1, "custo": 180.00, "proxima_data": "2026-05-01"}}` |
| PATCH | `/maintenances/{id}` | Atualizar campos específicos | - | `{"custo": 175.00}` | `{"data": {"id": 1, "custo": 175.00}}` |
| DELETE | `/maintenances/{id}` | Eliminar manutenção | - | - | `{"success": true, "message": "Manutenção eliminada com sucesso"}` |

---

## 5. DOCUMENTOS (Documents)

| HTTP Verb | Endpoint | Descrição | Parâmetros | Pedido | Resposta (JSON) |
|-----------|----------|-----------|------------|--------|-----------------|
| GET | `/documents` | Listar documentos | `vehicle_id`, `driver_id`, `tipo`, `status`, `page`, `limit` | - | `{"data": [{"id": 1, "tipo": "seguro", "data_validade": "2025-12-31", "status": "valido", "vehicle": {"matricula": "AA-00-AA"}}]}` |
| GET | `/documents/{id}` | Obter documento específico | - | - | `{"data": {"id": 1, "company_id": 1, "file_id": 1, "vehicle_id": 1, "tipo": "seguro", "data_validade": "2025-12-31", "status": "valido", "notas": "Seguro contra terceiros"}}` |
| POST | `/documents` | Criar novo documento | - | `{"company_id": 1, "file_id": 2, "vehicle_id": 1, "tipo": "inspecao", "data_validade": "2026-06-30", "notas": "Inspeção periódica"}` | `{"data": {"id": 2, "tipo": "inspecao", "data_validade": "2026-06-30", "status": "valido"}}` |
| PUT | `/documents/{id}` | Atualizar documento completo | - | `{"data_validade": "2026-12-31", "notas": "Renovado antecipadamente"}` | `{"data": {"id": 1, "data_validade": "2026-12-31"}}` |
| PATCH | `/documents/{id}` | Atualizar campos específicos | - | `{"status": "expirado"}` | `{"data": {"id": 1, "status": "expirado"}}` |
| DELETE | `/documents/{id}` | Eliminar documento | - | - | `{"success": true, "message": "Documento eliminado com sucesso"}` |
| GET | `/documents/expiring` | Documentos a expirar | `dias` (default: 30) | - | `{"data": [{"id": 1, "tipo": "seguro", "dias_para_vencimento": 15, "entidade": "AA-00-AA"}]}` |

---

## 6. FICHEIROS (Files)

| HTTP Verb | Endpoint | Descrição | Parâmetros | Pedido | Resposta (JSON) |
|-----------|----------|-----------|------------|--------|-----------------|
| GET | `/files` | Listar ficheiros | `company_id`, `page`, `limit` | - | `{"data": [{"id": 1, "nome_original": "seguro.pdf", "tamanho": 1024000, "uploaded_by": 1, "created_at": "2025-11-08T10:00:00Z"}]}` |
| GET | `/files/{id}` | Obter ficheiro específico | - | - | `{"data": {"id": 1, "company_id": 1, "nome_original": "seguro.pdf", "tamanho": 1024000, "caminho": "/uploads/2025/11/seguro_123.pdf", "uploaded_by": 1}}` |
| POST | `/files` | Upload de ficheiro | - | `multipart/form-data: file, company_id` | `{"data": {"id": 2, "nome_original": "carta.pdf", "tamanho": 512000, "caminho": "/uploads/2025/11/carta_456.pdf"}}` |
| GET | `/files/{id}/download` | Download de ficheiro | - | - | `Ficheiro binário` |
| DELETE | `/files/{id}` | Eliminar ficheiro | - | - | `{"success": true, "message": "Ficheiro eliminado com sucesso"}` |

---

## 7. COMBUSTÍVEL (Fuel Logs)

| HTTP Verb | Endpoint | Descrição | Parâmetros | Pedido | Resposta (JSON) |
|-----------|----------|-----------|------------|--------|-----------------|
| GET | `/fuel-logs` | Listar registos de combustível | `vehicle_id`, `driver_id`, `data_inicio`, `data_fim`, `page`, `limit` | - | `{"data": [{"id": 1, "vehicle_id": 1, "data": "2025-11-01", "litros": 50.00, "valor": 85.00, "preco_litro": 1.70, "km_atual": 52000}]}` |
| GET | `/fuel-logs/{id}` | Obter registo específico | - | - | `{"data": {"id": 1, "company_id": 1, "vehicle_id": 1, "driver_id": 1, "data": "2025-11-01", "litros": 50.00, "valor": 85.00, "preco_litro": 1.70, "km_atual": 52000, "notas": "Posto BP"}}` |
| POST | `/fuel-logs` | Criar novo registo | - | `{"company_id": 1, "vehicle_id": 1, "driver_id": 1, "data": "2025-11-08", "litros": 45.50, "valor": 78.90, "km_atual": 52500, "notas": "Posto Galp"}` | `{"data": {"id": 2, "vehicle_id": 1, "data": "2025-11-08", "litros": 45.50, "valor": 78.90, "preco_litro": 1.73}}` |
| PUT | `/fuel-logs/{id}` | Atualizar registo completo | - | `{"litros": 46.00, "valor": 79.20, "km_atual": 52550}` | `{"data": {"id": 1, "litros": 46.00, "valor": 79.20, "preco_litro": 1.72}}` |
| PATCH | `/fuel-logs/{id}` | Atualizar campos específicos | - | `{"notas": "Posto Repsol - desconto"}` | `{"data": {"id": 1, "notas": "Posto Repsol - desconto"}}` |
| DELETE | `/fuel-logs/{id}` | Eliminar registo | - | - | `{"success": true, "message": "Registo de combustível eliminado"}` |

---

## 8. ROTAS (Routes)

| HTTP Verb | Endpoint | Descrição | Parâmetros | Pedido | Resposta (JSON) |
|-----------|----------|-----------|------------|--------|-----------------|
| GET | `/routes` | Listar rotas | `vehicle_id`, `driver_id`, `status`, `data_inicio`, `data_fim`, `page`, `limit` | - | `{"data": [{"id": 1, "vehicle_id": 1, "driver_id": 1, "inicio": "2025-11-08T09:00:00Z", "fim": "2025-11-08T17:30:00Z", "origem": "Lisboa", "destino": "Porto", "status": "concluida"}]}` |
| GET | `/routes/{id}` | Obter rota específica | - | - | `{"data": {"id": 1, "company_id": 1, "vehicle_id": 1, "driver_id": 1, "inicio": "2025-11-08T09:00:00Z", "fim": "2025-11-08T17:30:00Z", "km_inicial": 50000, "km_final": 50320, "origem": "Lisboa", "destino": "Porto", "distancia_km": 320.5, "status": "concluida"}}` |
| POST | `/routes` | Iniciar nova rota | - | `{"company_id": 1, "vehicle_id": 1, "driver_id": 1, "km_inicial": 52500, "origem": "Lisboa", "destino": "Coimbra"}` | `{"data": {"id": 2, "vehicle_id": 1, "driver_id": 1, "inicio": "2025-11-08T14:00:00Z", "status": "em_andamento"}}` |
| PATCH | `/routes/{id}/finish` | Finalizar rota | - | `{"km_final": 52650, "notas": "Viagem sem incidentes"}` | `{"data": {"id": 2, "fim": "2025-11-08T16:30:00Z", "km_final": 52650, "status": "concluida"}}` |
| PATCH | `/routes/{id}/cancel` | Cancelar rota | - | `{"notas": "Cancelada por avaria"}` | `{"data": {"id": 2, "status": "cancelada"}}` |
| PUT | `/routes/{id}` | Atualizar rota completa | - | `{"origem": "Lisboa Centro", "destino": "Coimbra Sul", "notas": "Rota atualizada"}` | `{"data": {"id": 2, "origem": "Lisboa Centro", "destino": "Coimbra Sul"}}` |
| DELETE | `/routes/{id}` | Eliminar rota | - | - | `{"success": true, "message": "Rota eliminada com sucesso"}` |

---

## 9. PONTOS GPS (GPS Entries)

| HTTP Verb | Endpoint | Descrição | Parâmetros | Pedido | Resposta (JSON) |
|-----------|----------|-----------|------------|--------|-----------------|
| GET | `/gps-entries` | Listar pontos GPS | `route_id`, `timestamp_inicio`, `timestamp_fim`, `page`, `limit` | - | `{"data": [{"id": 1, "route_id": 1, "latitude": 38.7169, "longitude": -9.1395, "timestamp": "2025-11-08T09:15:00Z", "velocidade": 50.5}]}` |
| GET | `/routes/{route_id}/gps-entries` | Pontos GPS de uma rota | `page`, `limit` | - | `{"data": [{"id": 1, "latitude": 38.7169, "longitude": -9.1395, "timestamp": "2025-11-08T09:15:00Z", "velocidade": 50.5, "altitude": 125.3}]}` |
| POST | `/gps-entries` | Criar ponto GPS | - | `{"route_id": 2, "latitude": 38.7169, "longitude": -9.1395, "velocidade": 60.0, "altitude": 130.0, "precisao": 5.0}` | `{"data": {"id": 3, "route_id": 2, "latitude": 38.7169, "longitude": -9.1395, "timestamp": "2025-11-08T14:30:00Z"}}` |
| POST | `/gps-entries/batch` | Criar múltiplos pontos GPS | - | `{"route_id": 2, "entries": [{"latitude": 38.7169, "longitude": -9.1395, "velocidade": 60.0}, {"latitude": 38.7269, "longitude": -9.1295, "velocidade": 65.0}]}` | `{"success": true, "count": 2, "message": "Pontos GPS registados"}` |
| DELETE | `/gps-entries/{id}` | Eliminar ponto GPS | - | - | `{"success": true, "message": "Ponto GPS eliminado"}` |

---

## 10. ALERTAS (Alerts)

| HTTP Verb | Endpoint | Descrição | Parâmetros | Pedido | Resposta (JSON) |
|-----------|----------|-----------|------------|--------|-----------------|
| GET | `/alerts` | Listar alertas | `tipo`, `status`, `prioridade`, `page`, `limit` | - | `{"data": [{"id": 1, "tipo": "documento", "titulo": "Seguro a expirar", "prioridade": "alta", "status": "ativo", "created_at": "2025-11-08T10:00:00Z"}]}` |
| GET | `/alerts/{id}` | Obter alerta específico | - | - | `{"data": {"id": 1, "company_id": 1, "tipo": "documento", "titulo": "Seguro a expirar", "descricao": "O seguro do veículo AA-00-AA expira em 15 dias", "prioridade": "alta", "status": "ativo", "detalhes": {"vehicle_id": 1, "document_id": 1}}}` |
| POST | `/alerts` | Criar novo alerta | - | `{"company_id": 1, "tipo": "manutencao", "titulo": "Revisão em atraso", "descricao": "Veículo necessita de revisão", "prioridade": "media", "detalhes": {"vehicle_id": 1}}` | `{"data": {"id": 2, "tipo": "manutencao", "titulo": "Revisão em atraso", "status": "ativo"}}` |
| PATCH | `/alerts/{id}/resolve` | Resolver alerta | - | `{"notas": "Documento renovado"}` | `{"data": {"id": 1, "status": "resolvido", "resolvido_em": "2025-11-08T15:30:00Z"}}` |
| PATCH | `/alerts/{id}/ignore` | Ignorar alerta | - | `{"notas": "Falso positivo"}` | `{"data": {"id": 1, "status": "ignorado"}}` |
| DELETE | `/alerts/{id}` | Eliminar alerta | - | - | `{"success": true, "message": "Alerta eliminado com sucesso"}` |

---

## 11. LOGS DE ATIVIDADE (Activity Logs)

| HTTP Verb | Endpoint | Descrição | Parâmetros | Pedido | Resposta (JSON) |
|-----------|----------|-----------|------------|--------|-----------------|
| GET | `/activity-logs` | Listar logs de atividade | `user_id`, `entidade`, `entidade_id`, `data_inicio`, `data_fim`, `page`, `limit` | - | `{"data": [{"id": 1, "user_id": 1, "acao": "vehicle.create", "entidade": "vehicle", "entidade_id": 1, "ip": "192.168.1.100", "created_at": "2025-11-08T10:00:00Z"}]}` |
| GET | `/activity-logs/{id}` | Obter log específico | - | - | `{"data": {"id": 1, "company_id": 1, "user_id": 1, "acao": "vehicle.create", "entidade": "vehicle", "entidade_id": 1, "detalhes": {"matricula": "AA-00-AA", "marca": "Toyota"}, "ip": "192.168.1.100"}}` |
| POST | `/activity-logs` | Criar log de atividade | - | `{"company_id": 1, "user_id": 1, "acao": "document.upload", "entidade": "document", "entidade_id": 2, "detalhes": {"tipo": "seguro"}, "ip": "192.168.1.101"}` | `{"data": {"id": 2, "acao": "document.upload", "created_at": "2025-11-08T15:30:00Z"}}` |

---

## 12. RELATÓRIOS E ESTATÍSTICAS

| HTTP Verb | Endpoint | Descrição | Parâmetros | Pedido | Resposta (JSON) |
|-----------|----------|-----------|------------|--------|-----------------|
| GET | `/reports/company-stats` | Estatísticas da empresa | `company_id` | - | `{"data": {"total_users": 15, "total_vehicles": 8, "total_drivers": 12, "total_storage_bytes": 50000000}}` |
| GET | `/reports/vehicle-costs` | Custos por veículo | `vehicle_id`, `data_inicio`, `data_fim` | - | `{"data": [{"vehicle_id": 1, "matricula": "AA-00-AA", "total_maintenance": 500.00, "total_fuel": 1200.00, "total_costs": 1700.00}]}` |
| GET | `/reports/fuel-consumption` | Consumo de combustível | `vehicle_id`, `periodo` | - | `{"data": {"periodo": "2025-11", "total_litros": 150.5, "total_valor": 255.85, "media_preco_litro": 1.70, "consumo_medio": 7.5}}` |
| GET | `/reports/maintenance-schedule` | Cronograma de manutenções | `data_inicio`, `data_fim` | - | `{"data": [{"vehicle_id": 1, "matricula": "AA-00-AA", "proximo_servico": "2025-12-15", "tipo": "Revisão", "dias_restantes": 37}]}` |
| GET | `/reports/driver-performance` | Performance dos condutores | `driver_id`, `periodo` | - | `{"data": {"driver_id": 1, "nome": "João Silva", "km_percorridos": 2500, "rotas_concluidas": 15, "consumo_medio": 7.2, "score": 85}}` |

---

## 13. AUTENTICAÇÃO E AUTORIZAÇÃO

| HTTP Verb | Endpoint | Descrição | Parâmetros | Pedido | Resposta (JSON) |
|-----------|----------|-----------|------------|--------|-----------------|
| POST | `/auth/login` | Login do utilizador | - | `{"email": "user@empresa.com", "password": "senha123"}` | `{"access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...", "token_type": "Bearer", "expires_in": 3600, "user": {...}}` |
| POST | `/auth/refresh` | Renovar token | - | `{"refresh_token": "refresh_token_here"}` | `{"access_token": "new_token_here", "expires_in": 3600}` |
| POST | `/auth/logout` | Logout do utilizador | - | - | `{"success": true, "message": "Logout realizado com sucesso"}` |
| GET | `/auth/me` | Dados do utilizador autenticado | - | - | `{"data": {"id": 1, "nome": "João Silva", "email": "joao@empresa.com", "roles": ["gestor"], "permissions": [...]}}` |

---

## Códigos de Estado HTTP

- **200 OK**: Pedido bem-sucedido
- **201 Created**: Recurso criado com sucesso
- **204 No Content**: Pedido bem-sucedido sem conteúdo de resposta
- **400 Bad Request**: Dados do pedido inválidos
- **401 Unauthorized**: Não autenticado
- **403 Forbidden**: Não autorizado (sem permissões)
- **404 Not Found**: Recurso não encontrado
- **422 Unprocessable Entity**: Erro de validação
- **500 Internal Server Error**: Erro interno do servidor

## Estrutura de Erro Padrão

```json
{
  "error": {
    "code": 422,
    "message": "Validation failed",
    "details": [
      {
        "field": "email",
        "message": "Email is required"
      }
    ]
  }
}
```

## Paginação Padrão

```json
{
  "data": [...],
  "pagination": {
    "current_page": 1,
    "per_page": 20,
    "total_pages": 5,
    "total_count": 100,
    "has_next": true,
    "has_prev": false
  }
}
```