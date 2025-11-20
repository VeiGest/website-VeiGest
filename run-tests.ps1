param(
    [string]$TestSuite = "all"
)

Write-Host "VeiGest API - Execucao de Testes TDD" -ForegroundColor Cyan
Write-Host "====================================" -ForegroundColor Cyan

$ProjectRoot = "C:\wamp64\www\website-VeiGest\veigest"
Set-Location $ProjectRoot
Write-Host "Diretorio: $ProjectRoot" -ForegroundColor Green

# Verificar API
Write-Host "Verificando API..." -ForegroundColor Yellow
try {
    $Response = Invoke-WebRequest -Uri "http://localhost:8080/api/v1/auth/info" -TimeoutSec 5
    if ($Response.StatusCode -eq 200) {
        Write-Host "API respondendo" -ForegroundColor Green
    }
} catch {
    Write-Host "API pode nao estar respondendo" -ForegroundColor Yellow
}

# Navegar para backend
Set-Location "backend"
$Command = "php ../vendor/bin/codecept run api"

Write-Host "Executando: $Command" -ForegroundColor Blue
Write-Host ""

$ExitCode = 0
try {
    Invoke-Expression $Command
    $ExitCode = $LASTEXITCODE
} catch {
    Write-Host "Erro na execucao" -ForegroundColor Red
    $ExitCode = 1
}

Write-Host ""
if ($ExitCode -eq 0) {
    Write-Host "Testes concluidos com sucesso!" -ForegroundColor Green
} else {
    Write-Host "Alguns testes falharam" -ForegroundColor Red
}

Write-Host "Logs em: tests/_output/" -ForegroundColor Blue