<?php
session_start();
include 'includes/conexion.php';

// 1. Obtener todas las categorías para los Selects dentro de los modales
$categorias = $pdo->query("SELECT * FROM categorias_mantencion ORDER BY nombre_categoria ASC")->fetchAll();

// 2. Consulta Maestra: Proveedores Activos + Sus Categorías
$sql = "SELECT p.*, 
               GROUP_CONCAT(c.nombre_categoria SEPARATOR ', ') AS lista_categorias,
               GROUP_CONCAT(c.id_categoria SEPARATOR ',') AS ids_categorias
        FROM proveedores p
        LEFT JOIN proveedor_especialidad pe ON p.id_proveedor = pe.id_proveedor
        LEFT JOIN categorias_mantencion c ON pe.id_categoria = c.id_categoria
        WHERE p.estado = 1 
        GROUP BY p.id_proveedor
        ORDER BY p.nombre ASC";

$proveedores = $pdo->query($sql)->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Gestión de Proveedores - CondoPro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .badge-categoria {
            font-size: 0.75rem;
            border: 1px solid #0d6efd;
            color: #0d6efd;
            background: #f0f7ff;
            font-weight: 600;
        }

        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
        }

        /* --- ESTILOS BOTONES DEGRADADOS --- */
        .btn-group-gradient {
            display: inline-flex;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e0e0e0;
        }

        .btn-grad {
            border: none;
            padding: 8px 12px;
            background-color: white;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .btn-grad i {
            font-size: 1.1rem;
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Editar: Naranja / Amarillo */
        .btn-grad-edit { border-right: 1px solid #e0e0e0; }
        .btn-grad-edit i { background-image: linear-gradient(135deg, #ff9a1e 0%, #ffc82c 100%); }
        .btn-grad-edit:hover { background-color: #fff9f0; }

        /* Eliminar: Rojo / Rosado */
        .btn-grad-delete i { background-image: linear-gradient(135deg, #f01e2c 0%, #ff6b8a 100%); }
        .btn-grad-delete:hover { background-color: #fff5f5; }
    </style>
</head>

<body class="bg-light">

    <?php include 'includes/menu.php'; ?>

    <div class="container mt-3">
        <?php if (isset($_GET['prov']) && $_GET['prov'] == 'ok'): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-start border-success border-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> <strong>¡Éxito!</strong> Proveedor guardado correctamente.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['prov']) && $_GET['prov'] == 'desactivado'): ?>
            <div class="alert alert-warning alert-dismissible fade show shadow-sm border-start border-warning border-4" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i> El proveedor ha sido ocultado de la lista.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-start border-danger border-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <strong>Error:</strong> <?php echo htmlspecialchars($_GET['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    </div>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
            <div>
                <h3 class="fw-bold text-dark mb-0">Gestión de Proveedores</h3>
                <p class="text-muted mb-0 small">Directorio técnico y especialidades de mantenimiento</p>
            </div>
            <div class="btn-group shadow-sm">
                <button class="btn btn-primary px-3" data-bs-toggle="modal" data-bs-target="#modalProveedor" id="btnNuevoProveedor">
                    <i class="bi bi-person-plus-fill me-1"></i> Nuevo Proveedor
                </button>
                <button class="btn btn-outline-dark" data-bs-toggle="modal" data-bs-target="#modalCategorias">
                    <i class="bi bi-tags-fill me-1"></i> Ver Categorías
                </button>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-center">
                        <thead class="table-light border-bottom">
                            <tr>
                                <th class="ps-4 text-start py-3">Proveedor / RUT</th>
                                <th>Categorías de Servicio</th>
                                <th>Contacto Directo</th>
                                <th class="pe-4">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($proveedores)): ?>
                                <tr>
                                    <td colspan="4" class="py-5 text-muted text-center">No hay proveedores registrados.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($proveedores as $p): ?>
                                    <tr>
                                        <td class="ps-4 text-start">
                                            <div class="fw-bold text-primary fs-6"><?php echo htmlspecialchars($p['nombre']); ?></div>
                                            <small class="text-muted fw-bold"><?php echo htmlspecialchars($p['rut']); ?></small>
                                        </td>
                                        <td>
                                            <?php
                                            if ($p['lista_categorias']) {
                                                foreach (explode(', ', $p['lista_categorias']) as $t) {
                                                    echo "<span class='badge rounded-pill badge-categoria me-1 mb-1'>$t</span>";
                                                }
                                            } else {
                                                echo "<span class='text-muted small italic'>Sin especialidad</span>";
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <a href="tel:<?php echo $p['telefono']; ?>" class="btn btn-sm btn-outline-success rounded-pill px-3">
                                                <i class="bi bi-telephone-fill me-1"></i> <?php echo $p['telefono']; ?>
                                            </a>
                                        </td>
                                        <td class="pe-4 text-end">
                                            <div class="btn-group-gradient">
                                                <button class="btn-grad btn-grad-edit btn-editar-prov" 
                                                        data-id="<?php echo $p['id_proveedor']; ?>"
                                                        data-rut="<?php echo $p['rut']; ?>"
                                                        data-nombre="<?php echo $p['nombre']; ?>"
                                                        data-telefono="<?php echo $p['telefono']; ?>"
                                                        data-categorias="<?php echo $p['ids_categorias']; ?>">
                                                    <i class="bi bi-pencil-fill"></i>
                                                </button>
                                                <button class="btn-grad btn-grad-delete btn-desactivar-prov" 
                                                        data-id="<?php echo $p['id_proveedor']; ?>" 
                                                        data-nombre="<?php echo htmlspecialchars($p['nombre']); ?>">
                                                    <i class="bi bi-trash3-fill"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/modals/modal_proveedor.php'; ?>
    <?php include 'includes/modals/modal_categorias.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // --- LÓGICA PARA EDITAR (Cargar datos en Modal) ---
        document.querySelectorAll('.btn-editar-prov').forEach(btn => {
            btn.addEventListener('click', function() {
                const modal = new bootstrap.Modal(document.getElementById('modalProveedor'));
                
                // Cambiar textos del modal
                document.getElementById('id_proveedor').value = this.dataset.id;
                document.getElementById('prov_rut').value = this.dataset.rut;
                document.getElementById('prov_nombre').value = this.dataset.nombre;
                document.getElementById('prov_telefono').value = this.dataset.telefono;
                document.getElementById('textoModal').innerText = 'Editar Proveedor';
                document.getElementById('btnGuardarProveedor').innerText = 'Actualizar Proveedor';

                // Marcar checkboxes de categorías
                const idsCat = this.dataset.categorias.split(',');
                document.querySelectorAll('.check-categoria').forEach(check => {
                    check.checked = idsCat.includes(check.value);
                });

                modal.show();
            });
        });

        // Limpiar modal al presionar "Nuevo Proveedor"
        document.getElementById('btnNuevoProveedor').addEventListener('click', function() {
            document.getElementById('formProveedor').reset();
            document.getElementById('id_proveedor').value = '';
            document.getElementById('textoModal').innerText = 'Registrar Proveedor';
            document.getElementById('btnGuardarProveedor').innerText = 'Guardar Proveedor';
        });

        // --- LÓGICA PARA DESACTIVAR (SweetAlert2) ---
        document.querySelectorAll('.btn-desactivar-prov').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const nombre = this.dataset.nombre;

                Swal.fire({
                    title: '¿Ocultar proveedor?',
                    text: `El proveedor "${nombre}" ya no aparecerá en la lista activa.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#f01e2c',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, ocultar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = `modulos/guardar_proveedor_completo.php?id_estado=${id}&set=0`;
                    }
                });
            });
        });
    </script>
</body>
</html>