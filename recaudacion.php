<?php
session_start();
include 'includes/conexion.php';

// 1. Capturar filtros de periodo
$mes_sel = isset($_GET['mes']) ? $_GET['mes'] : '';
$anio_sel = isset($_GET['anio']) ? $_GET['anio'] : date('Y');

try {
    // Consulta que contempla filtros y calcula saldos con abonos
    $sql = "SELECT 
                c.id_contrato,
                casa.numero_casa, 
                arr.nombre AS inquilino, 
                SUM(dc.monto_total) AS deuda_original,
                SUM(dc.monto_pagado_acumulado) AS total_abonado,
                (SUM(dc.monto_total) - SUM(dc.monto_pagado_acumulado)) AS saldo_pendiente,
                COUNT(dc.id_detalle) AS cantidad_items
            FROM detalle_cobros dc
            JOIN contratos c ON dc.id_contrato = c.id_contrato
            JOIN casas casa ON c.id_casa = casa.id_casa
            JOIN arrendatarios arr ON c.id_arrendatario = arr.id_arrendatario
            WHERE dc.pagado = 0";

    if ($mes_sel != '') {
        $sql .= " AND MONTH(dc.periodo) = :mes AND YEAR(dc.periodo) = :anio";
    }

    $sql .= " GROUP BY c.id_contrato HAVING saldo_pendiente > 0 ORDER BY casa.numero_casa ASC";

    $stmt = $pdo->prepare($sql);

    if ($mes_sel != '') {
        $stmt->execute(['mes' => $mes_sel, 'anio' => $anio_sel]);
    } else {
        $stmt->execute();
    }

    $pendientes = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recaudación - CondoPro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .bg-azul-pro { background-color: #0d6efd !important; }
        .btn-azul { background-color: #0d6efd; color: white; border-radius: 20px; }
        .btn-azul:hover { background-color: #0b5ed7; color: white; }
    </style>
</head>
<body class="bg-light">

    <?php include 'includes/menu.php'; ?>

    <div class="container mt-3">
        <?php if (isset($_GET['pago']) && $_GET['pago'] == 'exito'): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-start border-success border-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                    <div><strong>¡Pago Procesado!</strong> El registro se ha guardado correctamente.</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    </div>

    <div class="container mt-4">
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-header bg-azul-pro text-white py-2 small fw-bold">
                <i class="bi bi-funnel-fill"></i> Filtrar Periodo de Cobro
            </div>
            <div class="card-body bg-white border">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Mes:</label>
                        <select name="mes" class="form-select border-primary">
                            <option value="">-- Ver toda la deuda --</option>
                            <?php
                            $meses = ["01"=>"Enero","02"=>"Febrero","03"=>"Marzo","04"=>"Abril","05"=>"Mayo","06"=>"Junio","07"=>"Julio","08"=>"Agosto","09"=>"Septiembre","10"=>"Octubre","11"=>"Noviembre","12"=>"Diciembre"];
                            foreach ($meses as $num => $nombre) {
                                $sel = ($num == $mes_sel) ? "selected" : "";
                                echo "<option value='$num' $sel>$nombre</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Año:</label>
                        <input type="number" name="anio" class="form-control border-primary" value="<?php echo $anio_sel; ?>">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100 shadow-sm"><i class="bi bi-search"></i> Filtrar</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow border-0">
            <div class="card-header bg-azul-pro text-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Cobranza y Abonos</h5>
                <input type="text" id="buscador" class="form-control form-control-sm w-25" placeholder="🔍 Buscar...">
            </div>
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0" id="tablaArrendatarios">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">CASA</th>
                            <th>ARRENDATARIO</th>
                            <th>ESTADO</th>
                            <th>SALDO PENDIENTE</th>
                            <th class="text-center pe-4">ACCIÓN</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendientes as $p): ?>
                        <tr>
                            <td class="ps-4 fw-bold">#<?php echo $p['numero_casa']; ?></td>
                            <td><?php echo $p['inquilino']; ?></td>
                            <td>
                                <span class="badge bg-danger"><?php echo $p['cantidad_items']; ?> items</span>
                                <?php if($p['total_abonado'] > 0) echo '<span class="badge bg-info text-dark">Abonado</span>'; ?>
                            </td>
                            <td class="fs-5 fw-bold">$<?php echo number_format($p['saldo_pendiente'], 0, ',', '.'); ?></td>
                            <td class="text-center pe-4">
                                <button class="btn btn-azul btn-sm btn-abrir-pago" 
                                    data-id-contrato="<?php echo $p['id_contrato']; ?>"
                                    data-nombre="<?php echo $p['inquilino']; ?>"
                                    data-casa="<?php echo $p['numero_casa']; ?>">Registrar Pago</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php include 'includes/modals/modal_pago.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Buscador Rápido
    document.getElementById('buscador').addEventListener('keyup', function() {
        let filtro = this.value.toLowerCase();
        let filas = document.querySelectorAll('#tablaArrendatarios tbody tr');
        filas.forEach(fila => {
            let texto = fila.innerText.toLowerCase();
            fila.style.display = texto.includes(filtro) ? '' : 'none';
        });
    });

    const bModal = new bootstrap.Modal(document.getElementById('modalPagoPro'));

    document.querySelectorAll('.btn-abrir-pago').forEach(btn => {
        btn.addEventListener('click', function() {
            const idContrato = this.dataset.idContrato;
            document.getElementById('modal_nombre').innerText = this.dataset.nombre;
            document.getElementById('modal_casa').innerText = "Casa " + this.dataset.casa;

            fetch(`modulos/obtener_deudas_json.php?id_contrato=${idContrato}`)
                .then(res => res.json())
                .then(data => {
                    const divAviso = document.getElementById('aviso_saldo_favor');
                    const checkSaldo = document.getElementById('usar_saldo_favor');

                    // 1. Manejar Saldo a Favor acumulado
                    if (data.saldo_favor > 0) {
                        if(divAviso) divAviso.style.display = 'block';
                        document.getElementById('monto_disponible_favor').innerText = "$" + new Intl.NumberFormat('es-CL').format(data.saldo_favor);
                        if(checkSaldo) checkSaldo.dataset.montoFavor = data.saldo_favor;
                    } else {
                        if(divAviso) divAviso.style.display = 'none';
                        if(checkSaldo) {
                            checkSaldo.checked = false;
                            checkSaldo.dataset.montoFavor = 0;
                        }
                    }

                    // 2. Cargar Deudas
                    let html = '';
                    if (data.deudas && data.deudas.length > 0) {
                        data.deudas.forEach(d => {
                            let pendiente = parseFloat(d.monto_total) - parseFloat(d.monto_pagado_acumulado);
                            html += `
                            <label class="list-group-item d-flex justify-content-between align-items-center py-2">
                                <div class="form-check">
                                    <input class="form-check-input chk-deuda" type="checkbox" name="deudas_ids[]" value="${d.id_detalle}" data-monto="${pendiente}" checked>
                                    <span class="fw-bold text-dark">${d.concepto}</span>
                                    <small class="text-muted d-block">${d.periodo} ($${new Intl.NumberFormat('es-CL').format(pendiente)})</small>
                                </div>
                            </label>`;
                        });
                    } else {
                        html = '<div class="p-3 text-center text-muted small">Sin deudas pendientes</div>';
                    }
                    
                    document.getElementById('lista_deudas').innerHTML = html;

                    // Re-asignar eventos
                    document.querySelectorAll('.chk-deuda').forEach(c => c.addEventListener('change', calcularTotales));
                    if(checkSaldo) checkSaldo.addEventListener('change', calcularTotales);

                    bModal.show();
                    calcularTotales();
                })
                .catch(err => console.error("Error cargando deudas:", err));
        });
    });

    document.getElementById('monto_recibido').addEventListener('input', calcularTotales);

    function calcularTotales() {
        let totalDeudas = 0;
        document.querySelectorAll('.chk-deuda:checked').forEach(c => {
            totalDeudas += parseFloat(c.dataset.monto);
        });

        const checkSaldo = document.getElementById('usar_saldo_favor');
        const montoFavorAcumulado = (checkSaldo && checkSaldo.checked) ? parseFloat(checkSaldo.dataset.montoFavor) : 0;

        // Total que el cliente debe pagar después de usar su saldo guardado
        const totalFinal = Math.max(0, totalDeudas - montoFavorAcumulado);
        
        const recibido = parseFloat(document.getElementById('monto_recibido').value) || 0;
        const diferencia = recibido - totalFinal;

        // Actualizar Interfaz
        document.getElementById('txt_total_selec').innerText = "$" + new Intl.NumberFormat('es-CL').format(totalFinal);
        document.getElementById('txt_vuelto').innerText = "$" + new Intl.NumberFormat('es-CL').format(Math.abs(diferencia));
        
        const lblVuelto = document.getElementById('label_vuelto');
        const txtVuelto = document.getElementById('txt_vuelto');
        
        if (diferencia >= 0) {
            lblVuelto.innerText = "Vuelto / Saldo Favor:";
            txtVuelto.className = "fw-bold text-success fs-5";
        } else {
            lblVuelto.innerText = "Faltante:";
            txtVuelto.className = "fw-bold text-danger fs-5";
        }

        // Mostrar u ocultar opciones de excedente
        const divExcedente = document.getElementById('opcion_saldo_favor');
        const radioSaldo = document.getElementById('dest2');

        if (divExcedente) {
            if (diferencia > 0) {
                divExcedente.style.display = 'block';
                // Actualizar el texto del radio button para que sea dinámico
                if(radioSaldo) {
                    radioSaldo.nextElementSibling.innerText = `Guardar $${new Intl.NumberFormat('es-CL').format(diferencia)} como Saldo a Favor`;
                }
            } else {
                divExcedente.style.display = 'none';
            }
        }
    }
</script>
</body>
</html>