// 1. Capturamos el modal por su ID
const modalArrendatario = document.getElementById('modalArrendatario');

if (modalArrendatario) {
    // 2. Escuchamos el evento cuando el modal se está por mostrar
    modalArrendatario.addEventListener('show.bs.modal', event => {
        const boton = event.relatedTarget;
        const accion = boton.getAttribute('data-accion');
        
        // Elementos del formulario
        const titulo = modalArrendatario.querySelector('.modal-title');
        const inputAccion = modalArrendatario.querySelector('#accion_arrendatario');
        const btnGuardar = modalArrendatario.querySelector('#btnGuardarArrendatario');
        
        // Elementos nuevos del Perfil Público
        const switchPerfil = document.getElementById('crearPerfil');
        const seccionPass = document.getElementById('seccion_password');
        const inputPass = document.getElementById('password_usuario');

        if (accion === 'editar') {
            // Configuramos para EDICIÓN ✏️
            titulo.textContent = '✏️ Editar Arrendatario';
            inputAccion.value = 'editar';
            btnGuardar.className = 'btn btn-warning';

            // Rellenamos los campos básicos
            document.getElementById('id_arrendatario').value = boton.getAttribute('data-id');
            document.getElementById('nombre').value = boton.getAttribute('data-nombre');
            document.getElementById('rut').value = boton.getAttribute('data-rut');
            document.getElementById('telefono').value = boton.getAttribute('data-telefono');
            document.getElementById('correo').value = boton.getAttribute('data-correo');

            // --- Lógica del Perfil Público ---
            const tienePerfil = boton.getAttribute('data-perfil'); // 1 o 0
            if (tienePerfil == '1') {
                switchPerfil.checked = true;
                seccionPass.style.display = 'block';
                inputPass.required = false; // No obligatorio al editar (solo si se cambia)
                inputPass.placeholder = "Dejar en blanco para mantener actual";
            } else {
                switchPerfil.checked = false;
                seccionPass.style.display = 'none';
                inputPass.required = false;
                inputPass.placeholder = "Defina una clave inicial";
            }
        } else {
            // Configuramos para CREACIÓN ✨
            titulo.textContent = '👤 Nuevo Arrendatario';
            inputAccion.value = 'crear';
            btnGuardar.className = 'btn btn-primary';

            // Limpiamos el formulario
            document.getElementById('formArrendatario').reset();
            document.getElementById('id_arrendatario').value = '';
            
            // Resetear Perfil Público
            switchPerfil.checked = false;
            seccionPass.style.display = 'none';
            inputPass.required = false;
            inputPass.placeholder = "Defina una clave inicial";
        }
    });
}

// Función para manejar la visibilidad de la contraseña (la que llamamos desde el HTML)
function togglePassword() {
    const section = document.getElementById('seccion_password');
    const inputPass = document.getElementById('password_usuario');
    const isChecked = document.getElementById('crearPerfil').checked;
    
    section.style.display = isChecked ? 'block' : 'none';
    inputPass.required = isChecked;
}

// 3. Alerta de eliminación
function confirmarEliminarArrendatario(id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "El arrendatario y su acceso al sistema serán desactivados.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `modulos/arrendatarios_controlador.php?accion=eliminar&id=${id}`;
        }
    });
}