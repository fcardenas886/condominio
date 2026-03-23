<?php
include '../includes/conexion.php';
$id_contrato = $_GET['id_contrato'] ?? 0;

try {
    // Traemos las deudas
    $sql_deudas = "SELECT id_detalle, concepto, monto_total, monto_pagado_acumulado, 
                          DATE_FORMAT(periodo, '%m-%Y') as periodo 
                   FROM detalle_cobros 
                   WHERE id_contrato = ? AND pagado = 0 
                   ORDER BY periodo ASC";
    $stmt = $pdo->prepare($sql_deudas);
    $stmt->execute([$id_contrato]);
    $deudas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // NUEVO: Traemos el saldo a favor del arrendatario de este contrato
    $sql_saldo = "SELECT a.saldo_favor 
                  FROM arrendatarios a 
                  JOIN contratos c ON a.id_arrendatario = c.id_arrendatario 
                  WHERE c.id_contrato = ?";
    $stmt_s = $pdo->prepare($sql_saldo);
    $stmt_s->execute([$id_contrato]);
    $saldo_favor = $stmt_s->fetchColumn() ?: 0;

    // Enviamos todo junto
    echo json_encode([
        "deudas" => $deudas,
        "saldo_favor" => (float)$saldo_favor
    ]);
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}