<?php
session_start();
// Seguridad: Solo si el admin está logueado
if (!isset($_SESSION['id_admin'])) { exit("Acceso denegado"); }

require_once '../includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numero = $_POST['numero'];
    $desc   = $_POST['descripcion'];
    $estado = $_POST['estado'];

    try {
        // 🚀 Preparamos la inserción
        $sql = "INSERT INTO casas (numero_casa, descripcion, estado) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$numero, $desc, $estado]);

        // ✅ Éxito: volvemos con código 1 (Creado)
        header("Location: ../mantencion_casas.php?success=1");
        exit();

    } catch (PDOException $e) {
        // ❌ Error (por ejemplo, si el número de casa ya existe)
        header("Location: ../mantencion_casas.php?error=insert_failed");
        exit();
    }
}