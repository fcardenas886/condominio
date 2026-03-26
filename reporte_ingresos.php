<?php
session_start();
include 'includes/conexion.php';

// 1. Datos para los filtros
$arrendatarios = $pdo->query("SELECT id_arrendatario, nombre FROM arrendatarios WHERE activo = 1 ORDER BY nombre")->fetchAll();

// 2. Captura de filtros (por defecto mes y año actual)
$f_mes = $_GET['mes'] ?? date('m');
$f_anio = $_GET['anio'] ?? date('Y');
$f_arr = $_GET['arrendatario'] ?? '';

// 3. Consulta con filtros
$sql = "SELECT * FROM vista_reporte_ingresos_completo WHERE mes = ? AND anio = ?";
$params = [$f_mes, $f_anio];

if ($f_arr != '') {
    $sql .= " AND id_arrendatario = ?";
    $params[] = $f_arr;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$datos = $stmt->fetchAll();

// 4. Lógica de Resumen
$resumen_metodos = [];
$caja_real = 0;
$uso_saldos = 0;

foreach ($datos as $d) {
    $metodo = $d['metodo_pago'];
    if (!isset($resumen_metodos[$metodo])) $resumen_metodos[$metodo] = 0;
    
    $resumen_metodos[$metodo] += $d['monto_pagado'];
    
    // Sumamos al flujo real solo si NO es saldo a favor
    if ($metodo == 'Saldo a Favor') {
        $uso_saldos += $d['monto_pagado'];
    } else {
        $caja_real += $d['monto_pagado'];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Ingresos Detallado - CondoPro</title>
    <base href="http://localhost/condominio/">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<?php include 'includes/menu.php'; ?>

<div class="container mt-4">
    <h3 class="fw-bold mb-4"><i class="bi bi-graph-up-arrow text-primary"></i> Reporte de Ingresos Detallado</h3>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-md-2">
                    <select name="anio" class="form-select shadow-sm">
                        <?php for($i=date('Y'); $i>=2025; $i--) echo "<option value='$i' ".($f_anio==$i?'selected':'').">$i</option>"; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="mes" class="form-select shadow-sm">
                        <?php 
                        $meses = [1=>'Enero', 2=>'Febrero', 3=>'Marzo', 4=>'Abril', 5=>'Mayo', 6=>'Junio', 7=>'Julio', 8=>'Agosto', 9=>'Septiembre', 10=>'Octubre', 11=>'Noviembre', 12=>'Diciembre'];
                        foreach($meses as $n => $m) echo "<option value='$n' ".($f_mes==$n?'selected':'').">$m</option>";
                        ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <select name="arrendatario" class="form-select shadow-sm">
                        <option value="">-- Todos los Arrendatarios --</option>
                        <?php foreach($arrendatarios as $a) echo "<option value='{$a['id_arrendatario']}' ".($f_arr==$a['id_arrendatario']?'selected':'').">{$a['nombre']}</option>"; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary w-100 shadow-sm"><i class="bi bi-funnel"></i> Filtrar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-primary text-white p-3 h-100">
                <small class="fw-bold opacity-75">CAJA REAL (EFECTIVO/TRANSF)</small>
                <h2 class="mb-0 fw-bold">$<?php echo number_format($caja_real, 0, ',', '.'); ?></h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-white p-3 h-100 border-start border-warning border-5">
                <small class="text-muted fw-bold">USO DE SALDOS A FAVOR</small>
                <h2 class="mb-0 fw-bold text-warning">$<?php echo number_format($uso_saldos, 0, ',', '.'); ?></h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-dark text-white p-3 h-100">
                <small class="fw-bold opacity-75">TOTAL CONTABLE</small>
                <h2 class="mb-0 fw-bold">$<?php echo number_format($caja_real + $uso_saldos, 0, ',', '.'); ?></h2>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white fw-bold py-3">Detalle de Pagos Recibidos</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th>
                            <th>Casa</th>
                            <th>Inquilino</th>
                            <th>Concepto</th>
                            <th>Método</th>
                            <th class="text-end">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($datos as $r): ?>
                        <tr>
                            <td><small><?php echo date('d/m/Y', strtotime($r['fecha_pago'])); ?></small></td>
                            <td><span class="badge bg-light text-dark border">#<?php echo $r['numero_casa']; ?></span></td>
                            <td class="fw-bold"><?php echo $r['inquilino']; ?></td>
                            <td><small class="text-muted"><?php echo $r['concepto']; ?></small></td>
                            <td>
                                <span class="badge <?php echo ($r['metodo_pago']=='Saldo a Favor'?'bg-warning text-dark':'bg-info text-dark'); ?>">
                                    <?php echo $r['metodo_pago']; ?>
                                </span>
                            </td>
                            <td class="text-end fw-bold">$<?php echo number_format($r['monto_pagado'], 0, ',', '.'); ?></td>
                        </tr>
                        <?php endforeach; ?>
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