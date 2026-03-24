<?php
include '../includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Recibimos los datos del formulario
    $id_casa = intval($_POST['id_casa']);
    $descripcion = $_POST['descripcion'];
    $id_proveedor = intval($_POST['id_proveedor']);
    $monto_total_mantencion = floatval($_POST['monto_pagado']);
    $fecha_registro = $_POST['fecha'];

    // Datos de cobro al arrendatario
    $cobrar = isset($_POST['cobrar_a_arrendatario']) ? 1 : 0;
    $num_cuotas = isset($_POST['cuotas']) ? intval($_POST['cuotas']) : 1;
    $mes_inicio = isset($_POST['mes_inicio']) ? $_POST['mes_inicio'] : $fecha_registro;

    try {
        $pdo->beginTransaction();

        // 2. Insertamos el registro técnico en la tabla 'mantenciones'
        $sqlM = "INSERT INTO mantenciones (descripcion, id_casa, id_proveedor, monto_pagado, cobrar_a_arrendatario, fecha) 
                 VALUES (?, ?, ?, ?, ?, ?)";
        $stmtM = $pdo->prepare($sqlM);
        $stmtM->execute([$descripcion, $id_casa, $id_proveedor, $monto_total_mantencion, $cobrar, $fecha_registro]);

        // 3. Si se activó el cobro, generamos las cuotas en 'detalle_cobros'
        if ($cobrar == 1 && $id_casa > 0) {

            // Buscamos el ID del contrato ACTIVO para esa casa (Usando tu columna 'activo')
            $stmtC = $pdo->prepare("SELECT id_contrato FROM contratos WHERE id_casa = ? AND activo = 1 LIMIT 1");
            $stmtC->execute([$id_casa]);
            $contrato = $stmtC->fetch();

            if ($contrato) {
                // ... (dentro del if de cobrar y contrato) ...

                $id_con = $contrato['id_contrato'];
                $monto_acumulado = 0; // Para rastrear cuánto vamos asignando
                $monto_cuota_normal = floor($monto_total_mantencion / $num_cuotas); // Cuota base sin decimales

                for ($i = 0; $i < $num_cuotas; $i++) {
                    $n_cuota = $i + 1;
                    $fecha_cuota = date('Y-m-d', strtotime("$mes_inicio +$i month"));
                    $concepto = "Mantención: $descripcion (Cuota $n_cuota/$num_cuotas)";

                    // LÓGICA DE PRECISIÓN:
                    if ($n_cuota < $num_cuotas) {
                        // Cuotas normales
                        $monto_a_insertar = $monto_cuota_normal;
                        $monto_acumulado += $monto_a_insertar;
                    } else {
                        // LA ÚLTIMA CUOTA: Absorbe la diferencia
                        // Total - lo que ya cobramos en las anteriores
                        $monto_a_insertar = $monto_total_mantencion - $monto_acumulado;
                    }

                    $sqlD = "INSERT INTO detalle_cobros (id_contrato, periodo, concepto, monto_total, monto_pagado_acumulado, pagado) 
             VALUES (?, ?, ?, ?, 0.00, 0)";
                    $stmtD = $pdo->prepare($sqlD);
                    $stmtD->execute([$id_con, $fecha_cuota, $concepto, $monto_a_insertar]);
                }
            }
        }

        $pdo->commit();
        header("Location: ../gestion_mantenciones.php?res=ok");
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Error fatal: " . $e->getMessage();
    }
    exit();
}
