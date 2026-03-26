<?php
session_start();
include 'includes/conexion.php';

// 1. Filtros de periodo (Mes y Año actual por defecto)
$f_mes = $_GET['mes'] ?? date('m');
$f_anio = $_GET['anio'] ?? date('Y');

// 2. Obtener Total Ingresos (Solo flujo real del mes seleccionado)
$sql_ingresos = "SELECT SUM(monto_pagado) as total_ingresos 
                 FROM vista_reporte_ingresos_completo 
                 WHERE mes = ? AND anio = ? AND metodo_pago != 'Saldo a Favor'";
$stmt_in = $pdo->prepare($sql_ingresos);
$stmt_in->execute([$f_mes, $f_anio]);
$res_in = $stmt_in->fetch();
$total_ingresos = $res_in['total_ingresos'] ?? 0;

// 3. Obtener Total Egresos (Mantenciones del mes seleccionado)
$sql_egresos = "SELECT SUM(monto_pagado) as total_egresos 
                FROM vista_reporte_egresos 
                WHERE mes = ? AND anio = ?";
$stmt_eg = $pdo->prepare($sql_egresos);
$stmt_eg->execute([$f_mes, $f_anio]);
$res_eg = $stmt_eg->fetch();
$total_egresos = $res_eg['total_egresos'] ?? 0;

// 4. Cálculos de Utilidad
$utilidad_neta = $total_ingresos - $total_egresos;
$margen = ($total_ingresos > 0) ? ($utilidad_neta / $total_ingresos) * 100 : 0;

// 5. Datos para el Gráfico Histórico (Últimos 6 meses)
$sql_grafico = "SELECT 
                    anio, mes, 
                    SUM(monto_flujo_real) as ingresos,
                    (SELECT SUM(monto_pagado) FROM vista_reporte_egresos e WHERE e.mes = v.mes AND e.anio = v.anio) as egresos
                FROM vista_reporte_ingresos_completo v
                GROUP BY anio, mes
                ORDER BY anio DESC, mes DESC
                LIMIT 6";
$stmt_g = $pdo->query($sql_grafico);
$historico = array_reverse($stmt_g->fetchAll());

$labels = []; $data_in = []; $data_eg = [];
$meses_nom = [1=>'Ene', 2=>'Feb', 3=>'Mar', 4=>'Abr', 5=>'May', 6=>'Jun', 7=>'Jul', 8=>'Ago', 9=>'Sep', 10=>'Oct', 11=>'Nov', 12=>'Dic'];

foreach($historico as $h) {
    $labels[] = $meses_nom[$h['mes']] . " " . $h['anio'];
    $data_in[] = $h['ingresos'] ?? 0;
    $data_eg[] = $h['egresos'] ?? 0;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Balance General - GestiDom</title>
    <base href="http://localhost/condominio/">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .card { border-radius: 12px; }
        .progress { border-radius: 10px; }
    </style>
</head>
<body class="bg-light">

<?php include 'includes/menu.php'; ?>

<div class="container mt-4 pb-5">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h3 class="fw-bold"><i class="bi bi-pie-chart-fill text-primary"></i> Balance de Rendimiento</h3>
        </div>
        <div class="col-md-6">
            <form method="GET" class="d-flex justify-content-end gap-2">
                <select name="mes" class="form-select w-auto shadow-sm">
                    <?php foreach($meses_nom as $n => $m) echo "<option value='$n' ".($f_mes==$n?'selected':'').">$m</option>"; ?>
                </select>
                <select name="anio" class="form-select w-auto shadow-sm">
                    <option value="2026" selected>2026</option>
                </select>
                <button class="btn btn-primary shadow-sm">Ver Periodo</button>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 border-start border-success border-5">
                <small class="text-muted fw-bold">INGRESOS REALES</small>
                <h2 class="text-success fw-bold">$<?php echo number_format($total_ingresos, 0, ',', '.'); ?></h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 border-start border-danger border-5">
                <small class="text-muted fw-bold">GASTOS Y MANTENCIÓN</small>
                <h2 class="text-danger fw-bold">$<?php echo number_format($total_egresos, 0, ',', '.'); ?></h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 border-start border-primary border-5 bg-white">
                <small class="text-muted fw-bold">UTILIDAD NETA (MARGEN <?php echo number_format($margen, 0); ?>%)</small>
                <h2 class="text-primary fw-bold">$<?php echo number_format($utilidad_neta, 0, ',', '.'); ?></h2>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold py-3">Comparativa Semestral</div>
                <div class="card-body">
                    <canvas id="graficoBalance" style="max-height: 350px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white fw-bold">Rendimiento del Mes Seleccionado</div>
        <div class="card-body text-center">
            <?php 
            $p_gastos = ($total_ingresos > 0) ? ($total_egresos / $total_ingresos) * 100 : 0;
            $p_utilidad = 100 - $p_gastos;
            if($p_utilidad < 0) $p_utilidad = 0; 
            ?>
            <div class="progress mb-2" style="height: 30px;">
                <div class="progress-bar bg-danger" style="width: <?php echo $p_gastos; ?>%">Gastos</div>
                <div class="progress-bar bg-success" style="width: <?php echo $p_utilidad; ?>%">Utilidad</div>
            </div>
            <small class="text-muted">El <?php echo number_format($p_gastos, 1); ?>% de los ingresos se destinó a gastos de mantención.</small>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const ctx = document.getElementById('graficoBalance').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($labels); ?>,
        datasets: [
            {
                label: 'Ingresos',
                data: <?php echo json_encode($data_in); ?>,
                backgroundColor: '#198754',
                borderRadius: 5
            },
            {
                label: 'Egresos',
                data: <?php echo json_encode($data_eg); ?>,
                backgroundColor: '#dc3545',
                borderRadius: 5
            }
        ]
    },
    options: {
        responsive: true,
        scales: { y: { beginAtZero: true } }
    }
});
</script>

</body>
</html>