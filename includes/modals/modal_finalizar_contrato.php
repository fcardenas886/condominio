<div class="modal fade" id="modalFinalizar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-x-circle"></i> Finalizar Contrato</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="modulos/contratos_controlador.php" method="POST">
                <input type="hidden" name="accion" value="finalizar">
                <input type="hidden" name="id_contrato" id="fin_id_contrato">
                <input type="hidden" name="id_casa" id="fin_id_casa">
                
                <div class="modal-body">
                    <p id="texto_confirmacion" class="fw-bold"></p>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Motivo del Cierre</label>
                        <select name="motivo" class="form-select" required>
                            <option value="Termino Normal">Término Normal</option>
                            <option value="Rescisión Anticipada">Rescisión Anticipada</option>
                            <option value="Desalojo">Desalojo</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Monto Garantía a Devolver ($)</label>
                        <input type="number" name="monto_devuelto" class="form-control" placeholder="Ej: 450000" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Observaciones de Entrega</label>
                        <textarea name="detalles" class="form-control" rows="3" placeholder="Ej: Entrega llaves, casa pintada..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Confirmar Cierre</button>
                </div>
            </form>
        </div>
    </div>
</div>