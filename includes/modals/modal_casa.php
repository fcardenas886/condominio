<div class="modal fade" id="modalCasa" tabindex="-1" aria-labelledby="modalCasaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalTitulo">Nueva Casa</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formCasa" action="modulos/guardar_casa.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id_casa" id="input_id">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Número de Casa</label>
                        <input type="text" name="numero" id="input_numero" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Descripción</label>
                        <textarea name="descripcion" id="input_descripcion" class="form-control" rows="2"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Estado</label>
                        <select name="estado" id="input_estado" class="form-select">
                            <option value="Disponible">Disponible ✅</option>
                            <option value="Ocupada">Ocupada 👤</option>
                            <option value="Mantenimiento">Mantenimiento 🛠️</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardar">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>