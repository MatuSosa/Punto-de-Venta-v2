<?php
session_start();
require_once "../../conexion.php";
require_once 'fpdf/fpdf.php';

// Cambiar el tamaño del documento a A4 (210 x 297 mm)
$pdf = new FPDF('P', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetMargins(10, 10, 10);
$pdf->SetTitle("Ventas");
$pdf->SetFont('Arial', 'B', 16);
$id = $_GET['v'];
$idcliente = $_GET['cl'];

// Consulta de configuración
$config = $conexion->query("SELECT * FROM configuracion");
$datos = $config->fetch(PDO::FETCH_ASSOC);

// Consulta del cliente
$clientes = $conexion->prepare("SELECT * FROM cliente WHERE idcliente = :idcliente");
$clientes->bindParam(':idcliente', $idcliente, PDO::PARAM_INT);
$clientes->execute();
$datosC = $clientes->fetch(PDO::FETCH_ASSOC);

// Consulta de ventas
$ventas = $conexion->prepare("SELECT d.*, p.codproducto, p.descripcion FROM detalle_venta d INNER JOIN producto p ON d.id_producto = p.codproducto WHERE d.id_venta = :id");
$ventas->bindParam(':id', $id, PDO::PARAM_INT);
$ventas->execute();

// Recuadro con "X" grande de presupuesto
$pdf->SetFont('Arial', 'B', 50);
$pdf->SetTextColor(0, 0, 0);  // Color negro
$width = 20;
$height = 20;
$x = ($pdf->GetPageWidth() - $width) / 2;
$y = 30;
$pdf->Rect($x, $y, $width, $height, 'D'); // Recuadro centrado
$pdf->SetXY($x, $y + 2);
$pdf->Cell($width, $height, 'X', 0, 0, 'C');
$pdf->SetTextColor(0, 0, 0); // Asegurar que el color del texto se restaure
$pdf->Ln(20);
// Líneas horizontales al costado del recuadro
$pdf->Line($x - 100, $y + $height / 2, $x, $y + $height / 2); // Línea izquierda
$pdf->Line($x + $width, $y + $height / 2, $x + $width + 100, $y + $height / 2); // Línea derecha
$pdf->Ln(20);

// Encabezado
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, utf8_decode($datos['nombre']), 0, 1, 'C');
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, utf8_decode("Teléfono: " . $datos['telefono']), 0, 1, 'L');
$pdf->Cell(0, 10, utf8_decode("Dirección: " . $datos['direccion']), 0, 1, 'L');
$pdf->Cell(0, 10, utf8_decode("Correo: " . $datos['email']), 0, 1, 'L');
$pdf->Image("../../assets/img/logo.png", 170, 60, 20, 20, 'PNG'); 

$pdf->Ln(10);

// Datos del cliente
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, "Datos del cliente", 1, 1, 'C', 1);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, utf8_decode('Nombre: ' . $datosC['nombre']), 0, 1, 'L');
$pdf->Cell(0, 10, utf8_decode('Teléfono: ' . $datosC['telefono']), 0, 1, 'L');
$pdf->Cell(0, 10, utf8_decode('Dirección: ' . $datosC['direccion']), 0, 1, 'L');
$pdf->Ln(10);

// Detalle de Producto
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, "Detalle de Producto", 1, 1, 'C', 1);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(90, 10, utf8_decode('Descripción'), 1, 0, 'L');
$pdf->Cell(30, 10, 'Cant.', 1, 0, 'L');
$pdf->Cell(35, 10, 'Precio Unit', 1, 0, 'L');
$pdf->Cell(35, 10, 'Sub Total', 1, 1, 'L');
$total = 0.00;
$desc = 0.00;
while ($row = $ventas->fetch(PDO::FETCH_ASSOC)) {
    $pdf->Cell(90, 10, utf8_decode($row['descripcion']), 1, 0, 'L');
    $pdf->Cell(30, 10, $row['cantidad'], 1, 0, 'L');
    $pdf->Cell(35, 10, '$' . number_format($row['precio'], 2, ',', '.'), 1, 0, 'L');
    
    $sub_total = $row['total'];
    $total = $total + $sub_total;
    $desc = $desc + $row['descuento'];
    
    $pdf->Cell(35, 10, '$' . number_format($sub_total, 2, ',', '.'), 1, 1, 'L');
}
$pdf->Ln(10);

// Totales
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Descuento Total', 0, 1, 'R');
$pdf->SetFont('Arial', '', 12);
$desc_formatted = number_format($desc, 2, ',', '.') . " %";
$pdf->Cell(0, 10, $desc_formatted, 0, 1, 'R');

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Total a Pagar', 0, 1, 'R');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, '$' . number_format($total, 2, '.', ','), 0, 1, 'R');

$pdf->Output("ventas.pdf", "I");
?>
