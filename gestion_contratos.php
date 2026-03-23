<?php
session_start();
if (!isset($_SESSION['id_admin'])) {
    header("Location: index.php");
    exit();
}
require_once 'includes/conexion.php';

// 1. Casas disponibles para el select 🏠
$stmt_casas = $pdo->query("SELECT id_casa, numero_casa FROM casas WHERE activo = 1 AND estado = 'Disponible' ORDER BY numero_casa ASC");
$casas_disponibles = $stmt_casas->fetchAll();

// 2. Arrendatarios para el select 👤
$stmt_arr = $pdo->query("SELECT id_arrendatario, nombre FROM arrendatarios WHERE activo = 1 ORDER BY nombre ASC");
$arrendatarios_lista = $stmt_arr->fetchAll();

// 3. Listado de contratos para la tabla (Usamos JOIN para traer nombres) 📜
$sql_tabla = "SELECT con.*, cas.numero_casa, arr.nombre as nombre_arrendatario 
              FROM contratos con
              JOIN casas cas ON con.id_casa = cas.id_casa
              JOIN arrendatarios arr ON con.id_arrendatario = arr.id_arrendatario
              WHERE con.activo = 1 
              ORDER BY con.fecha_inicio DESC";
$contratos = $pdo->query($sql_tabla)->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Contratos - CondoPro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <?php include 'includes/menu.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-secondary">📜 Gestión de Contratos</h2>
            <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalContrato">
                <i class="bi bi-plus-lg"></i> Nuevo Contrato
            </button>
        </div>

        <div class="card shadow">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Casa</th>
                                <th>Arrendatario</th>
                                <th>Monto Mensual</th>
                                <th>Fecha Inicio</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($contratos as $c): ?>
                            <tr>
                                <td class="fw-bold">Casa <?php echo $c['numero_casa']; ?></td>
                                <td><?php echo htmlspecialchars($c['nombre_arrendatario']); ?></td>
                                <td>$<?php echo number_format($c['monto_fijo'], 0, ',', '.'); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($c['fecha_inicio'])); ?></td>
                                <td><span class="badge bg-success">Vigente</span></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-danger" title="Finalizar Contrato">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/modals/modal_contrato.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>