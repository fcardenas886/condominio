<?php
session_start();
require_once __DIR__ . '/../includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Traemos también el id_entidad_asociada para saber qué casa mostrarle al arrendatario
    $stmt = $pdo->prepare("SELECT id_usuario, nombre_completo, password_hash, rol, activo, id_entidad_asociada FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();

    if (!$usuario) {
        $_SESSION['error'] = 'Usuario no está registrado.';
    } elseif ($usuario['password_hash'] !== $password) {
        $_SESSION['error'] = 'Verifique usuario y/o contraseña.';
    } elseif ($usuario['activo'] == 0) {
        $_SESSION['error'] = 'Tu cuenta ha sido desactivada. Contacta al administrador.';
    } else {
        // --- SESIÓN EXITOSA ---
        $_SESSION['id_usuario'] = $usuario['id_usuario'];
        $_SESSION['nombre_usuario'] = $usuario['nombre_completo'];
        $_SESSION['rol'] = $usuario['rol'];
        $_SESSION['id_entidad'] = $usuario['id_entidad_asociada']; // Clave para el portal del residente

        // Redirección por ROL
        if ($usuario['rol'] === 'admin') {
            $_SESSION['id_admin'] = $usuario['id_usuario']; // Mantenemos tu variable actual para no romper nada
            header("Location: ../dashboard.php");
        } else {
            // Si el rol es 'arrendatario', lo mandamos a su portal
            header("Location: ../residente/panel_residente.php");
        }
        exit();
    }
    
    header("Location: ../index.php");
    exit();
}