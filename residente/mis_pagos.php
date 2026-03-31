<?php
session_start();
require_once '../includes/conexion.php'; // Salimos de /residente/ para buscar la conexión

// 1. SEGURIDAD: Solo arrendatarios autorizados
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'arrendatario') {
    header("Location: ../index.php");
    exit();
}

$id_contrato = isset($_GET['id']) ? intval($_GET['id']) : 0;
$id_arrendatario = $_SESSION['id_entidad']; // ID del residente vinculado

// 2. VALIDACIÓN: Verificar que el contrato pertenece al usuario logueado
$stmt_check = $pdo->prepare("SELECT id_contrato FROM contratos WHERE id_contrato = ? AND id_arrendatario = ?");
$stmt_check->execute([$id_contrato, $id_arrendatario]);
if (!$stmt_check->fetch()) {
    die("Acceso denegado: No tienes permiso para ver esta propiedad.");
}

// 3. CONSULTA: Desglose de deudas y pagos realizados
$sql = "SELECT 
            dc.id_detalle,
            dc.periodo, 
            dc.concepto, 
            dc.monto_total, 
            dc.monto_pagado_acumulado, 
            dc.pagado,
            (dc.monto_total - dc.monto_pagado_acumulado) AS saldo_fila,
            p.id_transaccion,
            p.token
        FROM detalle_cobros dc
        LEFT JOIN pagos p ON dc.id_detalle = p.id_detalle
        WHERE dc.id_contrato = ? 
        ORDER BY dc.periodo DESC, p.id_pago DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$id_contrato]);
$cobros = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de Pagos - GeSTIDom</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="panel_residente.php" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Volver al Panel
            </a>
            <h4 class="fw-bold">Detalle de Cobros y Pagos</h4>
        </div>

        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Periodo / Concepto</th>
                            <th class="text-end">Monto Cobro</th>
                            <th class="text-end">Pagado</th>
                            <th class="text-end text-warning">Saldo</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Recibo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cobros as $c): ?>
                        <tr>
                            <td>
                                <span class="fw-bold d-block"><?php echo htmlspecialchars($c['concepto']); ?></span>
                                <small class="text-muted"><?php echo date('m / Y', strtotime($c['periodo'])); ?></small>
                            </td>
                            <td class="text-end">$<?php echo number_format($c['monto_total'], 0, ',', '.'); ?></td>
                            <td class="text-end text-success">$<?php echo number_format($c['monto_pagado_acumulado'], 0, ',', '.'); ?></td>
                            <td class="text-end text-danger fw-bold">$<?php echo number_format($c['saldo_fila'], 0, ',', '.'); ?></td>
                            <td class="text-center">
                                <?php if ($c['pagado']): ?>
                                    <span class="badge bg-success">PAGADO</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">PENDIENTE</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($c['monto_pagado_acumulado'] > 0 && $c['id_transaccion']): ?>
                                    <a href="../reportes/generar_pdf.php?transaccion=<?php echo $c['id_transaccion']; ?>&token=<?php echo $c['token']; ?>" 
                                       target="_blank" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-file-pdf"></i> PDF
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>