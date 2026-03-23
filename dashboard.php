<?php
session_start();

// 1. El Guardia: Si no hay sesión, al login
if (!isset($_SESSION['id_admin'])) {
    header("Location: index.php");
    exit();
}

// 2. Traemos la conexión (está en la carpeta includes)
require_once 'includes/conexion.php';

// 3. Consultamos el total de casas
$stmt = $pdo->query("SELECT COUNT(*) FROM casas");
$totalCasas = $stmt->fetchColumn();

// 2. Solo las que están marcadas como 'Disponible'
$stmt = $pdo->query("SELECT COUNT(*) FROM casas WHERE estado = 'Disponible'");
$totalDisponibles = $stmt->fetchColumn();

// 3. Solo las que están marcadas como 'Ocupada'
$stmt = $pdo->query("SELECT COUNT(*) FROM casas WHERE estado = 'Ocupada'");
$totalOcupadas = $stmt->fetchColumn();

$nombreAdmin = $_SESSION['nombre_admin'];
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Panel de Administración</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <!-- <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container-fluid">
            <span class="navbar-brand">Condominio Pro</span>
            <span class="navbar-text text-white">Bienvenido, <?php echo $nombreAdmin; ?></span>
            <a href="logout.php" class="btn btn-outline-danger btn-sm">Salir</a>
        </div>
    </nav> -->
    <?php include 'includes/menu.php'; ?>

    <div class="container">


        <div class="row text-center">
            <div class="col-md-4 mb-3">
                <div class="card bg-primary text-white shadow">
                    <div class="card-body">
                        <h6>Total de Casas 🏠</h6>
                        <h2 class="display-4"><?php echo $totalCasas; ?></h2>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card bg-success text-white shadow">
                    <div class="card-body">
                        <h6>Disponibles ✅</h6>
                        <h2 class="display-4"><?php echo $totalDisponibles; ?></h2>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card bg-warning text-dark shadow">
                    <div class="card-body">
                        <h6>Ocupadas 👤</h6>
                        <h2 class="display-4"><?php echo $totalOcupadas; ?></h2>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>