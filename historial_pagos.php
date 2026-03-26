<?php
session_start();
include 'includes/conexion.php';

// 1. Capturar fechas de filtro (por defecto el mes actual)
$fecha_inicio = $_GET['desde'] ?? date('Y-m-01');
$fecha_fin = $_GET['hasta'] ?? date('Y-m-d');

// 2. Consulta a la vista (Asegúrate de haber actualizado la vista para incluir 'token')
$sql = "SELECT * FROM vista_historial_pagos 
        WHERE DATE(fecha_pago) BETWEEN ? AND ? 
        ORDER BY fecha_pago DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$fecha_inicio, $fecha_fin]);
$pagos = $stmt->fetchAll();

// 3. Calcular Total Recaudado
$total_recaudado = 0;
foreach($pagos as $p) {
    if($p['metodo_pago'] != 'Saldo a Favor') {
        $total_recaudado += $p['monto_pagado'];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de Pagos - CondoPro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        @media print { .no-print { display: none !important; } .card { border: none !important; shadow: none !important; } }
        .table-hover tbody tr { cursor: pointer; }
    </style>
</head>
<body class="bg-light">

<?php include 'includes/menu.php'; ?>

<div class="container mt-4">
    <div class="card shadow-sm border-0 mb-4 no-print">
        <div class="card-body bg-white border rounded">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Desde:</label>
                    <input type="date" name="desde" class="form-control" value="<?php echo $fecha_inicio; ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Hasta:</label>
                    <input type="date" name="hasta" class="form-control" value="<?php echo $fecha_fin; ?>">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel"></i> Filtrar
                    </button>
                </div>
                <div class="col-md-3">
                    <div class="p-2 bg-primary text-white rounded shadow-sm text-center">
                        <small class="d-block text-uppercase" style="font-size: 0.7rem;">Recaudación Real:</small>
                        <strong class="fs-5">$<?php echo number_format($total_recaudado, 0, ',', '.'); ?></strong>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow border-0">
        <div class="card-header bg-dark text-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><i class="bi bi-journal-text"></i> Movimientos del Periodo</h5>
            <div class="no-print">
                <input type="text" id="buscador" class="form-control form-control-sm d-inline-block w-auto me-2" placeholder="Buscar...">
                <button onclick="window.print()" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-printer"></i>
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0 align-middle" id="tablaHistorial">
                <thead class="table-light">
                    <tr>
                        <th>Fecha</th>
                        <th>Folio</th>
                        <th>Propiedad</th>
                        <th>Inquilino</th>
                        <th>Concepto</th>
                        <th class="text-end">Monto</th>
                        <th>Método</th>
                        <th class="text-center no-print">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($pagos)): ?>
                        <tr><td colspan="8" class="text-center py-5 text-muted">No hay movimientos.</td></tr>
                    <?php else: ?>
                        <?php foreach($pagos as $p): ?>
                        <tr>
                            <td class="small"><?php echo date('d/m H:i', strtotime($p['fecha_pago'])); ?></td>
                            <td><small class="badge bg-light text-dark border"><?php echo $p['id_transaccion']; ?></small></td>
                            <td><span class="badge bg-secondary">#<?php echo $p['numero_casa']; ?></span></td>
                            <td class="fw-bold"><?php echo $p['inquilino']; ?></td>
                            <td class="small"><?php echo $p['concepto']; ?></td>
                            <td class="fw-bold text-end">$<?php echo number_format($p['monto_pagado'], 0, ',', '.'); ?></td>
                            <td>
                                <?php 
                                $bg = 'bg-secondary';
                                if($p['metodo_pago'] == 'Transferencia') $bg = 'bg-primary';
                                if($p['metodo_pago'] == 'Efectivo') $bg = 'bg-success';
                                if($p['metodo_pago'] == 'Saldo a Favor') $bg = 'bg-info text-dark';
                                ?>
                                <span class="badge <?php echo $bg; ?>" style="font-size: 0.7rem;"><?php echo $p['metodo_pago']; ?></span>
                            </td>
                            <td class="text-center no-print">
                                <button class="btn btn-outline-primary btn-sm btn-visor" 
                                        data-folio="<?php echo $p['id_transaccion']; ?>"
                                        data-token="<?php echo $p['token']; ?>">
                                    <i class="bi bi-file-earmark-pdf"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/modals/modal_visor_pdf.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function(){
    // Buscador en tiempo real
    $("#buscador").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#tablaHistorial tbody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    // Acción del botón visor corregida para incluir el Token
    $(".btn-visor").click(function(){
        let folio = $(this).data('folio');
        let token = $(this).data('token'); // Capturamos el token del botón
        
        // Construimos la URL con el token
        let urlSegura = "reportes/generar_pdf.php?transaccion=" + folio + "&token=" + token;
        
        // Cargar el PDF en el iframe
        $("#framePDF").attr("src", urlSegura);
        
        // Preparar link de WhatsApp con el token incluido
        let urlCompleta = window.location.origin + "/condominio/" + urlSegura;
        let msg = encodeURIComponent("Hola, adjunto copia de su recibo " + folio + ": " + urlCompleta);
        $("#btnWsp").attr("href", "https://wa.me/?text=" + msg);
        
        // Abrir el modal
        var myModal = new bootstrap.Modal(document.getElementById('modalVisorPDF'));
        myModal.show();
    });
});
</script>

</body>
</html>