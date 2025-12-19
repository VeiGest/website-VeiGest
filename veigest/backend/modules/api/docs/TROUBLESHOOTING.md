# 🔧 Troubleshooting - Solução de Problemas

## 📋 Problemas Comuns e Soluções

### 🐳 Problemas com Docker

#### Containers não iniciam
**Sintomas:**
```
ERROR: Couldn't connect to Docker daemon
```

**Soluções:**
```bash
# Verificar se Docker está rodando
sudo systemctl status docker

# Iniciar Docker
sudo systemctl start docker

# Adicionar usuário ao grupo docker
sudo usermod -aG docker $USER
# Fazer logout e login novamente

# Reiniciar containers
docker-compose down
docker-compose up -d --build
```

#### Porta já em uso
**Sintomas:**
```
ERROR: Port 21080 is already in use
```

**Soluções:**
```bash
# Verificar quem está usando a porta
sudo lsof -i :21080

# Matar processo
sudo kill -9 PID_DO_PROCESSO

# Ou alterar porta no docker-compose.yml
ports:
  - "21081:80"  # Muda para 21081
```

#### Containers saem imediatamente
**Sintomas:**
```
Container exits with code 1
```

**Verificar logs:**
```bash
# Ver logs detalhados
docker-compose logs backend

# Ver logs do banco
docker-compose logs db

# Verificar se portas estão livres
docker-compose ps
```

### 🗄️ Problemas com Banco de Dados

#### Conexão recusada
**Sintomas:**
```
SQLSTATE[HY000] [2002] Connection refused
```

**Soluções:**
```bash
# Verificar se container do banco está rodando
docker-compose ps

# Verificar logs do MySQL
docker-compose logs db

# Testar conexão manual
docker-compose exec db mysql -u veigest_user -pveigest_pass veigest -e "SELECT 1;"

# Reiniciar containers
docker-compose restart db
```

#### Tabelas não existem
**Sintomas:**
```
Table 'veigest.user' doesn't exist
```

**Soluções:**
```bash
# Importar schema novamente
docker-compose exec db mysql -u root -pverysecret veigest < database.sql

# Ou recriar banco
docker-compose exec db mysql -u root -pverysecret -e "DROP DATABASE veigest; CREATE DATABASE veigest;"
docker-compose exec db mysql -u root -pverysecret veigest < database.sql
```

#### Dados corrompidos
```bash
# Backup dos dados atuais
docker-compose exec db mysqldump -u root -pverysecret veigest > backup.sql

# Recriar banco
docker-compose exec db mysql -u root -pverysecret -e "DROP DATABASE veigest; CREATE DATABASE veigest;"

# Restaurar backup
docker-compose exec db mysql -u root -pverysecret veigest < backup.sql
```

### 🔐 Problemas de Autenticação

#### Token inválido
**Sintomas:**
```
401 Unauthorized - Invalid token
```

**Verificações:**
```bash
# Verificar formato do token
curl -X GET http://localhost:21080/api/auth/me \
  -H "Authorization: Bearer SEU_TOKEN"

# Decodificar token manualmente (usando jwt.io ou script)
node -e "
const jwt = require('jsonwebtoken');
const token = 'SEU_TOKEN_AQUI';
try {
  const decoded = jwt.decode(token);
  console.log(JSON.stringify(decoded, null, 2));
} catch (e) {
  console.log('Token inválido:', e.message);
}
"
```

#### Usuário não encontrado
**Sintomas:**
```
User not found or invalid credentials
```

**Verificações:**
```bash
# Verificar usuários no banco
docker-compose exec db mysql -u veigest_user -pveigest_pass veigest \
  -e "SELECT id, username, password_hash, estado FROM user;"

# Verificar se senha está correta
# Usar script para testar hash
php -r "
\$hash = '\$2y\$13\$...'; // hash do banco
\$password = 'admin123';
if (password_verify(\$password, \$hash)) {
    echo 'Senha correta\n';
} else {
    echo 'Senha incorreta\n';
}
"
```

