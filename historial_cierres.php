<?php
session_start();
if (!isset($_SESSION['id_admin'])) {
    header("Location: index.php");
    exit();
}
require_once 'includes/conexion.php';

// Usamos la vista para obtener los datos
$sql = "SELECT * FROM vista_historial_cierres ORDER BY fecha_cierre DESC";
$historial = $pdo->query($sql)->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de Salidas - GeSTIDom</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <?php include 'includes/menu.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-secondary"><i class="bi bi-clock-history"></i> Historial de Cierres</h2>
            <a href="gestion_contratos.php" class="btn btn-outline-primary btn-sm">Volver</a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Fecha</th>
                            <th>Propiedad</th>
                            <th>Arrendatario</th>
                            <th>Motivo</th>
                            <th class="text-center">Detalles</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($historial as $h): ?>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime($h['fecha_cierre'])); ?></td>
                            <td>Casa <?php echo $h['numero_casa']; ?></td>
                            <td><?php echo htmlspecialchars($h['nombre_arrendatario']); ?></td>
                            <td><span class="badge bg-info"><?php echo $h['motivo']; ?></span></td>
                            <td class="text-center">
                                <button class="btn btn-primary btn-sm" 
                                    onclick="mostrarDetalleCompleto(<?php echo htmlspecialchars(json_encode($h)); ?>)">
                                    <i class="bi bi-search"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDetalleCierre" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title">Detalle de Finalización</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="fw-bold text-muted small">PROPIEDAD</label>
                            <div id="det_casa" class="fs-5 fw-bold"></div>
                        </div>
                        <div class="col-6 text-end">
                            <label class="fw-bold text-muted small">FECHA CIERRE</label>
                            <div id="det_fecha"></div>
                        </div>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <label class="fw-bold text-muted small">ARRENDATARIO</label>
                        <div id="det_nombre" class="text-uppercase"></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="fw-bold text-muted small">MOTIVO</label>
                            <div id="det_motivo"></div>
                        </div>
                        <div class="col-6 text-end">
                            <label class="fw-bold text-muted small">GARANTÍA DEVUELTA</label>
                            <div id="det_garantia" class="text-success fw-bold"></div>
                        </div>
                    </div>
                    <div class="mb-3 p-3 bg-light border rounded">
                        <label class="fw-bold text-muted small d-block mb-1">OBSERVACIONES DE ENTREGA</label>
                        <div id="det_observaciones" style="white-space: pre-wrap;"></div>
                    </div>
                    <div class="text-end">
                        <small class="text-muted">Autorizado por: <span id="det_admin" class="fw-bold"></span></small>
                    </div>
                    
                    <div class="p-2 mb-3 bg-primary bg-opacity-10 border-start border-primary border-4 rounded">
    <div class="row text-center">
        <div class="col-4 border-end">
            <small class="text-muted d-block">Desde</small>
            <span id="det_inicio" class="fw-bold"></span>
        </div>
        <div class="col-4 border-end">
            <small class="text-muted d-block">Hasta</small>
            <span id="det_fin" class="fw-bold"></span>
        </div>
        <div class="col-4">
            <small class="text-muted d-block">Tiempo Total</small>
            <span id="det_tiempo" class="badge bg-primary"></span>
        </div>
    </div>
</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function mostrarDetalleCompleto(data) {
    // Datos básicos
    document.getElementById('det_casa').innerText = "Casa " + data.numero_casa;
    document.getElementById('det_nombre').innerText = data.nombre_arrendatario;
    document.getElementById('det_motivo').innerText = data.motivo;
    document.getElementById('det_garantia').innerText = "$" + new Intl.NumberFormat('es-CL').format(data.monto_garantia_devuelto);
    document.getElementById('det_observaciones').innerText = data.detalle_entrega || "Sin observaciones.";
    document.getElementById('det_admin').innerText = data.admin_nombre || "N/A";

    // NUEVO: Fechas y Periodo
    const fInicio = new Date(data.fecha_inicio).toLocaleDateString('es-CL');
    const fCierre = new Date(data.fecha_cierre).toLocaleDateString('es-CL');
    
    document.getElementById('det_inicio').innerText = fInicio;
    document.getElementById('det_fin').innerText = fCierre;
    document.getElementById('det_tiempo').innerText = data.meses_estancia + " Meses";

    var modal = new bootstrap.Modal(document.getElementById('modalDetalleCierre'));
    modal.show();
}
    </script>
</body>
</html>