<?php

namespace common\fixtures;

use yii\test\ActiveFixture;

/**
 * Fixture para modelo Maintenance
 */
class MaintenanceFixture extends ActiveFixture
{
    public $modelClass = 'frontend\models\Maintenance';
    public $tableName = '{{%maintenances}}';
    
    /**
     * Dependências - Vehicle deve ser carregada antes
     */
    public $depends = [
        'common\fixtures\VehicleFixture',
    ];
}
