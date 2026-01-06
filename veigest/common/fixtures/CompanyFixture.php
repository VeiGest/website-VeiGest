<?php

namespace common\fixtures;

use yii\test\ActiveFixture;

/**
 * Fixture para modelo Company
 */
class CompanyFixture extends ActiveFixture
{
    public $modelClass = 'common\models\Company';
    public $tableName = '{{%companies}}';
}
