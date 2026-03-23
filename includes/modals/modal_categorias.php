<div class="modal fade" id="modalCategorias" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title"><i class="bi bi-tags"></i> Categorías de Mantención</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="modulos/guardar_categoria.php" method="POST" class="d-flex gap-2 mb-3">
                    <input type="text" name="nombre_categoria" class="form-control" placeholder="Nueva categoría..." required>
                    <button type="submit" class="btn btn-success">Añadir</button>
                </form>

                <ul class="list-group">
                    <?php foreach ($categorias as $cat): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?php echo htmlspecialchars($cat['nombre_categoria']); ?>

                            <button type="button" 
                                    class="btn btn-sm text-danger btn-borrar-cat" 
                                    data-id="<?php echo $cat['id_categoria']; ?>" 
                                    data-nombre="<?php echo htmlspecialchars($cat['nombre_categoria']); ?>">
                                <i class="bi bi-trash"></i>
                            </button>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>