#### Problemas com multi-tenancy
**Sintomas:**
```
Company not found or inactive
```

**Verificações:**
```bash
# Verificar empresas
docker-compose exec db mysql -u veigest_user -pveigest_pass veigest \
  -e "SELECT id, code, name, status FROM company;"

# Verificar relação usuário-empresa
docker-compose exec db mysql -u veigest_user -pveigest_pass veigest \
  -e "SELECT u.username, c.name, c.status FROM user u JOIN company c ON u.company_id = c.id;"
```

### 🌐 Problemas de Rede/API

#### API não responde
**Sintomas:**
```
Connection refused ou timeout
```

**Verificações:**
```bash
# Verificar se container está rodando
docker-compose ps

# Testar conectividade
curl -v http://localhost:21080/api

# Verificar logs do backend
docker-compose logs backend

# Testar dentro do container
docker-compose exec backend curl -v http://localhost/api
```

#### CORS errors
**Sintomas:**
```
Access-Control-Allow-Origin header missing
```

**Verificações:**
```bash
# Verificar configuração CORS no código
grep -r "cors" veigest/backend/modules/api/

# Testar com curl (deve funcionar)
curl -X OPTIONS http://localhost:21080/api/auth/login \
  -H "Origin: http://localhost:3000" \
  -H "Access-Control-Request-Method: POST" \
  -v
```

#### 404 Not Found
**Sintomas:**
```
Endpoint not found
```

**Verificações:**
```bash
# Verificar URL rules
grep -r "rules" veigest/backend/modules/api/config/

# Testar endpoint existente
curl -X GET http://localhost:21080/api/auth/me \
  -H "Authorization: Bearer SEU_TOKEN"

# Verificar se módulo está carregado
docker-compose exec backend php -r "
\$config = require('/app/backend/config/main.php');
print_r(array_keys(\$config['modules']));
"
```

### 🧪 Problemas com Testes

#### Testes falham
**Sintomas:**
```
Test suite fails with various errors
```

**Verificações:**
```bash
# Executar testes com debug
cd api-tests
npm run test:verbose

# Verificar se API está rodando
curl -X GET http://localhost:21080/api

# Verificar configuração dos testes
cat config/test-config.js

# Executar teste específico
npm test -- --grep "login"
```

#### Timeout nos testes
```bash
# Aumentar timeout
export MOCHA_TIMEOUT=10000

# Ou editar package.json
"scripts": {
  "test": "mocha --timeout 10000"
}
```

#### Dependências faltando
```bash
# Reinstalar dependências
cd api-tests
rm -rf node_modules package-lock.json
npm install
```

### 📁 Problemas de Arquivos/Permissões

#### Permissões incorretas
**Sintomas:**
```
Permission denied
```

**Soluções:**
```bash
# Corrigir permissões
sudo chown -R www-data:www-data veigest/
sudo chmod -R 755 veigest/
sudo chmod -R 777 veigest/runtime/
sudo chmod -R 777 veigest/web/assets/

# Para desenvolvimento local
sudo chown -R $USER:$USER veigest/
```

#### Arquivos não encontrados
**Sintomas:**
```
File not found: /app/backend/config/main.php
```

**Verificações:**
```bash
# Verificar estrutura de arquivos
find veigest/ -name "main.php" -type f

# Verificar se volume está montado
docker-compose exec backend ls -la /app/backend/config/

# Recriar containers
docker-compose down
docker-compose up -d --build
```

### 🔄 Problemas de Cache/Configuração

#### Configurações não aplicam
```bash
# Limpar cache do Yii
docker-compose exec backend php yii cache/flush-all

# Reiniciar containers
docker-compose restart

# Verificar configuração carregada
docker-compose exec backend php -r "
\$config = require('/app/backend/config/main.php');
echo 'DB DSN: ' . \$config['components']['db']['dsn'] . PHP_EOL;
"
```

