<div id="loader-global" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.85); z-index: 9999; flex-direction: column; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
    <div class="spinner-border text-primary" style="width: 3.5rem; height: 3.5rem;" role="status">
        <span class="visually-hidden">Cargando...</span>
    </div>
    <h5 class="mt-3 fw-bold text-dark">GestiDom</h5>
    <p class="text-muted small" id="loader-text">Procesando solicitud...</p>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Se activa al enviar cualquier formulario (Login, Pagos, Filtros)
    $('form').on('submit', function() {
        $('#loader-global').css('display', 'flex').hide().fadeIn(200);
        
        // Desactivamos el botón para evitar doble envío accidental
        $(this).find('button[type="submit"]').prop('disabled', true);
    });

    // También puedes activarlo manualmente en enlaces de reportes pesados
    $('.btn-load').on('click', function() {
        $('#loader-global').css('display', 'flex').hide().fadeIn(200);
    });
});
</script>