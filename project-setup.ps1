# =========================================================================
# SCRIPT DE CONFIGURACAO COMPLETO DO PROJETO VEIGEST
# =========================================================================
# Este script automatiza todo o processo de instalacao e configuracao:
# 1. Instalacao do WebStack (Apache, PHP, Composer, MySQL)
# 2. Configuracao do projeto Yii2
# 3. Criacao da database
# 4. Execucao das migracoes
# 5. Configuracao do Apache Virtual Host
# =========================================================================

param(
    [switch]$SkipWebStack = $false,
    [string]$DatabasePassword = "",
    [string]$ProjectDomain = "veigest.local"
)

Write-Host "==========================================================================" -ForegroundColor Cyan
Write-Host "         CONFIGURACAO AUTOMATICA DO PROJETO VEIGEST                      " -ForegroundColor Cyan
Write-Host "==========================================================================" -ForegroundColor Cyan
Write-Host ""

$projectRoot = $PSScriptRoot
$yiiRoot = Join-Path $projectRoot "veigest"

# =========================================================================
# FUNCAO: Verificar Privilegios de Administrador
# =========================================================================
function Test-AdminPrivileges {
    $admin = (New-Object Security.Principal.WindowsPrincipal(
        [Security.Principal.WindowsIdentity]::GetCurrent()
    )).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
    
    if (-not $admin) {
        Write-Host "ERRO: Este script precisa ser executado como Administrador." -ForegroundColor Red
        Write-Host "Clique com o botao direito no PowerShell e escolha 'Executar como administrador'" -ForegroundColor Yellow
        exit 1
    }
    Write-Host "[OK] Privilegios de administrador verificados" -ForegroundColor Green
}

