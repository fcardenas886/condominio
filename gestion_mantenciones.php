<?php
session_start();
include 'includes/conexion.php';

// 1. Obtener proveedores activos
$proveedores = $pdo->query("SELECT id_proveedor, nombre FROM proveedores WHERE estado = 1 ORDER BY nombre ASC")->fetchAll();

// 2. Obtener casas para el selector (ID 0 se reserva para General en la lógica del modal)
// Busca donde obtienes las casas y cámbialo por esto:
// Busca donde obtienes las casas y cámbialo por esto:
$sql_casas = "SELECT c.id_casa, c.numero_casa, 
             (SELECT COUNT(*) FROM contratos con WHERE con.id_casa = c.id_casa AND con.activo = 1) as tiene_contrato
             FROM casas c 
             ORDER BY CAST(c.numero_casa AS UNSIGNED) ASC";
$casas = $pdo->query($sql_casas)->fetchAll();

// 3. Consulta de mantenciones unida con proveedores
$sql = "SELECT m.*, p.nombre AS proveedor_nombre 
        FROM mantenciones m
        LEFT JOIN proveedores p ON m.id_proveedor = p.id_proveedor
        ORDER BY m.fecha DESC";
$mantenciones = $pdo->query($sql)->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Mantenciones - CondoPro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .badge-cobro { font-size: 0.7rem; padding: 0.4em 0.6em; }
        .table-hover tbody tr:hover { background-color: #f8f9fa; }
        .btn-group-gradient { display: inline-flex; border-radius: 8px; overflow: hidden; border: 1px solid #e0e0e0; }
        .btn-grad { border: none; padding: 8px 12px; background-color: white; transition: all 0.3s ease; cursor: pointer; }
        .btn-grad i { font-size: 1.1rem; background-clip: text; -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .btn-grad-edit { border-right: 1px solid #e0e0e0; }
        .btn-grad-edit i { background-image: linear-gradient(135deg, #ff9a1e 0%, #ffc82c 100%); }
        .btn-grad-delete i { background-image: linear-gradient(135deg, #f01e2c 0%, #ff6b8a 100%); }
    </style>
</head>
<body class="bg-light">

    <?php include 'includes/menu.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm border-top border-primary border-4">
            <div>
                <h3 class="fw-bold text-dark mb-0">Control de Mantenciones</h3>
                <p class="text-muted mb-0 small">Registro de reparaciones y servicios</p>
            </div>
            <button class="btn btn-primary px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNuevaMantencion">
                <i class="bi bi-plus-lg me-1"></i> Nueva Mantención
            </button>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-center">
                        <thead class="table-light border-bottom">
                            <tr>
                                <th class="ps-4 text-start py-3">Fecha / Ubicación</th>
                                <th class="text-start">Descripción</th>
                                <th>Proveedor</th>
                                <th>Monto</th>
                                <th>Cobro</th>
                                <th class="pe-4">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($mantenciones as $m): ?>
                            <tr>
                                <td class="ps-4 text-start">
                                    <div class="fw-bold small"><?php echo date("d/m/Y", strtotime($m['fecha'])); ?></div>
                                    <?php if ($m['id_casa'] == 0 || empty($m['id_casa'])): ?>
                                        <span class="badge bg-primary-subtle text-primary border border-primary" style="font-size:0.65rem;">🏢 GENERAL</span>
                                    <?php else: ?>
                                        <span class="badge bg-dark" style="font-size:0.65rem;">CASA #<?php echo $m['id_casa']; ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-start">
                                    <small class="text-secondary"><?php echo htmlspecialchars($m['descripcion']); ?></small>
                                </td>
                                <td><span class="small fw-bold"><?php echo htmlspecialchars($m['proveedor_nombre'] ?? 'N/A'); ?></span></td>
                                <td class="fw-bold text-success">$<?php echo number_format($m['monto_pagado'], 0, ',', '.'); ?></td>
                                <td>
                                    <?php if ($m['id_casa'] != 0): ?>
                                        <span class="badge rounded-pill <?php echo ($m['cobrar_a_arrendatario'] == 1) ? 'bg-danger-subtle text-danger border-danger' : 'bg-success-subtle text-success border-success'; ?> border badge-cobro">
                                            <?php echo ($m['cobrar_a_arrendatario'] == 1) ? 'Arrendatario' : 'Propietario'; ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="btn-group-gradient">
                                        <button class="btn-grad btn-grad-edit"><i class="bi bi-pencil-fill"></i></button>
                                        <button class="btn-grad btn-grad-delete"><i class="bi bi-trash3-fill"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/modals/modal_nueva_mantencion.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>