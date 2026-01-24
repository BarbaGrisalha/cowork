<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="<?= Yii::$app->homeUrl ?>" class="brand-link text-center py-3">
        <span class="brand-text font-weight-light fw-bold text-white">Cowork IPLeiria - Admin</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center">
            <div class="image">
                <img src="<?= $assetDir ?>/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <a href="#" class="d-block text-white">
                    <?= Yii::$app->user->isGuest ? 'Convidado' : ucfirst(Yii::$app->user->identity->username ?? 'Admin') ?>
                </a>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <?= \hail812\adminlte\widgets\Menu::widget([
                'options' => ['class' => 'nav nav-pills nav-sidebar flex-column', 'data-widget' => 'treeview', 'role' => 'menu', 'data-accordion' => 'false'],
                'items' => [
                    ['label' => 'Dashboard', 'icon' => 'tachometer-alt', 'url' => ['/admin/index']],

                    ['label' => 'Utilizadores', 'icon' => 'users', 'items' => [
                        ['label' => 'Utilizadores', 'icon' => 'circle', 'url' => ['/user/index']],

                    ]],

                    ['label' => 'Espaços', 'icon' => 'building', 'items' => [
                        ['label' => 'Salas/Mesas', 'icon' => 'circle', 'url' => ['/rooms/index']],
                    ]],



                    ['label' => 'Relatórios', 'icon' => 'chart-bar', 'items' => [
                        ['label' => 'Faturamento Mensal', 'icon' => 'circle', 'url' => ['/relatorio/clientes-mes-atual']],
                        ['label' => 'Reservas Futuras', 'icon' => 'circle', 'url' => ['/relatorio/clientes-futuros']],
                        ['label' => 'Reservas por Sala', 'icon' => 'circle', 'url' => ['/relatorio/reservas-salas']],
                        ['label' => 'Salas Mais Alugadas', 'icon' => 'circle', 'url' => ['/relatorio/salas-ranking']],
                        ['label' => 'Reservas Pendentes', 'icon' => 'circle', 'url' => ['/relatorio/reservas-pendentes']],
                    ]],

                    ['label' => 'Ferramentas', 'icon' => 'tools', 'items' => [
                        ['label' => 'Gii', 'icon' => 'circle', 'url' => ['/gii'], 'visible' => !Yii::$app->user->isGuest],
                        ['label' => 'Debug', 'icon' => 'circle', 'url' => ['/debug'], 'visible' => YII_DEBUG],
                    ]],

                    ['label' => 'Logout', 'icon' => 'sign-out-alt', 'url' => ['/site/logout'], 'visible' => !Yii::$app->user->isGuest, 'linkOptions' => ['data-method' => 'post']],
                ],
            ]) ?>
        </nav>
    </div>
</aside>