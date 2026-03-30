<?php
session_start();
if (!isset($_SESSION['id_admin'])) { header("Location: index.php"); exit(); }
require_once 'includes/conexion.php';

// Filtros
$desde = $_GET['desde'] ?? date('Y-m-01');
$hasta = $_GET['hasta'] ?? date('Y-m-t');
$estado = $_GET['estado'] ?? 'todos';

// 1. Datos para el Dashboard (Resumen rápido)
$totales = $pdo->query("SELECT 
    (SELECT COUNT(*) FROM contratos WHERE activo = 1) as activos,
    (SELECT COUNT(*) FROM contratos WHERE activo = 0) as finalizados,
    (SELECT SUM(monto_fijo) FROM contratos WHERE activo = 1) as renta_mensual
")->fetch();

// 2. Consulta Principal con Filtros
$sql = "SELECT 'Activo' as situacion, con.fecha_inicio, NULL as fecha_cierre, con.monto_fijo, cas.numero_casa, arr.nombre 
        FROM contratos con 
        JOIN casas cas ON con.id_casa = cas.id_casa 
        JOIN arrendatarios arr ON con.id_arrendatario = arr.id_arrendatario
        WHERE con.activo = 1 AND con.fecha_inicio BETWEEN ? AND ?
        UNION ALL
        SELECT 'Finalizado' as situacion, con.fecha_inicio, cie.fecha_cierre, con.monto_fijo, cas.numero_casa, arr.nombre 
        FROM cierres_contrato cie
        JOIN contratos con ON cie.id_contrato = con.id_contrato
        JOIN casas cas ON con.id_casa = cas.id_casa
        JOIN arrendatarios arr ON con.id_arrendatario = arr.id_arrendatario
        WHERE cie.fecha_cierre BETWEEN ? AND ?
        ORDER BY fecha_inicio DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$desde, $hasta, $desde, $hasta]);
$reporte = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Total - GeSTIDom</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <?php include 'includes/menu.php'; ?>

    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white shadow-sm">
                    <div class="card-body">
                        <h6>Contratos Activos</h6>
                        <h2><?php echo $totales['activos']; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-secondary text-white shadow-sm">
                    <div class="card-body">
                        <h6>Contratos Finalizados</h6>
                        <h2><?php echo $totales['finalizados']; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white shadow-sm">
                    <div class="card-body">
                        <h6>Recaudación Mensual Proyectada</h6>
                        <h2>$<?php echo number_format($totales['renta_mensual'], 0, ',', '.'); ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Desde</label>
                        <input type="date" name="desde" class="form-control" value="<?php echo $desde; ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Hasta</label>
                        <input type="date" name="hasta" class="form-control" value="<?php echo $hasta; ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-dark w-100"><i class="bi bi-filter"></i> Filtrar</button>
                    </div>
                    <div class="col-md-2">
                        <button type="button" onclick="window.print()" class="btn btn-outline-danger w-100"><i class="bi bi-file-pdf"></i> Exportar</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Situación</th>
                            <th>Casa</th>
                            <th>Arrendatario</th>
                            <th>Inicio</th>
                            <th>Cierre</th>
                            <th>Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reporte as $r): ?>
                        <tr>
                            <td>
                                <span class="badge <?php echo $r['situacion'] == 'Activo' ? 'bg-success' : 'bg-danger'; ?>">
                                    <?php echo $r['situacion']; ?>
                                </span>
                            </td>
                            <td>Casa <?php echo $r['numero_casa']; ?></td>
                            <td><?php echo $r['nombre']; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($r['fecha_inicio'])); ?></td>
                            <td><?php echo $r['fecha_cierre'] ? date('d/m/Y', strtotime($r['fecha_cierre'])) : '-'; ?></td>
                            <td class="fw-bold">$<?php echo number_format($r['monto_fijo'], 0, ',', '.'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>  
                </table>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>