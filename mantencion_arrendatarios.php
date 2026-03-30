<?php
session_start();
if (!isset($_SESSION['id_admin'])) {
    header("Location: index.php");
    exit();
}
require_once 'includes/conexion.php';

// 1. CONSULTA ACTUALIZADA: Detectamos si el arrendatario tiene perfil de usuario asociado
$sql = "SELECT a.*, 
        (SELECT COUNT(*) FROM usuarios u WHERE u.id_entidad_asociada = a.id_arrendatario AND u.rol = 'user') as tiene_perfil 
        FROM arrendatarios a 
        WHERE a.activo = 1 
        ORDER BY a.nombre ASC";

$stmt = $pdo->query($sql);
$arrendatarios = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Administración de Arrendatarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>

<body class="bg-light">
    <?php include 'includes/menu.php'; ?>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <?php
            if ($_GET['success'] == '1') echo "👤 ¡Nuevo arrendatario registrado!";
            elseif ($_GET['success'] == '2') echo "✏️ ¡Datos actualizados!";
            elseif ($_GET['success'] == '3') echo "🗑️ ¡Arrendatario eliminado!";
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="container mt-4">
        <div class="row justify-content-center mb-5">
            <div class="col-md-8">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="text-secondary">👤 Gestión de Arrendatarios</h2>
                    <button type="button"
                        class="btn btn-primary shadow-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#modalArrendatario"
                        data-accion="crear">
                        <i class="bi bi-plus-lg"></i> Nuevo Arrendatario
                    </button>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                        <h4 class="card-title text-secondary">📋 Listado de Arrendatarios</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Nombre</th>
                                        <th>RUT</th>
                                        <th>Teléfono</th>
                                        <th>Correo</th>
                                        <th class="text-center">Perfil Web</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($arrendatarios as $a): ?>
                                        <tr>
                                            <td class="fw-bold"><?php echo htmlspecialchars($a['nombre']); ?></td>
                                            <td><?php echo htmlspecialchars($a['rut']); ?></td>
                                            <td><?php echo htmlspecialchars($a['telefono']); ?></td>
                                            <td><?php echo htmlspecialchars($a['correo']); ?></td>
                                            <td class="text-center">
                                                <?php if ($a['tiene_perfil'] > 0): ?>
                                                    <span class="badge bg-info text-dark"><i class="bi bi-check-circle"></i> Activo</span>
                                                <?php else: ?>
                                                    <span class="badge bg-light text-muted border">No habilitado</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button"
                                                        class="btn btn-outline-warning"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#modalArrendatario"
                                                        data-id="<?php echo $a['id_arrendatario']; ?>"
                                                        data-nombre="<?php echo htmlspecialchars($a['nombre']); ?>"
                                                        data-rut="<?php echo $a['rut']; ?>"
                                                        data-telefono="<?php echo $a['telefono']; ?>"
                                                        data-correo="<?php echo $a['correo']; ?>"
                                                        data-perfil="<?php echo $a['tiene_perfil']; ?>" 
                                                        data-accion="editar">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    
                                                    <button type="button"
                                                        class="btn btn-outline-danger"
                                                        onclick="confirmarEliminarArrendatario('<?php echo $a['id_arrendatario']; ?>')">
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

    <?php include 'includes/modals/modal_arrendatario.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/arrendatarios.js"></script>
</body>
</html>