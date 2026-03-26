<?php
require('../libs/fpdf.php'); 
include '../includes/conexion.php';

// Capturamos Transacción y el nuevo TOKEN
$id_t = isset($_GET['transaccion']) ? $_GET['transaccion'] : '';
$token_url = isset($_GET['token']) ? $_GET['token'] : '';

if (empty($id_t)) {
    die("Error: Folio de transacción no válido.");
}

// --- NUEVO: VALIDACIÓN DE SEGURIDAD POR TOKEN ---
// Buscamos si existe la transacción con ese token específico
$check = $pdo->prepare("SELECT id_pago FROM pagos WHERE id_transaccion = ? AND token = ? LIMIT 1");
$check->execute([$id_t, $token_url]);

if (!$check->fetch()) {
    // Si no hay coincidencia, bloqueamos el acceso
    die("<h3>Acceso Denegado:</h3> El enlace es inválido o no tiene permisos para ver este recibo.");
}

// 1. Consulta de datos (mantenemos tu lógica pero aseguramos traer el campo 'notas')
$sql = "SELECT p.*, dc.concepto, arr.nombre as arrendatario, cas.numero_casa
        FROM pagos p
        JOIN detalle_cobros dc ON p.id_detalle = dc.id_detalle
        JOIN contratos con ON dc.id_contrato = con.id_contrato
        JOIN arrendatarios arr ON con.id_arrendatario = arr.id_arrendatario
        JOIN casas cas ON con.id_casa = cas.id_casa
        WHERE p.id_transaccion = ?
        ORDER BY p.id_pago ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$id_t]);
$pagos = $stmt->fetchAll();

if (!$pagos) {
    die("Error: No se encontró información para el folio " . htmlspecialchars($id_t));
}

$info = $pagos[0]; 

// --- INICIO DE PDF ---
$pdf = new FPDF('P', 'mm', array(100, 165)); // Subimos a 165mm por si hay excedente
$pdf->AddPage();
$pdf->SetAutoPageBreak(false);
$pdf->SetMargins(10, 10, 10);

// Encabezado
$pdf->SetFont('Arial', 'B', 15);
$pdf->SetTextColor(0, 102, 204); 
$pdf->Cell(0, 10, utf8_decode('CondoPro'), 0, 1, 'C');
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetTextColor(100, 100, 100);
$pdf->Cell(0, 5, utf8_decode('COMPROBANTE DE PAGO'), 0, 1, 'C');
$pdf->Ln(5);

// Bloque Folio y Fecha
$pdf->SetFillColor(245, 245, 245);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(42, 6, utf8_decode(' FOLIO: ') . $id_t, 0, 0, 'L', true);
$pdf->Cell(38, 6, utf8_decode(' FECHA: ') . date("d/m/Y", strtotime($info['fecha_pago'])), 0, 1, 'R', true);
$pdf->Ln(4);

// Datos del Arrendatario
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(0, 5, utf8_decode('ARRENDATARIO:'), 0, 1);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, utf8_decode($info['arrendatario']), 0, 1);
$pdf->Cell(0, 6, utf8_decode('Propiedad: Casa N° ') . $info['numero_casa'], 0, 1);
$pdf->Ln(5);

// Tabla de Detalle
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetDrawColor(200, 200, 200);
$pdf->Cell(55, 7, utf8_decode('Descripción'), 'B', 0, 'L');
$pdf->Cell(25, 7, utf8_decode('Monto'), 'B', 1, 'R');

$pdf->SetFont('Arial', '', 8);
$total_final = 0;

foreach ($pagos as $p) {
    // --- LÓGICA DE EXCEDENTE ---
    // Si en las notas del pago dice "Excedente", cambiamos el nombre del concepto
    if (strpos($p['notas'], 'Excedente') !== false) {
        $concepto_texto = "Excedente (Saldo a Favor)";
        $pdf->SetTextColor(0, 128, 0); // Verde para el saldo a favor
    } else {
        $concepto_texto = $p['concepto'];
        $pdf->SetTextColor(0, 0, 0); // Negro para lo demás
    }

    // Acortar si es muy largo
    if(strlen($concepto_texto) > 30) {
        $concepto_texto = substr($concepto_texto, 0, 27) . "...";
    }
    
    $pdf->Cell(55, 7, utf8_decode($concepto_texto), 0, 0, 'L');
    $pdf->Cell(25, 7, '$' . number_format($p['monto_pagado'], 0, ',', '.'), 0, 1, 'R');
    $total_final += $p['monto_pagado'];
}

// Reset color
$pdf->SetTextColor(0, 0, 0);
$pdf->Ln(2);
$pdf->SetDrawColor(0, 102, 204);
$pdf->SetLineWidth(0.5);
$pdf->Cell(80, 0, '', 'T', 1);
$pdf->Ln(2);

// TOTAL
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetTextColor(40, 167, 69); 
$pdf->Cell(45, 10, 'TOTAL PAGADO:', 0, 0, 'L');
$pdf->Cell(35, 10, '$' . number_format($total_final, 0, ',', '.'), 0, 1, 'R');

// Notas finales (Limpiamos para que no repita la observación del excedente abajo)
if(!empty($info['notas']) && strpos($info['notas'], 'Excedente') === false){
    $pdf->Ln(2);
    $pdf->SetFont('Arial', 'I', 8);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->MultiCell(0, 4, utf8_decode("Obs: " . $info['notas']), 0, 'L');
}

// Pie de página
$pdf->SetY(-20);
$pdf->SetFont('Arial', 'I', 7);
$pdf->SetTextColor(180, 180, 180);
$pdf->Cell(0, 5, utf8_decode('Este es un comprobante automático de CondoPro'), 0, 1, 'C');
$pdf->Cell(0, 5, utf8_decode('Gracias por su pago.'), 0, 1, 'C');

$pdf->Output('I', 'Recibo_' . $id_t . '.pdf');