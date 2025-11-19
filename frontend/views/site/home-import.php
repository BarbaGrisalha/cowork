<?php
/* @var $this yii\web\View */

$this->params['breadcrumbs'] = [];
// Certifique-se de que os ícones Font Awesome 5 estão carregados no seu layout principal!
use yii\web\View;
use yii\helpers\Url;
?>

<div class="cowork-landing-page">

    <header class="bg-primary text-white text-center py-5">
        <div class="container">
            <h1 class="display-4 fw-bold mb-3">Seu Espaço de Trabalho Ideal Começa Aqui.</h1>
            <p class="lead mb-4">Escritórios flexíveis, salas de reunião de última geração e comunidade vibrante.
                Encontre o Cowork IPLeiria mais perto de si.</p>

            <a href="#planos" class="btn btn-lg btn-light text-primary fw-bold px-4 me-2">Ver Planos e Preços</a>


        </div>
    </header>

    <section id="planos" class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="text-3xl md:text-4xl fw-bold text-dark mb-4">Escolha o Plano Ideal</h2>
                <p class="lead text-muted max-w-2xl mx-auto">Flexibilidade total para atender suas necessidades de
                    trabalho. Desde algumas horas até um escritório completo.</p>
            </div>

            <div class="row justify-content-center">

                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm transition-hover border-1">
                        <div class="card-body p-4 text-center">
                            <i class="fas fa-clock fa-2x text-primary mb-3"></i>
                            <h3 class="h4 fw-bold text-dark mb-2">Por Hora</h3>
                            <p class="text-muted mb-4">Ideal para reuniões rápidas e trabalho pontual</p>
                            <div class="mb-4">
                                <span class="display-5 fw-bold text-dark">7 €</span>
                                <span class="text-muted ml-2">por hora</span>
                            </div>

                            <ul class="list-unstyled text-start mb-4 mx-auto" style="max-width: 250px;">
                                <li class="d-flex align-items-center mb-2"><i class="fas fa-check-circle text-success me-2">

                                    </i>Acesso a mesa compartilhada</li>
                                <li class="d-flex align-items-center mb-2"><i class="fas fa-check-circle text-success me-2">

                                    </i>Wi-Fi de alta velocidade</li>
                                <li class="d-flex align-items-center mb-2"><i class="fas fa-check-circle text-success me-2">

                                    </i>Café e água inclusos</li>
                                <li class="d-flex align-items-center mb-2"><i class="fas fa-check-circle text-success me-2">

                                    </i>Sala de reunião (30min)</li>
                                <li class="d-flex align-items-center mb-2"><i class="fas fa-check-circle text-success me-2">

                                    </i>Suporte técnico</li>
                            </ul>

                            <a href="#" class="btn btn-outline-primary w-100 py-3 fw-bold">Escolher Por Hora</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-lg border-primary border-3 transition-hover">
                        <div class="position-relative">
                            <span class="badge bg-primary position-absolute top-0 start-50 translate-middle-x mt-n3 py-2 px-3 fw-bold shadow-sm">Mais Popular</span>
                        </div>
                        <div class="card-body p-4 text-center mt-3">
                            <i class="fas fa-calendar-alt fa-2x text-primary mb-3"></i>
                            <h3 class="h4 fw-bold text-dark mb-2">Diário</h3>
                            <p class="text-muted mb-4">Perfeito para um dia produtivo de trabalho</p>
                            <div class="mb-4">
                                <span class="display-5 fw-bold text-dark">32 €</span>
                                <span class="text-muted ml-2">por dia</span>
                            </div>

                            <ul class="list-unstyled text-start mb-4 mx-auto" style="max-width: 250px;">
                                <li class="d-flex align-items-center mb-2"><i class="fas fa-check-circle text-success me-2">

                                    </i>Acesso completo por 8 horas</li>
                                <li class="d-flex align-items-center mb-2"><i class="fas fa-check-circle text-success me-2">

                                    </i>Mesa fixa durante o dia</li>
                                <li class="d-flex align-items-center mb-2"><i class="fas fa-check-circle text-success me-2">

                                    </i>Wi-Fi premium</li>
                                <li class="d-flex align-items-center mb-2"><i class="fas fa-check-circle text-success me-2">

                                    </i>Café, água e lanches</li>
                                <li class="d-flex align-items-center mb-2"><i class="fas fa-check-circle text-success me-2">

                                    </i>Sala de reunião (2 horas)</li>
                                <li class="d-flex align-items-center mb-2"><i class="fas fa-check-circle text-success me-2">

                                    </i>Armário com chave</li>
                                <li class="d-flex align-items-center mb-2"><i class="fas fa-check-circle text-success me-2">

                                    </i>Suporte prioritário</li>
                            </ul>

                            <a href="#" class="btn btn-primary w-100 py-3 fw-bold shadow-lg">Escolher Diário</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm transition-hover border-1">
                        <div class="card-body p-4 text-center">
                            <i class="fas fa-credit-card fa-2x text-primary mb-3"></i>
                            <h3 class="h4 fw-bold text-dark mb-2">Mensal</h3>
                            <p class="text-muted mb-4">A melhor opção para profissionais dedicados</p>
                            <div class="mb-4">
                                <span class="display-5 fw-bold text-dark">225 €</span>
                                <span class="text-muted ml-2">por mês</span>
                            </div>

                            <ul class="list-unstyled text-start mb-4 mx-auto" style="max-width: 250px;">
                                <li class="d-flex align-items-center mb-2"><i class="fas fa-check-circle text-success me-2">

                                    </i>Acesso ilimitado 24/7</li>
                                <li class="d-flex align-items-center mb-2"><i class="fas fa-check-circle text-success me-2">

                                    </i>Mesa fixa personalizada</li>
                                <li class="d-flex align-items-center mb-2"><i class="fas fa-check-circle text-success me-2">

                                    </i>Wi-Fi dedicado</li>
                                <li class="d-flex align-items-center mb-2"><i class="fas fa-check-circle text-success me-2">

                                    </i>Todas as bebidas inclusas</li>
                                <li class="d-flex align-items-center mb-2"><i class="fas fa-check-circle text-success me-2">

                                    </i>Salas de reunião ilimitadas</li>
                                <li class="d-flex align-items-center mb-2"><i class="fas fa-check-circle text-success me-2">

                                    </i>Armário privativo</li>
                                <li class="d-flex align-items-center mb-2"><i class="fas fa-check-circle text-success me-2">

                                    </i>Endereço comercial</li>
                                <li class="d-flex align-items-center mb-2"><i class="fas fa-check-circle text-success me-2">

                                    </i>Suporte VIP</li>
                                <li class="d-flex align-items-center mb-2"><i class="fas fa-check-circle text-success me-2">

                                    </i>Desconto em eventos</li>
                            </ul>

                            <a href="#" class="btn btn-outline-primary w-100 py-3 fw-bold">Escolher Mensal</a>
                        </div>
                    </div>
                </div>

            </div>

            <div class="text-center mt-5">
                <p class="text-muted mb-3">Precisa de algo personalizado?</p>
                <a href="#" class="text-primary hover:text-blue-700 fw-bold text-decoration-underline">Entre em contato para planos empresariais</a>
            </div>

        </div>
    </section>

    <section class="bg-light py-5">
        <div class="container text-center">
            <h2 class="fw-bold mb-3">Pronto para Mudar a Sua Forma de Trabalhar?</h2>
            <p class="lead mb-4 text-muted">Aproveite nossa oferta de teste grátis por um dia.</p>
            <a href="#" class="btn btn-success btn-lg fw-bold">Venha fazer nos uma visita e conhecer nosso espaço</a>
        </div>
    </section>

