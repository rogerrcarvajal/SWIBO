<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../../home.php");
    exit();
}
// Ajustar la ruta para que suba tres niveles desde /reportes/lib/
require_once "../../conn/conexion.php";
require('fpdf.php');

// --- OBTENER PERÍODO ESCOLAR ACTIVO ---
$periodo_stmt = $conn->query("SELECT id, nombre_periodo FROM periodos_escolares WHERE activo = TRUE LIMIT 1");
$periodo = $periodo_stmt->fetch(PDO::FETCH_ASSOC);
if (!$periodo) { die("No hay período activo."); }
$periodo_id = $periodo['id'];
$nombre_periodo = $periodo['nombre_periodo'];

// --- OBTENER PERSONAL ---
$profesores_sql = "SELECT p.nombre_completo, pp.posicion FROM profesor_periodo pp JOIN profesores p ON pp.profesor_id = p.id WHERE pp.periodo_id = :periodo_id ORDER BY pp.posicion, p.nombre_completo";
$profesores_stmt = $conn->prepare($profesores_sql);
$profesores_stmt->execute([':periodo_id' => $periodo_id]);
$profesores = $profesores_stmt->fetchAll(PDO::FETCH_ASSOC);

// --- OBTENER ESTUDIANTES ---
$estudiantes_sql = "SELECT nombre_completo, grado_ingreso FROM estudiantes WHERE activo = TRUE AND periodo_id = :periodo_id ORDER BY FIELD(grado_ingreso, 'Daycare', 'Prekinder 3', 'Prekinder 4', 'Kindergarten', '1er Grado', '2do Grado', '3er Grado', '4to Grado', '5to Grado', '6to Grado', '7mo Grado', '8vo Grado', '9no Grado', '10mo Grado', '11vo Grado', '12vo Grado'), nombre_completo";
$estudiantes_stmt = $conn->prepare($estudiantes_sql);
$estudiantes_stmt->execute([':periodo_id' => $periodo_id]);
$estudiantes_result = $estudiantes_stmt->fetchAll(PDO::FETCH_ASSOC);
$estudiantes_por_grado = [];
foreach ($estudiantes_result as $estudiante) {
    $grado = $estudiante['grado_ingreso'];
    if (!isset($estudiantes_por_grado[$grado])) {
        $estudiantes_por_grado[$grado] = [];
    }
    $estudiantes_por_grado[$grado][] = $estudiante;
}

// --- GENERACIÓN DEL PDF ---
class PDF extends FPDF
{
    // Cabecera de página
    function Header()
    {
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, 'Asociacion Civil Centro Educativo Internacional Anzoategui', 0, 1, 'C');
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 5, 'www.ceia.k12.org - Av. Jose Antonio Anzoategui, Km 98 - Anaco', 0, 1, 'C');
        $this->Ln(5);
    }

    // Pie de página
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo(), 0, 0, 'C');
    }
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 18);
$pdf->Cell(0, 10, 'Roster ' . $nombre_periodo, 0, 1, 'C');
$pdf->Ln(10);

// --- TABLA DE PERSONAL ---
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'Personal Administrativo y Docente', 0, 1);
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetFillColor(0, 74, 143);
$pdf->SetTextColor(255);
$pdf->Cell(95, 8, 'Especialidad / Cargo', 1, 0, 'C', true);
$pdf->Cell(95, 8, 'Nombre Completo', 1, 1, 'C', true);
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(0);
if (empty($profesores)) {
    $pdf->Cell(190, 10, 'No hay personal registrado para este periodo.', 1, 1, 'C');
} else {
    foreach ($profesores as $profesor) {
        $pdf->Cell(95, 7, utf8_decode($profesor['posicion']), 1);
        $pdf->Cell(95, 7, utf8_decode($profesor['nombre_completo']), 1);
        $pdf->Ln();
    }
}
$pdf->Ln(10);

// --- LISTADO DE ESTUDIANTES ---
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'Listado de Estudiantes por Grado', 0, 1);
$pdf->SetFont('Arial', '', 10);
$pdf->SetFillColor(240, 240, 240);

if (empty($estudiantes_por_grado)) {
    $pdf->Cell(0, 10, 'No hay estudiantes registrados para este periodo.', 0, 1, 'C');
} else {
    $column_width = 63; // Ancho para 3 columnas
    $y_start = $pdf->GetY();
    $x_start = $pdf->GetX();
    $current_col = 0;

    foreach($estudiantes_por_grado as $grado => $estudiantes) {
        $current_y = $pdf->GetY();
        $cell_height = 5 + (count($estudiantes) * 5) + 5; // Altura estimada del bloque
        
        if ($current_y + $cell_height > $pdf->GetPageHeight() - 20) { // Si no cabe, saltar
             $current_col++;
             if ($current_col > 2) {
                 $pdf->AddPage();
                 $current_col = 0;
                 $y_start = $pdf->GetY();
             }
             $pdf->SetY($y_start);
        }

        $pdf->SetX($x_start + ($current_col * $column_width));
        $y_before_block = $pdf->GetY();
        
        // Dibuja el bloque
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->SetFillColor(220, 220, 220);
        $pdf->Cell($column_width - 1, 7, utf8_decode($grado) . ' (' . count($estudiantes) . ')', 1, 2, 'C', true);
        $pdf->SetFont('Arial', '', 10);
        foreach($estudiantes as $estudiante) {
            $pdf->SetX($x_start + ($current_col * $column_width));
            $pdf->MultiCell($column_width - 1, 5, utf8_decode($estudiante['nombre_completo']), 'LR', 'L');
        }
        $pdf->SetX($x_start + ($current_col * $column_width));
        $pdf->Cell($column_width-1, 0, '', 'T', 1);

        $y_after_block = $pdf->GetY();
        
        // Gestionar salto de columna
        $current_col++;
        if ($current_col > 2) {
            $current_col = 0;
            $pdf->SetY($y_start);
            // Esto necesita una lógica más avanzada para encontrar el Y más alto de la fila,
            // por simplicidad, reiniciamos a una nueva línea después de 3 columnas
            $pdf->Ln(max($pdf->GetY(), $y_after_block) + 2); 
            $y_start = $pdf->GetY();
        } else {
            $pdf->SetY($y_before_block);
        }
    }
}
$pdf->Output('I', 'Roster_' . str_replace(' ', '_', $nombre_periodo) . '.pdf');
?>