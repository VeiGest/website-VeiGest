<?php

/** @var yii\web\View $this */

$this->title = 'My Yii Application';
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Condutores - VeiGest</title>

    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.0/font/bootstrap-icons.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

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
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .driver-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: box-shadow 0.3s ease;
        }

        .driver-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .status-badge-ativo {
            background-color: #D1FAE5;
            color: #065F46;
        }

        .status-badge-inativo {
            background-color: #FEE2E2;
            color: #991B1B;
        }

        .rbac-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .rbac-admin {
            background-color: #dc3545;
            color: white;
        }

        .rbac-gestor {
            background-color: #ffc107;
            color: #333;
        }

        .rbac-condutor {
            background-color: #17a2b8;
            color: white;
        }

        .rbac-convidado {
            background-color: #6c757d;
            color: white;
        }

        .driver-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
    </style>
</head>
<!-- Content -->
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0" style="color: var(--dark-color); font-weight: 700;">Condutores</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <button class="btn btn-primary">
                        <i class="fas fa-plus mr-2"></i>Novo Condutor
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <input type="text" class="form-control" placeholder="Procurar nome ou NIF...">
                        </div>
                        <div class="col-md-4">
                            <select class="form-control">
                                <option>Todos os Estados</option>
                                <option>Ativo</option>
                                <option>Inativo</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-secondary"><i class="fas fa-filter mr-2"></i>Filtrar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Drivers Grid -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Condutores do Sistema</h3>
                    <div class="card-tools">
                        <span class="badge badge-primary">24 Condutores</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>NIF</th>
                                <th>Carta</th>
                                <th>Válida até</th>
                                <th>Veículo</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>João Silva</td>
                                <td>123456789</td>
                                <td>123-AB-45</td>
                                <td>15/05/2028</td>
                                <td>ABC-1234</td>
                                <td><span class="badge badge-success">Ativo</span></td>
                                <td>
                                    <button class="btn btn-sm btn-info" title="Editar"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-sm btn-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>Maria Oliveira</td>
                                <td>987654321</td>
                                <td>456-CD-78</td>
                                <td>22/11/2029</td>
                                <td>DEF-5678</td>
                                <td><span class="badge badge-success">Ativo</span></td>
                                <td>
                                    <button class="btn btn-sm btn-info" title="Editar"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-sm btn-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>Carlos Pereira</td>
                                <td>456789123</td>
                                <td>789-EF-01</td>
                                <td>10/02/2026</td>
                                <td>GHI-9012</td>
                                <td><span class="badge badge-warning">Próximo Vencimento</span></td>
                                <td>
                                    <button class="btn btn-sm btn-info" title="Editar"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-sm btn-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

</html>