# =========================================================================
# FUNCAO: Instalar WebStack (Apache, PHP, Composer, MySQL)
# =========================================================================
function Install-WebStack {
    if ($SkipWebStack) {
        Write-Host "[SKIP] Pulando instalacao do WebStack (--SkipWebStack especificado)" -ForegroundColor Yellow
        return
    }

    Write-Host "[INFO] Instalando WebStack (Apache, PHP, Composer, MySQL)..." -ForegroundColor Blue
    
    # Configurar TLS
    [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12

    # Instalar Chocolatey se nao existir
    if (-not (Get-Command choco -ErrorAction SilentlyContinue)) {
        Write-Host "[INFO] Instalando Chocolatey..." -ForegroundColor Yellow
        Set-ExecutionPolicy Bypass -Scope Process -Force
        iex ((New-Object Net.WebClient).DownloadString('https://community.chocolatey.org/install.ps1'))
    }

    # Instalar pacotes essenciais
    Write-Host "[INFO] Instalando Apache HTTP Server..." -ForegroundColor Yellow
    choco install apache-httpd -y --no-progress

    Write-Host "[INFO] Instalando PHP..." -ForegroundColor Yellow
    choco install php -y --no-progress

    Write-Host "[INFO] Instalando Composer..." -ForegroundColor Yellow
    choco install composer -y --no-progress

    Write-Host "[INFO] Instalando MySQL..." -ForegroundColor Yellow
    choco install mysql -y --no-progress

    # Atualizar variaveis de ambiente
    Write-Host "[INFO] Atualizando variaveis de ambiente..." -ForegroundColor Yellow
    Import-Module $env:ChocolateyInstall\helpers\chocolateyProfile.psm1 -ErrorAction SilentlyContinue
    refreshenv
    
    # Adicionar PHP ao PATH da sessao atual
    $env:PATH += ";C:\tools\php84"
    
    Write-Host "[OK] WebStack instalado com sucesso!" -ForegroundColor Green
}

# =========================================================================
# FUNCAO: Configurar Projeto Yii2
# =========================================================================
function Initialize-YiiProject {
    Write-Host "[INFO] Configurando projeto Yii2..." -ForegroundColor Blue
    
    Set-Location $yiiRoot
    
    # Verificar se o Composer esta disponivel
    if (-not (Get-Command composer -ErrorAction SilentlyContinue)) {
        Write-Host "[ERROR] Composer nao encontrado. Verifique a instalacao." -ForegroundColor Red
        exit 1
    }
    
    # Verificar se o PHP esta disponivel
    if (-not (Get-Command php -ErrorAction SilentlyContinue)) {
        Write-Host "[WARN] PHP nao encontrado. Adicionando ao PATH..." -ForegroundColor Yellow
        $env:PATH += ";C:\tools\php84"
    }
    
    # Instalar dependencias
    Write-Host "[INFO] Instalando dependencias do Composer..." -ForegroundColor Yellow
    composer install --no-interaction --optimize-autoloader
    
    # Inicializar projeto Yii2 para desenvolvimento
    Write-Host "[INFO] Inicializando projeto Yii2 (ambiente de desenvolvimento)..." -ForegroundColor Yellow
    echo "0" | php init --env=Development --overwrite=All
    
    Write-Host "[OK] Projeto Yii2 configurado!" -ForegroundColor Green
    Set-Location $projectRoot
}

# =========================================================================
# FUNCAO: Configurar Database MySQL
# =========================================================================
function Setup-Database {
    Write-Host "[INFO] Configurando database MySQL..." -ForegroundColor Blue
    
    # Verificar se o MySQL esta rodando
    $mysqlService = Get-Service -Name "*mysql*" -ErrorAction SilentlyContinue | Where-Object { $_.Status -eq 'Running' } | Select-Object -First 1
    
    if (-not $mysqlService) {
        Write-Host "[WARN] MySQL nao esta rodando. Tentando iniciar..." -ForegroundColor Yellow
        try {
            Start-Service MySQL* -ErrorAction Stop
            Start-Sleep 5
            Write-Host "[OK] MySQL iniciado!" -ForegroundColor Green
        }
        catch {
            Write-Host "[ERROR] Nao foi possivel iniciar o MySQL. Verifique a instalacao." -ForegroundColor Red
            Write-Host "Se estiver usando WAMP, inicie o WAMP primeiro." -ForegroundColor Yellow
            return $false
        }
    }
    
    # Detectar comando MySQL (WAMP ou instalacao standalone)
    $mysqlPaths = @(
        "mysql", # Se estiver no PATH
        "C:\wamp64\bin\mysql\mysql8.0.*\bin\mysql.exe",
        "C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe",
        "C:\Program Files\MySQL\MySQL Server 8.4\bin\mysql.exe"
    )
    
    $mysqlCmd = $null
    foreach ($path in $mysqlPaths) {
        if ($path -eq "mysql") {
            if (Get-Command mysql -ErrorAction SilentlyContinue) {
                $mysqlCmd = "mysql"
                break
            }
        } elseif ($path -like "*wamp64*") {
            # Para WAMP, procurar versao mais recente
            $wampMysql = Get-ChildItem "C:\wamp64\bin\mysql" -Directory -ErrorAction SilentlyContinue | Sort-Object Name -Descending | Select-Object -First 1
            if ($wampMysql) {
                $mysqlPath = Join-Path $wampMysql.FullName "bin\mysql.exe"
                if (Test-Path $mysqlPath) {
                    $mysqlCmd = "`"$mysqlPath`""
                    break
                }
            }
        } elseif (Test-Path $path) {
            $mysqlCmd = "`"$path`""
            break
        }
    }
    
    if (-not $mysqlCmd) {
        Write-Host "[ERROR] MySQL nao encontrado. Verifique se o WAMP esta iniciado ou MySQL instalado." -ForegroundColor Red
        return $false
    }
    
    Write-Host "[INFO] Criando database 'veigest' usando: $mysqlCmd" -ForegroundColor Yellow
    
    $createDbScript = @'
CREATE DATABASE IF NOT EXISTS veigest CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON veigest.* TO 'root'@'localhost';
FLUSH PRIVILEGES;
'@
    
    try {
        if ($DatabasePassword) {
            $createDbScript | & cmd /c "$mysqlCmd -u root -p$DatabasePassword"
        } else {
            $createDbScript | & cmd /c "$mysqlCmd -u root"
        }
        Write-Host "[OK] Database 'veigest' criada/verificada!" -ForegroundColor Green
        return $true
    }
    catch {
        Write-Host "[ERROR] Erro ao criar database." -ForegroundColor Red
        Write-Host "[INFO] Tente criar manualmente via phpMyAdmin do WAMP:" -ForegroundColor Yellow
        Write-Host "  1. Abra http://localhost/phpmyadmin" -ForegroundColor Yellow
        Write-Host "  2. Crie database 'veigest' com charset utf8mb4_unicode_ci" -ForegroundColor Yellow
        return $false
    }
}

# =========================================================================
# FUNCAO: Executar Migracoes
# =========================================================================
function Run-Migrations {
    Write-Host "[INFO] Executando migracoes da database..." -ForegroundColor Blue
    
    Set-Location $yiiRoot
    
    # Verificar se existe migration sem a primeira que foi removida
    if (-not (Test-Path "console/migrations/m251118_000001_create_companies_table.php")) {
        Write-Host "[WARN] Migracao principal nao encontrada. Recriando..." -ForegroundColor Yellow
        Create-CompaniesTableMigration
    }
    
    # Executar migracoes
    Write-Host "[INFO] Aplicando migracoes..." -ForegroundColor Yellow
    try {
        php yii migrate --interactive=0
        Write-Host "[OK] Migracoes aplicadas com sucesso!" -ForegroundColor Green
    }
    catch {
        Write-Host "[ERROR] Erro ao executar migracoes." -ForegroundColor Red
        Write-Host "Verifique se a database esta acessivel e as configuracoes estao corretas." -ForegroundColor Yellow
    }
    
    Set-Location $projectRoot
}

# =========================================================================
# FUNCAO: Criar Migracao da Tabela Companies (caso tenha sido removida)
# =========================================================================
function Create-CompaniesTableMigration {
    $migrationContent = @'
<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%companies}}`.
 */
class m251118_000001_create_companies_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%companies}}', [
            'id' => $this->primaryKey(),
            'nome' => $this->string(200)->notNull(),
            'nif' => $this->string(20)->notNull()->unique(),
            'email' => $this->string(150),
            'telefone' => $this->string(20),
            'estado' => "ENUM('ativa','suspensa','inativa') NOT NULL DEFAULT 'ativa'",
            'plano' => "ENUM('basico','profissional','enterprise') NOT NULL DEFAULT 'basico'",
            'configuracoes' => $this->json()->comment('Configurações específicas da empresa'),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Criar índices
        $this->createIndex('idx_nif', '{{%companies}}', 'nif');
        $this->createIndex('idx_estado', '{{%companies}}', 'estado');

        // Inserir empresa demo inicial
        $this->insert('{{%companies}}', [
            'nome' => 'VeiGest - Empresa Demo',
            'nif' => '999999990',
            'email' => 'admin@veigest.com',
            'estado' => 'ativa',
            'plano' => 'enterprise',
            'configuracoes' => json_encode([
                'moeda' => 'EUR',
                'timezone' => 'Europe/Lisbon',
                'idioma' => 'pt',
                'alertas_email' => true,
                'dias_alerta_documentos' => 30
            ])
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%companies}}');
    }
}
'@
    
    $migrationFile = Join-Path $yiiRoot "console/migrations/m251118_000001_create_companies_table.php"
    $migrationContent | Out-File -FilePath $migrationFile -Encoding UTF8
}

# =========================================================================
# FUNCAO: Configurar Apache Virtual Host
# =========================================================================
function Setup-ApacheVirtualHost {
    Write-Host "[INFO] Configurando Apache Virtual Host..." -ForegroundColor Blue
    
    # Detectar instalacao do Apache usando WMI
    $apacheService = Get-WmiObject -Class Win32_Service | Where-Object {$_.Name -eq "Apache"}
    $apacheRoot = $null
    
    if ($apacheService -and $apacheService.PathName) {
        # Extrair caminho do executavel
        $execPath = $apacheService.PathName -replace '"', '' -replace ' -k.*$', ''
        if (Test-Path $execPath) {
            $apacheRoot = Split-Path (Split-Path $execPath -Parent) -Parent
            Write-Host "[INFO] Apache detectado via servico em: $apacheRoot" -ForegroundColor Yellow
        }
    }
    
    # Fallback: Verificar caminhos comuns
    if (-not $apacheRoot) {
        $apachePaths = @(
            "C:\tools\Apache24",
            "C:\Apache24",
            "C:\xampp\apache",
            "C:\Program Files\Apache*"
        )
        
        foreach ($path in $apachePaths) {
            $expandedPaths = Get-ChildItem $path -Directory -ErrorAction SilentlyContinue
            foreach ($expandedPath in $expandedPaths) {
                if (Test-Path "$($expandedPath.FullName)\conf\httpd.conf") {
                    $apacheRoot = $expandedPath.FullName
                    break
                }
            }
            if ($apacheRoot) { break }
            
            # Verificar caminho direto tambem
            if (Test-Path "$path\conf\httpd.conf") {
                $apacheRoot = $path
                break
            }
        }
        
        # WAMP como ultimo recurso
        if (-not $apacheRoot -and (Test-Path "C:\wamp64\bin\apache")) {
            $wampApache = Get-ChildItem "C:\wamp64\bin\apache" -Directory -ErrorAction SilentlyContinue | Where-Object { $_.Name -like "apache*" } | Sort-Object Name -Descending | Select-Object -First 1
            if ($wampApache -and (Test-Path "$($wampApache.FullName)\conf\httpd.conf")) {
                $apacheRoot = $wampApache.FullName
            }
        }
    }
    
    if (-not $apacheRoot) {
        Write-Host "[ERROR] Apache nao encontrado. Configuracao manual necessaria." -ForegroundColor Red
        return $false
    }
    
    Write-Host "[INFO] Apache encontrado em: $apacheRoot" -ForegroundColor Green
    
    $httpdConf = Join-Path $apacheRoot "conf\httpd.conf"
    
    # Verificar se o arquivo de configuracao existe
    if (-not (Test-Path $httpdConf)) {
        Write-Host "[ERROR] httpd.conf nao encontrado em $httpdConf" -ForegroundColor Red
        return $false
    }
    
    try {
        # Fazer backup do httpd.conf
        $backupPath = "$httpdConf.backup"
        if (-not (Test-Path $backupPath)) {
            Copy-Item $httpdConf $backupPath
            Write-Host "[INFO] Backup criado: $backupPath" -ForegroundColor Yellow
        }
        
        # Ler configuracao atual
        $httpdContent = Get-Content $httpdConf
        
        # Habilitar mod_rewrite e mod_cgi se nao estiverem ativos
        $rewriteEnabled = $false
        $cgiEnabled = $false
        for ($i = 0; $i -lt $httpdContent.Length; $i++) {
            if ($httpdContent[$i] -match "^#LoadModule rewrite_module") {
                $httpdContent[$i] = $httpdContent[$i] -replace "^#", ""
                $rewriteEnabled = $true
                Write-Host "[INFO] mod_rewrite habilitado" -ForegroundColor Green
            }
            if ($httpdContent[$i] -match "^#LoadModule cgi_module") {
                $httpdContent[$i] = $httpdContent[$i] -replace "^#", ""
                $cgiEnabled = $true
                Write-Host "[INFO] mod_cgi habilitado" -ForegroundColor Green
            }
        }
        
        # Verificar e corrigir porta Listen
        $portCorrected = $false
        for ($i = 0; $i -lt $httpdContent.Length; $i++) {
            if ($httpdContent[$i] -match "^Listen\s+8080") {
                $httpdContent[$i] = "Listen 80"
                $portCorrected = $true
                Write-Host "[INFO] Porta alterada de 8080 para 80" -ForegroundColor Green
            }
        }
        
        # Configurar DocumentRoot para localhost apontar para VeiGest
        $veigestPath = (Join-Path $yiiRoot "frontend/web") -replace "\\", "/"
        for ($i = 0; $i -lt $httpdContent.Length; $i++) {
            if ($httpdContent[$i] -match "^DocumentRoot") {
                $httpdContent[$i] = "DocumentRoot `"$veigestPath`""
                Write-Host "[INFO] DocumentRoot configurado para: $veigestPath" -ForegroundColor Green
            }
            if ($httpdContent[$i] -match "^<Directory.*htdocs") {
                $httpdContent[$i] = "<Directory `"$veigestPath`">"
                Write-Host "[INFO] Directory configurado para: $veigestPath" -ForegroundColor Green
            }
        }
        
        # Adicionar configuração PHP CGI se não existir
        $phpConfigExists = $httpdContent | Where-Object { $_ -match "ScriptAlias.*php-cgi" }
        if (-not $phpConfigExists) {
            # Detectar caminho do PHP
            $phpPath = ""
            $phpPaths = @("C:/tools/php84", "C:/php", "C:/xampp/php", "C:/wamp64/bin/php/php*")
            
            foreach ($path in $phpPaths) {
                if ($path -like "*php*" -and $path.Contains("*")) {
                    $expandedPaths = Get-ChildItem ($path -replace "/", "\\") -Directory -ErrorAction SilentlyContinue 2>$null
                    foreach ($expandedPath in $expandedPaths) {
                        if (Test-Path "$($expandedPath.FullName)\php-cgi.exe") {
                            $phpPath = $expandedPath.FullName -replace "\\", "/"
                            break
                        }
                    }
                } else {
                    $testPath = $path -replace "/", "\\"
                    if (Test-Path "$testPath\php-cgi.exe") {
                        $phpPath = $path
                        break
                    }
                }
                if ($phpPath) { break }
            }
            
            if ($phpPath) {
                $httpdContent += @(
                    "",
                    "# Configuração PHP CGI",
                    "LoadModule cgi_module modules/mod_cgi.so",
                    "ScriptAlias /php-cgi/ `"$phpPath/`"",
                    "Action php-script /php-cgi/php-cgi.exe",
                    "AddHandler php-script .php",
                    "<Directory `"$phpPath`">",
                    "    AllowOverride None",
                    "    Options ExecCGI",
                    "    Require all granted",
                    "</Directory>"
                )
                Write-Host "[INFO] Configuração PHP CGI adicionada (PHP: $phpPath)" -ForegroundColor Green
            } else {
                Write-Host "[WARNING] PHP não encontrado. Execute 'where.exe php' para verificar." -ForegroundColor Yellow
            }
        }
        
        # Salvar httpd.conf modificado
        $httpdContent | Set-Content $httpdConf
        
        # Reiniciar Apache para aplicar configuracoes
        Restart-ApacheService
        
        Write-Host "[OK] Apache configurado com sucesso para localhost!" -ForegroundColor Green
        Write-Host "[INFO] URL disponível:" -ForegroundColor Cyan
        Write-Host "  VeiGest: http://localhost" -ForegroundColor Green
        Write-Host "[INFO] O DocumentRoot aponta diretamente para: $veigestPath" -ForegroundColor Yellow
        
        return $true
        
    } catch {
        Write-Host "[ERROR] Erro ao configurar Apache: $($_.Exception.Message)" -ForegroundColor Red
        return $false
    }
}

# =========================================================================
# FUNCAO: Limpar configurações antigas (SSL, virtual hosts, etc.)
# =========================================================================
function Clean-OldConfigurations {
    Write-Host "[INFO] Limpando configurações antigas..." -ForegroundColor Yellow
    
    # Remover entradas do arquivo hosts
    try {
        $hostsFile = "$env:SystemRoot\System32\drivers\etc\hosts"
        if (Test-Path $hostsFile) {
            $hostsContent = Get-Content $hostsFile | Where-Object { 
                $_ -notmatch "veigest\.dev|backend\.veigest\.dev|phpmyadmin\.veigest\.dev|admin\.veigest" 
            }
            $hostsContent | Set-Content $hostsFile
            Write-Host "[INFO] Entradas DNS antigas removidas do hosts" -ForegroundColor Green
        }
    } catch {
        Write-Host "[WARN] Não foi possível limpar arquivo hosts: $($_.Exception.Message)" -ForegroundColor Yellow
    }
}

# =========================================================================
# FUNCAO: Configurar SSL/HTTPS com certificados auto-assinados
# =========================================================================
function Setup-SSLCertificates {
    param(
        [string]$ApacheRoot,
        [string]$ProjectDomain = "veigest.dev"
    )
    
    Write-Host "[INFO] Configurando SSL/HTTPS..." -ForegroundColor Blue
    
    try {
        # Criar diretorio para certificados
        $certDir = Join-Path $ApacheRoot "conf\ssl"
        if (-not (Test-Path $certDir)) {
            New-Item -Path $certDir -ItemType Directory -Force | Out-Null
            Write-Host "[INFO] Diretorio SSL criado: $certDir" -ForegroundColor Yellow
        }
        
        # Verificar se OpenSSL esta disponivel
        $opensslPath = $null
        $possiblePaths = @(
            Join-Path $ApacheRoot "bin\openssl.exe",
            "C:\tools\openssl\openssl.exe",
            "C:\OpenSSL-Win64\bin\openssl.exe",
            "openssl.exe"  # PATH
        )
        
        foreach ($path in $possiblePaths) {
            if ((Test-Path $path -ErrorAction SilentlyContinue) -or ($path -eq "openssl.exe")) {
                try {
                    $testResult = if ($path -eq "openssl.exe") { 
                        & openssl version 2>$null 
                    } else { 
                        & $path version 2>$null 
                    }
                    if ($LASTEXITCODE -eq 0) {
                        $opensslPath = $path
                        Write-Host "[INFO] OpenSSL encontrado: $opensslPath" -ForegroundColor Green
                        break
                    }
                } catch { }
            }
        }
        
        if (-not $opensslPath) {
            Write-Host "[WARN] OpenSSL nao encontrado. Tentando instalar..." -ForegroundColor Yellow
            
            # Tentar instalar OpenSSL via Chocolatey
            try {
                choco install openssl -y --no-progress 2>$null
                if ($LASTEXITCODE -eq 0) {
                    $opensslPath = "openssl.exe"
                    Write-Host "[INFO] OpenSSL instalado via Chocolatey" -ForegroundColor Green
                } else {
                    throw "Chocolatey falhou"
                }
            } catch {
                Write-Host "[WARN] Falha ao instalar OpenSSL. Usando certificados pre-gerados..." -ForegroundColor Yellow
                return Setup-PreGeneratedSSL -ApacheRoot $ApacheRoot -ProjectDomain $ProjectDomain
            }
        }
        
        # Gerar chave privada
        $keyFile = Join-Path $certDir "$ProjectDomain.key"
        $csrFile = Join-Path $certDir "$ProjectDomain.csr" 
        $crtFile = Join-Path $certDir "$ProjectDomain.crt"
        
        Write-Host "[INFO] Gerando chave privada SSL..." -ForegroundColor Yellow
        $genKeyCmd = if ($opensslPath -eq "openssl.exe") {
            "openssl genrsa -out `"$keyFile`" 2048"
        } else {
            "`"$opensslPath`" genrsa -out `"$keyFile`" 2048"
        }
        
        Invoke-Expression $genKeyCmd 2>$null
        if (-not (Test-Path $keyFile)) {
            throw "Falha ao gerar chave privada"
        }
        
        # Gerar certificado auto-assinado
        Write-Host "[INFO] Gerando certificado SSL auto-assinado..." -ForegroundColor Yellow
        $genCertCmd = if ($opensslPath -eq "openssl.exe") {
            "openssl req -new -x509 -key `"$keyFile`" -out `"$crtFile`" -days 365 -subj `"/C=PT/ST=Lisboa/L=Lisboa/O=VeiGest/OU=Dev/CN=$ProjectDomain`""
        } else {
            "`"$opensslPath`" req -new -x509 -key `"$keyFile`" -out `"$crtFile`" -days 365 -subj `"/C=PT/ST=Lisboa/L=Lisboa/O=VeiGest/OU=Dev/CN=$ProjectDomain`""
        }
        
        Invoke-Expression $genCertCmd 2>$null
        if (-not (Test-Path $crtFile)) {
            throw "Falha ao gerar certificado"
        }
        
        Write-Host "[OK] Certificados SSL gerados com sucesso!" -ForegroundColor Green
        Write-Host "[INFO] Chave: $keyFile" -ForegroundColor Gray
        Write-Host "[INFO] Certificado: $crtFile" -ForegroundColor Gray
        
        # Configurar Apache para SSL
        Setup-ApacheSSLConfig -ApacheRoot $ApacheRoot -ProjectDomain $ProjectDomain -KeyFile $keyFile -CrtFile $crtFile
        
        return $true
        
    } catch {
        Write-Host "[ERROR] Erro ao configurar SSL: $($_.Exception.Message)" -ForegroundColor Red
        Write-Host "[INFO] Tentando metodo alternativo..." -ForegroundColor Yellow
        return Setup-PreGeneratedSSL -ApacheRoot $ApacheRoot -ProjectDomain $ProjectDomain
    }
}

# =========================================================================
# FUNCAO: Configurar Apache para SSL
# =========================================================================
function Setup-ApacheSSLConfig {
    param(
        [string]$ApacheRoot,
        [string]$ProjectDomain,
        [string]$KeyFile,
        [string]$CrtFile
    )
    
    Write-Host "[INFO] Configurando Apache para SSL..." -ForegroundColor Yellow
    
    $httpdConf = Join-Path $ApacheRoot "conf\httpd.conf"
    $sslConf = Join-Path $ApacheRoot "conf\ssl.conf"
    
    # Habilitar mod_ssl no httpd.conf
    $httpdContent = Get-Content $httpdConf
    $sslEnabled = $false
    
    for ($i = 0; $i -lt $httpdContent.Length; $i++) {
        if ($httpdContent[$i] -match "^#.*LoadModule ssl_module") {
            $httpdContent[$i] = $httpdContent[$i] -replace "^#", ""
            $sslEnabled = $true
            Write-Host "[INFO] mod_ssl habilitado" -ForegroundColor Green
        }
    }
    
    # Adicionar Listen 443 se nao existir
    $listen443Exists = $httpdContent | Where-Object { $_ -match "^Listen\s+443" }
    if (-not $listen443Exists) {
        # Encontrar linha Listen 80 e adicionar 443 depois
        for ($i = 0; $i -lt $httpdContent.Length; $i++) {
            if ($httpdContent[$i] -match "^Listen\s+80") {
                $httpdContent = $httpdContent[0..$i] + @("Listen 443 ssl") + $httpdContent[($i+1)..($httpdContent.Length-1)]
                Write-Host "[INFO] Listen 443 ssl adicionado" -ForegroundColor Green
                break
            }
        }
    }
    
    # Adicionar include ssl.conf se nao existir
    $sslIncludeExists = $httpdContent | Where-Object { $_ -match "Include.*ssl\.conf" }
    if (-not $sslIncludeExists) {
        $httpdContent += @(
            "",
            "# SSL Configuration",
            "Include conf/ssl.conf"
        )
        Write-Host "[INFO] Include ssl.conf adicionado" -ForegroundColor Green
    }
    
    # Salvar httpd.conf
    $httpdContent | Set-Content $httpdConf
    
    # Criar ssl.conf
    $keyFileApache = $KeyFile -replace "\\", "/"
    $crtFileApache = $CrtFile -replace "\\", "/"
    $frontendPathApache = (Join-Path $yiiRoot "frontend/web") -replace "\\", "/"
    $backendPathApache = (Join-Path $yiiRoot "backend/web") -replace "\\", "/"
    
    $sslConfigContent = @"
# =========================================================================
# SSL/HTTPS CONFIGURATION FOR VEIGEST
# =========================================================================

# SSL Protocol and Cipher Configuration
SSLEngine on
SSLProtocol all -SSLv2 -SSLv3
SSLCipherSuite HIGH:MEDIUM:!aNULL:!MD5
SSLHonorCipherOrder on

# Frontend HTTPS
<VirtualHost *:443>
    ServerName $ProjectDomain
    DocumentRoot "$frontendPathApache"
    
    SSLEngine on
    SSLCertificateFile "$crtFileApache"
    SSLCertificateKeyFile "$keyFileApache"
    
    <Directory "$frontendPathApache">
        AllowOverride All
        Require all granted
        DirectoryIndex index.php
        
        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule ^(.*)$ index.php [QSA,L]
    </Directory>
    
    ErrorLog "logs/veigest_ssl_error.log"
    CustomLog "logs/veigest_ssl_access.log" combined
</VirtualHost>

# Backend HTTPS
<VirtualHost *:443>
    ServerName admin.$ProjectDomain
    DocumentRoot "$backendPathApache"
    
    SSLEngine on
    SSLCertificateFile "$crtFileApache"
    SSLCertificateKeyFile "$keyFileApache"
    
    <Directory "$backendPathApache">
        AllowOverride All
        Require all granted
        DirectoryIndex index.php
        
        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule ^(.*)$ index.php [QSA,L]
    </Directory>
    
    ErrorLog "logs/veigest_admin_ssl_error.log"
    CustomLog "logs/veigest_admin_ssl_access.log" combined
</VirtualHost>

# Redirect HTTP to HTTPS (opcional - comente se nao quiser)
<VirtualHost *:80>
    ServerName $ProjectDomain
    Redirect permanent / https://$ProjectDomain/
</VirtualHost>

<VirtualHost *:80>
    ServerName admin.$ProjectDomain
    Redirect permanent / https://admin.$ProjectDomain/
</VirtualHost>
"@

    $sslConfigContent | Set-Content $sslConf -Encoding UTF8
    Write-Host "[OK] Configuracao SSL criada: $sslConf" -ForegroundColor Green
}

# =========================================================================
# FUNCAO: Configurar SSL com certificados pre-gerados (fallback)
# =========================================================================
function Setup-PreGeneratedSSL {
    param(
        [string]$ApacheRoot,
        [string]$ProjectDomain
    )
    
    Write-Host "[INFO] Usando certificados SSL pre-gerados..." -ForegroundColor Yellow
    
    $certDir = Join-Path $ApacheRoot "conf\ssl"
    if (-not (Test-Path $certDir)) {
        New-Item -Path $certDir -ItemType Directory -Force | Out-Null
    }
    
    $keyFile = Join-Path $certDir "$ProjectDomain.key"
    $crtFile = Join-Path $certDir "$ProjectDomain.crt"
    
    # Gerar chave privada simples (apenas para desenvolvimento)
    $keyContent = @"
-----BEGIN PRIVATE KEY-----
MIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQC7VJTUt9Us8cKB
wQNVrIOlCWvJsQBJaYkPLMKQE6FLsH7HiULVTBjd1i5xDi8+s2C5F9B
-----END PRIVATE KEY-----
"@
    
    # Gerar certificado simples (apenas para desenvolvimento)  
    $crtContent = @"
-----BEGIN CERTIFICATE-----
MIIDXTCCAkWgAwIBAgIJALkQ7/VB7BsHMA0GCSqGSIb3DQEBCwUAMEUxCzAJBgNV
BAYTAkFVMRMwEQYDVQQIDApTb21lLVN0YXRlMSEwHwYDVQQKDBhJbnRlcm5ldCBX
aWRnaXRzIFB0eSBMdGQwHhcNMTYwNzI4MDA1NjM3WhcNMjYwNzI2MDA1NjM3WjBF
MQswCQYDVQQGEwJBVTETMBEGA1UECAwKU29tZS1TdGF0ZTEhMB8GA1UECgwYSW50
ZXJuZXQgV2lkZ2l0cyBQdHkgTHRkMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIB
CgKCAQEAuVSU1LfVLPHCgcEDVayDpQlrybEASWmJDyzCkBOhS7B+x4lC1UwY3dYu
cQ4vPrNguRfQvYmQ9Axy+Oy7+xvzV/QNJaTzqgX3n9lLgdNKVOv9Aj3FjTqQr4zA
VRpmeQa1oJ4T9DIrnmqOgvuYOF3SgMB1XKj/aWp29EvJhHWNZljPudT8hz4R1+vQ
N1ZUJ7gwR5X9UEJnZWjVkzEmYdXkvjLJhH5m+ULc9goy1J+GfsV+cIr31nJIdJWQ
R4N7Rx9j6QLT5IJS4VK5ghgq8vF2MJgRIXwoK2xP5AsVVSlaK8p5cHLd9ILr7VbM
zs1gQd6LQUpMIZ7gGE9VnDVOkBE8BwIDAQABo1AwTjAdBgNVHQ4EFgQU4lzOhs/A
XgYU05msX6ig65kZvdwwHwYDVR0jBBgwFoAU4lzOhs/AXgYU05msX6ig65kZvdww
DAYDVR0TBAUwAwEB/zANBgkqhkiG9w0BAQsFAAOCAQEAPi8uQv4YCVo4C1r35fZs
QlSGzYma7T7cVlqxYMQ8Z9k91z0M0qtZfzYZWdVgCZgJ+2TblGeLYYhz3E5r9b8S
YjfzK+ODq3zry4MKKvg1bRpKX5K8YGGOxUgKFz7J+GGf3bZpZ8tT3e3g7VrT4Hbu
-----END CERTIFICATE-----
"@
    
    # Este é apenas um exemplo - em produção use certificados reais
    Write-Host "[WARN] Usando certificados de exemplo - NAO USE EM PRODUCAO!" -ForegroundColor Red
    Write-Host "[INFO] Para desenvolvimento local apenas" -ForegroundColor Yellow
    
    # Por simplicidade, vamos pular a geração de certificados e só configurar HTTP
    Write-Host "[INFO] Configurando apenas HTTP por enquanto..." -ForegroundColor Yellow
    return $false
}

# =========================================================================
# FUNCAO: Reiniciar Apache
# =========================================================================
function Restart-ApacheService {
    Write-Host "[INFO] Reiniciando Apache..." -ForegroundColor Blue
    
    # Lista de servicos Apache comuns
    $apacheServices = @("Apache", "Apache2.4", "Apache24", "wampapache64", "httpd")
    $restarted = $false
    
    foreach ($serviceName in $apacheServices) {
        $service = Get-Service -Name $serviceName -ErrorAction SilentlyContinue
        if ($service -and $service.Status -eq "Running") {
            try {
                Write-Host "[INFO] Tentando reiniciar servico: $serviceName" -ForegroundColor Yellow
                
                # Tentar usar net stop/start para evitar problemas de permissao
                $stopResult = Start-Process -FilePath "net" -ArgumentList "stop", $serviceName -Wait -PassThru -NoNewWindow -RedirectStandardError "temp_error.log" -RedirectStandardOutput "temp_output.log"
                Start-Sleep -Seconds 2
                $startResult = Start-Process -FilePath "net" -ArgumentList "start", $serviceName -Wait -PassThru -NoNewWindow -RedirectStandardError "temp_error2.log" -RedirectStandardOutput "temp_output2.log"
                
                if ($startResult.ExitCode -eq 0) {
                    Write-Host "[OK] Apache reiniciado ($serviceName)!" -ForegroundColor Green
                    $restarted = $true
                    break
                } else {
                    # Fallback para Restart-Service
                    Restart-Service $serviceName -Force -ErrorAction Stop
                    Write-Host "[OK] Apache reiniciado via PowerShell ($serviceName)!" -ForegroundColor Green
                    $restarted = $true
                    break
                }
            } catch {
                Write-Host "[WARN] Falha ao reiniciar $serviceName : $($_.Exception.Message)" -ForegroundColor Yellow
            }
        }
    }
    
    # Limpar arquivos temporarios
    @("temp_error.log", "temp_output.log", "temp_error2.log", "temp_output2.log") | ForEach-Object {
        if (Test-Path $_) { Remove-Item $_ -Force -ErrorAction SilentlyContinue }
    }
    
    if (-not $restarted) {
        Write-Host "[WARN] Apache nao foi reiniciado automaticamente." -ForegroundColor Yellow
        Write-Host "[INFO] Para reiniciar manualmente execute como Administrador:" -ForegroundColor Cyan
        Write-Host "  net stop Apache && net start Apache" -ForegroundColor White
        
        # Verificar se pelo menos esta rodando
        $runningApache = Get-Service | Where-Object { $_.Name -like "*apache*" -and $_.Status -eq "Running" }
        if ($runningApache) {
            Write-Host "[INFO] Apache ainda esta em execucao: $($runningApache.Name)" -ForegroundColor Green
        }
    }
    
    return $restarted
}

# =========================================================================
# FUNCAO: Verificar Configuracao Final
# =========================================================================
function Test-ProjectSetup {
    Write-Host "[INFO] Verificando configuracao..." -ForegroundColor Blue
    
    # Verificar se o Yii2 foi inicializado
    $frontendIndex = Join-Path $yiiRoot "frontend/web/index.php"
    $backendIndex = Join-Path $yiiRoot "backend/web/index.php"
    
    if ((Test-Path $frontendIndex) -and (Test-Path $backendIndex)) {
        Write-Host "[OK] Arquivos do Yii2 encontrados" -ForegroundColor Green
    } else {
        Write-Host "[ERROR] Arquivos do Yii2 nao encontrados" -ForegroundColor Red
    }
    
    # Verificar configuracao da database
    $dbConfig = Join-Path $yiiRoot "common/config/main-local.php"
    if (Test-Path $dbConfig) {
        $configContent = Get-Content $dbConfig -Raw
        if ($configContent -match "dbname=veigest") {
            Write-Host "[OK] Configuracao da database correta" -ForegroundColor Green
        } else {
            Write-Host "[ERROR] Configuracao da database incorreta" -ForegroundColor Red
        }
    }
}

# =========================================================================
# EXECUCAO PRINCIPAL
# =========================================================================

try {
    # Verificar privilegios de administrador
    Test-AdminPrivileges
    
    # 1. Instalar WebStack
    Install-WebStack
    
    # 2. Configurar projeto Yii2
    Initialize-YiiProject
    
    # 3. Configurar database
    if (Setup-Database) {
        # 4. Executar migracoes
        Run-Migrations
    }
    
    # 5. Configurar Apache Virtual Host
    if (Setup-ApacheVirtualHost) {
        # 6. Reiniciar Apache
        Restart-ApacheService
    }
    
    # 7. Verificacao final
    Test-ProjectSetup
    
    # Conclusao
    Write-Host ""
    Write-Host "==========================================================================" -ForegroundColor Green
    Write-Host "                      CONFIGURACAO CONCLUIDA!                            " -ForegroundColor Green
    Write-Host "==========================================================================" -ForegroundColor Green
    Write-Host ""
    Write-Host "URLs do Projeto:" -ForegroundColor Cyan
    Write-Host "   Frontend: http://$ProjectDomain" -ForegroundColor White
    Write-Host "   Backend:  http://admin.$ProjectDomain" -ForegroundColor White
    Write-Host ""
    Write-Host "Credenciais de Administrador:" -ForegroundColor Cyan
    Write-Host "   Email:    admin@veigest.com" -ForegroundColor White
    Write-Host "   Password: admin" -ForegroundColor White
    Write-Host ""
    Write-Host "Diretorios Importantes:" -ForegroundColor Cyan
    Write-Host "   Projeto:  $yiiRoot" -ForegroundColor White
    Write-Host "   Frontend: $yiiRoot\frontend\web" -ForegroundColor White
    Write-Host "   Backend:  $yiiRoot\backend\web" -ForegroundColor White
    Write-Host ""
    Write-Host "Proximos Passos:" -ForegroundColor Yellow
    Write-Host "   1. Acesse http://$ProjectDomain para ver o frontend" -ForegroundColor White
    Write-Host "   2. Acesse http://admin.$ProjectDomain para a area administrativa" -ForegroundColor White
    Write-Host "   3. Faca login com as credenciais acima" -ForegroundColor White
    Write-Host "   4. Comece a desenvolver seu sistema VeiGest!" -ForegroundColor White
    Write-Host ""
    
} catch {
    Write-Host ""
    Write-Host "ERRO durante a configuracao:" -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red
    Write-Host ""
    Write-Host "Sugestoes:" -ForegroundColor Yellow
    Write-Host "   - Verifique se esta executando como Administrador" -ForegroundColor White
    Write-Host "   - Verifique sua conexao com a internet" -ForegroundColor White
    Write-Host "   - Certifique-se de que nao ha antivirus bloqueando" -ForegroundColor White
    Write-Host "   - Execute novamente o script" -ForegroundColor White
    exit 1
}
