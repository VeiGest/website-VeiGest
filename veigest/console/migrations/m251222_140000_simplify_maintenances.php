<?php

use yii\db\Migration;

/**
 * Simplifica tabela maintenances:
 * - Remove proxima_data
 * - data agora é a ÚNICA data (quando a manutenção deve/foi feita)
 * - status define se é scheduled/completed/overdue
 */
class m251222_140000_simplify_maintenances extends Migration
{
    public function safeUp()
    {
        // Atualizar registos existentes ANTES de remover a coluna
        // Manutenções agendadas: copiar proxima_data para data
        $this->execute("
            UPDATE maintenances 
            SET data = proxima_data 
            WHERE status = 'scheduled' AND proxima_data IS NOT NULL
        ");

        // Remover coluna proxima_data
        $this->dropColumn('maintenances', 'proxima_data');

        // Garantir que data é NOT NULL
        $this->alterColumn('maintenances', 'data', $this->date()->notNull());

        echo "✓ Tabela maintenances simplificada\n";
        echo "✓ Agora cada manutenção tem apenas UMA data\n";
    }

    public function safeDown()
    {
        // Adicionar coluna de volta
        $this->addColumn('maintenances', 'proxima_data', $this->date()->null());

        // Permitir data NULL novamente
        $this->alterColumn('maintenances', 'data', $this->date()->null());

        // Restaurar lógica antiga
        $this->execute("
            UPDATE maintenances 
            SET proxima_data = data, data = NULL 
            WHERE status = 'scheduled'
        ");

        echo "✓ Revertido para estrutura antiga\n";
    }
}
