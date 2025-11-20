<?php

use \yii\db\Migration;

class m190124_110200_add_verification_token_column_to_users_table extends Migration
{
    public function up()
    {
        // Verificar se a coluna já existe antes de adicionar
        $schema = $this->db->schema->getTableSchema('{{%users}}');
        if ($schema !== null && !isset($schema->columns['verification_token'])) {
            $this->addColumn('{{%users}}', 'verification_token', $this->string()->defaultValue(null));
        }
    }

    public function down()
    {
        $this->dropColumn('{{%users}}', 'verification_token');
    }
}
