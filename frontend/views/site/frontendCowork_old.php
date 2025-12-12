<?php

/**
 * @var yii\web\View $this
 * * @var common\models\User $identity
 */


use yii\helpers\Html; // Importe o Html helper, ele é seu amigo na segurança!

// Acessa o componente user do Yii2
$userComponent = Yii::$app->user;

// Verifica se está logado. isGuest é o correto (true se for visitante, false se logado)
$isGuest = $userComponent->isGuest;

// Se estiver logado, obtém o objeto de identidade.
// Usamos o operador NULL COALESCE (??) para evitar erros se o identity for nulo (embora não deva ser, se a action estiver protegida).
$identity = $userComponent->identity ?? null;

// Obtém o nome (username) se o usuário estiver logado, senão usa 'Visitante'
$nome_usuario = !$isGuest && $identity ? Html::encode($identity->username) : 'Visitante';
$nome_display = !$isGuest && $identity ? Html::encode($identity->username) : 'Amigo';

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bem-vindo(a)!</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins. ', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .welcome-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 500px;
            width: 90%;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .welcome-header {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            padding: 2.5rem 1.5rem;
            text-align: center;
        }

        .welcome-header h1 {
            font-weight: 700;
            margin: 0;
            font-size: 2.2rem;
        }

        .welcome-header p {
            opacity: 0.9;
            font-size: 1.1rem;
            margin-top: 0.5rem;
        }

        .welcome-body {
            padding: 2rem;
            text-align: center;
        }

        .btn-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 50px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .icon {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            color: #667eea;
        }
    </style>
</head>

<body>

    <div class="welcome-card">
        <div class="welcome-header">
            <h1>🎉 Bem-vindo, <?= $nome_display ?>!</h1>
            <p>Estamos felizes em tê-lo(a) por aqui.</p>
        </div>

        <div class="welcome-body">
            <?php if (!$isGuest): ?>
                <div class="icon">👋</div>
                <h4>Olá novamente, <?= $nome_usuario ?>!</h4>
                <p>Explore tudo o que preparamos para você.</p>
            <?php else: ?>
                <div class="icon">🌟</div>
                <h4>Primeira vez aqui?</h4>
                <p>Crie uma conta ou faça login para começar.</p>
            <?php endif; ?>

            <div class="mt-4">
                <?php if (!$isGuest): ?>
                    <?= Html::a('Ir para o Dashboard', ['/site/frontendOffice'], ['class' => 'btn btn-custom text-white']) ?>

                    <?= Html::a('Sair (Logout)', ['/site/logout'], [
                        'class' => 'btn btn-outline-danger mt-2',
                        'data' => [
                            'method' => 'post', // Logout deve ser sempre via POST para segurança!
                        ],
                    ]) ?>
                <?php else: ?>
                    <?= Html::a('Fazer Login', ['/site/login'], ['class' => 'btn btn-custom text-white me-2']) ?>
                    <?= Html::a('Criar Conta', ['/site/signup'], ['class' => 'btn btn-outline-primary']) ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

</body>

</html>

</html>