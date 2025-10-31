#!/bin/bash
# filepath: /home/pedro-jesus/Git/website-VeiGest/commit.sh

# Script para facilitar a execução do commiter automatizado
# Uso: ./commit.sh

set -e  # Sair em caso de erro

# Diretório do script
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
AUTOMATIONS_DIR="$SCRIPT_DIR/automations"
COMMITER_SCRIPT="$AUTOMATIONS_DIR/commiter.js"

# Verificar se o arquivo commiter.js existe
if [ ! -f "$COMMITER_SCRIPT" ]; then
    echo "❌ Erro: Script commiter.js não encontrado em $COMMITER_SCRIPT"
    exit 1
fi

# Verificar se o arquivo commit.md existe
if [ ! -f "$AUTOMATIONS_DIR/commit.md" ]; then
    echo "❌ Erro: Arquivo commit.md não encontrado em $AUTOMATIONS_DIR/"
    echo "💡 Crie o arquivo commit.md com o título e descrição do commit antes de executar."
    exit 1
fi

# Verificar se Node.js está disponível
if ! command -v node &> /dev/null; then
    echo "❌ Erro: Node.js não está instalado ou não está no PATH"
    exit 1
fi

echo "🚀 Executando commit automatizado..."
echo "📁 Diretório: $SCRIPT_DIR"
echo "📝 Script: $COMMITER_SCRIPT"
echo ""

# Executar o script de commit
cd "$SCRIPT_DIR"
node "$COMMITER_SCRIPT"

echo ""
echo "✅ Script executado com sucesso!"