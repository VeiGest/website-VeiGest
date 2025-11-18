# 🚀 Script de Configuração Automática do VeiGest

Este script automatiza completamente a configuração do projeto VeiGest, desde a instalação do ambiente até a configuração do Apache com virtual hosts.

## 📋 O que o Script Faz

### 1. **Instalação do WebStack**
- ✅ Apache HTTP Server
- ✅ PHP 8.4
- ✅ Composer
- ✅ MySQL Server
- ✅ Chocolatey (gerenciador de pacotes)

### 2. **Configuração do Projeto Yii2**
- ✅ Instalação de dependências via Composer
- ✅ Inicialização do projeto para ambiente de desenvolvimento
- ✅ Configuração automática da database

### 3. **Configuração da Database**
- ✅ Criação da database `veigest`
- ✅ Configuração do charset UTF-8 completo
- ✅ Execução das migrações automaticamente

### 4. **Configuração do Apache**
- ✅ Habilitação do mod_rewrite
- ✅ Criação de Virtual Hosts
- ✅ Configuração do arquivo hosts do Windows
- ✅ URLs amigáveis para frontend e backend

## 🎯 Como Usar

### ⚡ Método Mais Fácil
1. **Clique com botão direito** em `INSTALAR-VEIGEST.bat`
2. **Escolha "Executar como administrador"**
3. **Aguarde** a instalação automática
4. **Acesse** `http://veigest.local` quando terminar

### 🔧 Se Já Tiver WAMP Instalado
1. **Inicie o WAMP** primeiro
2. **Clique com botão direito** em `INSTALAR-VEIGEST-WAMP.bat`
3. **Escolha "Executar como administrador"**
4. **Acesse** `http://veigest.local` quando terminar

### 💻 Execução via PowerShell

#### Básica (Instalação Completa)
```powershell
# Execute como Administrador
.\project-setup.ps1
```

#### Com WAMP Existente
```powershell
# Pular instalação do WebStack
.\project-setup.ps1 -SkipWebStack
```

#### Opções Avançadas
```powershell
# Especificar senha do MySQL
.\project-setup.ps1 -DatabasePassword "minhasenha"

# Usar domínio personalizado
.\project-setup.ps1 -ProjectDomain "meudominio.local"

# Combinação de opções
.\project-setup.ps1 -SkipWebStack -ProjectDomain "veigest.dev" -DatabasePassword "123456"
```

## 📋 Pré-requisitos

1. **Windows 10/11**
2. **PowerShell 5.0+** (já incluído no Windows)
3. **Privilégios de Administrador**
4. **Conexão com Internet** (para downloads)

## 🌐 URLs Resultantes

Após a execução bem-sucedida:

- **Frontend**: `http://veigest.local`
- **Backend**: `http://admin.veigest.local`

## 🔑 Credenciais Padrão

- **Email**: `admin@veigest.com`
- **Senha**: `admin`

## 📁 Estrutura Criada

```
veigest/
├── frontend/web/          # Frontend público
├── backend/web/           # Área administrativa  
├── common/config/         # Configurações compartilhadas
├── console/migrations/    # Migrações da database
└── ...                   # Outros arquivos do Yii2
```

## 🔧 Resolução de Problemas

### ❌ "Não é possível executar scripts"
```powershell
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
```

### ❌ "MySQL não está rodando"
- Se usando WAMP: Inicie o WAMP primeiro
- Se MySQL standalone: `net start MySQL80`

### ❌ "Apache não reinicia"
- Verifique se não há conflitos na porta 80
- Pare outros servidores web (IIS, XAMPP, etc.)

### ❌ "Erro de permissões"
- Certifique-se de executar como Administrador
- Desative temporariamente o antivírus

### ❌ "Sites não carregam"
- Verifique se o Apache está rodando: `Get-Service Apache*`
- Verifique o arquivo hosts: `C:\Windows\System32\drivers\etc\hosts`

## 📊 Migrações Incluídas

O script executa automaticamente todas as migrações:

1. **Empresas** - Sistema multi-tenant
2. **RBAC** - Roles e permissões
3. **Utilizadores** - Com perfis de condutor
4. **Veículos** - Gestão da frota
5. **Manutenções** - Histórico de manutenções
6. **Documentos** - Gestão de documentos
7. **Combustível** - Registos de abastecimento
8. **Alertas** - Sistema de notificações
9. **Logs** - Auditoria do sistema
10. **Views** - Para relatórios

## 🎨 Personalização

### Alterar Domínio
```powershell
.\project-setup.ps1 -ProjectDomain "meusite.local"
```

### Configurar Senha do MySQL
```powershell
.\project-setup.ps1 -DatabasePassword "minhasenha123"
```

### Executar Apenas Parte do Setup
```powershell
# Apenas configuração do projeto (sem WebStack)
.\project-setup.ps1 -SkipWebStack
```

## 🔄 Re-executar o Script

O script é idempotente, ou seja, pode ser executado múltiplas vezes sem problemas. Ele detectará o que já foi configurado e pulará essas etapas.

## 🆘 Suporte

Se encontrar problemas:

1. **Verifique os logs**: 
   - Apache: `C:\tools\Apache24\logs\`
   - PHP: Verifique `php.ini`
   
2. **Execute passo a passo**: 
   - Use as opções do script para isolar problemas
   
3. **Configuração manual**: 
   - O script cria todos os arquivos necessários
   - Você pode continuar manualmente se necessário

## 🎉 Próximos Passos

Após a execução bem-sucedida:

1. **Acesse o frontend**: `http://veigest.local`
2. **Faça login no backend**: `http://admin.veigest.local`
3. **Explore o sistema**: Comece criando novos utilizadores e veículos
4. **Desenvolva**: Adicione novas funcionalidades conforme necessário

---

**Nota**: Este script foi testado no Windows 10/11 com PowerShell 5.0+. Para outros ambientes, pode ser necessário ajustar os caminhos e comandos.