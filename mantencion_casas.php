<?php
session_start();
if (!isset($_SESSION['id_admin'])) {
    header("Location: index.php");
    exit();
}
require_once 'includes/conexion.php';

// CONSULTA PARA OBTENER TODAS LAS CASAS
$stmt = $pdo->query("SELECT * FROM casas WHERE activo = 1 ORDER BY numero_casa ASC");
$casas = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Administración de Casas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>

<body class="bg-light">
    <?php include 'includes/menu.php'; ?>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <?php
            // Personalizamos el mensaje según el código
            if ($_GET['success'] == '1') {
                echo "🏠 ¡Nueva casa registrada con éxito!";
            } elseif ($_GET['success'] == '2') {
                echo "✏️ ¡Datos de la casa actualizados correctamente!";
            } elseif ($_GET['success'] == '3') {
                echo "🗑️ ¡Casa eliminada correctamente!";
            }
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <?php
            if ($_GET['error'] == '1') {
                echo "❌ No se encontró la casa a eliminar.";
            } elseif ($_GET['error'] == '2') {
                echo "❌ Error en la base de datos. Contacte al administrador.";
            }
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="container mt-4">
        <div class="row justify-content-center mb-5">
            <div class="col-md-8">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="text-secondary">🏠 Gestión de Casas</h2>

                    <button type="button"
                        class="btn btn-primary shadow-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#modalCasa"
                        data-accion="crear">
                        <i class="bi bi-plus-lg"></i> Nueva Casa
                    </button>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                        <h4 class="card-title text-secondary">📋 Listado de Casas</h4>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                                    <input type="text" class="form-control border-start-0" placeholder="Buscar casa...">
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover table-striped align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Nº Casa</th>
                                        <th>Estado</th>
                                        <th>Descripción</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($casas as $casa):
                                        // Lógica de colores para el Estado (Badges)
                                        $badgeClass = 'bg-secondary'; // Por defecto (Mantenimiento)
                                        if ($casa['estado'] === 'Disponible') $badgeClass = 'bg-success';
                                        if ($casa['estado'] === 'Ocupada') $badgeClass = 'bg-danger';
                                    ?>
                                        <tr>
                                            <td class="fw-bold"><?php echo htmlspecialchars($casa['numero_casa']); ?></td>
                                            <td>
                                                <span class="badge <?php echo $badgeClass; ?> rounded-pill px-3 py-2">
                                                    <?php echo htmlspecialchars($casa['estado']); ?>
                                                </span>
                                            </td>
                                            <td class="text-muted small"><?php echo htmlspecialchars($casa['descripcion']); ?></td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm">
                                                    <a href="#" class="btn btn-outline-primary" title="Ver Detalle"><i class="bi bi-eye"></i></a>
                                                    <!-- <a href="#" class="btn btn-outline-warning" title="Editar"><i class="bi bi-pencil"></i></a> -->
                                                    <!-- Editar solo si no está ocupada -->
                                                    <button type="button"
                                                        class="btn btn-outline-warning btn-sm"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#modalCasa"
                                                        data-id="<?php echo $casa['id_casa']; ?>"
                                                        data-numero="<?php echo $casa['numero_casa']; ?>"
                                                        data-descripcion="<?php echo $casa['descripcion']; ?>"
                                                        data-estado="<?php echo $casa['estado']; ?>"
                                                        data-accion="editar"
                                                        title="Editar">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <!-- Eliminar solo si no está ocupada -->
                                                    <button type="button"
                                                        class="btn btn-outline-danger btn-sm"
                                                        onclick="confirmarEliminar('<?php echo htmlspecialchars($casa['id_casa']); ?>')"
                                                        title="Eliminar">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
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
        </div>
    </div>


    <?php include 'includes/modals/modal_casa.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/casas.js"></script>
</body>

</html>