// 1. Capturamos el modal por su ID
const modalArrendatario = document.getElementById('modalArrendatario');

if (modalArrendatario) {
    // 2. Escuchamos el evento cuando el modal se está por mostrar
    modalArrendatario.addEventListener('show.bs.modal', event => {
        // El botón que activó el modal
        const boton = event.relatedTarget;
        
        // Extraemos la información de los atributos data-
        const accion = boton.getAttribute('data-accion');
        
        // Elementos del formulario
        const titulo = modalArrendatario.querySelector('.modal-title');
        const inputAccion = modalArrendatario.querySelector('#accion_arrendatario');
        const btnGuardar = modalArrendatario.querySelector('#btnGuardarArrendatario');

        if (accion === 'editar') {
            // Configuramos para EDICIÓN ✏️
            titulo.textContent = '✏️ Editar Arrendatario';
            inputAccion.value = 'editar';
            btnGuardar.className = 'btn btn-warning';

            // Rellenamos los campos con los datos del botón
            document.getElementById('id_arrendatario').value = boton.getAttribute('data-id');
            document.getElementById('nombre').value = boton.getAttribute('data-nombre');
            document.getElementById('rut').value = boton.getAttribute('data-rut');
            document.getElementById('telefono').value = boton.getAttribute('data-telefono');
            document.getElementById('correo').value = boton.getAttribute('data-correo');
        } else {
            // Configuramos para CREACIÓN ✨
            titulo.textContent = '👤 Nuevo Arrendatario';
            inputAccion.value = 'crear';
            btnGuardar.className = 'btn btn-primary';

            // Limpiamos el formulario
            document.getElementById('formArrendatario').reset();
            document.getElementById('id_arrendatario').value = '';
        }
    });
}

// 3. Función para la alerta de eliminación con SweetAlert2 🗑️
function confirmarEliminarArrendatario(id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "El arrendatario será desactivado del sistema.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Redirigimos al controlador con la acción eliminar
            window.location.href = `modulos/arrendatarios_controlador.php?accion=eliminar&id=${id}`;
        }
    });
}