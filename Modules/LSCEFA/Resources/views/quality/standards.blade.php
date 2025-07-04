@extends('lscefa::layouts.app')

@section('title', 'Estándares de Calidad')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Estándares de Calidad</h4>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStandardModal">
                        <i class="fas fa-plus"></i> Nuevo Estándar
                    </button>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th>Estado</th>
                                    <th>Última Actualización</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Aquí se cargarán los estándares dinámicamente -->
                                <tr>
                                    <td colspan="6" class="text-center">No hay estándares registrados</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para agregar nuevo estándar -->
<div class="modal fade" id="addStandardModal" tabindex="-1" aria-labelledby="addStandardModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addStandardModalLabel">Nuevo Estándar de Calidad</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addStandardForm">
                    <div class="mb-3">
                        <label for="standardName" class="form-label">Nombre del Estándar</label>
                        <input type="text" class="form-control" id="standardName" required>
                    </div>
                    <div class="mb-3">
                        <label for="standardDescription" class="form-label">Descripción</label>
                        <textarea class="form-control" id="standardDescription" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="standardStatus" class="form-label">Estado</label>
                        <select class="form-select" id="standardStatus" required>
                            <option value="active">Activo</option>
                            <option value="inactive">Inactivo</option>
                            <option value="draft">Borrador</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="saveStandard">Guardar</button>
            </div>
        </div>
    </div>
</div>
@endsection 