<?php

use yii\db\Migration;

/**
 * Adds explicit status column to maintenances.
 */
class m251222_120000_add_status_to_maintenances extends Migration
{
    public function safeUp()
    {
        // Add column with default 'scheduled'
        $this->addColumn('{{%maintenances}}', 'status', $this->string(20)->notNull()->defaultValue('scheduled'));
        $this->createIndex('idx_maintenances_status', '{{%maintenances}}', 'status');

        // Backfill based on existing data
        // Completed: data IS NOT NULL and proxima_data IS NULL
        $this->execute("UPDATE {{%maintenances}} SET status = 'completed' WHERE data IS NOT NULL AND proxima_data IS NULL");
        // Scheduled: data IS NULL and proxima_data IS NOT NULL
        $this->execute("UPDATE {{%maintenances}} SET status = 'scheduled' WHERE data IS NULL AND proxima_data IS NOT NULL");
        // Overdue: scheduled with proxima_data < today
        $this->execute("UPDATE {{%maintenances}} SET status = 'overdue' WHERE data IS NULL AND proxima_data IS NOT NULL AND proxima_data < CURDATE()");
    }

    public function safeDown()
    {
        $this->dropIndex('idx_maintenances_status', '{{%maintenances}}');
        $this->dropColumn('{{%maintenances}}', 'status');
    }
}
