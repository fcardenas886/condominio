<?php
include '../includes/conexion.php'; // Asegúrate de que use $pdo

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contratos_ids'])) {
    
    $ids = $_POST['contratos_ids']; 
    $periodo = $_POST['periodo_cobro']; 
    
    // 1. Preparar el texto del Mes y Año (Ej: "Abril 2026")
    $meses_nombre = [
        "01"=>"Enero", "02"=>"Febrero", "03"=>"Marzo", "04"=>"Abril",
        "05"=>"Mayo", "06"=>"Junio", "07"=>"Julio", "08"=>"Agosto",
        "09"=>"Septiembre", "10"=>"Octubre", "11"=>"Noviembre", "12"=>"Diciembre"
    ];
    $mes_num = date('m', strtotime($periodo));
    $anio_num = date('Y', strtotime($periodo));
    $texto_mes_anio = $meses_nombre[$mes_num] . " " . $anio_num;

    try {
        $pdo->beginTransaction(); 

        // 2. Preparamos el CALL una sola vez fuera del bucle para mayor eficiencia
        $stmt = $pdo->prepare("CALL sp_generar_cobro_arriendo(?, ?, ?)");

        foreach ($ids as $id) {
            // 3. Ejecutamos el procedimiento para cada ID
            $stmt->execute([
                $id, 
                $periodo, 
                $texto_mes_anio
            ]);
            
            // IMPORTANTE: Limpiar el cursor para la siguiente ejecución del procedimiento
            $stmt->closeCursor(); 
        }

        $pdo->commit(); // Guardamos todos los cobros generados ✅
        
        header("Location: ../generar_cobros.php?status=success&count=" . count($ids));
        exit();

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack(); // Si falla, deshacemos todo lo del bucle
        }
        die("Error al procesar los cobros: " . $e->getMessage());
    }

} else {
    header("Location: ../generar_cobros.php?status=error");
    exit();
}