<?php

/** @var yii\web\View $this */

$this->title = 'My Yii Application';
?>

<div>
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0" style="color: var(--dark-color); font-weight: 700;">Plano de Manutenção</h1>
                    </div>
                    <div class="col-sm-6 text-right">
                        <button class="btn btn-primary" data-toggle="modal" data-target="#addMaintenanceModal">
                            <i class="fas fa-plus mr-2"></i>Agendar Manutenção
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <!-- Stats -->
                <div class="row mb-4">
                    <div class="col-md-3 col-sm-6">
                        <div class="small-box" style="background-color: #17a2b8;">
                            <div class="inner text-white">
                                <h3>12</h3>
                                <p>Agendadas</p>
                            </div>
                            <div class="icon"><i class="fas fa-calendar"></i></div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="small-box" style="background-color: var(--primary-color);">
                            <div class="inner">
                                <h3>8</h3>
                                <p>Em Progresso</p>
                            </div>
                            <div class="icon"><i class="fas fa-spinner"></i></div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="small-box" style="background-color: #28a745;">
                            <div class="inner text-white">
                                <h3>95</h3>
                                <p>Concluídas</p>
                            </div>
                            <div class="icon"><i class="fas fa-check-circle"></i></div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="small-box" style="background-color: #ffc107;">
                            <div class="inner">
                                <h3>3</h3>
                                <p>Atrasos</p>
                            </div>
                            <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                        </div>
                    </div>
                </div>

                <!-- Maintenance Table -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Histórico de Manutenção</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Veículo</th>
                                    <th>Tipo</th>
                                    <th>Data Programada</th>
                                    <th>Custo Estimado</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>A-123-AB</td>
                                    <td>Troca de óleo</td>
                                    <td>25/11/2024</td>
                                    <td>€150.00</td>
                                    <td><span class="badge badge-info">Agendada</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-success"><i class="fas fa-check"></i></button>
                                        <button class="btn btn-sm btn-danger"><i class="fas fa-times"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>B-456-BC</td>
                                    <td>Revisão completa</td>
                                    <td>20/11/2024</td>
                                    <td>€500.00</td>
                                    <td><span class="badge badge-success">Concluída</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-info"><i class="fas fa-eye"></i></button>
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
    <div class="modal fade" id="addMaintenanceModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background-color: var(--primary-color); color: white;">
                    <h4 class="modal-title">Agendar Manutenção</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Veículo</label>
                            <select class="form-control">
                                <option>Selecione um veículo...</option>
                                <option>A-123-AB</option>
                                <option>B-456-BC</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Tipo de Manutenção</label>
                            <select class="form-control">
                                <option>Selecione...</option>
                                <option>Troca de óleo</option>
                                <option>Revisão completa</option>
                                <option>Pneus</option>
                                <option>Freios</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Data</label>
                            <input type="date" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Custo Estimado</label>
                            <input type="number" class="form-control" placeholder="0.00" step="0.01">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Agendar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>