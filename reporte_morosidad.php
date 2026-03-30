<?php
session_start();
// Salimos de la carpeta 'reportes' para buscar 'includes'
include 'includes/conexion.php'; 

// 1. Consultamos la vista de morosidad que creamos en la BD
$sql = "SELECT * FROM vista_reporte_morosidad";
$stmt = $pdo->query($sql);
$morosos = $stmt->fetchAll();

// 2. Calculamos el total de deuda para el cuadro informativo
$deuda_total_sistema = 0;
foreach($morosos as $m) {
    $deuda_total_sistema += $m['saldo_pendiente'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Morosidad - CondoPro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .card { border-radius: 15px; }
        .badge { font-size: 0.85rem; }
    </style>
</head>
<body class="bg-light">

<?php include 'includes/menu.php'; ?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h3 class="fw-bold"><i class="bi bi-exclamation-octagon-fill text-danger"></i> Reporte de Morosidad</h3>
            <p class="text-muted">Lista detallada de deudas pendientes por unidad.</p>
        </div>
        <div class="col-md-4 text-end">
            <div class="p-3 bg-white border-start border-danger border-5 shadow-sm rounded">
                <small class="text-muted fw-bold">TOTAL POR COBRAR</small>
                <h3 class="text-danger fw-bold mb-0">$<?php echo number_format($deuda_total_sistema, 0, ',', '.'); ?></h3>
            </div>
        </div>
    </div>

    <div class="card shadow border-0 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Casa</th>
                            <th>Inquilino</th>
                            <th>Concepto</th>
                            <th>Vencimiento</th>
                            <th class="text-center">Atraso</th>
                            <th class="text-end">Monto Pendiente</th>
                            <th class="text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($morosos)): ?>
                            <tr><td colspan="7" class="text-center py-5 text-muted">✅ No hay deudas registradas.</td></tr>
                        <?php else: ?>
                            <?php foreach($morosos as $m): ?>
                            <tr>
                                <td class="fw-bold">#<?php echo $m['numero_casa']; ?></td>
                                <td><?php echo $m['inquilino']; ?></td>
                                <td><small class="text-muted"><?php echo $m['concepto']; ?></small></td>
                                <td><?php echo date('d/m/Y', strtotime($m['periodo'])); ?></td>
                                <td class="text-center">
                                    <?php 
                                    // Semáforo de días
                                    $clase = 'bg-success';
                                    if($m['dias_atraso'] > 30) $clase = 'bg-danger';
                                    elseif($m['dias_atraso'] > 5) $clase = 'bg-warning text-dark';
                                    ?>
                                    <span class="badge <?php echo $clase; ?> rounded-pill px-3">
                                        <?php echo $m['dias_atraso']; ?> días
                                    </span>
                                </td>
                                <td class="fw-bold text-end text-danger">
                                    $<?php echo number_format($m['saldo_pendiente'], 0, ',', '.'); ?>
                                </td>
                                <td class="text-center">
                                    <a href="https://wa.me/<?php echo $m['telefono']; ?>?text=Hola%20<?php echo urlencode($m['inquilino']); ?>,%20le%20escribimos%20de%20CondoPro%20para%20recordarle%20su%20pago%20de%20$<?php echo number_format($m['saldo_pendiente']); ?>." 
                                       target="_blank" class="btn btn-success btn-sm shadow-sm">
                                        <i class="bi bi-whatsapp"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>