<?php
session_start();

// Detectar flash messages
$errorMessage = null;
$successMessage = null;
$warningMessage = null;

if (isset($_SESSION['error'])) {
    $errorMessage = $_SESSION['error'];
    unset($_SESSION['error']);
}
if (isset($_SESSION['success'])) {
    $successMessage = $_SESSION['success'];
    unset($_SESSION['success']);
}
if (isset($_SESSION['warning'])) {
    $warningMessage = $_SESSION['warning'];
    unset($_SESSION['warning']);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - Administración GeSTIDom</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        .card { border-radius: 15px; border: none; }
        .btn-primary { border-radius: 10px; padding: 12px; font-weight: bold; }
        /* Ajuste para el loader en esta página específica si fuera necesario */
    </style>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-4">
                
                <?php if ($errorMessage): ?>
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                        <strong>⚠️ Error:</strong> <?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <div class="card shadow-lg mt-4">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <h2 class="fw-bold">GeSTIDom</h2>
                            <p class="text-muted small text-uppercase" style="letter-spacing: 2px;">Sistema STI</p>
                        </div>
                        
                        <form id="loginForm" action="modulos/login_proceso.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-muted">CORREO ELECTRÓNICO</label>
                                <input type="email" name="email" class="form-control form-control-lg" placeholder="ejemplo@correo.com" required>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-bold small text-muted">CONTRASEÑA</label>
                                <input type="password" name="password" class="form-control form-control-lg" placeholder="••••••••" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 shadow-sm" id="btnEntrar">
                                <i class="bi bi-box-arrow-in-right"></i> Entrar al Panel
                            </button>
                        </form>
                    </div>
                </div>
                <p class="text-center mt-4 text-muted small">&copy; 2026 GeSTIDom by STI</p>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <?php include 'includes/scripts_comun.php'; ?>

    <script>
    $(document).ready(function() {
        // Interceptamos el envío del formulario de login
        $('#loginForm').on('submit', function(e) {
            e.preventDefault(); // Detenemos el envío inmediato
            
            var formulario = this;
            var boton = $('#btnEntrar');

            // 1. Mostrar el loader global (el que pusimos en scripts_comun)
            // Ajustamos el texto para el login
            $('#loader-text').text('Validando credenciales STI...');
            $('#loader-global').css('display', 'flex').hide().fadeIn(400);

            // 2. Cambiar estado del botón
            boton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Verificando...');

            // 3. Esperar 2 segundos antes de procesar en el servidor
            setTimeout(function() {
                formulario.submit();
            }, 2000); 
        });
    });
    </script>
</body>
</html>