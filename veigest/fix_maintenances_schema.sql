-- Corrigir schema da tabela maintenances
-- Permitir data = NULL para manutenções agendadas (ainda não realizadas)

USE veigest;

-- Alterar coluna data para permitir NULL
ALTER TABLE maintenances 
MODIFY COLUMN data DATE NULL;

-- Comentário explicativo
-- Regras:
-- AGENDADA: data = NULL, proxima_data = data_agendada
-- CONCLUÍDA: data = data_conclusao, proxima_data = NULL
