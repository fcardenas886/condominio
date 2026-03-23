<?php
session_start(); // 1. ESTO ES VITAL para que el menu.php no de error
include 'includes/conexion.php'; // 2. Corregido el '<' que sobraba

// Capturar el periodo seleccionado (por defecto el mes actual)
$mes_sel = isset($_POST['mes']) ? $_POST['mes'] : date('m');
$anio_sel = isset($_POST['anio']) ? $_POST['anio'] : date('Y');
$periodo_busqueda = "$anio_sel-$mes_sel-01";

$contratos = [];
$error_msg = "";

try {
    // Llamar al Procedimiento Almacenado con PDO
    $stmt = $pdo->prepare("CALL sp_obtener_contratos_sin_cobro(?)");
    $stmt->execute([$periodo_busqueda]);
    $contratos = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_msg = "Error en la base de datos: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generar Cobros Mensuales</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php include 'includes/menu.php'; ?>

<div class="container mt-4">
    <div class="card shadow border-0">
        <div class="card-header bg-primary text-white py-3">
            <h4 class="mb-0"><i class="bi bi-cash-stack"></i> Panel de Generación de Cobros</h4>
        </div>
        <div class="card-body p-4">

            <?php if (isset($_GET['status'])): ?>
                <?php if ($_GET['status'] == 'success'): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <strong>¡Éxito!</strong> Se han generado <?php echo (int)$_GET['count']; ?> cobros correctamente.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php elseif ($_GET['status'] == 'error'): ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <strong>Atención:</strong> No seleccionaste ningún contrato.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if($error_msg): ?>
                <div class="alert alert-danger"><?php echo $error_msg; ?></div>
            <?php endif; ?>

            <form method="POST" class="row g-3 align-items-end mb-4 bg-light p-3 rounded border">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Mes a Procesar:</label>
                    <select name="mes" class="form-select">
                        <?php
                        $meses = [
                            "01"=>"Enero", "02"=>"Febrero", "03"=>"Marzo", "04"=>"Abril",
                            "05"=>"Mayo", "06"=>"Junio", "07"=>"Julio", "08"=>"Agosto",
                            "09"=>"Septiembre", "10"=>"Octubre", "11"=>"Noviembre", "12"=>"Diciembre"
                        ];
                        foreach($meses as $num => $nombre) {
                            $selected = ($num == $mes_sel) ? "selected" : "";
                            echo "<option value='$num' $selected>$nombre</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Año:</label>
                    <input type="number" name="anio" class="form-control" value="<?php echo $anio_sel; ?>">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-dark w-100">🔍 Consultar Pendientes</button>
                </div>
            </form>

            <hr>

            <form action="modulos/procesar_insercion.php" method="POST">
                <input type="hidden" name="periodo_cobro" value="<?php echo $periodo_busqueda; ?>">
                
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle border">
                        <thead class="table-dark">
                            <tr>
                                <th width="50"><input type="checkbox" id="select_all" class="form-check-input"></th>
                                <th>Casa</th>
                                <th>Arrendatario</th>
                                <th>Monto Arriendo</th>
                                <th>Modalidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($contratos)): ?>
                                <?php foreach ($contratos as $row): ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="contratos_ids[]" 
                                                   value="<?php echo $row['id_contrato']; ?>" 
                                                   class="form-check-input case">
                                        </td>
                                        <td><strong><?php echo $row['numero_casa']; ?></strong></td>
                                        <td><?php echo $row['nombre_arrendatario']; ?></td>
                                        <td>$<?php echo number_format($row['monto_fijo'], 0, ',', '.'); ?></td>
                                        <td>
                                            <span class="badge <?php echo ($row['modalidad_cobro'] == 'Anticipado') ? 'bg-success' : 'bg-warning text-dark'; ?>">
                                                <?php echo $row['modalidad_cobro']; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        No se encontraron contratos pendientes para el periodo seleccionado.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (!empty($contratos)): ?>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
                        <button type="submit" class="btn btn-success btn-lg shadow">
                            ✅ Generar Cobros Seleccionados
                        </button>
                    </div>
                <?php endif; ?>
            </form>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('select_all').onclick = function() {
        var checkboxes = document.getElementsByClassName('case');
        for (var checkbox of checkboxes) {
            checkbox.checked = this.checked;
        }
    }
</script>

</body>
</html>