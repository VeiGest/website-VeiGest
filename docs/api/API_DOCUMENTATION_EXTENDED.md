# VeiGest API - Documentação Extendida de Endpoints

Este documento descreve os endpoints expostos pelo módulo REST da API VeiGest (backend/modules/api). Inclui método HTTP, caminho, parâmetros principais, requisitos de autenticação e notas sobre RBAC / multi-tenancy.

Observações gerais
- Base URL (local de desenvolvimento): `http://localhost:8002/api`
- Autenticação: JWT token retornado em `/auth/login`. Enviar header `Authorization: Bearer <token>` em chamadas autenticadas.
- Multi-tenancy: muitos endpoints filtram automaticamente por `company_id` do usuário autenticado — não enviar `company_id` diretamente a menos que o endpoint explicitamente permita.
- Paginação: usar query params `page`, `per-page` (ou `per_page` conforme implementado nos handlers) para listagens.
- Formato de resposta: JSON com objeto padrão `{ success: true|false, message: '', data: ... }`.

Endpoints principais

1) Autenticação
- POST `/auth/login`
  - Body: `{ username, password }`
  - Retorno: `{ data: { access_token, refresh_token?, user } }`
  - Permite obter token para chamadas autenticadas.
- GET `/auth/me`
  - Retorna dados do usuário autenticado.
- POST `/auth/refresh`
  - Body: `{ refresh_token }` — renova access token.
- POST `/auth/logout`
  - Invalida sessão/token.

2) Empresas
- GET `/company` ou `/companies`
  - Lista empresas (RBAC: admin/gestor). Filtragem por `per-page`, `page`.
- GET `/company/{id}`
  - Visualiza detalhes da empresa (autorização: pertence ao mesmo `company_id` ou permissão).
- POST `/company`
  - Cria empresa (admin). Campos: `name`, `email`, `tax_id`, `code` (opcional — gerado automaticamente se vazio).
- PUT `/company/{id}`
  - Atualiza empresa.
- DELETE `/company/{id}`
  - Remove empresa (restrito).

3) Usuários
- GET `/users` ou `/user`
  - Lista usuários (filtrado por `company_id`). Query: `role`, `per-page`.
- GET `/users/{id}` ou `/user/{id}`
  - Visualiza usuário.
- POST `/users` ou `/user`
  - Cria usuário; campos: `username`, `email`, `name`, `phone`, `password`, `status`, `company_id`, `tempRole` (usado pelo backend para atribuir RBAC).
- PUT `/users/{id}`
  - Atualiza usuário — role é manipulada via RBAC (authManager), não via atributo persistente direto.
- POST `/users/{id}/photo`
  - Upload de foto do usuário.

4) Veículos
- GET `/vehicles` ou `/vehicle`
  - Lista veículos da empresa.
- GET `/vehicles/{id}` ou `/vehicle/{id}`
  - Visualiza veículo.
- POST `/vehicles` ou `/vehicle`
  - Cria veículo (campos: `license_plate`, `brand`, `model`, `year`, `fuel_type`, `mileage`, `status`).
- PUT `/vehicles/{id}`
  - Atualiza veículo.
- GET `/vehicles/{id}/maintenances`, `/vehicles/{id}/fuel-logs` etc.
  - Endpoints relacionados retornam coleções associadas.

5) Manutenções
- GET `/maintenance`
  - Lista manutenções; filtros: `vehicle_id`, `status`, `period`.
- POST `/maintenance` — criar; PUT `/maintenance/{id}` — atualizar; DELETE `/maintenance/{id}` — deletar.
- Endpoints adicionais: relatórios mensais `/maintenance/reports-monthly`, custos `/maintenance/reports-costs`.

6) Abastecimentos (Fuel Logs)
- GET `/fuel-logs` ou `/fuel-log`
  - Lista registros de abastecimento; filtros: `vehicle_id`, `period`.
- POST `/fuel-logs` — criar; PUT `/fuel-logs/{id}` — atualizar.
- Endpoints de eficiência/alerts disponíveis (`/fuel-logs/efficiency-report`, `/fuel-logs/alerts`).

7) Rotas
- GET `/routes` ou `/route`
  - Lista rotas; filtros: `vehicle_id`, `driver_id`, `status`, `per-page`.
- POST `/routes` — criar rota; PUT `/routes/{id}` — atualizar; POST `/routes/{id}/complete` — marcar como concluída.

8) Alertas
- GET `/alerts` ou `/alert`
  - Lista alertas; filtros por `type`, `status`, `priority`.
- GET `/alerts/{id}` — visualizar.
- POST `/alerts` — criar; PUT `/alerts/{id}` — atualizar; POST `/alerts/{id}/resolve` — resolver; POST `/alerts/{id}/ignore` — ignorar.
  - Observação: Alertas em alguns fluxos publicam via MQTT (ver `MqttPublisher` componente).

9) Documentos
- GET `/documents` ou `/document`
  - Lista documentos da empresa; filtros: `vehicle_id`, `driver_id`, `type`, `expiring`.
- GET `/documents/{id}` — visualizar; POST `/documents` — criar; PUT `/documents/{id}` — atualizar; DELETE `/documents/{id}` — deletar.
- GET `/documents/expiring` — documentos com vencimento próximo.

10) Arquivos (Files)
- GET `/files` — lista arquivos; filtros: `uploaded_by`, `per-page`.
- GET `/files/{id}` — visualizar info; POST `/files` — upload; DELETE `/files/{id}` — remover.
- GET `/files/stats` — estatísticas agregadas.

11) Activity Log
- GET `/activity-logs` — listar logs de atividade.
- GET `/activity-logs/stats` — estatísticas; GET `/activity-logs/by-user/{user_id}` — filtrar por usuário; `/activity-logs/by-entity/{entity}/{id}` — filtrar por entidade.

Erros comuns e códigos
- 401 Unauthorized: token ausente ou inválido.
- 403 Forbidden: usuário sem permissão RBAC para ação.
- 400 Bad Request: validações de dados falharam (ex.: NIF duplicado).
- 404 Not Found: recurso inexistente ou fora do contexto `company_id`.

RBAC e permissão por endpoint
- O sistema utiliza `authManager` do Yii2. As permissões são verificadas nos controllers (`checkAccess` e `hasPermission`).
- Roles típicas: `admin`, `gestor` (manager), `condutor` (driver). Algumas operações CRUD são restritas a `admin` ou `gestor`.

Dicas para testes automatizados
- Obter token via `/auth/login` e usá-lo em `Authorization: Bearer <token>`.
- Popular o banco de dados com dados de teste (empresas, veículos, usuários) antes de rodar a suíte completa.
- Executar testes de conectividade simples: `node backend/modules/api-tests/test-connection.js`.

Arquivo(s) de referência no repositório
- Código-fonte dos controllers: `backend/modules/api/controllers/*`.
- Modelos/API fields: `backend/modules/api/models/*`.
- Test suite JS: `backend/modules/api-tests/`.

Fim da documentação estendida.
