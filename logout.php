<?php
session_start();
// Limpiamos todas las variables de sesión
$_SESSION = array();
// Destruimos la sesión físicamente en el servidor
session_destroy();
// Redirigimos al Login
header("Location: index.php");
exit();
?>