<?php

/** @var yii\web\View $this */

use yii\bootstrap5\Html;

// Configurações da View
$nomeEmpresa = "Cowork IPLeiria";
$emailEmpresa = "contacto@coworkipleiria.pt";
$dataAtualizacao = "01 de Novembro de 2025";

$this->title = 'Política de Privacidade';
//$this->params['breadcrumbs'][] = $this->title;

// Você pode adicionar seu CSS customizado ou usar classes Bootstrap aqui
// Ex: para dar um espaço entre as seções
?>

<div class="site-privacity">
    <h1><?= Html::encode($this->title) ?></h1>

    <p class="lead">A sua privacidade é importante para nós. É política do **<?= Html::encode($nomeEmpresa) ?>** respeitar a sua privacidade em relação a qualquer informação sua que possamos coletar no site.</p>

    <hr>
    <h2 class="mt-4">1. Coleta de Informações Pessoais</h2>
    <p>Solicitamos informações pessoais apenas quando realmente precisamos delas para lhe fornecer um serviço. Fazemo-lo por meios justos e legais, com o seu conhecimento e consentimento.</p>

    <p>As informações que coletamos podem incluir:</p>
    <ul>
        <li>Nome e sobrenome;</li>
        <li>Endereço de e-mail;</li>
        <li>Informações de uso do site (por meio de cookies e tecnologias de rastreamento).</li>
    </ul>

    <h2 class="mt-4">2. Uso das Informações</h2>
    <p>Utilizamos as informações coletadas para os seguintes propósitos:</p>
    <ul>
        <li>Fornecer, operar e manter nosso site e serviços;</li>
        <li>Melhorar, personalizar e expandir nosso site;</li>
        <li>Entender e analisar como você usa nosso site;</li>
        <li>Comunicar-nos com você, incluindo para atendimento ao cliente, atualizações e informações de marketing.</li>
    </ul>

    <h2 class="mt-4">3. Cookies</h2>
    <p>Utilizamos "cookies" para ajudar a personalizar a sua experiência online. Você tem a possibilidade de aceitar ou recusar cookies. Se você optar por recusar, isso pode limitar o uso de alguns recursos em nosso site.</p>

    <h2 class="mt-4">4. Segurança dos Dados</h2>
    <p>Empregamos medidas de segurança razoáveis para proteger as informações pessoais que você nos fornece contra acesso não autorizado, divulgação, alteração ou destruição.</p>

    <h2 class="mt-4">5. Contato</h2>
    <p>Se você tiver alguma dúvida sobre esta Política de Privacidade, entre em contato conosco através do e-mail:
        **<?= Html::a(
                Html::encode($emailEmpresa),         // O TEXTO visível do link
                'mailto:' . Html::encode($emailEmpresa) // O DESTINO do link (href)
            ) ?>**.
</div>