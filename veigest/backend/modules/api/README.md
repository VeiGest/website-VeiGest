# API - Backend (versioned)

Esta pasta contém o módulo de API do backend. A API implementada segue um padrão RESTful e possui versionamento por módulos (por ex. `v1`).

## Visão geral da estrutura

- `backend/modules/api/Module.php` — módulo principal da API (registro do namespace)
- `backend/modules/api/v1/Module.php` — módulo da versão `v1`
- `backend/modules/api/v1/controllers/` — controllers REST da versão `v1`:
  - `AuthController.php` — endpoint de login (POST `/api/v1/auth/login`) que retorna `access_token`
  - `UserController.php` — `yii\\rest\\ActiveController` para `common\\models\\User` (rotas RESTful `GET /api/v1/user`, `GET /api/v1/user/<id>`, etc.)

As rotas REST estão configuradas em `backend/config/main.php` via `yii\\rest\\UrlRule`.

## Endpoints principais (v1)

- POST /api/v1/auth/login
  - Body (application/json):
    - `username`: string
    - `password`: string
  - Resposta (200):
    - `{ "access_token": "...", "user": { "id": 1, "nome": "...", "email": "..." } }`
  - Erros: `{ "error": "mensagem" }`

- GET /api/v1/user
  - Lista de usuários (requer header Authorization)
  - Header: `Authorization: Bearer <access_token>`

- GET /api/v1/user/<id>
  - Detalhes de um usuário (requer token)

Observação: `UserController` é um `ActiveController` que expõe as ações padrão (`index`, `view`, `create`, `update`, `delete`) do modelo `common\\models\\User`. Em `UrlRule` o pluralize foi configurado como `false` (use `/user`).

## Autenticação

A autenticação atual é simples: a API usa Bearer token no header `Authorization`. O token armazenado/consultado é o campo `auth_key` do modelo `User`.

Fluxo de login:
1. Cliente envia POST `/api/v1/auth/login` com `username` e `password`.
2. Se as credenciais forem válidas, o controller retorna `access_token` (que é `auth_key`). Se `auth_key` estiver vazio, o controller gera e salva um novo.
3. Cliente inclui o header `Authorization: Bearer <access_token>` em requisições subsequentes.

Segurança e melhorias recomendadas:
- Em produção, não use `auth_key` simples sem expiração. Prefira JWTs (com assinatura e claims) ou uma tabela separada de tokens com `expires_at` e `revoked`.
- Limite origens permitidas no CORS em vez de liberar '*' globalmente.
- Use HTTPS sempre.

## Como modificar / estender a API

### Adicionar um novo controller em v1
1. Criar o arquivo `backend/modules/api/v1/controllers/NomeController.php` com namespace `backend\\modules\\api\\v1\\controllers`.
2. Estender `yii\\rest\\ActiveController` (ou `yii\\rest\\Controller` para endpoints customizados).
3. Se o endpoint deve exigir autenticação, adicione o comportamento de autenticação no método `behaviors()` do controller, por exemplo:

```php
public function behaviors()
{
    $behaviors = parent::behaviors();
    // CORS
    $behaviors['corsFilter'] = [ 'class' => \yii\filters\Cors::class ];
    // Bearer auth
    $behaviors['authenticator'] = [
        'class' => \yii\filters\auth\CompositeAuth::class,
        'authMethods' => [\yii\filters\auth\HttpBearerAuth::class],
    ];
    return $behaviors;
}
```

### Criar uma nova versão (v2)
1. Criar nova pasta `backend/modules/api/v2` e adicionar `Module.php` com `controllerNamespace = 'backend\\modules\\api\\v2\\controllers'`.
2. Criar controllers em `backend/modules/api/v2/controllers`.
3. Registrar o submódulo `v2` no `backend/config/main.php` sob `'modules' => ['api' => ['modules' => ['v2' => ['class' => 'backend\\\\modules\\\\api\\\\v2\\\\Module']]]]`.
4. Adicionar/atualizar regras `yii\\rest\\UrlRule` se quiser rotas diferentes.

### Alterar formato de resposta / comportamento global
- Para alterar behaviors globais (por ex. serialização, autenticação padrão), você pode editar os controllers base ou criar um componente/base controller comum que os controllers da API estendam.

## Exemplos de testes (PowerShell)

1) Login (gera token):

```powershell
$body = @{ username = 'seu_usuario'; password = 'sua_senha' } | ConvertTo-Json
Invoke-RestMethod -Method Post -Uri 'http://localhost:21080/api/v1/auth/login' -Body $body -ContentType 'application/json'
```

2) Acessar lista de usuários com token:

```powershell
$token = 'SEU_TOKEN_AQUI'
Invoke-RestMethod -Method Get -Uri 'http://localhost:21080/api/v1/user' -Headers @{ Authorization = "Bearer $token" }
```

Exemplo com curl:

```bash
curl -X POST http://localhost:21080/api/v1/auth/login -H "Content-Type: application/json" -d '{"username":"user","password":"pass"}'
curl -X GET http://localhost:21080/api/v1/user -H "Authorization: Bearer SEU_TOKEN"
```

## Dicas de debugging
- Verifique logs em `backend/runtime/logs` para erros de execução.
- Se não conseguir acessar rotas, confirme que o servidor está rodando e que o `urlManager` está corretamente configurado (e que o servidor permite reescrita de URL quando necessário).

## Próximos passos sugeridos
- Implementar expiração e revogação de tokens (tabela `access_tokens` ou JWT com expiração).
- Adicionar testes automatizados (Codeception) para os endpoints de autenticação e acesso protegido.
- Restringir CORS e adicionar rate limiting para endpoints sensíveis.

---

Se quiser, eu posso:

- criar um endpoint de logout/revogação;
- migrar para JWT com refresh tokens;
- adicionar testes Codeception para `AuthController` e `UserController`.
