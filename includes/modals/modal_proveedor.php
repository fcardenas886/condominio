<div class="modal fade" id="modalProveedor" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="modulos/guardar_proveedor_completo.php" method="POST" id="formProveedor">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="tituloModalProveedor">
                        <i class="bi bi-person-plus" id="iconoModal"></i> 
                        <span id="textoModal">Registrar Proveedor</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_proveedor" id="id_proveedor">

                    <div class="row g-3">
                        <div class="col-6">
                            <label class="small fw-bold">RUT:</label>
                            <input type="text" name="rut" id="prov_rut" class="form-control" placeholder="12.345.678-9" required>
                        </div>
                        <div class="col-6">
                            <label class="small fw-bold">Nombre:</label>
                            <input type="text" name="nombre" id="prov_nombre" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="small fw-bold">Categorías de Mantención:</label>
                            <div class="border rounded p-2 bg-light" style="max-height: 150px; overflow-y: auto;">
                                <?php foreach($categorias as $cat): ?>
                                    <div class="form-check">
                                        <input class="form-check-input check-categoria" type="checkbox" 
                                               name="categorias[]" 
                                               value="<?php echo $cat['id_categoria']; ?>" 
                                               id="cat_<?php echo $cat['id_categoria']; ?>">
                                        <label class="form-check-label small" for="cat_<?php echo $cat['id_categoria']; ?>">
                                            <?php echo $cat['nombre_categoria']; ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="small fw-bold">Teléfono de contacto:</label>
                            <input type="text" name="telefono" id="prov_telefono" class="form-control" placeholder="+56 9 ...">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4" id="btnGuardarProveedor">Guardar Proveedor</button>
                </div>
            </form>
        </div>
    </div>
</div>