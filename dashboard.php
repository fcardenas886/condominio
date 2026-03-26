<?php
session_start();

if (!isset($_SESSION['id_admin'])) {
    header("Location: index.php");
    exit();
}

require_once 'includes/conexion.php';

// --- DATOS OPERATIVOS ---
$totalCasas = $pdo->query("SELECT COUNT(*) FROM casas")->fetchColumn();
$totalDisponibles = $pdo->query("SELECT COUNT(*) FROM casas WHERE estado = 'Disponible'")->fetchColumn();
$totalOcupadas = $pdo->query("SELECT COUNT(*) FROM casas WHERE estado = 'Ocupada'")->fetchColumn();

// --- DATOS FINANCIEROS (Usando tus nuevas vistas) ---

// 1. Recaudación del mes actual
$mes_actual = date('m');
$anio_actual = date('Y');
$stmt_ing = $pdo->prepare("SELECT SUM(monto_pagado) FROM vista_reporte_ingresos_completo WHERE mes = ? AND anio = ? AND metodo_pago != 'Saldo a Favor'");
$stmt_ing->execute([$mes_actual, $anio_actual]);
$recaudacionMes = $stmt_ing->fetchColumn() ?: 0;

// 2. Morosidad Total Crítica
$totalMoroso = $pdo->query("SELECT SUM(saldo_pendiente) FROM vista_reporte_morosidad")->fetchColumn() ?: 0;

$nombreAdmin = $_SESSION['nombre_admin'] ?? 'Admin';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - GestiDom</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .card-stat { border: none; border-radius: 15px; transition: transform 0.3s; }
        .card-stat:hover { transform: translateY(-5px); }
        .icon-box { font-size: 2rem; opacity: 0.3; position: absolute; right: 15px; top: 15px; }
    </style>
</head>
<body class="bg-light">

    <?php include 'includes/menu.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Resumen General</h2>
            <span class="badge bg-white text-dark shadow-sm p-2 text-uppercase">Período: <?php echo date('F Y'); ?></span>
        </div>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card card-stat bg-primary text-white shadow-sm p-3">
                    <div class="icon-box"><i class="bi bi-house-door"></i></div>
                    <small class="fw-bold">TOTAL PROPIEDADES</small>
                    <h2 class="display-6 fw-bold"><?php echo $totalCasas; ?></h2>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-stat bg-success text-white shadow-sm p-3">
                    <div class="icon-box"><i class="bi bi-check-circle"></i></div>
                    <small class="fw-bold">CASAS DISPONIBLES</small>
                    <h2 class="display-6 fw-bold"><?php echo $totalDisponibles; ?></h2>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-stat bg-dark text-white shadow-sm p-3">
                    <div class="icon-box"><i class="bi bi-people"></i></div>
                    <small class="fw-bold">UNIDADES OCUPADAS</small>
                    <h2 class="display-6 fw-bold"><?php echo $totalOcupadas; ?></h2>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card card-stat bg-white shadow-sm p-4 border-start border-primary border-5">
                    <div class="d-flex justify-content-between">
                        <div>
                            <small class="text-muted fw-bold text-uppercase">Recaudación Real (Mes)</small>
                            <h2 class="fw-bold text-primary mt-1">$<?php echo number_format($recaudacionMes, 0, ',', '.'); ?></h2>
                        </div>
                        <div class="text-primary fs-1"><i class="bi bi-cash-coin"></i></div>
                    </div>
                    <hr>
                    <a href="reportes/reporte_ingresos.php" class="small text-decoration-none">Ver detalle de ingresos →</a>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-stat bg-white shadow-sm p-4 border-start border-danger border-5">
                    <div class="d-flex justify-content-between">
                        <div>
                            <small class="text-muted fw-bold text-uppercase">Deuda Total Pendiente</small>
                            <h2 class="fw-bold text-danger mt-1">$<?php echo number_format($totalMoroso, 0, ',', '.'); ?></h2>
                        </div>
                        <div class="text-danger fs-1"><i class="bi bi-exclamation-octagon"></i></div>
                    </div>
                    <hr>
                    <a href="reportes/reporte_morosidad.php" class="small text-danger text-decoration-none fw-bold">Gestionar cobranza ahora →</a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm p-3">
                    <h6 class="fw-bold mb-3 small text-muted">ACCIONES RÁPIDAS</h6>
                    <div class="d-flex gap-2">
                        <a href="recaudacion.php" class="btn btn-outline-primary"><i class="bi bi-plus-circle"></i> Nuevo Pago</a>
                        <a href="reportes/balance_general.php" class="btn btn-outline-dark"><i class="bi bi-bar-chart"></i> Ver Balance</a>
                        <a href="casas.php" class="btn btn-outline-secondary"><i class="bi bi-gear"></i> Gestionar Unidades</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>