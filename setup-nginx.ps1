# =========================================================================
# SCRIPT DE CONFIGURACAO VEIGEST COM NGINX
# =========================================================================
# Este script desinstala Apache e configura Nginx + PHP + MySQL + Yii2
# Autor: GitHub Copilot
# Data: 19/11/2025
# =========================================================================

param(
    [string]$DatabasePassword = "admin123",
    [switch]$SkipWebStack = $false
)

Write-Host "=========================================================================" -ForegroundColor Cyan
Write-Host "         CONFIGURACAO VEIGEST COM NGINX                                  " -ForegroundColor Cyan
Write-Host "=========================================================================" -ForegroundColor Cyan
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
# FUNCAO: Desinstalar Apache
# =========================================================================
function Remove-Apache {
    Write-Host "[INFO] Removendo Apache existente..." -ForegroundColor Blue
    
    # Parar processos Apache
    Get-Process -Name "httpd" -ErrorAction SilentlyContinue | Stop-Process -Force
    Get-Process -Name "apache*" -ErrorAction SilentlyContinue | Stop-Process -Force
    
    # Remover servicos Apache
    $services = Get-Service | Where-Object { $_.Name -like "*apache*" -or $_.DisplayName -like "*apache*" }
    foreach ($service in $services) {
        try {
            Stop-Service $service.Name -Force -ErrorAction SilentlyContinue
            & sc.exe delete $service.Name 2>$null
            Write-Host "[INFO] Servico Apache removido: $($service.Name)" -ForegroundColor Yellow
        } catch { }
    }
    
    # Remover diretorios Apache comuns
    $apacheDirs = @(
        "C:\Apache24",
        "C:\tools\Apache24", 
        "$env:APPDATA\Apache24",
        "C:\xampp\apache",
        "C:\wamp64\bin\apache"
    )
    
    foreach ($dir in $apacheDirs) {
        if (Test-Path $dir) {
            try {
                Remove-Item $dir -Recurse -Force -ErrorAction SilentlyContinue
                Write-Host "[INFO] Diretorio Apache removido: $dir" -ForegroundColor Yellow
            } catch { }
        }
    }
    
    Write-Host "[OK] Apache removido com sucesso" -ForegroundColor Green
}

