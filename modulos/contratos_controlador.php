<?php
require_once '../includes/conexion.php';

$accion = $_REQUEST['accion'] ?? '';

switch ($accion) {
    case 'crear':
    // 1. Recibimos los datos del formulario
    $id_casa = $_POST['id_casa'];
    $id_arr = $_POST['id_arrendatario'];
    $monto = $_POST['monto_fijo'];
    $garantia = $_POST['monto_garantia'];
    $inicio = $_POST['fecha_inicio']; // Formato YYYY-MM-DD
    $termino = !empty($_POST['fecha_termino']) ? $_POST['fecha_termino'] : null;

    try {
        $pdo->beginTransaction();

        // PASO A: Insertar el Contrato 📜
        $sql_ins = "INSERT INTO contratos (id_casa, id_arrendatario, monto_fijo, monto_garantia, fecha_inicio, fecha_termino, activo) 
                    VALUES (?, ?, ?, ?, ?, ?, 1)";
        $stmt_ins = $pdo->prepare($sql_ins);
        $stmt_ins->execute([$id_casa, $id_arr, $monto, $garantia, $inicio, $termino]);
        
        // Recuperamos el ID que la base de datos le asignó al contrato
        $id_contrato = $pdo->lastInsertId();

        // PASO B: Generar Cobro de Garantía (Fecha actual) 🛡️
        $hoy = date('Y-m-d'); 
        $sql_gar = "INSERT INTO detalle_cobros (id_contrato, periodo, concepto, monto_total, pagado) 
                    VALUES (?, ?, ?, ?, 0)";
        $stmt_gar = $pdo->prepare($sql_gar);
        $stmt_gar->execute([
            $id_contrato, 
            $hoy, 
            "Mes de Garantía - Casa " . $id_casa, 
            $garantia
        ]);

        // PASO C: Generar Primer Arriendo (Mes Vencido) 📅
        // Calculamos el primer día del mes siguiente a la fecha de inicio
 /*        $fecha_primer_cobro = date('Y-m-01', strtotime($inicio . ' +1 month'));
        
        $sql_arr = "INSERT INTO detalle_cobros (id_contrato, periodo, concepto, monto_total, pagado) 
                    VALUES (?, ?, ?, ?, 0)";
        $stmt_arr = $pdo->prepare($sql_arr);
        $stmt_arr->execute([
            $id_contrato, 
            $fecha_primer_cobro, 
            "Arriendo Mensual - Casa " . $id_casa, 
            $monto
        ]); */

        // PASO D: Actualizar estado de la Casa 🏠🔒
        $sql_upd = "UPDATE casas SET estado = 'Ocupada' WHERE id_casa = ?";
        $stmt_upd = $pdo->prepare($sql_upd);
        $stmt_upd->execute([$id_casa]);

        // Si llegamos aquí sin errores, guardamos todo permanentemente
        $pdo->commit();
        header("Location: ../gestion_contratos.php?success=1");

    } catch (Exception $e) {
        // Si algo falla, se deshacen todos los pasos (A, B, C y D) 🔙
        $pdo->rollBack();
        // Opcional: imprimir $e->getMessage() para debuggear
        header("Location: ../gestion_contratos.php?error=1");
    }
    break;
}