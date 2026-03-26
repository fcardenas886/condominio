$(document).ready(function() {
    console.log("✅ Sistema de Pagos con Seguridad Token Activo");

    $(document).on("submit", "#formPago", function(e) {
        e.preventDefault(); 
        
        const btn = $(this).find('button[type="submit"]');
        const originalText = btn.html();
        
        // Bloqueamos el botón para evitar doble clic
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Procesando...');

        $.ajax({
            url: 'modulos/procesar_pago_masivo.php',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                console.log("Respuesta bruta del servidor:", response);
                
                // 1. Separamos el Folio del Token usando el divisor "|"
                let partes = response.trim().split('|');

                // Si la respuesta no tiene el formato correcto, mostramos el error
                if (partes.length < 2) {
                    alert("Error: " + response);
                    btn.prop('disabled', false).html(originalText);
                    return;
                }

                let folio = partes[0];
                let token = partes[1];

                // 2. Construimos la URL con ambos parámetros (GET)
                let urlSegura = "reportes/generar_pdf.php?transaccion=" + folio + "&token=" + token;

                // 3. Inyectamos la URL en el Visor PDF
                $("#framePDF").attr("src", urlSegura);
                
                // 4. Preparamos el mensaje de WhatsApp con el Link de Invitado
                let urlCompleta = window.location.origin + "/condominio/" + urlSegura;
                let mensajeWsp = encodeURIComponent(
                    "¡Hola! 👋 Adjunto el link de su comprobante de pago " + folio + ".\n\n" +
                    "Puede verlo aquí: " + urlCompleta + "\n\n" +
                    "Saludos de CondoPro 🏢"
                );
                $("#btnWsp").attr("href", "https://wa.me/?text=" + mensajeWsp);

                // 5. Cerramos el modal de cobro y abrimos el visor
                const modalCobro = bootstrap.Modal.getInstance(document.getElementById('modalPagoPro'));
                if(modalCobro) modalCobro.hide();

                const visor = new bootstrap.Modal(document.getElementById('modalVisorPDF'));
                visor.show();

                // Resetear el botón por si cierran el visor y quieren pagar otro
                btn.prop('disabled', false).html(originalText);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("Error AJAX:", textStatus, errorThrown);
                alert("Error crítico de conexión. Intente de nuevo.");
                btn.prop('disabled', false).html(originalText);
            }
        });
    });
});