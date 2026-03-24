<?php
include '../includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deudas_ids'])) {
    $ids = $_POST['deudas_ids'];
    $metodo = $_POST['metodo'] ?? 'Otros';
    $monto_recibido = floatval($_POST['monto_recibido']);
    $usar_saldo = isset($_POST['usar_saldo_favor']) ? true : false;
    $dinero_disponible = $monto_recibido;
    
    $id_transaccion = ""; 
    $prefijo_fecha = "CP-" . date('ymd'); 

    try {
        $pdo->beginTransaction();

        // --- 1. LÓGICA DE SALDO A FAVOR ---
        if ($usar_saldo) {
            $stmt_s = $pdo->prepare("SELECT a.saldo_favor, a.id_arrendatario 
                                     FROM arrendatarios a 
                                     JOIN contratos c ON a.id_arrendatario = c.id_arrendatario 
                                     WHERE c.id_contrato = (SELECT id_contrato FROM detalle_cobros WHERE id_detalle = ? LIMIT 1)");
            $stmt_s->execute([$ids[0]]);
            $res_s = $stmt_s->fetch();
            
            if ($res_s && $res_s['saldo_favor'] > 0) {
                $monto_saldo_usado = (float)$res_s['saldo_favor'];
                $dinero_disponible += $monto_saldo_usado;

                $stmt_zero = $pdo->prepare("UPDATE arrendatarios SET saldo_favor = 0 WHERE id_arrendatario = ?");
                $stmt_zero->execute([$res_s['id_arrendatario']]);

                $stmt_hist_saldo = $pdo->prepare("INSERT INTO pagos (id_detalle, monto_pagado, fecha_pago, metodo_pago, notas) 
                                                 VALUES (?, ?, NOW(), 'Saldo a Favor', ?)");
                $stmt_hist_saldo->execute([$ids[0], $monto_saldo_usado, "Uso de saldo acumulado anterior"]);
                
                $primer_id = $pdo->lastInsertId();
                $id_transaccion = $prefijo_fecha . "-" . str_pad($primer_id, 4, "0", STR_PAD_LEFT);
                
                $pdo->prepare("UPDATE pagos SET id_transaccion = ? WHERE id_pago = ?")
                    ->execute([$id_transaccion, $primer_id]);
            }
        }

        // --- 2. REPARTO DE DINERO ENTRE DEUDAS ---
        foreach ($ids as $index => $id) {
            if ($dinero_disponible <= 0) break;

            $stmt = $pdo->prepare("SELECT monto_total, monto_pagado_acumulado, concepto FROM detalle_cobros WHERE id_detalle = ?");
            $stmt->execute([$id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);

            $deuda_pendiente = $item['monto_total'] - $item['monto_pagado_acumulado'];
            $abono = ($dinero_disponible >= $deuda_pendiente) ? $deuda_pendiente : $dinero_disponible;

            $stmt_pago = $pdo->prepare("INSERT INTO pagos (id_detalle, monto_pagado, fecha_pago, metodo_pago, notas) 
                                        VALUES (?, ?, NOW(), ?, ?)");
            $stmt_pago->execute([$id, $abono, $metodo, "Abono a " . $item['concepto']]);
            
            $nuevo_id_pago = $pdo->lastInsertId();

            if (empty($id_transaccion)) {
                $id_transaccion = $prefijo_fecha . "-" . str_pad($nuevo_id_pago, 4, "0", STR_PAD_LEFT);
            }

            $pdo->prepare("UPDATE pagos SET id_transaccion = ? WHERE id_pago = ?")
                ->execute([$id_transaccion, $nuevo_id_pago]);

            $nuevo_acumulado = $item['monto_pagado_acumulado'] + $abono;
            $estado_pagado = ($nuevo_acumulado >= $item['monto_total']) ? 1 : 0;
            $pdo->prepare("UPDATE detalle_cobros SET monto_pagado_acumulado = ?, pagado = ? WHERE id_detalle = ?")
                ->execute([$nuevo_acumulado, $estado_pagado, $id]);

            $dinero_disponible -= $abono;
        }

        // --- 3. MANEJO DE EXCEDENTES ---
        $dinero_sobrante = $dinero_disponible;
        $destino = $_POST['destino_excedente'] ?? 'vuelto';

        if ($dinero_sobrante > 0 && $destino === 'saldo') {
            $sql_saldo = "UPDATE arrendatarios SET saldo_favor = saldo_favor + ? 
                          WHERE id_arrendatario = (SELECT id_arrendatario FROM contratos WHERE id_contrato = (SELECT id_contrato FROM detalle_cobros WHERE id_detalle = ? LIMIT 1))";
            $pdo->prepare($sql_saldo)->execute([$dinero_sobrante, $ids[0]]);

            $stmt_pago_excedente = $pdo->prepare("INSERT INTO pagos (id_detalle, id_transaccion, monto_pagado, fecha_pago, metodo_pago, notas) 
                                                 VALUES (?, ?, NOW(), ?, ?)");
            $stmt_pago_excedente->execute([$ids[0], $id_transaccion, $dinero_sobrante, $metodo, "Excedente guardado como Saldo a Favor"]);
        }

        $pdo->commit();

        // --- CAMBIO CLAVE PARA AJAX ---
        // Simplemente imprimimos el ID de transacción. El JS lo recibirá como 'response'
        echo $id_transaccion; 

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Error: Solicitud no válida.";
}