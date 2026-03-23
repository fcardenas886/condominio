<?php
session_start();
include 'includes/conexion.php';

// 1. Capturar fechas de filtro (por defecto hoy)
$fecha_inicio = $_GET['desde'] ?? date('Y-m-d');
$fecha_fin = $_GET['hasta'] ?? date('Y-m-d');

// 2. Consulta filtrada a la VISTA
$sql = "SELECT * FROM vista_historial_pagos 
        WHERE DATE(fecha_pago) BETWEEN ? AND ? 
        ORDER BY fecha_pago DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$fecha_inicio, $fecha_fin]);
$pagos = $stmt->fetchAll();

// 3. Calcular Total Recaudado (Sin contar 'Saldo a Favor' para no duplicar dinero real)
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
</head>
<body class="bg-light">

<?php include 'includes/menu.php'; ?>

<div class="container mt-4">
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body bg-white border">
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
                    <button type="submit" class="btn btn-primary w-100 shadow-sm">
                        <i class="bi bi-funnel"></i> Filtrar Historial
                    </button>
                </div>
                <div class="col-md-3 text-end">
                    <div class="p-2 bg-success text-white rounded shadow-sm">
                        <small class="d-block">Recaudación Real:</small>
                        <strong class="fs-5">$<?php echo number_format($total_recaudado, 0, ',', '.'); ?></strong>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow border-0">
        <div class="card-header bg-dark text-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-journal-text"></i> Movimientos del Periodo</h5>
            <button onclick="window.print()" class="btn btn-outline-light btn-sm">
                <i class="bi bi-printer"></i> Imprimir Reporte
            </button>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Fecha</th>
                        <th>Propiedad</th>
                        <th>Inquilino</th>
                        <th>Concepto</th>
                        <th class="text-end">Monto</th>
                        <th>Método</th>
                        <th>Notas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($pagos)): ?>
                        <tr><td colspan="7" class="text-center py-5 text-muted">No hay movimientos en este rango de fechas.</td></tr>
                    <?php else: ?>
                        <?php foreach($pagos as $p): ?>
                        <tr>
                            <td class="small"><?php echo date('d/m/H:i', strtotime($p['fecha_pago'])); ?></td>
                            <td><span class="badge bg-secondary">Casa #<?php echo $p['numero_casa']; ?></span></td>
                            <td class="fw-bold"><?php echo $p['inquilino']; ?></td>
                            <td>
                                <div class="small fw-bold"><?php echo $p['concepto']; ?></div>
                            </td>
                            <td class="fw-bold text-end text-primary">$<?php echo number_format($p['monto_pagado'], 0, ',', '.'); ?></td>
                            <td>
                                <?php 
                                $bg = 'bg-secondary';
                                if($p['metodo_pago'] == 'Transferencia') $bg = 'bg-primary';
                                if($p['metodo_pago'] == 'Efectivo') $bg = 'bg-success';
                                if($p['metodo_pago'] == 'Saldo a Favor') $bg = 'bg-info text-dark';
                                ?>
                                <span class="badge <?php echo $bg; ?>"><?php echo $p['metodo_pago']; ?></span>
                            </td>
                            <td class="small text-muted fst-italic"><?php echo $p['notas']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>