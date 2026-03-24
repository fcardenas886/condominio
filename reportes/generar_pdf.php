<?php
require('../libs/fpdf.php'); 
include '../includes/conexion.php';

$id_t = isset($_GET['transaccion']) ? $_GET['transaccion'] : '';

if (empty($id_t)) {
    die("Error: Folio de transacción no válido.");
}

// 1. Consulta agrupada
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

$info = $pagos[0]; // Datos comunes (Arrendatario, Casa, Fecha)

// --- INICIO DE PDF ---
$pdf = new FPDF('P', 'mm', array(100, 160)); // Un poquito más alto por si hay muchos ítems
$pdf->AddPage();
$pdf->SetAutoPageBreak(false);
$pdf->SetMargins(10, 10, 10);

// Encabezado con Estilo
$pdf->SetFont('Arial', 'B', 15);
$pdf->SetTextColor(0, 102, 204); // Azul CondoPro
$pdf->Cell(0, 10, utf8_decode('CondoPro'), 0, 1, 'C');
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetTextColor(100, 100, 100);
$pdf->Cell(0, 5, utf8_decode('COMPROBANTE DE PAGO'), 0, 1, 'C');
$pdf->Ln(5);

// Bloque de Información de Transacción
$pdf->SetFillColor(245, 245, 245);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(40, 6, utf8_decode(' FOLIO: ') . $id_t, 0, 0, 'L', true);
$pdf->Cell(40, 6, utf8_decode(' FECHA: ') . date("d/m/Y", strtotime($info['fecha_pago'])), 0, 1, 'R', true);
$pdf->Ln(4);

// Datos del Arrendatario
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(0, 5, utf8_decode('ARRENDATARIO:'), 0, 1);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, utf8_decode($info['arrendatario']), 0, 1);
$pdf->Cell(0, 6, utf8_decode('Propiedad: Casa N° ') . $info['numero_casa'], 0, 1);
$pdf->Ln(5);

// Tabla de Detalle (Aquí listamos todo lo pagado)
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetDrawColor(200, 200, 200);
$pdf->Cell(55, 7, utf8_decode('Descripción'), 'B', 0, 'L');
$pdf->Cell(25, 7, utf8_decode('Monto'), 'B', 1, 'R');

$pdf->SetFont('Arial', '', 8);
$total_final = 0;

foreach ($pagos as $p) {
    // Si la descripción es muy larga, MultiCell o acortar
    $concepto = (strlen($p['concepto']) > 30) ? substr($p['concepto'], 0, 27) . "..." : $p['concepto'];
    
    $pdf->Cell(55, 7, utf8_decode($concepto), 0, 0, 'L');
    $pdf->Cell(25, 7, '$' . number_format($p['monto_pagado'], 0, ',', '.'), 0, 1, 'R');
    $total_final += $p['monto_pagado'];
}

// Línea divisoria para el total
$pdf->Ln(2);
$pdf->SetDrawColor(0, 102, 204);
$pdf->SetLineWidth(0.5);
$pdf->Cell(80, 0, '', 'T', 1);
$pdf->Ln(2);

// TOTAL
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetTextColor(40, 167, 69); // Verde éxito
$pdf->Cell(45, 10, 'TOTAL PAGADO:', 0, 0, 'L');
$pdf->Cell(35, 10, '$' . number_format($total_final, 0, ',', '.'), 0, 1, 'R');

// Notas (Si el primer pago tiene notas, las mostramos)
if(!empty($info['notas'])){
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