<?php
$this->title = 'VeiGest';
?>

<!-- HERO SECTION -->
<div class="container-fluid bg-light py-5 text-center">
    <div class="d-flex flex-column align-items-center">

        <img src="<?= Yii::getAlias('@web/images/veigest-logo.png') ?>"
             class="img-fluid mb-4"
             style="max-width:100px;">

        <h1 class="fw-bold">
            Bem-vindo ao <span class="text-success">VeiGest</span>
        </h1>

        <p class="text-muted fs-5">
            A plataforma inteligente para gestão eficiente de frotas.
        </p>
    </div>
</div>

<!-- FEATURE CARDS -->
<div class="container mt-5">
    <div class="row g-4">

        <!-- Veículos -->
        <div class="col-md-4">
            <div class="card text-center shadow-sm border-0">
                <div class="card-body">
                    <div class="fs-1 mb-2">🚗</div>
                    <h4 class="fw-bold">Gestão de Veículos</h4>
                    <p class="text-muted">
                        Registe, consulte e acompanhe a sua frota.
                    </p>
                    <a href="/veiculos/index" class="btn btn-success">Aceder</a>
                </div>
            </div>
        </div>

        <!-- Condutores -->
        <div class="col-md-4">
            <div class="card text-center shadow-sm border-0">
                <div class="card-body">
                    <div class="fs-1 mb-2">🧑‍✈️</div>
                    <h4 class="fw-bold">Gestão de Condutores</h4>
                    <p class="text-muted">
                        Adicione condutores e acompanhe atividades.
                    </p>
                    <a href="/condutores/index" class="btn btn-success">Aceder</a>
                </div>
            </div>
        </div>

        <!-- Manutenções -->
        <div class="col-md-4">
            <div class="card text-center shadow-sm border-0">
                <div class="card-body">
                    <div class="fs-1 mb-2">🛠️</div>
                    <h4 class="fw-bold">Manutenções</h4>
                    <p class="text-muted">
                        Controle manutenções preventivas e corretivas.
                    </p>
                    <a href="/manutencoes/index" class="btn btn-success">Aceder</a>
                </div>
            </div>
        </div>

    </div>
</div>
