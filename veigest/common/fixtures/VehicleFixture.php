<?php

namespace common\fixtures;

use yii\test\ActiveFixture;

/**
 * Fixture para modelo Vehicle
 */
class VehicleFixture extends ActiveFixture
{
    public $modelClass = 'frontend\models\Vehicle';
    public $tableName = 'vehicles';
    
    /**
     * Dependências - Company deve ser carregada antes
     */
    public $depends = [
        'common\fixtures\CompanyFixture',
    ];
}
