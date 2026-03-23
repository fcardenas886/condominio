<?php
session_start(); // Siempre debe ser la primera línea
//require_once 'includes/conexion.php'; 
require_once __DIR__ . '/../includes/conexion.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Primero verificamos si el usuario existe (sin filtrar por activo)
    $stmt = $pdo->prepare("SELECT id_usuario, nombre_completo, password_hash, rol, activo FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();

    // Detectar diferentes tipos de error
    if (!$usuario) {
        // El email no existe en la base de datos
        $_SESSION['error'] = 'Usuario no está registrado.';
    } elseif ($usuario['password_hash'] !== $password) {
        // El email existe pero la contraseña es incorrecta
        $_SESSION['error'] = 'Verifique usuario y/o contraseña.';
    } elseif ($usuario['activo'] == 0) {
        // El usuario existe pero está inactivo
        $_SESSION['error'] = 'Tu cuenta ha sido desactivada. Contacta al administrador.';
    } else {
        // Todo bien, login exitoso
        $_SESSION['id_admin'] = $usuario['id_usuario'];
        $_SESSION['nombre_admin'] = $usuario['nombre_completo'];
        $_SESSION['rol'] = $usuario['rol'];
        
        header("Location: ../dashboard.php");
        exit();
    }
    
    // Si llegamos aquí, hubo error
    header("Location: ../index.php");
    exit();
}