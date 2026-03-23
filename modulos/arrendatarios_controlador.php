<?php
// Reporte de errores para ver qué pasa en lugar de pantalla blanca
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../includes/conexion.php';

// Capturamos la acción (unificamos POST y GET)
$accion = $_REQUEST['accion'] ?? '';

switch ($accion) {
    case 'crear':
        try {
            $sql = "INSERT INTO arrendatarios (nombre, rut, telefono, correo, activo) VALUES (?, ?, ?, ?, 1)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $_POST['nombre'], 
                $_POST['rut'], 
                $_POST['telefono'], 
                $_POST['correo']
            ]);
            header("Location: ../mantencion_arrendatarios.php?success=1");
            exit();
        } catch (PDOException $e) {
            header("Location: ../mantencion_arrendatarios.php?error=2");
            exit();
        }
        break;

    case 'editar':
        try {
            // Recogemos el ID
            $id = $_POST['id_arrendatario'] ?? '';

            if (empty($id)) {
                die("Error: No se recibió el ID del arrendatario.");
            }

            $sql = "UPDATE arrendatarios SET nombre = ?, rut = ?, telefono = ?, correo = ? WHERE id_arrendatario = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $_POST['nombre'], 
                $_POST['rut'], 
                $_POST['telefono'], 
                $_POST['correo'], 
                $id
            ]);
            
            header("Location: ../mantencion_arrendatarios.php?success=2");
            exit();
        } catch (PDOException $e) {
            die("Error en la base de datos: " . $e->getMessage());
        }
        break;

    case 'eliminar':
        try {
            $id = $_GET['id'] ?? '';
            $sql = "UPDATE arrendatarios SET activo = 0 WHERE id_arrendatario = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);
            header("Location: ../mantencion_arrendatarios.php?success=3");
            exit();
        } catch (PDOException $e) {
            header("Location: ../mantencion_arrendatarios.php?error=2");
            exit();
        }
        break;

    default:
        die("Acción no válida o no especificada.");
        break;
}