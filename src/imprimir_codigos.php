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
        $this->write1DBarcode($codigo, 'C128', '', '', 60, 18, 0.4, array('position'=>'S', 'align'=>'C', 'stretch'=>false, 'fitwidth'=>true, 'cellfitalign'=>'', 'border'=>true, 'hpadding'=>'', 'vpadding'=>'', 'fgcolor'=>array(0,0,0), 'bgcolor'=>false, 'text'=>true, 'label'=> $codigo, 'font'=>'helvetica', 'fontsize'=>8, 'stretchtext'=>2), 'N');
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
