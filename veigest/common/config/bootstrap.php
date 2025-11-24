<?php
Yii::setAlias('@common', dirname(__DIR__));
Yii::setAlias('@frontend', dirname(dirname(__DIR__)) . '/frontend');
Yii::setAlias('@backend', dirname(dirname(__DIR__)) . '/backend');
Yii::setAlias('@console', dirname(dirname(__DIR__)) . '/console');
Yii::setAlias('@backendUrl', 'http://homelab.op:8002/backend/web');
Yii::setAlias('@frontendUrl', 'http://homelab.op:8002/frontend/web');

