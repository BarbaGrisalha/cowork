<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;

class RbacController extends Controller
{
    public function actionInit()
    {
        $auth = Yii::$app->authManager;
        $auth->removeAll(); // CUIDADO: só em desenvolvimento!

        echo "Criando permissões...\n";

        // PERMISSÕES BÁSICAS
        $fazerReserva      = $auth->createPermission('fazerReserva');
        $fazerReserva->description = 'Fazer uma nova reserva';
        $auth->add($fazerReserva);

        $listarMinhasReservas = $auth->createPermission('listarMinhasReservas');
        $listarMinhasReservas->description = 'Listar as próprias reservas';
        $auth->add($listarMinhasReservas);

        $verReserva        = $auth->createPermission('verReserva');
        $verReserva->description = 'Ver detalhes de uma reserva (regra de dono)';
        $auth->add($verReserva);

        $atualizarReserva  = $auth->createPermission('atualizarReserva');
        $atualizarReserva->description = 'Atualizar própria reserva (regra de dono)';
        $auth->add($atualizarReserva);

        $cancelarReserva   = $auth->createPermission('cancelarReserva');
        $cancelarReserva->description = 'Cancelar própria reserva (regra de dono)';
        $auth->add($cancelarReserva);

        $deletarReserva    = $auth->createPermission('deletarReserva');
        $deletarReserva->description = 'Apagar reserva (só admin)';
        $auth->add($deletarReserva);

        $gerenciarTudo     = $auth->createPermission('gerenciarTudo');
        $gerenciarTudo->description = 'Acesso total ao sistema (admin)';
        $auth->add($gerenciarTudo);

        // ROLES
        $user = $auth->createRole('user');
        $auth->add($user);
        $auth->addChild($user, $fazerReserva);
        $auth->addChild($user, $listarMinhasReservas);
        $auth->addChild($user, $verReserva);
        $auth->addChild($user, $atualizarReserva);
        $auth->addChild($user, $cancelarReserva);

        $admin = $auth->createRole('admin');
        $auth->add($admin);
        $auth->addChild($admin, $gerenciarTudo);
        $auth->addChild($admin, $user); // admin herda tudo do user

        // REGRAS DE NEGÓCIO: "só o dono da reserva"
        $donoDaReserva = new \common\rbac\OwnerRule();
        $auth->add($donoDaReserva);

        // Aplica a regra nas permissões que dependem do dono
        $verReserva->ruleName = $donoDaReserva->name;
        $atualizarReserva->ruleName = $donoDaReserva->name;
        $cancelarReserva->ruleName = $donoDaReserva->name;
        $auth->update($verReserva->name, $verReserva);
        $auth->update($atualizarReserva->name, $atualizarReserva);
        $auth->update($cancelarReserva->name, $cancelarReserva);

        echo "RBAC criado com sucesso!\n";
        echo "→ Roles: admin, user\n";
        echo "→ Regra de dono aplicada em ver/atualizar/cancelar reserva\n";
    }
}