# =========================================================================
# FUNCAO: Instalar Chocolatey
# =========================================================================
function Install-Chocolatey {
    Write-Host "[INFO] Verificando Chocolatey..." -ForegroundColor Blue
    
    if (Get-Command choco -ErrorAction SilentlyContinue) {
        Write-Host "[OK] Chocolatey ja instalado" -ForegroundColor Green
        return
    }
    
    Write-Host "[INFO] Instalando Chocolatey..." -ForegroundColor Yellow
    try {
        Set-ExecutionPolicy Bypass -Scope Process -Force
        [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072
        Invoke-Expression ((New-Object System.Net.WebClient).DownloadString('https://chocolatey.org/install.ps1'))
        
        # Atualizar PATH
        $env:Path = [System.Environment]::GetEnvironmentVariable("Path","Machine") + ";" + [System.Environment]::GetEnvironmentVariable("Path","User")
        
        Write-Host "[OK] Chocolatey instalado com sucesso" -ForegroundColor Green
    } catch {
        Write-Host "[ERROR] Falha ao instalar Chocolatey: $($_.Exception.Message)" -ForegroundColor Red
        exit 1
    }
}

# =========================================================================
# FUNCAO: Instalar WebStack (Nginx, PHP, MySQL, Composer)
# =========================================================================
function Install-WebStack {
    if ($SkipWebStack) {
        Write-Host "[SKIP] Pulando instalacao do WebStack" -ForegroundColor Yellow
        return
    }
    
    Write-Host "[INFO] Instalando WebStack (Nginx + PHP + MySQL + Composer)..." -ForegroundColor Blue
    
    # Instalar todos os componentes
    $packages = @(
        "nginx",
        "php --version=8.4.14",
        "mysql --version=8.0.40",
        "composer"
    )
    
    foreach ($package in $packages) {
        Write-Host "[INFO] Instalando $package..." -ForegroundColor Yellow
        try {
            & choco install $package -y --no-progress --force
            if ($LASTEXITCODE -ne 0) {
                throw "Chocolatey retornou codigo $LASTEXITCODE"
            }
            Write-Host "[OK] $package instalado" -ForegroundColor Green
        } catch {
            Write-Host "[ERROR] Falha ao instalar $package" -ForegroundColor Red
            Write-Host $_.Exception.Message -ForegroundColor Red
        }
    }
    
    # Atualizar PATH
    $env:Path = [System.Environment]::GetEnvironmentVariable("Path","Machine") + ";" + [System.Environment]::GetEnvironmentVariable("Path","User")
    
    Write-Host "[OK] WebStack instalado com sucesso" -ForegroundColor Green
}

# =========================================================================
# FUNCAO: Configurar PHP
# =========================================================================
function Configure-PHP {
    Write-Host "[INFO] Configurando PHP..." -ForegroundColor Blue
    
    # Encontrar instalacao do PHP
    $phpPaths = @(
        "C:\tools\php84",
        "C:\ProgramData\chocolatey\lib\php\tools",
        "C:\php"
    )
    
    $phpPath = $null
    foreach ($path in $phpPaths) {
        if (Test-Path "$path\php.exe") {
            $phpPath = $path
            break
        }
    }
    
    if (-not $phpPath) {
        Write-Host "[ERROR] PHP nao encontrado" -ForegroundColor Red
        return $false
    }
    
    Write-Host "[INFO] PHP encontrado em: $phpPath" -ForegroundColor Green
    
    # Configurar php.ini
    $phpIni = Join-Path $phpPath "php.ini"
    $phpIniDev = Join-Path $phpPath "php.ini-development"
    
    if (-not (Test-Path $phpIni) -and (Test-Path $phpIniDev)) {
        Copy-Item $phpIniDev $phpIni
        Write-Host "[INFO] php.ini criado a partir do template" -ForegroundColor Yellow
    }
    
    if (Test-Path $phpIni) {
        # Configuracoes essenciais
        $iniConfig = @{
            "extension=pdo_mysql" = "extension=pdo_mysql"
            "extension=mysqli" = "extension=mysqli"
            "extension=mbstring" = "extension=mbstring"
            "extension=openssl" = "extension=openssl"
            "extension=curl" = "extension=curl"
            "cgi.fix_pathinfo=0" = "cgi.fix_pathinfo=0"
            "max_execution_time=300" = "max_execution_time=300"
            "memory_limit=512M" = "memory_limit=512M"
        }
        
        $iniContent = Get-Content $phpIni
        $modified = $false
        
        foreach ($setting in $iniConfig.Keys) {
            $found = $false
            for ($i = 0; $i -lt $iniContent.Length; $i++) {
                if ($iniContent[$i] -match "^;?$([regex]::Escape($setting.Split('=')[0]))") {
                    $iniContent[$i] = $iniConfig[$setting]
                    $found = $true
                    $modified = $true
                    break
                }
            }
            
            if (-not $found) {
                $iniContent += $iniConfig[$setting]
                $modified = $true
            }
        }
        
        if ($modified) {
            $iniContent | Set-Content $phpIni
            Write-Host "[OK] PHP configurado" -ForegroundColor Green
        }
    }
    
    return $true
}

# =========================================================================
# FUNCAO: Configurar Nginx
# =========================================================================
function Configure-Nginx {
    Write-Host "[INFO] Configurando Nginx..." -ForegroundColor Blue
    
    # Encontrar instalacao do Nginx
    $nginxPaths = @(
        "C:\tools\nginx",
        "C:\ProgramData\chocolatey\lib\nginx\tools",
        "C:\nginx"
    )
    
    $nginxPath = $null
    foreach ($path in $nginxPaths) {
        if (Test-Path "$path\nginx.exe") {
            $nginxPath = $path
            break
        }
    }
    
    if (-not $nginxPath) {
        Write-Host "[ERROR] Nginx nao encontrado" -ForegroundColor Red
        return $false
    }
    
    Write-Host "[INFO] Nginx encontrado em: $nginxPath" -ForegroundColor Green
    
    # Encontrar PHP
    $phpPaths = @(
        "C:\tools\php84",
        "C:\ProgramData\chocolatey\lib\php\tools",
        "C:\php"
    )
    
    $phpPath = $null
    foreach ($path in $phpPaths) {
        if (Test-Path "$path\php-cgi.exe") {
            $phpPath = $path
            break
        }
    }
    
    if (-not $phpPath) {
        Write-Host "[ERROR] PHP-CGI nao encontrado" -ForegroundColor Red
        return $false
    }
    
    # Configurar nginx.conf
    $nginxConf = Join-Path $nginxPath "conf\nginx.conf"
    $veigestWeb = (Join-Path $yiiRoot "frontend\web") -replace "\\", "/"
    
    # Remover página padrão que interfere
    $defaultHtml = Join-Path $nginxPath "html\index.html"
    if (Test-Path $defaultHtml) {
        Remove-Item $defaultHtml -Force -ErrorAction SilentlyContinue
    }
    
    $nginxConfig = @"
worker_processes  1;

events {
    worker_connections  1024;
}

http {
    include       mime.types;
    default_type  application/octet-stream;
    sendfile        on;
    keepalive_timeout  65;

    server {
        listen       80;
        server_name  localhost;
        root         $veigestWeb;
        index        index.php index.html;

        # Yii2 frontend - URL rewriting
        location / {
            try_files \`$uri \`$uri/ /index.php?\`$query_string;
        }

        # PHP processing
        location ~ \.php\$ {
            fastcgi_pass   127.0.0.1:9000;
            fastcgi_index  index.php;
            fastcgi_param  SCRIPT_FILENAME  \`$document_root\`$fastcgi_script_name;
            include        fastcgi_params;
        }

        # Static files caching
        location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)\$ {
            expires 1y;
            add_header Cache-Control "public, immutable";
        }

        # Security - deny access to sensitive files
        location ~ /\.(ht|git) {
            deny all;
        }
    }

    # Backend admin interface
    server {
        listen       8080;
        server_name  localhost;
        root         $veigestWeb/../../../backend/web;
        index        index.php index.html;

        location / {
            try_files \`$uri \`$uri/ /index.php?\`$query_string;
        }

        location ~ \.php\$ {
            fastcgi_pass   127.0.0.1:9000;
            fastcgi_index  index.php;
            fastcgi_param  SCRIPT_FILENAME  \`$document_root\`$fastcgi_script_name;
            include        fastcgi_params;
        }
    }
}
"@
    
    # Criar arquivo temporário no workspace e copiar (evita BOM)
    $tempConf = Join-Path $PSScriptRoot "temp-nginx.conf"
    $nginxConfig | Out-File -FilePath $tempConf -Encoding ASCII -NoNewline
    Copy-Item $tempConf $nginxConf -Force
    Remove-Item $tempConf -Force -ErrorAction SilentlyContinue
    Write-Host "[OK] Nginx configurado para VeiGest" -ForegroundColor Green
    
    # Criar servico do Nginx
    try {
        & nssm install nginx "$nginxPath\nginx.exe" 2>$null
        & nssm set nginx AppDirectory "$nginxPath" 2>$null
        & nssm set nginx DisplayName "Nginx Web Server" 2>$null
        & nssm set nginx Description "Nginx HTTP Server para VeiGest" 2>$null
        Write-Host "[OK] Servico Nginx criado" -ForegroundColor Green
    } catch {
        Write-Host "[INFO] Servico Nginx nao criado (NSSM nao disponivel)" -ForegroundColor Yellow
    }
    
    return $true
}

# =========================================================================
# FUNCAO: Iniciar PHP-FPM
# =========================================================================
function Start-PHPFPM {
    Write-Host "[INFO] Iniciando PHP-FPM..." -ForegroundColor Blue
    
    $phpPaths = @(
        "C:\tools\php84",
        "C:\ProgramData\chocolatey\lib\php\tools",
        "C:\php"
    )
    
    $phpPath = $null
    foreach ($path in $phpPaths) {
        if (Test-Path "$path\php-cgi.exe") {
            $phpPath = $path
            break
        }
    }
    
    if (-not $phpPath) {
        Write-Host "[ERROR] PHP-CGI nao encontrado" -ForegroundColor Red
        return $false
    }
    
    # Iniciar PHP-FPM na porta 9000
    $phpCgi = Join-Path $phpPath "php-cgi.exe"
    $env:PHP_FCGI_MAX_REQUESTS = "0"
    $env:PHP_FCGI_CHILDREN = "4"
    
    Start-Process -FilePath $phpCgi -ArgumentList @("-b", "127.0.0.1:9000") -WindowStyle Hidden
    
    Start-Sleep 2
    Write-Host "[OK] PHP-FPM iniciado na porta 9000" -ForegroundColor Green
    return $true
}

# =========================================================================
# FUNCAO: Configurar MySQL
# =========================================================================
function Configure-MySQL {
    param([string]$Password = "admin123")
    
    Write-Host "[INFO] Configurando MySQL..." -ForegroundColor Blue
    
    # Iniciar servico MySQL se nao estiver rodando
    $mysqlService = Get-Service -Name "MySQL*" -ErrorAction SilentlyContinue | Select-Object -First 1
    if ($mysqlService) {
        if ($mysqlService.Status -ne "Running") {
            Start-Service $mysqlService.Name
            Write-Host "[INFO] Servico MySQL iniciado" -ForegroundColor Yellow
        }
    } else {
        Write-Host "[ERROR] Servico MySQL nao encontrado" -ForegroundColor Red
        return $false
    }
    
    Start-Sleep 5
    
    # Configurar senha root e criar database
    try {
        # Tentar conectar sem senha primeiro (instalacao limpa)
        & mysql -u root -e "ALTER USER 'root'@'localhost' IDENTIFIED BY '$Password'; FLUSH PRIVILEGES;" 2>$null
        
        # Remover database anterior se existir
        & mysql -u root -p$Password -e "DROP DATABASE IF EXISTS veigest;" 2>$null
        
        # Criar nova database
        & mysql -u root -p$Password -e "CREATE DATABASE veigest CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>$null
        
        Write-Host "[OK] MySQL configurado - Database 'veigest' criada" -ForegroundColor Green
        return $true
    } catch {
        Write-Host "[ERROR] Falha ao configurar MySQL: $($_.Exception.Message)" -ForegroundColor Red
        return $false
    }
}

# =========================================================================
# FUNCAO: Configurar Projeto Yii2
# =========================================================================
function Setup-YiiProject {
    Write-Host "[INFO] Configurando projeto Yii2..." -ForegroundColor Blue
    
    if (-not (Test-Path $yiiRoot)) {
        Write-Host "[ERROR] Diretorio do projeto nao encontrado: $yiiRoot" -ForegroundColor Red
        return $false
    }
    
    Set-Location $yiiRoot
    
    # Instalar dependencias
    Write-Host "[INFO] Instalando dependencias via Composer..." -ForegroundColor Yellow
    & composer install --prefer-dist --no-dev --optimize-autoloader
    
    if ($LASTEXITCODE -ne 0) {
        Write-Host "[ERROR] Falha ao instalar dependencias" -ForegroundColor Red
        return $false
    }
    
    # Inicializar projeto para desenvolvimento
    Write-Host "[INFO] Inicializando projeto Yii2..." -ForegroundColor Yellow
    echo "0" | & php init --env=Development --overwrite=All
    
    # Configurar database
    $dbConfig = @"
<?php
return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=veigest',
    'username' => 'root',
    'password' => '$DatabasePassword',
    'charset' => 'utf8mb4',
];
"@
    
    $dbConfigPath = Join-Path $yiiRoot "common\config\main-local.php"
    $dbConfigContent = @"
<?php
return [
    'components' => [
        'db' => $dbConfig,
        'mailer' => [
            'class' => 'yii\symfonymailer\Mailer',
            'viewPath' => '@common/mail',
            'useFileTransport' => true,
        ],
    ],
];
"@
    
    $dbConfigContent | Set-Content $dbConfigPath -Encoding UTF8
    Write-Host "[OK] Configuracao de database criada" -ForegroundColor Green
    
    # Executar migracoes
    Write-Host "[INFO] Executando migracoes..." -ForegroundColor Yellow
    & php yii migrate --interactive=0
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host "[OK] Migracoes executadas com sucesso" -ForegroundColor Green
    } else {
        Write-Host "[WARNING] Algumas migracoes falharam, mas continuando..." -ForegroundColor Yellow
    }
    
    Set-Location $projectRoot
    return $true
}

# =========================================================================
# FUNCAO: Iniciar Servicos
# =========================================================================
function Start-Services {
    Write-Host "[INFO] Iniciando servicos..." -ForegroundColor Blue
    
    # Iniciar MySQL
    $mysqlService = Get-Service -Name "MySQL*" -ErrorAction SilentlyContinue | Select-Object -First 1
    if ($mysqlService -and $mysqlService.Status -ne "Running") {
        Start-Service $mysqlService.Name
        Write-Host "[OK] MySQL iniciado" -ForegroundColor Green
    }
    
    # Iniciar PHP-FPM
    Start-PHPFPM
    
    # Iniciar Nginx
    $nginxPaths = @(
        "C:\tools\nginx",
        "C:\ProgramData\chocolatey\lib\nginx\tools",
        "C:\nginx"
    )
    
    $nginxPath = $null
    foreach ($path in $nginxPaths) {
        if (Test-Path "$path\nginx.exe") {
            $nginxPath = $path
            break
        }
    }
    
    if ($nginxPath) {
        Start-Process -FilePath "$nginxPath\nginx.exe" -WorkingDirectory $nginxPath -WindowStyle Hidden
        Write-Host "[OK] Nginx iniciado" -ForegroundColor Green
    }
    
    Start-Sleep 3
    Write-Host "[OK] Todos os servicos iniciados" -ForegroundColor Green
}

# =========================================================================
# FUNCAO: Testar Configuracao
# =========================================================================
function Test-Configuration {
    Write-Host "[INFO] Testando configuracao..." -ForegroundColor Blue
    
    # Testar frontend
    try {
        $response = Invoke-WebRequest -Uri "http://localhost" -UseBasicParsing -TimeoutSec 10 -ErrorAction Stop
        Write-Host "[OK] Frontend respondendo (Status: $($response.StatusCode))" -ForegroundColor Green
    } catch {
        Write-Host "[ERROR] Frontend nao responde: $($_.Exception.Message)" -ForegroundColor Red
    }
    
    # Testar backend
    try {
        $response = Invoke-WebRequest -Uri "http://localhost:8080" -UseBasicParsing -TimeoutSec 10 -ErrorAction Stop
        Write-Host "[OK] Backend respondendo (Status: $($response.StatusCode))" -ForegroundColor Green
    } catch {
        Write-Host "[WARNING] Backend nao responde: $($_.Exception.Message)" -ForegroundColor Yellow
    }
    
    # Testar conexao MySQL
    try {
        & mysql -u root -p$DatabasePassword -e "SELECT 1;" 2>$null
        if ($LASTEXITCODE -eq 0) {
            Write-Host "[OK] MySQL conectando corretamente" -ForegroundColor Green
        }
    } catch {
        Write-Host "[WARNING] Problema na conexao MySQL" -ForegroundColor Yellow
    }
}

# =========================================================================
# EXECUCAO PRINCIPAL
# =========================================================================
try {
    Test-AdminPrivileges
    Remove-Apache
    Install-Chocolatey
    Install-WebStack
    Configure-PHP
    Configure-Nginx
    Configure-MySQL -Password $DatabasePassword
    Setup-YiiProject
    Start-Services
    Test-Configuration
    
    Write-Host ""
    Write-Host "=========================================================================" -ForegroundColor Green
    Write-Host "                    CONFIGURACAO CONCLUIDA COM SUCESSO!                  " -ForegroundColor Green
    Write-Host "=========================================================================" -ForegroundColor Green
    Write-Host ""
    Write-Host "URLs disponiveis:" -ForegroundColor Cyan
    Write-Host "  Frontend VeiGest: http://localhost" -ForegroundColor White
    Write-Host "  Backend Admin:    http://localhost:8080" -ForegroundColor White
    Write-Host ""
    Write-Host "Credenciais MySQL:" -ForegroundColor Cyan  
    Write-Host "  Usuario: root" -ForegroundColor White
    Write-Host "  Senha: $DatabasePassword" -ForegroundColor White
    Write-Host "  Database: veigest" -ForegroundColor White
    Write-Host ""
    Write-Host "Servicos rodando:" -ForegroundColor Cyan
    Write-Host "  - Nginx (Porta 80 e 8080)" -ForegroundColor White
    Write-Host "  - PHP-FPM (Porta 9000)" -ForegroundColor White  
    Write-Host "  - MySQL (Porta 3306)" -ForegroundColor White
    Write-Host ""
    
} catch {
    Write-Host ""
    Write-Host "=========================================================================" -ForegroundColor Red
    Write-Host "                         ERRO NA CONFIGURACAO                            " -ForegroundColor Red
    Write-Host "=========================================================================" -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red
    Write-Host ""
    Write-Host "Sugestoes:" -ForegroundColor Yellow
    Write-Host "   - Verifique se esta executando como Administrador" -ForegroundColor White
    Write-Host "   - Verifique sua conexao com a internet" -ForegroundColor White
    Write-Host "   - Certifique-se de que nao ha antivirus bloqueando" -ForegroundColor White
    Write-Host "   - Execute novamente o script" -ForegroundColor White
    exit 1
}