<div class="modal fade" id="modalArrendatario" tabindex="-1" aria-labelledby="modalArrendatarioLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalArrendatarioLabel">👤 Datos del Arrendatario</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formArrendatario" action="modulos/arrendatarios_controlador.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id_arrendatario" id="id_arrendatario">
                    <input type="hidden" name="accion" id="accion_arrendatario" value="crear">

                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre Completo</label>
                        <input type="text" class="form-control" name="nombre" id="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="rut" class="form-label">RUT</label>
                        <input type="text" class="form-control" name="rut" id="rut" placeholder="12.345.678-9" required>
                    </div>
                    <div class="mb-3">
                        <label for="telefono" class="form-label">Teléfono</label>
                        <div class="input-group">
                            <span class="input-group-text">+56</span>
                            <input type="tel" class="form-control" name="telefono" id="telefono" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="correo" class="form-label">Correo Electrónico</label>
                        <input type="email" class="form-control" name="correo" id="correo" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarArrendatario">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>