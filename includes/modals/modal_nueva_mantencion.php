<div class="modal fade" id="modalNuevaMantencion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg">
            <form action="modulos/guardar_mantencion.php" method="POST" id="formMantencion">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-calculator me-2"></i>Registrar Mantención y Cobro</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="small fw-bold text-muted text-uppercase">Ubicación / Destino:</label>
                            <select name="id_casa" id="id_casa_selector" class="form-select border-primary" required>
                                <option value="0" data-contrato="0">🏢 ÁREAS COMUNES (General)</option>
                                <optgroup label="Casas Individuales">
                                    <?php foreach($casas as $c): ?>
                                        <option value="<?php echo $c['id_casa']; ?>" data-contrato="<?php echo $c['tiene_contrato']; ?>">
                                            Casa N° <?php echo htmlspecialchars($c['numero_casa']); ?> 
                                            <?php echo ($c['tiene_contrato'] == 0) ? '— (SIN CONTRATO)' : ''; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="small fw-bold text-muted text-uppercase">Descripción:</label>
                            <textarea name="descripcion" class="form-control" rows="2" required></textarea>
                        </div>

                        <div class="col-12">
                            <label class="small fw-bold text-muted text-uppercase">Proveedor:</label>
                            <select name="id_proveedor" class="form-select" required>
                                <option value="">Seleccionar...</option>
                                <?php foreach($proveedores as $p): ?>
                                    <option value="<?php echo $p['id_proveedor']; ?>"><?php echo htmlspecialchars($p['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-6">
                            <label class="small fw-bold text-muted text-uppercase">Monto Total ($):</label>
                            <input type="number" name="monto_pagado" class="form-control font-monospace fw-bold" placeholder="0" required>
                        </div>

                        <div class="col-6">
                            <label class="small fw-bold text-muted text-uppercase">Fecha Registro:</label>
                            <input type="date" name="fecha" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>

                        <div class="col-12" id="seccion_cobro" style="display: none;">
                            <div class="p-3 border rounded bg-light border-warning">
                                <div id="alerta_sin_contrato" class="alert alert-danger py-2 small mb-2" style="display:none;">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i> No se puede cobrar: Propiedad sin contrato activo.
                                </div>

                                <div class="form-check form-switch mb-3" id="contenedor_switch">
                                    <input class="form-check-input" type="checkbox" name="cobrar_a_arrendatario" value="1" id="checkCobro">
                                    <label class="form-check-label fw-bold text-danger" for="checkCobro">¿Cobrar al Arrendatario?</label>
                                </div>

                                <div id="detalles_cuotas" style="display: none;" class="row g-2 pt-2 border-top border-warning-subtle">
                                    <div class="col-6">
                                        <label class="small fw-bold">N° de Cuotas:</label>
                                        <select name="cuotas" class="form-select form-select-sm">
                                            <?php for($i=1; $i<=12; $i++): ?>
                                                <option value="<?php echo $i; ?>"><?php echo $i; ?> <?php echo ($i==1)?'cuota':'cuotas'; ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="small fw-bold">Mes de Inicio:</label>
                                        <input type="date" name="mes_inicio" class="form-control form-select-sm" value="<?php echo date('Y-m-d'); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary px-4">Confirmar y Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectorCasa = document.getElementById('id_casa_selector');
    const seccionCobro = document.getElementById('seccion_cobro');
    const checkCobro = document.getElementById('checkCobro');
    const detallesCuotas = document.getElementById('detalles_cuotas');
    const alertaSinContrato = document.getElementById('alerta_sin_contrato');
    const contenedorSwitch = document.getElementById('contenedor_switch');

    selectorCasa.addEventListener('change', function() {
        // Obtenemos si la casa seleccionada tiene contrato
        const selectedOption = this.options[this.selectedIndex];
        const tieneContrato = selectedOption.getAttribute('data-contrato');

        if (this.value != "0") {
            seccionCobro.style.display = 'block';
            
            if (tieneContrato == "0") {
                // Si no hay contrato, bloqueamos el cobro
                alertaSinContrato.style.display = 'block';
                contenedorSwitch.style.opacity = '0.5';
                checkCobro.disabled = true;
                checkCobro.checked = false;
                detallesCuotas.style.display = 'none';
            } else {
                // Si hay contrato, habilitamos todo
                alertaSinContrato.style.display = 'none';
                contenedorSwitch.style.opacity = '1';
                checkCobro.disabled = false;
            }
        } else {
            seccionCobro.style.display = 'none';
            checkCobro.checked = false;
            detallesCuotas.style.display = 'none';
        }
    });

    checkCobro.addEventListener('change', function() {
        detallesCuotas.style.display = this.checked ? 'flex' : 'none';
    });
});
</script>