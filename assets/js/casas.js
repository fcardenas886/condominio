// assets/js/casas.js
const modalCasa = document.getElementById('modalCasa');

if (modalCasa) {
    modalCasa.addEventListener('show.bs.modal', event => {
        const boton = event.relatedTarget;
        const accion = boton.getAttribute('data-accion');
        
        const titulo = document.getElementById('modalTitulo');
        const formulario = document.getElementById('formCasa');
        const inputId = document.getElementById('input_id');
        const inputNumero = document.getElementById('input_numero');
        const inputDesc = document.getElementById('input_descripcion');
        const inputEstado = document.getElementById('input_estado');

        if (accion === 'editar') {
            titulo.textContent = 'Editar Casa ✏️';
            formulario.action = './modulos/editar_casa_proceso.php';
            
            inputId.value = boton.getAttribute('data-id');
            inputNumero.value = boton.getAttribute('data-numero');
            inputDesc.value = boton.getAttribute('data-descripcion');
            inputEstado.value = boton.getAttribute('data-estado');
        } else {
            titulo.textContent = 'Nueva Casa 🏠';
            formulario.action = './modulos/guardar_casa.php';
            formulario.reset();
            inputId.value = '';
        }
    });
}

function confirmarEliminar(id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "La casa se ocultará de la lista principal.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Si el usuario confirma, redirigimos
            window.location.href = 'modulos/eliminar_casa.php?id=' + id;
        }
    })
}