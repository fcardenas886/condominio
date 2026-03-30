<?php
session_start();
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
    // Si el switch está marcado, será 'Anticipado', si no, será 'Vencido'
    $modalidad = (isset($_POST['modalidad_cobro'])) ? 'Anticipado' : 'Vencido';
    try {
        $pdo->beginTransaction();

        // PASO A: Insertar el Contrato 📜
        $sql_ins = "INSERT INTO contratos (id_casa, id_arrendatario, monto_fijo, monto_garantia, fecha_inicio, fecha_termino, activo, modalidad_cobro) 
                    VALUES (?, ?, ?, ?, ?, ?, 1, ?)";
        $stmt_ins = $pdo->prepare($sql_ins);
        $stmt_ins->execute([$id_casa, $id_arr, $monto, $garantia, $inicio, $termino, $modalidad]);
        
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

    case 'finalizar':
    $id_contrato = $_POST['id_contrato'];
    $id_casa     = $_POST['id_casa'];
    $motivo      = $_POST['motivo'];
    $devuelto    = $_POST['monto_devuelto'];
    $detalles    = $_POST['detalles'];
    $id_admin    = $_SESSION['id_admin']; 

    try {
        $pdo->beginTransaction();

        // 1. Insertar en la tabla de cierres (Auditoría)
        $sql_cierre = "INSERT INTO cierres_contrato (id_contrato, motivo, detalle_entrega, monto_garantia_devuelto, id_usuario_autoriza) 
                       VALUES (?, ?, ?, ?, ?)";
        $pdo->prepare($sql_cierre)->execute([$id_contrato, $motivo, $detalles, $devuelto, $id_admin]);

        // 2. Desactivar el Contrato
        $pdo->prepare("UPDATE contratos SET activo = 0 WHERE id_contrato = ?")->execute([$id_contrato]);

        // 3. Liberar la Casa
        $pdo->prepare("UPDATE casas SET estado = 'Disponible' WHERE id_casa = ?")->execute([$id_casa]);

        $pdo->commit();
        header("Location: ../gestion_contratos.php?success=finalizado");
    } catch (Exception $e) {
        $pdo->rollBack();
        header("Location: ../gestion_contratos.php?error=1");
    }
    break;
}