<?php
require_once __DIR__ . '/../src/protector.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../src/lib/fpdf.php';

$producto_id = $_GET['producto_id'] ?? null;
if (!$producto_id) {
    die("Producto no especificado.");
}

// Obtener datos del producto
$stmt_prod = $conn->prepare("SELECT * FROM productos WHERE id = ?");
$stmt_prod->execute([$producto_id]);
$producto = $stmt_prod->fetch(PDO::FETCH_ASSOC);

// Obtener movimientos
$stmt_mov = $conn->prepare("
    SELECT m.*, u.nombre as usuario_nombre 
    FROM movimientos_inventario m
    LEFT JOIN usuarios u ON m.usuario_id = u.id
    WHERE m.producto_id = ? 
    ORDER BY m.fecha_movimiento ASC, m.created_at ASC
");
$stmt_mov->execute([$producto_id]);
$movimientos = $stmt_mov->fetchAll(PDO::FETCH_ASSOC);

// Clase extendida para crear Header y Footer
class PDF extends FPDF
{
    // Cabecera de página
    function Header()
    {
        // Logo
        $this->Image('../public/img/logo.png', 10, 8, 33);
        // Arial bold 15
        $this->SetFont('Arial', 'B', 15);
        // Movernos a la derecha
        $this->Cell(80);
        // Título
        $this->Cell(30, 10, 'Kardex de Producto', 0, 0, 'C');
        // Subtítulo
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(1, 25, '(Historial de Movimientos)', 0, 0, 'C');
        // Fecha
        $this->SetFont('Arial', '', 10);
        $this->Cell(70, 10, 'Fecha: ' . date('d/m/Y'), 0, 0, 'R');
        // Salto de línea
        $this->Ln(20);
    }

    // Pie de página
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

// Creación del PDF
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);
$pdf->Ln(10);

// Info del Producto
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, utf8_decode($producto['descripcion']), 0, 1);
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 8, 'Codigo Profit: ' . utf8_decode($producto['codigo_profit']), 0, 1);
$pdf->Cell(0, 8, 'Stock Actual: ' . $producto['stock'] . ' unidades', 0, 1);
$pdf->Ln(5);

// Cabecera de la tabla de movimientos
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(230, 230, 230);
$pdf->Cell(25, 10, 'Fecha', 1, 0, 'C', true);
$pdf->Cell(25, 10, 'Tipo', 1, 0, 'C', true);
$pdf->Cell(20, 10, 'Cantidad', 1, 0, 'C', true);
$pdf->Cell(80, 10, 'Documento Ref.', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Usuario', 1, 1, 'C', true);

// Datos de la tabla
$pdf->SetFont('Arial', '', 9);
foreach ($movimientos as $mov) {
    $pdf->Cell(25, 8, date("d/m/Y", strtotime($mov['fecha_movimiento'])), 1);
    $pdf->Cell(25, 8, strtoupper($mov['tipo']), 1);
    $pdf->Cell(20, 8, $mov['cantidad'], 1, 0, 'C');
    $pdf->Cell(80, 8, utf8_decode($mov['doc_referencia']), 1);
    $pdf->Cell(40, 8, utf8_decode($mov['usuario_nombre']), 1, 1);
}

// --- LÍNEA FINAL MODIFICADA ---
// Limpiar el nombre del producto para que sea un nombre de archivo válido
$nombre_archivo = preg_replace('/[^a-zA-Z0-9-_\.]/', '_', $producto['descripcion']);
$fecha_actual = date('Y-m-d');

// Nombre del archivo de salida: Kardex_Descripcion-Del-Producto_Fecha.pdf
$pdf->Output('D', 'Kardex_' . $nombre_archivo . '_' . $fecha_actual . '.pdf');
?>