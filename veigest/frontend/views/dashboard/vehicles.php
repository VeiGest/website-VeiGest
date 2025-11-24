<?php

/** @var yii\web\View $this */

$this->title = 'My Yii Application';
?>

<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">


    
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0" style="color: var(--dark-color); font-weight: 700;">Gestão de Frota</h1>
                    </div>
                    <div class="col-sm-6 text-right">
                        <button class="btn btn-primary" data-toggle="modal" data-target="#addVehicleModal">
                            <i class="fas fa-plus mr-2"></i>Novo Veículo
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <section class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Veículos Registados</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Matrícula</th>
                                    <th>Marca/Modelo</th>
                                    <th>Condutor Atual</th>
                                    <th>Manutenção</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>A-123-AB</td>
                                    <td>Mercedes C-Class</td>
                                    <td>João Carlos</td>
                                    <td><span class="badge badge-success">Em Dia</span></td>
                                    <td><span class="badge badge-success">Ativo</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-info"><i class="fas fa-eye"></i></button>
                                        <button class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>B-456-BC</td>
                                    <td>BMW 3 Series</td>
                                    <td>Maria Silva</td>
                                    <td><span class="badge badge-warning">Próximo: 30 dias</span></td>
                                    <td><span class="badge badge-success">Ativo</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-info"><i class="fas fa-eye"></i></button>
                                        <button class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>
    
    <!-- Modal -->
    <div class="modal fade" id="addVehicleModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background-color: var(--primary-color); color: white;">
                    <h4 class="modal-title">Novo Veículo</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Matrícula</label>
                                    <input type="text" class="form-control" placeholder="Ex: XX-XX-XX">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>VIN</label>
                                    <input type="text" class="form-control" placeholder="Número de chassis">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Marca</label>
                                    <input type="text" class="form-control" placeholder="Mercedes, BMW, etc">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Modelo</label>
                                    <input type="text" class="form-control" placeholder="Modelo do veículo">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Adicionar Veículo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    

</div>