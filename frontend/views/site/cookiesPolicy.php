<?php

/** @var yii\web\View $this */

use yii\bootstrap5\Html;
use yii\helpers\Url; // Incluído para gerar links internos

// Configurações da View
$nomeEmpresa = "Cowork IPLeiria";
$dataAtualizacao = "01 de Novembro de 2025";
$emailEmpresa = "contato@coworkipleiria.pt";
$linkPrivacidade = Url::to(['/site/privacity-page']);
$linkTermos = Url::to(['/site/terms-of-service']);

$this->title = 'Política de Cookies';
//$this->params['breadcrumbs'][] = $this->title;

// Classes Bootstrap são usadas para formatação (mt-4 para margem top, etc.)
?>

<div class="site-cookies-policy">
    <h1><?= Html::encode($this->title) ?></h1>

    <p class="lead">Esta política explica o que são cookies e como os utilizamos no **<?= Html::encode($nomeEmpresa) ?>**. Ao continuar a usar nosso website, você concorda com o uso de cookies de acordo com esta política.</p>

    <hr>

    <h2 class="mt-4">1. O que são Cookies?</h2>
    <p>Cookies são pequenos arquivos de texto que são armazenados no seu computador ou dispositivo móvel quando você visita um site. Eles são amplamente utilizados para fazer com que os sites funcionem, ou funcionem de forma mais eficiente, além de fornecer informações aos proprietários do site.</p>

    <h2 class="mt-4">2. Como Utilizamos os Cookies</h2>
    <p>Usamos cookies por diversos motivos, detalhados abaixo. Infelizmente, na maioria dos casos, não há opções padrão da indústria para desativar cookies sem desativar completamente a funcionalidade e os recursos que eles adicionam a este site. É recomendado que você deixe todos os cookies ativos se não tiver certeza se precisa deles ou não, caso sejam usados para fornecer um serviço que você utiliza.</p>

    <h2 class="mt-4">3. Tipos de Cookies Utilizados</h2>
    <p>Utilizamos os seguintes tipos de cookies:</p>
    <ul>
        <li>
            <strong>Cookies Necessários/Essenciais:</strong> São essenciais para permitir que você use o site e acesse recursos como áreas seguras.
        </li>
        <li>
            <strong>Cookies de Desempenho/Análise:</strong> Coletam informações sobre como os visitantes usam o site (ex: quais páginas visitam mais), ajudando-nos a melhorar o desempenho e a experiência do usuário.
        </li>
        <li>
            <strong>Cookies de Funcionalidade:</strong> Permitem que o site se lembre de escolhas feitas pelo usuário (como nome de usuário, idioma ou região).
        </li>
    </ul>

    <h2 class="mt-4">4. Gerenciamento de Cookies</h2>
    <p>Você pode impedir a configuração de cookies ajustando as configurações do seu navegador (consulte a Ajuda do seu navegador para saber como fazer isso).</p>
    <p>Tenha em atenção que a desativação de cookies afetará a funcionalidade deste e de muitos outros sites que você visita. A desativação de cookies geralmente resultará na desativação de certas funcionalidades e recursos deste site.</p>

    <h2 class="mt-4">5. Contato e Mais Informações</h2>
    <p>Para mais detalhes sobre como lidamos com seus dados pessoais, consulte nossa <a href="<?= $linkPrivacidade ?>">Política de Privacidade</a>. Se tiver dúvidas sobre esta Política de Cookies, entre em contato conosco por e-mail:
        **<?= Html::a(Html::encode($emailEmpresa), 'mailto:' . Html::encode($emailEmpresa)) ?>**.
    </p>


    <p class="text-muted mt-5">Esta política é efetiva a partir de **<?= Html::encode($dataAtualizacao) ?>**.</p>
</div>