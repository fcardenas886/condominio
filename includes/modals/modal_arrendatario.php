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
                        <label for="nombre" class="form-label fw-bold">Nombre Completo</label>
                        <input type="text" class="form-control" name="nombre" id="nombre" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="rut" class="form-label fw-bold">RUT</label>
                            <input type="text" class="form-control" name="rut" id="rut" placeholder="12.345.678-9" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="telefono" class="form-label fw-bold">Teléfono</label>
                            <div class="input-group">
                                <span class="input-group-text">+56</span>
                                <input type="tel" class="form-control" name="telefono" id="telefono" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="correo" class="form-label fw-bold">Correo Electrónico</label>
                        <input type="email" class="form-control" name="correo" id="correo" required>
                    </div>

                    <hr>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="crearPerfil" name="crear_perfil" value="1" onchange="togglePassword()">
                        <label class="form-check-label fw-bold text-primary" for="crearPerfil">
                            <i class="bi bi-person-badge"></i> Habilitar Perfil Público
                        </label>
                    </div>

                    <div id="seccion_password" style="display: none;" class="p-3 bg-light border rounded">
                        <label for="password_usuario" class="form-label fw-bold">Contraseña de Acceso</label>
                        <input type="password" class="form-control" name="password_usuario" id="password_usuario" placeholder="Defina una clave inicial">
                        <small class="text-muted">El usuario usará su correo y esta clave para ingresar.</small>
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

<script>
function togglePassword() {
    const section = document.getElementById('seccion_password');
    const inputPass = document.getElementById('password_usuario');
    const isChecked = document.getElementById('crearPerfil').checked;
    
    section.style.display = isChecked ? 'block' : 'none';
    inputPass.required = isChecked; // Hace obligatorio el campo si el switch está ON
}
</script>