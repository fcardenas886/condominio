<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 shadow">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="dashboard.php">CondoPro 🏢</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link" href="dashboard.php">Inicio</a>
        </li>

        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
            🛠️ Mantención
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="mantencion_casas.php">Casas</a></li>
            <li><a class="dropdown-item" href="mantencion_arrendatarios.php">Arrendatarios</a></li>
            <li><a class="dropdown-item" href="gestion_proveedores.php">Proveedores</a></li>
          </ul>
        </li>

        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
            📋 Gestión
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="gestion_contratos.php">Contratos</a></li>
            <li><a class="dropdown-item" href="gestion_gastos.php">Gastos Variables</a></li>
            <li><a class="dropdown-item" href="gestion_mantenciones.php">Mantenciones</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="historial_cierres.php"><i class="bi bi-clock-history me-2"></i> Historial cierres contratos</a></li>
          </ul>
        </li>

        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
            💰 Finanzas
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="generar_cobros.php">Cobros</a></li>
            <li><a class="dropdown-item" href="recaudacion.php">Pagos Recibidos</a></li>

            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="historial_pagos.php"><i class="bi bi-clock-history me-2"></i> Historial de Pagos</a></li>
            <!-- <li><a class="dropdown-item" href="reportes.php">📊 Reportes</a></li> -->
          </ul>
        </li>

        <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
        📊 Reportes
    </a>
    <ul class="dropdown-menu shadow">
        <li><a class="dropdown-item fw-bold" href="reporte_total.php"><i class="bi bi-clipboard-data"></i> Reporte Maestro de Contratos</a></li>
        <li><a class="dropdown-item" href="historial_cierres.php"><i class="bi bi-archive"></i> Historial de Cierres</a></li>
        
        <li><hr class="dropdown-divider"></li>
        
        <li><a class="dropdown-item" href="reporte_morosidad.php">Lista de Morosos</a></li>
        <li><a class="dropdown-item" href="reporte_ingresos.php">Resumen de Ingresos</a></li>
        <li><a class="dropdown-item" href="reporte_egresos.php">Resumen de Egresos</a></li>
        
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="balance_general.php">Balance General</a></li>
    </ul>
</li>
      </ul>
      
      <div class="d-flex align-items-center">
        <span class="navbar-text me-3 text-white">
          <small>Admin: <?php echo $_SESSION['nombre_admin']; ?></small>
        </span>
        <a href="logout.php" class="btn btn-outline-danger btn-sm">Salir</a>
      </div>
    </div>
  </div>
</nav>

<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> -->