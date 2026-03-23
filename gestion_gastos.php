<?php
session_start();
include 'includes/conexion.php';

// Obtener lista de casas para el selector
//$casas = $pdo->query("SELECT id_casa, numero_casa FROM casas ORDER BY numero_casa ASC")->fetchAll();
// Consulta mejorada: Trae el número de casa y el nombre del arrendatario
// Consulta usando la columna 'activo' que vimos en tu imagen
$sql_casas = "SELECT c.id_casa, c.numero_casa, arr.nombre AS inquilino
              FROM casas c 
              INNER JOIN contratos con ON c.id_casa = con.id_casa 
              INNER JOIN arrendatarios arr ON con.id_arrendatario = arr.id_arrendatario
              WHERE con.activo = 1 
              ORDER BY c.numero_casa ASC";
$casas = $pdo->query($sql_casas)->fetchAll();

// Obtener los últimos 10 gastos variables cargados para control visual
$ultimos = $pdo->query("SELECT dc.*, c.numero_casa 
                        FROM detalle_cobros dc 
                        JOIN contratos con ON dc.id_contrato = con.id_contrato
                        JOIN casas c ON con.id_casa = c.id_casa
                        WHERE dc.concepto NOT LIKE 'Arriendo%' 
                        ORDER BY dc.id_detalle DESC LIMIT 10")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Gestión de Gastos - CondoPro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>

<body class="bg-light">

    <?php include 'includes/menu.php'; ?>

    <div class="container mt-4">
        <?php if (isset($_GET['status']) && $_GET['status'] == 'ok'): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> <strong>¡Gasto Cargado!</strong> El cobro se registró correctamente y aparecerá en recaudación.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-5">
                <div class="card shadow border-0">
                    <div class="card-header bg-primary text-white py-3">
                        <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Cargar Gasto Variable</h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="modulos/guardar_gasto_variable.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Seleccionar Propiedad:</label>
                                <select name="id_casa" class="form-select border-primary" required>
                                    <option value="">-- Seleccione Casa Arrendada --</option>
                                    <?php foreach ($casas as $casa): ?>
                                        <option value="<?php echo $casa['id_casa']; ?>">
                                            Casa #<?php echo $casa['numero_casa']; ?> - <?php echo $casa['inquilino']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Concepto:</label>
                                <input type="text" name="concepto" class="form-control" placeholder="Ej: Consumo Agua Septiembre" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Monto ($):</label>
                                    <input type="number" name="monto" class="form-control text-end" placeholder="0" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Mes/Periodo:</label>
                                    <input type="date" name="periodo" class="form-control" value="<?php echo date('Y-m-01'); ?>" required>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 fw-bold shadow">
                                <i class="bi bi-save"></i> GUARDAR COBRO
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-7">
                <div class="card shadow border-0">
                    <div class="card-header bg-dark text-white py-3">
                        <h5 class="mb-0"><i class="bi bi-clock-history"></i> Últimos Cargos Realizados</h5>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Casa</th>
                                    <th>Concepto</th>
                                    <th>Monto</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($ultimos)): ?>
                                    <?php foreach ($ultimos as $u): ?>
                                        <tr>
                                            <td><strong>#<?php echo $u['numero_casa']; ?></strong></td>
                                            <td><?php echo $u['concepto']; ?></td>
                                            <td class="fw-bold text-primary">$<?php echo number_format($u['monto_total'], 0, ',', '.'); ?></td>
                                            <td>
                                                <?php if ($u['pagado']): ?>
                                                    <span class="badge bg-success">Pagado</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning text-dark">Pendiente</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">No hay gastos variables cargados aún.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>