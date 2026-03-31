<?php
session_start();
// Seguridad: Si no es arrendatario, lo mandamos al login
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'arrendatario') {
    header("Location: ../index.php");
    exit();
}

require_once '../includes/conexion.php';

$id_arrendatario = $_SESSION['id_entidad']; 

// 1. CONSULTA MAESTRA: Buscamos contratos activos O contratos inactivos con deuda pendiente
// 1. CONSULTA MAESTRA CORREGIDA
// 1. CONSULTA MAESTRA MEJORADA (Historial Permanente)
$sql_casas = "SELECT DISTINCT 
                c.id_contrato, 
                ca.numero_casa, 
                ca.descripcion, 
                c.monto_fijo, 
                c.fecha_inicio,
                c.activo 
              FROM contratos c
              JOIN casas ca ON c.id_casa = ca.id_casa
              WHERE c.id_arrendatario = ? 
              ORDER BY c.activo DESC, c.fecha_inicio DESC"; 

$stmt_casas = $pdo->prepare($sql_casas);
$stmt_casas->execute([$id_arrendatario]);
$mis_propiedades = $stmt_casas->fetchAll();

// 2. Buscamos deudas pendientes reales (Monto total - lo que ya ha abonado)
$sql_deuda = "SELECT SUM(monto_total - monto_pagado_acumulado) as total_deuda 
              FROM detalle_cobros dc
              JOIN contratos c ON dc.id_contrato = c.id_contrato
              WHERE c.id_arrendatario = ? AND dc.pagado = 0";
$stmt_deuda = $pdo->prepare($sql_deuda);
$stmt_deuda->execute([$id_arrendatario]);
$deuda = $stmt_deuda->fetch();
$total_deuda = $deuda['total_deuda'] ?? 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Residente - GeSTIDom</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .card-residente { border-radius: 15px; border: none; transition: 0.3s; }
        .card-residente:hover { transform: translateY(-5px); }
        .bg-sti { background: linear-gradient(45deg, #0d6efd, #0dcaf0); color: white; }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 shadow">
    <div class="container">
        <a class="navbar-brand fw-bold" href="#">GeSTIDom <small class="fw-light">Residente</small></a>
        <div class="d-flex align-items-center">
            <span class="text-white me-3 d-none d-md-inline small">Hola, <?php echo explode(' ', $_SESSION['nombre_usuario'] ?? 'Usuario')[0]; ?></span>
            <a href="../logout.php" class="btn btn-outline-light btn-sm">Salir</a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-sti shadow-sm p-4 text-center">
                <h6 class="text-uppercase small mb-1" style="letter-spacing: 1px;">Saldo Total Pendiente</h6>
                <h1 class="display-4 fw-bold">$<?php echo number_format($total_deuda, 0, ',', '.'); ?></h1>
                <?php if($total_deuda > 0): ?>
                    <p class="mb-0"><span class="badge bg-danger shadow">Tienes pagos atrasados</span></p>
                    <div class="text-center mb-4">
    <button type="button" class="btn btn-success shadow-sm btn-lg" data-bs-toggle="modal" data-bs-target="#modalPago">
        <i class="bi bi-bank me-2"></i> Ver Datos para Pagar
    </button>
</div>
                <?php else: ?>
                    <p class="mb-0"><span class="badge bg-success shadow">Estás al día con tus pagos</span></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <h5 class="mb-3 text-secondary"><i class="bi bi-house-door"></i> Mis Propiedades (<?php echo count($mis_propiedades); ?>)</h5>
    
    <div class="row">
        <?php foreach ($mis_propiedades as $prop): ?>
        <div class="col-md-6 mb-3">
            <div class="card card-residente shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h4 class="card-title fw-bold mb-0">Casa <?php echo $prop['numero_casa']; ?></h4>
                            <p class="text-muted small"><?php echo $prop['descripcion'] ?: 'Sin descripción adicional'; ?></p>
                        </div>
                        <?php if ($prop['activo'] == 1): ?>
                            <i class="bi bi-house-check text-primary fs-2"></i>
                        <?php else: ?>
                            <span class="badge bg-secondary">Contrato Finalizado</span>
                        <?php endif; ?>
                    </div>
                    <hr>
                    <div class="row text-center mb-3">
                        <div class="col-6 border-end">
                            <small class="text-muted d-block">Arriendo Mensual</small>
                            <span class="fw-bold">$<?php echo number_format($prop['monto_fijo'], 0, ',', '.'); ?></span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Fecha Contrato</small>
                            <span class="fw-bold"><?php echo date('d/m/Y', strtotime($prop['fecha_inicio'])); ?></span>
                        </div>
                    </div>
                    <div class="d-grid gap-2">
                        <a href="mis_pagos.php?id=<?php echo $prop['id_contrato']; ?>" class="btn btn-primary shadow-sm">
                            <i class="bi bi-receipt"></i> Ver Historial de Pagos
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <?php if(count($mis_propiedades) == 0 && $total_deuda == 0): ?>
            <div class="col-12 text-center py-5 text-muted">
                <i class="bi bi-emoji-slight-smile fs-1"></i>
                <p class="mt-2">No tienes servicios activos ni deudas pendientes.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="modalPago" tabindex="-1" aria-labelledby="modalPagoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="modalPagoLabel text-uppercase">
                    <i class="bi bi-credit-card-2-front me-2"></i> Información de Pago
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-4">
                    <p class="text-muted small">Realiza tu transferencia electrónica a la siguiente cuenta:</p>
                </div>
                
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                        <span class="text-muted">Banco</span>
                        <strong class="text-dark">Banco de Chile</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                        <span class="text-muted">Tipo de Cuenta</span>
                        <strong class="text-dark">Cuenta Corriente</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                        <span class="text-muted">Número</span>
                        <strong class="text-dark">123456789</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                        <span class="text-muted">RUT</span>
                        <strong class="text-dark">12.345.678-9</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-3 border-bottom-0">
                        <span class="text-muted">Correo</span>
                        <strong class="text-primary">pagos@gestidom.cl</strong>
                    </li>
                </ul>

                <div class="alert alert-info mt-4 mb-0 small border-0">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    <strong>Importante:</strong> Al transferir, indica tu <strong>Casa N°</strong> en el mensaje y envía el comprobante al correo para validar tu pago.
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Entendido</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>