<?php
session_start();
include 'includes/conexion.php';

$f_mes = $_GET['mes'] ?? date('m');
$f_anio = $_GET['anio'] ?? date('Y');

$sql = "SELECT * FROM vista_reporte_egresos WHERE mes = ? AND anio = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$f_mes, $f_anio]);
$egresos = $stmt->fetchAll();

$total_egresos = 0;
$total_recuperable = 0;

foreach($egresos as $e) {
    $total_egresos += $e['monto_pagado'];
    if($e['cobrar_a_arrendatario'] == 1) {
        $total_recuperable += $e['monto_pagado'];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Egresos - GestiDom</title>
    <base href="http://localhost/condominio/">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<?php include 'includes/menu.php'; ?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h3 class="fw-bold text-danger"><i class="bi bi-cart-dash"></i> Reporte de Egresos / Gastos</h3>
        </div>
        <div class="col-md-6 text-end">
            <form method="GET" class="d-flex justify-content-end gap-2">
                <select name="mes" class="form-select w-auto shadow-sm">
                    <?php 
                    $meses = [1=>'Ene', 2=>'Feb', 3=>'Mar', 4=>'Abr', 5=>'May', 6=>'Jun', 7=>'Jul', 8=>'Ago', 9=>'Sep', 10=>'Oct', 11=>'Nov', 12=>'Dic'];
                    foreach($meses as $n => $m) echo "<option value='$n' ".($f_mes==$n?'selected':'').">$m</option>";
                    ?>
                </select>
                <select name="anio" class="form-select w-auto shadow-sm">
                    <option value="2026" selected>2026</option>
                </select>
                <button class="btn btn-danger shadow-sm"><i class="bi bi-search"></i></button>
            </form>
        </div>
    </div>

    <div class="row mb-4 g-3">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm p-3 border-start border-danger border-5">
                <small class="text-muted fw-bold">TOTAL GASTOS DEL MES</small>
                <h2 class="fw-bold text-danger mb-0">$<?php echo number_format($total_egresos, 0, ',', '.'); ?></h2>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm p-3 border-start border-info border-5">
                <small class="text-muted fw-bold text-uppercase">Gastos a Cobrar a Inquilinos</small>
                <h2 class="fw-bold text-info mb-0">$<?php echo number_format($total_recuperable, 0, ',', '.'); ?></h2>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Fecha</th>
                        <th>Casa</th>
                        <th>Proveedor</th>
                        <th>Descripción</th>
                        <th class="text-center">¿Cobrar a Inquilino?</th>
                        <th class="text-end">Monto</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($egresos)): ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">No hay egresos registrados en este periodo.</td></tr>
                    <?php else: ?>
                        <?php foreach($egresos as $e): ?>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime($e['fecha'])); ?></td>
                            <td><span class="badge bg-secondary">#<?php echo $e['numero_casa'] ?? 'General'; ?></span></td>
                            <td class="fw-bold"><?php echo $e['proveedor'] ?? 'S/P'; ?></td>
                            <td><small class="text-muted"><?php echo $e['descripcion']; ?></small></td>
                            <td class="text-center">
                                <?php if($e['cobrar_a_arrendatario']): ?>
                                    <span class="badge bg-warning text-dark"><i class="bi bi-person-check"></i> Sí</span>
                                <?php else: ?>
                                    <span class="badge bg-light text-muted">No</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end fw-bold text-danger">$<?php echo number_format($e['monto_pagado'], 0, ',', '.'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>