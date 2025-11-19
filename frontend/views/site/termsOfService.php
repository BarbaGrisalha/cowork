<?php

/** @var yii\web\View $this */

use yii\bootstrap5\Html;
use yii\helpers\Url; // Incluído para futura necessidade

// Configurações da View
$nomeEmpresa = "Cowork IPLeiria";
$dataAtualizacao = "01 de Novembro de 2025";
$emailEmpresa = "contato@coworkipleiria.pt";
$linkPrivacidade = Url::to(['/site/privacity-page']); // Link para a Política de Privacidade

$this->title = 'Termos de Serviço';
//$this->params['breadcrumbs'][] = $this->title;

// Classes Bootstrap são usadas para formatação (mt-4 para margem top, etc.)
?>

<div class="site-terms-of-service">
    <h1><?= Html::encode($this->title) ?></h1>

    <p class="lead">Bem-vindo(a) aos Termos de Serviço do **<?= Html::encode($nomeEmpresa) ?>**. Este documento descreve as regras e regulamentos para o uso do nosso website/serviço.</p>

    <hr>

    <h2 class="mt-4">1. Aceitação dos Termos</h2>
    <p>Ao acessar e usar nosso serviço, você aceita e concorda em estar vinculado pelos termos e condições desta política. Se você não concordar com qualquer parte dos termos, não deve usar o serviço.</p>

    <h2 class="mt-4">2. Uso Permitido e Proibições</h2>
    <p>O serviço é destinado apenas para seu uso pessoal e não comercial. Você concorda em não:</p>
    <ul>
        <li>Violar leis locais, nacionais ou internacionais.</li>
        <li>Publicar material ofensivo, difamatório ou ilegal.</li>
        <li>Distribuir vírus ou qualquer outro código malicioso.</li>
        <li>Realizar engenharia reversa de qualquer parte do serviço.</li>
    </ul>

    <h2 class="mt-4">3. Propriedade Intelectual</h2>
    <p>Todo o conteúdo original, recursos e funcionalidade do serviço são e permanecerão como propriedade exclusiva do **<?= Html::encode($nomeEmpresa) ?>** e seus licenciadores.</p>
    <p>Você não pode copiar, modificar, distribuir, vender ou alugar nenhuma parte dos nossos serviços ou software incluído.</p>

    <h2 class="mt-4">4. Limitação de Responsabilidade</h2>
    <p>Em nenhuma circunstância o **<?= Html::encode($nomeEmpresa) ?>**, nem seus diretores, funcionários, parceiros, agentes, fornecedores ou afiliados, serão responsáveis por quaisquer danos indiretos, incidentais, especiais, consequenciais ou punitivos, incluindo, sem limitação, perda de lucros, dados, uso, boa vontade ou outras perdas intangíveis.</p>

    <h2 class="mt-4">5. Links para Outros Sites</h2>
    <p>Nosso Serviço pode conter links para sites ou serviços de terceiros que não são de propriedade ou controlados pelo **<?= Html::encode($nomeEmpresa) ?>**. Não temos controle e não assumimos responsabilidade pelo conteúdo, políticas de privacidade ou práticas de quaisquer sites ou serviços de terceiros.</p>

    <h2 class="mt-4">6. Privacidade e Contato</h2>
    <p>Sua utilização do serviço também é regida pela nossa <a href="<?= $linkPrivacidade ?>">Política de Privacidade</a>. Qualquer dúvida sobre estes Termos deve ser enviada para: **<?= Html::a(Html::encode($emailEmpresa), 'mailto:' . Html::encode($emailEmpresa)) ?>**.</p>


    <p class="text-muted mt-5">Estes termos são efetivos a partir de **<?= Html::encode($dataAtualizacao) ?>**.</p>
</div>