<?php
session_start();
if (!isset($_SESSION['id_admin'])) { exit("Acceso denegado"); }

require_once '../includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recogemos los datos del formulario
    $id     = $_POST['id_casa'];
    $numero = $_POST['numero'];
    $desc   = $_POST['descripcion'];
    $estado = $_POST['estado'];

    try {
        // Preparamos la sentencia SQL de actualización
        $sql = "UPDATE casas SET numero_casa = ?, descripcion = ?, estado = ? WHERE id_casa = ?";
        $stmt = $pdo->prepare($sql);
        
        // Ejecutamos pasando los valores en orden
        $stmt->execute([$numero, $desc, $estado, $id]);

        // Si tuvo éxito, volvemos a la tabla con aviso de éxito
        header("Location: ../mantencion_casas.php?success=2");
        exit();
    } catch (PDOException $e) {
        // En caso de error (ej: número duplicado)
        header("Location: ../mantencion_casas.php?error=update_failed");
        exit();
    }
}