</div>
</div>
<footer class="bg-white py-5 border-top">
    <div class="container">



        <div class="row">

            <div class="col-lg-12 mb-5">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-primary p-2 rounded-2 me-2">
                        <i class="fas fa-building fa-lg text-white"></i>
                    </div>
                    <span class="fs-4 fw-bold text-dark">Cowork IPLeiria</span>
                </div>
                <p class="text-secondary leading-relaxed mb-3">
                    Transformando a gestão de espaços colaborativos com tecnologia avançada e design intuitivo.
                </p>
                <div class="d-flex">
                    <a href="#" class="text-secondary me-3"><i class="fab fa-facebook-f fa-lg"></i></a>
                    <a href="#" class="text-secondary me-3"><i class="fab fa-twitter fa-lg"></i></a>
                    <a href="#" class="text-secondary me-3"><i class="fab fa-instagram fa-lg"></i></a>
                    <a href="#" class="text-secondary"><i class="fab fa-linkedin-in fa-lg"></i></a>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <h5 class="text-uppercase fw-bold mb-3 text-dark">Links Rápidos</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="#home" class="text-secondary text-decoration-none small hover-primary">Início</a></li>
                    <li class="mb-2"><a href="#about" class="text-secondary text-decoration-none small hover-primary">Sobre Nós</a></li>
                    <li class="mb-2"><a href="#plans" class="text-secondary text-decoration-none small hover-primary">Planos e Preços</a></li>
                    <li class="mb-2"><a href="#contact" class="text-secondary text-decoration-none small hover-primary">Contato</a></li>
                    <li class="mb-2"><a href="#support" class="text-secondary text-decoration-none small hover-primary">Suporte</a></li>
                </ul>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <h5 class="text-uppercase fw-bold mb-3 text-dark">Serviços</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="#" class="text-secondary text-decoration-none small hover-primary">Gestão de Reservas</a></li>
                    <li class="mb-2"><a href="#" class="text-secondary text-decoration-none small hover-primary">Controle de Acesso</a></li>
                    <li class="mb-2"><a href="#" class="text-secondary text-decoration-none small hover-primary">Relatórios Avançados</a></li>
                    <li class="mb-2"><a href="#" class="text-secondary text-decoration-none small hover-primary">Pagamentos</a></li>
                    <li class="mb-2"><a href="#" class="text-secondary text-decoration-none small hover-primary">API</a></li>
                </ul>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <h5 class="text-uppercase fw-bold mb-3 text-dark">Contato</h5>
                <div class="small text-secondary">
                    <p class="mb-2"><i class="fas fa-map-marker-alt text-primary me-2"></i> Rua das Startups, 123 <br class="ms-4"> Leiria, Portugal - 2400-000</p>
                    <p class="mb-2"><i class="fas fa-phone-alt text-primary me-2"></i> +351 999 888 777</p>
                    <p class="mb-3"><i class="fas fa-envelope text-primary me-2"></i> contato@coworkipleiria.pt</p>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <h5 class="text-uppercase fw-bold mb-3 text-dark">Newsletter</h5>
                <div class="input-group input-group-sm mb-4">
                    <input type="email" placeholder="Seu e-mail" class="form-control bg-light border-1" aria-label="Newsletter email">
                    <button class="btn btn-primary" type="button"><i class="fas fa-paper-plane"></i></button>
                </div>

                <h6 class="small fw-bold mb-2 mt-4 text-dark">Links Legais</h6>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="<?= Url::to(['/site/privacity-page']) ?>" class="text-secondary text-decoration-none small hover-primary">Política de Privacidade</a></li>
                    <li class="mb-2"><a href="<?= Url::to(['/site/terms-of-service']) ?>" class="text-secondary text-decoration-none small hover-primary">Termos de Serviço</a></li>
                    <li class="mb-2"><a href="<?= Url::to(['/site/cookies-policy']) ?>" class="text-secondary text-decoration-none small hover-primary">Política de Cookies</a></li>
                </ul>
            </div>

        </div>
        <hr class="text-secondary mt-4 mb-3">

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
            <p class="text-muted small mb-2 mb-md-0">© 2025 Cowork IPLeiria. Todos os direitos reservados. </p>
            <div class="d-flex flex-wrap">
                <a href="<?= Url::to(['/site/privacity-page']) ?>" class="text-muted text-decoration-none small me-3 hover-primary">Política de Privacidade</a>
                <a href="<?= Url::to(['/site/terms-of-service']) ?>" class="text-muted text-decoration-none small me-3 hover-primary">Termos de Uso</a>
                <a href="<?= Url::to(['/site/cookies-policy']) ?>" class="text-muted text-decoration-none small hover-primary">Cookies</a>
            </div>
        </div>

    </div>
</footer>

<style>
    /* Estilo customizado (opcional, mas recomendado) para os links de hover, como Tailwind faria */
    .hover-primary:hover {
        color: var(--bs-primary) !important;
        /* Usa a cor primária do Bootstrap no hover */
    }
</style>