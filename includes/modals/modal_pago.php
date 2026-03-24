<div class="modal fade" id="modalPagoPro" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-calculator"></i> Registrar Pago Recibido</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formPago" method="POST">
                <div class="modal-body">
                    <div class="row mb-3 bg-light p-2 rounded mx-0">
                        <div class="col-8">
                            <small class="text-muted d-block">Arrendatario:</small>
                            <span id="modal_nombre" class="fw-bold fs-5 text-dark"></span>
                        </div>
                        <div class="col-4 text-end">
                            <small class="text-muted d-block">Propiedad:</small>
                            <span id="modal_casa" class="badge bg-dark fs-6"></span>
                        </div>
                    </div>

                    <p class="fw-bold mb-2 small text-uppercase text-secondary">Seleccione conceptos que paga:</p>
                    <div id="lista_deudas" class="list-group mb-3 border rounded shadow-sm" style="max-height: 200px; overflow-y: auto;">
                    </div>

                    <div class="card bg-light border-0 mb-3">
                        <div class="card-body">
                            <div id="aviso_saldo_favor" class="alert alert-info py-2 mb-3 shadow-sm" style="display:none;">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="usar_saldo_favor" name="usar_saldo_favor">
                                    <label class="form-check-label fw-bold" for="usar_saldo_favor">
                                        ¡Tiene <span id="monto_disponible_favor"></span> a favor! ¿Usarlos?
                                    </label>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Total Seleccionado:</span>
                                <span class="fw-bold fs-5 text-primary" id="txt_total_selec">$0</span>
                            </div>

                            <div class="row align-items-center mb-2">
                                <div class="col-6">
                                    <span class="text-muted">Monto Recibido:</span>
                                </div>
                                <div class="col-6">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">$</span>
                                        <input type="number" name="monto_recibido" id="monto_recibido" class="form-control fw-bold text-end" required>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between border-top pt-2 mt-2">
                                <span id="label_vuelto" class="text-muted small">Saldo a favor:</span>
                                <span id="txt_vuelto" class="fw-bold text-success fs-5">$0</span>
                            </div>

                            <div id="opcion_saldo_favor" class="mt-2 p-2 border rounded bg-white shadow-sm" style="display:none;">
                                <p class="small fw-bold mb-1 text-primary">¿Qué hacer con el excedente?</p>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="destino_excedente" id="dest1" value="vuelto" checked>
                                        <label class="form-check-label small" for="dest1 text-muted">Entregar Vuelto</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="destino_excedente" id="dest2" value="saldo">
                                        <label class="form-check-label small" for="dest2 text-success fw-bold">Guardar como Saldo a Favor</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p class="fw-bold mb-2 small text-uppercase text-secondary">Método de pago:</p>
                    <div class="d-flex justify-content-around p-2 border rounded bg-white">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="metodo" id="m_trans" value="Transferencia" checked>
                            <label class="form-check-label cursor-pointer" for="m_trans">Transferencia</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="metodo" id="m_efec" value="Efectivo">
                            <label class="form-check-label cursor-pointer" for="m_efec">Efectivo</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="metodo" id="m_tarj" value="Tarjeta">
                            <label class="form-check-label cursor-pointer" for="m_tarj">Tarjeta</label>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light border-top-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success px-4 fw-bold shadow-sm">
                        <i class="bi bi-check-circle"></i> GUARDAR PAGO
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>