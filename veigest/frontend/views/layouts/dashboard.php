<?php

/** @var \yii\web\View $this */
/** @var string $content */

use common\widgets\Alert;
use frontend\assets\DashboardAsset;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;

DashboardAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?> - VeiGest</title>
    <?php $this->head() ?>

    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.0/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>

    <style>
        :root {
            --primary-color: #09BC8A;
            --dark-color: #3C3C3C;
            --light-turquoise: #75DDDD;
            --lavender-gray: #C8BFC7;
            --lavender-blush: #FFEAEE;
        }

        * {
            font-family: 'Poppins', sans-serif;
        }

        .navbar-light .navbar-brand {
            color: white !important;
            font-weight: 700;
            font-size: 1.5rem;
        }

        .main-sidebar .nav-link.active {
            background-color: var(--primary-color) !important;
            color: white !important;
        }

        .nav-link {
            color: #666 !important;
        }

        .nav-link:hover {
            background-color: rgba(9, 188, 138, 0.1) !important;
            color: var(--primary-color) !important;
        }

        .brand-link {
            background-color: var(--primary-color) !important;
        }

        .btn-primary {
            background-color: var(--primary-color) !important;
            border-color: var(--primary-color) !important;
        }

        .btn-primary:hover {
            background-color: #088570 !important;
        }

        .badge-success {
            background-color: var(--primary-color) !important;
        }

        .card {
            border-top: 3px solid var(--primary-color);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .small-box {
            border-radius: 8px;
        }

        .small-box.bg-teal {
            background-color: var(--primary-color) !important;
        }

        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }

        /* Ocultar texto do logo quando sidebar estiver colapsada */
        .sidebar-collapse .brand-link span {
            display: none;
        }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<?php $this->beginBody() ?>
<div class="wrapper">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-dark" style="background-color: var(--dark-color);">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                    <i class="fas fa-bars"></i>
                </a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="<?= Yii::$app->urlManager->createUrl(['dashboard/index']) ?>" class="nav-link">Home</a>
            </li>
        </ul>

        <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="fas fa-bell"></i>
                    <span class="badge badge-danger navbar-badge">3</span>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <span class="dropdown-header">3 Notificações</span>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item">
                        <i class="fas fa-warning text-warning mr-2"></i> Manutenção programada
                    </a>
                </div>
            </li>

            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="fas fa-user-circle"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <a href="<?= Yii::$app->urlManager->createUrl(['dashboard/logout']) ?>" class="dropdown-item">
                        <i class="fas fa-sign-out-alt mr-2"></i> Sair
                    </a>
                </div>
            </li>
        </ul>
    </nav>

    <!-- Main Sidebar -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4" style="background-color: #f8f9fa;">
        <a href="<?= Yii::$app->urlManager->createUrl(['dashboard/index']) ?>" class="brand-link d-flex align-items-center">
            <img src="/images/veigest-logo.png" alt="VeiGest" style="width: 35px; height: 35px; margin-right: 10px;">
            <span style="color: white; font-weight: 700;">VeiGest</span>
        </a>

        <div class="sidebar">
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="info">
                    <a href="#" class="d-block" style="color: var(--dark-color); font-weight: 600;"><?= Yii::$app->user->identity->nome ?? 'Usuário' ?></a>
                    <span style="display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; background-color: #dc3545; color: white;">Admin</span>
                </div>
            </div>

            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                    <li class="nav-item">
                        <a href="<?= Yii::$app->urlManager->createUrl(['dashboard/index']) ?>" class="nav-link active">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>

                    <li class="nav-item menu-open">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-car"></i>
                            <p>Frota <i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="<?= Yii::$app->urlManager->createUrl(['dashboard/vehicles']) ?>" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Veículos</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= Yii::$app->urlManager->createUrl(['dashboard/drivers']) ?>" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Condutores</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item">
                        <a href="<?= Yii::$app->urlManager->createUrl(['dashboard/maintenance']) ?>" class="nav-link">
                            <i class="nav-icon fas fa-tools"></i>
                            <p>Manutenção</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="<?= Yii::$app->urlManager->createUrl(['dashboard/documents']) ?>" class="nav-link">
                            <i class="nav-icon fas fa-file-alt"></i>
                            <p>Documentos</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="<?= Yii::$app->urlManager->createUrl(['dashboard/alerts']) ?>" class="nav-link">
                            <i class="nav-icon fas fa-bell"></i>
                            <p>Alertas</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="<?= Yii::$app->urlManager->createUrl(['dashboard/reports']) ?>" class="nav-link">
                            <i class="nav-icon fas fa-chart-bar"></i>
                            <p>Relatórios</p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <!-- Content Wrapper -->
    
    <!-- 
     Aqui deve ser inserido o conteúdo específico de cada página
    -->
    <div class="content-wrapper">
        <section class="content pt-3">
            <div class="container-fluid">
                <?= $content ?>
            </div>
        </section>
    </div>

    <footer class="main-footer">
        <strong>VeiGest &copy; 2025</strong>
        <div class="float-right d-none d-sm-inline-block">
            <b>Version</b> 1.0.0
        </div>
    </footer>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>