<?php
include '../includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_casa  = $_POST['id_casa'];
    $concepto = $_POST['concepto'];
    $monto    = $_POST['monto'];
    $periodo  = $_POST['periodo'];

    try {
        // 1. Buscamos el contrato usando la columna 'activo' con valor 1
        // (Vimos en tu tabla que 'id_casa' 3 y 4 tienen 'activo' en 1)
        $stmt = $pdo->prepare("SELECT id_contrato FROM contratos WHERE id_casa = ? AND activo = 1 LIMIT 1");
        $stmt->execute([$id_casa]);
        $contrato = $stmt->fetch();

        if ($contrato) {
            $id_contrato = $contrato['id_contrato'];

            // 2. Insertar el gasto en detalle_cobros
            // Se guarda como pagado = 0 (Pendiente) y monto_pagado_acumulado = 0
            $sql = "INSERT INTO detalle_cobros (id_contrato, periodo, concepto, monto_total, monto_pagado_acumulado, pagado) 
                    VALUES (?, ?, ?, ?, 0, 0)";
            
            $stmt_ins = $pdo->prepare($sql);
            $stmt_ins->execute([
                $id_contrato, 
                $periodo, 
                $concepto, 
                $monto
            ]);

            // Redirigir al panel con mensaje de éxito
            header("Location: ../gestion_gastos.php?status=ok");
            exit();
        } else {
            // Si por alguna razón la casa seleccionada no tiene contrato activo
            die("<div style='font-family:sans-serif; padding:20px; background:#f8d7da; color:#721c24; border-radius:5px;'>
                    <strong>Error:</strong> No se encontró un contrato activo (activo = 1) para esta casa. 
                    Verifique la tabla de contratos.
                 </div>");
        }
    } catch (PDOException $e) {
        die("Error en la base de datos: " . $e->getMessage());
    }
} else {
    // Si alguien intenta entrar directamente al archivo sin enviar el formulario
    header("Location: ../gestion_gastos.php");
    exit();
}