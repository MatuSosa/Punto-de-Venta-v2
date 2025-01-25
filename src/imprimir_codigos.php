<?php
session_start();
require_once "../conexion.php";
require_once 'tcpdf/tcpdf.php';

// Consulta de productos
$query = $conexion->query("SELECT codigo, descripcion FROM producto");
$productos = $query->fetchAll(PDO::FETCH_ASSOC);

class PDF extends TCPDF
{
    public function Header()
    {
        $this->SetFont('helvetica', 'B', 12);
        $this->Cell(0, 10, 'Códigos de Barras', 0, 1, 'C');
        $this->Ln(10);
    }

    public function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Página ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, 0, 'C');
    }

    public function Barcode($codigo, $descripcion)
    {
        $this->SetFont('helvetica', '', 12);
        $this->Cell(0, 10, $descripcion, 0, 1, 'L');
        //se eliminó la linea que generaba la imagen del barcode
        $this->Ln(10);
    }
}

$pdf = new PDF();
$pdf->AddPage();

foreach ($productos as $producto) {
    $pdf->Barcode($producto['codigo'], $producto['descripcion']);
}

$pdf->Output('codigos_de_barras.pdf', 'I');
?>
