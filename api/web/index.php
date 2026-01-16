<?php

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../vendor/yiisoft/yii2/Yii.php';

require __DIR__ . '/../../common/config/bootstrap.php';
require __DIR__ . '/../config/bootstrap.php';

// TEMPORÁRIO: PULA O common/config/main.php PARA TESTAR
$config = yii\helpers\ArrayHelper::merge(
    // require __DIR__ . '/../../common/config/main.php',  // ← COMENTE ESSA LINHA
    require __DIR__ . '/../../common/config/main-local.php',
    require __DIR__ . '/../config/main.php',
    require __DIR__ . '/../config/main-local.php'
);

// Força JSON response para API
$config['components']['response']['format'] = yii\web\Response::FORMAT_JSON;

(new yii\web\Application($config))->run();
