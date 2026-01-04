# 🔧 Guia de Solução - Problema de Conexão API

## ❌ Problema Identificado

O servidor rodando em `http://localhost:8002` está retornando respostas do **frontend** ao invés do **backend da API**.

**Evidências:**
- Headers retornam `_csrf-frontend` (deveria ser `_csrf-backend`)
- Content-Type é `text/html` ao invés de `application/json`
- Status 400 com página de erro HTML

## ✅ Soluções Disponíveis

### Opção 1: Iniciar o Backend (Recomendado)

Se você tem Docker Compose configurado:

```bash
cd /home/pedro/facul/website-VeiGest
docker-compose up -d backend
```

Isso irá iniciar o backend na porta **21080** conforme `docker-compose.yml`.

Depois, **altere a URL base** nos testes:
```bash
# Edite api-tests/utils/http-client.js
# Mude de:
const API_BASE_URL = 'http://localhost:8002/api/v1';

# Para:
const API_BASE_URL = 'http://localhost:21080/api/v1';
```

### Opção 2: PHP Built-in Server

Se preferir não usar Docker, inicie o PHP server apontando para o backend:

```bash
cd /home/pedro/facul/website-VeiGest/veigest/backend/web
php -S localhost:8002 -t .
```

**Importante:** Certifique-se de que:
1. O arquivo `.htaccess` está correto
2. O módulo API está configurado em `backend/config/main.php`

### Opção 3: Verificar Configuração Apache/Nginx

Se você está usando Apache ou Nginx, verifique que:

1. **VirtualHost para Backend** está configurado para porta 8002
2. **DocumentRoot** aponta para `/path/to/veigest/backend/web`
3. **Rewrite rules** estão habilitadas

Exemplo de configuração Apache:
```apache
<VirtualHost *:8002>
    DocumentRoot "/home/pedro/facul/website-VeiGest/veigest/backend/web"
    <Directory "/home/pedro/facul/website-VeiGest/veigest/backend/web">
        RewriteEngine on
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule . index.php
    </Directory>
</VirtualHost>
```

## 🧪 Testar a Solução

Depois de aplicar qualquer solução, teste a conexão:

```bash
cd /home/pedro/facul/website-VeiGest/api-tests
node test-connection.js
```

**Resposta esperada:**
```
✅ Resposta JSON detectada:
{
  "success": true,
  "data": {
    "token": "...",
    "user": {...}
  }
}
```

## 📝 Executar os Testes

Quando a API estiver respondendo corretamente:

```bash
# Teste de conexão
node test-connection.js

# Todos os testes
node run-all-tests.js

# Testes individuais
node tests/test-auth.js
node tests/test-vehicles.js
node tests/test-users.js
```

## 🔍 Diagnóstico Adicional

Se o problema persistir, verifique:

```bash
# 1. Qual processo está na porta 8002?
lsof -i :8002

# 2. Verificar logs do Apache/PHP
tail -f /var/log/apache2/error.log

# 3. Testar endpoint diretamente
curl -v http://localhost:8002/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin"}'
```

## 📚 Documentação

- **API Endpoints:** `/home/pedro/facul/website-VeiGest/veigest/backend/views/API_ENDPOINTS.md`
- **Implementação:** `/home/pedro/facul/website-VeiGest/veigest/API_IMPLEMENTATION.md`
- **Config Backend:** `/home/pedro/facul/website-VeiGest/veigest/backend/config/main.php`

---

**Última atualização:** 4 de dezembro de 2025
