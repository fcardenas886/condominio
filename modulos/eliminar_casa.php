<?php
session_start();

// Verificar que el usuario está autenticado
if (!isset($_SESSION['id_admin'])) {
    header("Location: ../index.php");
    exit();
}

require_once '../includes/conexion.php';

// Capturamos el ID de la URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: ../mantencion_casas.php");
    exit();
}

$id = intval($_GET['id']);

try {
    // Preparamos la consulta para cambiar 'activo' a 0
    $sql = "UPDATE casas SET activo = 0 WHERE id_casa = ?";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([$id]);

    // Verificamos si se actualizó algún registro
    if ($stmt->rowCount() > 0) {
        // Redirigimos de vuelta con un mensaje de éxito
        header("Location: ../mantencion_casas.php?success=3");
    } else {
        // Si no se encontró el registro
        header("Location: ../mantencion_casas.php?error=1");
    }
    exit();

} catch (PDOException $e) {
    // Log del error para debugging
    error_log("Error al eliminar casa: " . $e->getMessage());
    header("Location: ../mantencion_casas.php?error=2");
    exit();
}
?>