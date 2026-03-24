$(document).ready(function() {
    // Escuchamos el envío del formulario de pago
    $("#formPago").on("submit", function(e) {
        e.preventDefault(); // Evitamos que la página se recargue
        
        // Referencia al botón para mostrar feedback visual
        const btnGuardar = $(this).find('button[type="submit"]');
        const originalText = btnGuardar.html();
        
        // Bloqueamos el botón mientras procesa
        btnGuardar.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Procesando...');

        $.ajax({
            url: 'modulos/procesar_pago_masivo.php',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                // Limpiamos espacios en blanco de la respuesta
                let folio = response.trim(); 
                
                // Si la respuesta contiene "Error", lo mostramos
                if(folio.toLowerCase().includes("error")) {
                    alert("Ocurrió un problema: " + folio);
                    btnGuardar.prop('disabled', false).html(originalText);
                } else {
                    // 1. Cargamos el PDF en el iframe del modal visor
                    // Nota: Asegúrate que la ruta al PDF sea correcta desde el index
                    $("#framePDF").attr("src", "reportes/generar_pdf.php?transaccion=" + folio);
                    
                    // 2. Preparamos el link de WhatsApp
                    // Puedes personalizar el número y el mensaje aquí
                    let mensaje = encodeURIComponent("Hola! Adjunto el comprobante de pago " + folio + ". Saludos de CondoPro.");
                    $("#btnWsp").attr("href", "https://wa.me/?text=" + mensaje);
                    
                    // 3. Cerramos el modal de cobro (el que tiene el formulario)
                    $("#modalPago").modal('hide'); 
                    
                    // 4. Abrimos el modal visor que acabas de crear
                    var visor = new bootstrap.Modal(document.getElementById('modalVisorPDF'));
                    visor.show();
                }
            },
            error: function() {
                alert("Error crítico en el servidor. Intente de nuevo.");
                btnGuardar.prop('disabled', false).html(originalText);
            }
        });
    });
});