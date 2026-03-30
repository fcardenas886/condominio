<div id="loader-global" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.9); z-index: 9999; flex-direction: column; align-items: center; justify-content: center; backdrop-filter: blur(10px); color: white;">
    
    <div class="mb-3 text-center">
        <img src="assets/GeSTIDom2.gif" alt="Cargando..." style="width: 180px; height: auto; filter: drop-shadow(0 0 15px rgba(0, 200, 255, 0.4));">
    </div>

    <div class="text-center">
        <h4 class="fw-bold mb-0" style="letter-spacing: 2px;">GeSTIDom</h4>
        <p class="text-info small text-uppercase mb-2" style="letter-spacing: 1px; font-size: 0.75rem;">Sistema STI</p>
        <div id="loader-text" class="fw-light" style="font-size: 0.9rem; opacity: 0.8;">Procesando solicitud...</div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Se activa al enviar cualquier formulario
    $('form').on('submit', function() {
        // Mostramos el loader con un efecto de desvanecimiento
        $('#loader-global').css('display', 'flex').hide().fadeIn(300);
        
        // Desactivamos el botón de envío para evitar duplicados
        $(this).find('button[type="submit"]').prop('disabled', true);
    });

    // Función para activar manualmente en enlaces con la clase .btn-load
    $('.btn-load').on('click', function() {
        $('#loader-global').css('display', 'flex').hide().fadeIn(300);
    });
});
</script>