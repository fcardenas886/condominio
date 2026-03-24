<div class="modal fade" id="modalVisorPDF" data-bs-backdrop="static" tabindex="-1" aria-labelledby="visorLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="visorLabel">
                    <i class="fas fa-file-pdf me-2"></i> Comprobante de Pago
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" onclick="location.reload()"></button>
            </div>
            <div class="modal-body p-0" style="background-color: #525659;">
                <iframe id="framePDF" src="" width="100%" height="600px" style="border: none; display: block;"></iframe>
            </div>
            <div class="modal-footer justify-content-between">
                <div>
                    <a id="btnWsp" href="#" target="_blank" class="btn btn-success">
                        <i class="fab fa-whatsapp"></i> Enviar por WhatsApp
                    </a>
                </div>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="location.reload()">
                    Finalizar y Volver
                </button>
            </div>
        </div>
    </div>
</div>