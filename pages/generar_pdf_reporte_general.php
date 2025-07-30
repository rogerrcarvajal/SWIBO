<?php
require_once __DIR__ . '/../src/protector.php';
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/lib/fpdf.php';
date_default_timezone_set('America/Caracas');

// La misma consulta poderosa del reporte en pantalla
$sql = "SELECT p.codigo_profit, cp.nombre AS categoria_nombre, p.descripcion, p.ubicacion, p.stock,
               lm.fecha_movimiento, lm.tipo, lm.cantidad, lm.doc_referencia, u.nombre AS usuario_nombre
        FROM productos p
        JOIN categorias_producto cp ON p.categoria_id = cp.id
        LEFT JOIN LATERAL (
            SELECT * FROM movimientos_inventario mi WHERE mi.producto_id = p.id
            ORDER BY mi.fecha_movimiento DESC, mi.created_at DESC LIMIT 1
        ) lm ON true
        LEFT JOIN usuarios u ON lm.usuario_id = u.id
        ORDER BY cp.nombre, p.descripcion";
$reporte_data = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);

class PDF extends FPDF
{
    function Header()
    {
        $this->Image('../public/img/logo.png', 10, 8, 33);
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(276, 10, 'Reporte General de Inventario', 0, 1, 'C');
        $this->SetFont('Arial', '', 10);
        $this->Cell(276, 10, 'Listado de productos con su ultimo movimiento registrado. Fecha: ' . date('d/m/Y H:i'), 0, 1, 'C');
        $this->Ln(10);
    }
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

$pdf = new PDF('L', 'mm', 'A4'); // L para Landscape (apaisado)
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);

// Cabecera de la tabla
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetFillColor(230, 230, 230);
$pdf->Cell(20, 10, 'Codigo', 1, 0, 'C', true);
$pdf->Cell(35, 10, 'Categoria', 1, 0, 'C', true);
$pdf->Cell(70, 10, 'Descripcion', 1, 0, 'C', true);
$pdf->Cell(20, 10, 'Ubicacion', 1, 0, 'C', true);
$pdf->Cell(20, 10, 'Fecha Ult. Mov.', 1, 0, 'C', true);
$pdf->Cell(15, 10, 'Tipo', 1, 0, 'C', true);
$pdf->Cell(15, 10, 'Cant.', 1, 0, 'C', true);
$pdf->Cell(61, 10, 'Doc. Ref.', 1, 0, 'C', true);
$pdf->Cell(20, 10, 'Stock Actual', 1, 1, 'C', true);

// Datos de la tabla
$pdf->SetFont('Arial', '', 7);
foreach ($reporte_data as $item) {
    $pdf->Cell(20, 7, utf8_decode($item['codigo_profit']), 1);
    $pdf->Cell(35, 7, utf8_decode($item['categoria_nombre']), 1);
    $pdf->Cell(70, 7, utf8_decode($item['descripcion']), 1);
    $pdf->Cell(20, 7, utf8_decode($item['ubicacion']), 1);
    $pdf->Cell(20, 7, $item['fecha_movimiento'] ? date('d/m/Y', strtotime($item['fecha_movimiento'])) : 'N/A', 1, 0, 'C');
    $pdf->Cell(15, 7, $item['tipo'] ? strtoupper($item['tipo']) : 'N/A', 1, 0, 'C');
    $pdf->Cell(15, 7, $item['cantidad'], 1, 0, 'C');
    $pdf->Cell(61, 7, utf8_decode($item['doc_referencia']), 1);
    $pdf->Cell(20, 7, $item['stock'], 1, 1, 'C');
}

// --- LÍNEA FINAL MODIFICADA ---
$fecha_actual = date('Y-m-d');

// Nombre del archivo de salida: Reporte_General_Inventario_Fecha.pdf
$pdf->Output('D', 'Reporte_General_Inventario_' . $fecha_actual . '.pdf');
?>