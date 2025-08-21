@extends('lscefa::layouts.app')

@section('title', 'Auditorías de Calidad')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Auditorías de Calidad</h4>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAuditModal">
                        <i class="fas fa-plus"></i> Nueva Auditoría
                    </button>
                </div>

                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Pendientes</h5>
                                    <h2 class="mb-0">0</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Completadas</h5>
                                    <h2 class="mb-0">0</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h5 class="card-title">En Progreso</h5>
                                    <h2 class="mb-0">0</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Retrasadas</h5>
                                    <h2 class="mb-0">0</h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Fecha Programada</th>
                                    <th>Área</th>
                                    <th>Auditor</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Aquí se cargarán las auditorías dinámicamente -->
                                <tr>
                                    <td colspan="6" class="text-center">No hay auditorías registradas</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para agregar nueva auditoría -->
<div class="modal fade" id="addAuditModal" tabindex="-1" aria-labelledby="addAuditModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAuditModalLabel">Nueva Auditoría</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addAuditForm">
                    <div class="mb-3">
                        <label for="auditDate" class="form-label">Fecha Programada</label>
                        <input type="date" class="form-control" id="auditDate" required>
                    </div>
                    <div class="mb-3">
                        <label for="auditArea" class="form-label">Área</label>
                        <select class="form-select" id="auditArea" required>
                            <option value="">Seleccione un área</option>
                            <option value="laboratory">Laboratorio</option>
                            <option value="sampling">Muestreo</option>
                            <option value="analysis">Análisis</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="auditor" class="form-label">Auditor</label>
                        <select class="form-select" id="auditor" required>
                            <option value="">Seleccione un auditor</option>
                            <!-- Aquí se cargarán los auditores dinámicamente -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="auditType" class="form-label">Tipo de Auditoría</label>
                        <select class="form-select" id="auditType" required>
                            <option value="internal">Interna</option>
                            <option value="external">Externa</option>
                            <option value="surveillance">Vigilancia</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="saveAudit">Guardar</button>
            </div>
        </div>
    </div>
</div>
@endsection 