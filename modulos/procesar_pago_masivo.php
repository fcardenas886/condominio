<?php
include '../includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deudas_ids'])) {
    $ids = $_POST['deudas_ids'];
    //$monto_recibido = floatval($_POST['monto_recibido']);
    $metodo = $_POST['metodo'] ?? 'Otros';
    //$dinero_disponible = $monto_recibido;

    //$usar_saldo = isset($_POST['usar_saldo_favor']) ? true : false;

// ... después de capturar los datos POST ...
$monto_recibido = floatval($_POST['monto_recibido']);
$usar_saldo = isset($_POST['usar_saldo_favor']) ? true : false;
$dinero_disponible = $monto_recibido;
$monto_saldo_usado = 0; // Para el historial

if ($usar_saldo) {
    // 1. Obtener el saldo actual del arrendatario
    $stmt_s = $pdo->prepare("SELECT a.saldo_favor, a.id_arrendatario 
                             FROM arrendatarios a 
                             JOIN contratos c ON a.id_arrendatario = c.id_arrendatario 
                             WHERE c.id_contrato = (SELECT id_contrato FROM detalle_cobros WHERE id_detalle = ? LIMIT 1)");
    $stmt_s->execute([$ids[0]]);
    $res_s = $stmt_s->fetch();
    
    if ($res_s && $res_s['saldo_favor'] > 0) {
        $monto_saldo_usado = (float)$res_s['saldo_favor'];
        $dinero_disponible += $monto_saldo_usado; // Sumamos el "dinero virtual" al disponible

        // 2. Dejar el saldo en 0
        $stmt_zero = $pdo->prepare("UPDATE arrendatarios SET saldo_favor = 0 WHERE id_arrendatario = ?");
        $stmt_zero->execute([$res_s['id_arrendatario']]);

        // 3. REGISTRO CLAVE: Dejar constancia en la tabla PAGOS de que se usó el saldo
        $stmt_hist_saldo = $pdo->prepare("INSERT INTO pagos (id_detalle, monto_pagado, fecha_pago, metodo_pago, notas) 
                                         VALUES (?, ?, NOW(), 'Saldo a Favor', ?)");
        $stmt_hist_saldo->execute([$ids[0], $monto_saldo_usado, "Uso de saldo acumulado anterior"]);
    }
}

// ... aquí sigue tu bucle foreach que reparte el $dinero_disponible entre las deudas ...

    try {
        $pdo->beginTransaction();

        foreach ($ids as $id) {
            if ($dinero_disponible <= 0) break;

            // 1. Consultar estado actual del ítem
            $stmt = $pdo->prepare("SELECT monto_total, monto_pagado_acumulado, concepto FROM detalle_cobros WHERE id_detalle = ?");
            $stmt->execute([$id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);

            $deuda_pendiente = $item['monto_total'] - $item['monto_pagado_acumulado'];

            // 2. Determinar cuánto abonar
            $abono = ($dinero_disponible >= $deuda_pendiente) ? $deuda_pendiente : $dinero_disponible;

            // 3. Registrar en tabla PAGOS (Tu tabla de historial)
            $stmt_pago = $pdo->prepare("INSERT INTO pagos (id_detalle, monto_pagado, fecha_pago, metodo_pago, notas) 
                                        VALUES (?, ?, NOW(), ?, ?)");
            $stmt_pago->execute([$id, $abono, $metodo, "Abono a " . $item['concepto']]);

            // 4. Actualizar acumulado en DETALLE_COBROS
            $nuevo_acumulado = $item['monto_pagado_acumulado'] + $abono;
            $estado_pagado = ($nuevo_acumulado >= $item['monto_total']) ? 1 : 0;

            $stmt_upd = $pdo->prepare("UPDATE detalle_cobros SET monto_pagado_acumulado = ?, pagado = ? WHERE id_detalle = ?");
            $stmt_upd->execute([$nuevo_acumulado, $estado_pagado, $id]);

            $dinero_disponible -= $abono;
        }

        $dinero_sobrante = $dinero_disponible; // Lo que quedó después de pagar todas las deudas
        $destino = $_POST['destino_excedente'] ?? 'vuelto';

        if ($dinero_sobrante > 0 && $destino === 'saldo') {
            // 1. Sumamos el saldo al arrendatario
            $sql_saldo = "UPDATE arrendatarios SET saldo_favor = saldo_favor + ? 
                          WHERE id_arrendatario = (SELECT id_arrendatario FROM contratos WHERE id_contrato = (SELECT id_contrato FROM detalle_cobros WHERE id_detalle = ? LIMIT 1))";
            $stmt_saldo = $pdo->prepare($sql_saldo);
            $stmt_saldo->execute([$dinero_sobrante, $ids[0]]);

            // 2. REGISTRAMOS EL INGRESO en la tabla pagos (para que tu caja cuadre)
            // Usamos el primer id_detalle de la lista para vincularlo a la transacción
            $stmt_pago_excedente = $pdo->prepare("INSERT INTO pagos (id_detalle, monto_pagado, fecha_pago, metodo_pago, notas) 
                                                 VALUES (?, ?, NOW(), ?, ?)");
            $stmt_pago_excedente->execute([$ids[0], $dinero_sobrante, $metodo, "Excedente guardado como Saldo a Favor"]);
        }

        $pdo->commit();
        header("Location: ../recaudacion.php?pago=exito");
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        die("Error: " . $e->getMessage());
    }
} else {
    header("Location: ../recaudacion.php?error=vacio");
}
