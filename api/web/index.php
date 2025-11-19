<?php
// Define o ambiente, se não estiver definido
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev'); // Altere para 'prod' em produção!

// Carrega o autoloader do Composer e o Yii
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../vendor/yiisoft/yii2/Yii.php';

// Carrega os arquivos de bootstrap de common e api
require __DIR__ . '/../../common/config/bootstrap.php';
require __DIR__ . '/../config/bootstrap.php';

// Carrega as configurações (common e api, global e local)
// Ele buscará o /api/config/main.php que criamos na etapa anterior!
$config = \yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/../../common/config/main.php',
    require __DIR__ . '/../../common/config/main-local.php',
    require __DIR__ . '/../config/main.php',
    require __DIR__ . '/../config/main-local.php'
);

// Cria e executa a aplicação Web (sua API)
(new yii\web\Application($config))->run();
