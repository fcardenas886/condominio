<div class="modal fade" id="modalContrato" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-file-earmark-text"></i> Nuevo Contrato de Arriendo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formContrato" action="modulos/contratos_controlador.php" method="POST">
                <input type="hidden" name="accion" value="crear">
                
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Casa (Solo disponibles)</label>
                            <select name="id_casa" id="id_casa" class="form-select" required>
                                <option value="">Seleccione una propiedad...</option>
                                <?php foreach ($casas_disponibles as $casa): ?>
                                    <option value="<?php echo $casa['id_casa']; ?>">
                                        Casa № <?php echo $casa['numero_casa']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Arrendatario</label>
                            <select name="id_arrendatario" id="id_arrendatario" class="form-select" required>
                                <option value="">Seleccione arrendatario...</option>
                                <?php foreach ($arrendatarios_lista as $arr): ?>
                                    <option value="<?php echo $arr['id_arrendatario']; ?>">
                                        <?php echo htmlspecialchars($arr['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Monto Mensual ($)</label>
                            <input type="number" name="monto_fijo" class="form-control" placeholder="Ej: 450000" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Monto Garantía ($)</label>
                            <input type="number" name="monto_garantia" class="form-control" placeholder="Ej: 450000">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Fecha de Inicio</label>
                            <input type="date" name="fecha_inicio" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Fecha de Término (Opcional)</label>
                            <input type="date" name="fecha_termino" class="form-control">
                        </div>
                    </div>
                <div class="row">
    <div class="col-md-6 mb-3 d-flex align-items-center">
    <div class="form-check form-switch mt-4">
        <input class="form-check-input" type="checkbox" id="switchModalidad" name="modalidad_cobro" value="Anticipado">
        <label class="form-check-label fw-bold ms-2" for="switchModalidad">
            Cobro Anticipado <span class="text-muted fw-normal">(Por defecto: Vencido)</span>
        </label>
    </div>
</div>
    </div>

                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-check-circle"></i> Crear Contrato
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>