#### Dependências desatualizadas
```bash
# Atualizar Composer
docker-compose exec backend composer update

# Limpar cache do Composer
docker-compose exec backend composer clear-cache

# Verificar versão do PHP
docker-compose exec backend php --version
```

## 🛠️ Ferramentas de Diagnóstico

### Script de Diagnóstico Automático
```bash
#!/bin/bash
# diagnostic.sh

echo "=== DIAGNÓSTICO VEIGEST API ==="
echo

# Verificar Docker
echo "🐳 Docker Status:"
docker-compose ps
echo

# Verificar conectividade
echo "🌐 API Connectivity:"
curl -s -o /dev/null -w "%{http_code}" http://localhost:21080/api
echo " (deve ser 200 ou 404)"
echo

# Verificar banco
echo "🗄️ Database Connection:"
docker-compose exec -T db mysql -u veigest_user -pveigest_pass veigest -e "SELECT COUNT(*) as users FROM user;" 2>/dev/null || echo "Connection failed"
echo

# Verificar logs recentes
echo "📋 Recent Logs:"
docker-compose logs --tail=10 backend 2>/dev/null | head -20
echo

echo "=== FIM DO DIAGNÓSTICO ==="
```

### Comandos Úteis para Debug
```bash
# Ver todos os logs
docker-compose logs -f

# Entrar no container para debug
docker-compose exec backend bash

# Ver processos rodando
docker-compose exec backend ps aux

# Ver uso de memória/disco
docker stats

# Backup completo
docker-compose exec db mysqldump -u root -pverysecret --all-databases > backup_$(date +%Y%m%d_%H%M%S).sql

# Verificar configuração PHP
docker-compose exec backend php -i | grep -E "(memory_limit|max_execution_time|upload_max_filesize)"
```

## 🚨 Situações de Emergência

### API completamente inoperante
```bash
# Parar tudo
docker-compose down

# Limpar volumes (CUIDADO: perde dados)
docker volume rm $(docker volume ls -q | grep veigest)

# Reiniciar do zero
docker-compose up -d --build

# Recriar banco
docker-compose exec db mysql -u root -pverysecret -e "CREATE DATABASE veigest;"
docker-compose exec -T db mysql -u root -pverysecret veigest < database.sql
```

### Dados corrompidos
```bash
# Fazer backup
docker-compose exec db mysqldump -u root -pverysecret veigest > emergency_backup.sql

# Recriar estrutura
docker-compose exec db mysql -u root -pverysecret -e "DROP DATABASE veigest; CREATE DATABASE veigest;"
docker-compose exec -T db mysql -u root -pverysecret veigest < database.sql

# Restaurar dados essenciais (se possível)
# ... restaurar usuários, empresas, etc.
```

### Container travado
```bash
# Forçar parada
docker-compose kill

# Remover containers
docker-compose rm -f

# Limpar imagens (se necessário)
docker system prune -f

# Reiniciar
docker-compose up -d
```

## 📞 Quando Pedir Ajuda

### Informações para incluir no relatório de bug:
1. **Comandos executados** e suas saídas
2. **Logs completos** dos containers
3. **Versões** do Docker, Docker Compose, PHP, Node.js
4. **Sistema operacional** e versão
5. **Passos para reproduzir** o problema
6. **Resultado esperado** vs resultado atual

### Comandos para coletar informações:
```bash
# Informações do sistema
uname -a
docker --version
docker-compose --version

# Status completo
docker-compose ps -a
docker-compose logs > logs_completos.txt

# Configurações
cat docker-compose.yml
cat .env (remover senhas sensíveis)
```

---

**Próximo:** [FUTURAS_MODIFICACOES.md](FUTURAS_MODIFICACOES.md) - Plano de melhorias e expansões
