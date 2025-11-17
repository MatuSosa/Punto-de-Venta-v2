<?php
session_start();
require_once "../conexion.php";

// Función para formatear números al estilo argentino
function formatearNumeroAR($numero) {
    return number_format($numero, 2, ',', '.');
}

function formatearMonedaAR($numero) {
    return '$' . formatearNumeroAR($numero);
}

// Obtener parámetros
$tipo = $_GET['tipo'] ?? 'excel';
$fecha_inicio = $_GET['fecha_inicio'] . ' 00:00:00';
$fecha_fin = $_GET['fecha_fin'] . ' 23:59:59';
$usuario = $_GET['usuario'] ?? '';
$producto = $_GET['producto'] ?? '';
$turno = $_GET['turno'] ?? '';
$metodo_pago = $_GET['metodo_pago'] ?? '';

// Construir la consulta dinámica
$sql = "SELECT v.id, v.fecha, v.total, v.id_cliente, v.metodo_pago, v.turno, 
        u.nombre as usuario_nombre, c.nombre as cliente_nombre,
        GROUP_CONCAT(p.descripcion || ' (x' || dv.cantidad || ')' , ', ') as productos
        FROM ventas v
        LEFT JOIN usuario u ON v.id_usuario = u.idusuario
        LEFT JOIN cliente c ON v.id_cliente = c.idcliente
        LEFT JOIN detalle_venta dv ON v.id = dv.id_venta
        LEFT JOIN producto p ON dv.id_producto = p.codproducto
        WHERE v.fecha BETWEEN :fecha_inicio AND :fecha_fin";

// Agregar filtros opcionales
if (!empty($usuario)) {
    $sql .= " AND v.id_usuario = :usuario";
}
if (!empty($producto)) {
    $sql .= " AND dv.id_producto = :producto";
}
if (!empty($turno)) {
    $sql .= " AND v.turno = :turno";
}
if (!empty($metodo_pago)) {
    $sql .= " AND v.metodo_pago = :metodo_pago";
}

$sql .= " GROUP BY v.id ORDER BY v.fecha DESC";

$query = $conexion->prepare($sql);
$query->bindParam(':fecha_inicio', $fecha_inicio, PDO::PARAM_STR);
$query->bindParam(':fecha_fin', $fecha_fin, PDO::PARAM_STR);

if (!empty($usuario)) {
    $query->bindParam(':usuario', $usuario, PDO::PARAM_INT);
}
if (!empty($producto)) {
    $query->bindParam(':producto', $producto, PDO::PARAM_INT);
}
if (!empty($turno)) {
    $query->bindParam(':turno', $turno, PDO::PARAM_STR);
}
if (!empty($metodo_pago)) {
    $query->bindParam(':metodo_pago', $metodo_pago, PDO::PARAM_STR);
}

$query->execute();
$ventas = $query->fetchAll(PDO::FETCH_ASSOC);

// Calcular totales
$total_ventas = count($ventas);
$total_facturado = 0;
$cantidad_productos = 0;

foreach ($ventas as $venta) {
    $total_facturado += $venta['total'];
    
    $countQuery = $conexion->prepare("SELECT SUM(cantidad) as total FROM detalle_venta WHERE id_venta = ?");
    $countQuery->execute([$venta['id']]);
    $countResult = $countQuery->fetch(PDO::FETCH_ASSOC);
    $cantidad_productos += $countResult['total'] ?: 0;
}

$promedio_venta = $total_ventas > 0 ? $total_facturado / $total_ventas : 0;

if ($tipo == 'excel') {
    // ============================================
    // EXPORTAR A EXCEL (CSV)
    // ============================================
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=reporte_ventas_' . date('Y-m-d_His') . '.csv');
    
    $output = fopen('php://output', 'w');
    
    // BOM para UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Encabezado del reporte
    fputcsv($output, ['REPORTE DE VENTAS']);
    fputcsv($output, ['Período', $_GET['fecha_inicio'] . ' al ' . $_GET['fecha_fin']]);
    fputcsv($output, []);
    
    // Resumen
    fputcsv($output, ['RESUMEN']);
    fputcsv($output, ['Total de Ventas', $total_ventas]);
    fputcsv($output, ['Total Facturado', formatearMonedaAR($total_facturado)]);
    fputcsv($output, ['Productos Vendidos', $cantidad_productos]);
    fputcsv($output, ['Promedio por Venta', formatearMonedaAR($promedio_venta)]);
    fputcsv($output, []);
    
    // Encabezados de tabla
    fputcsv($output, ['ID Venta', 'Fecha', 'Hora', 'Usuario', /* 'Cliente', */ 'Productos', 'Método Pago', 'Total', 'Turno']);
    
    // Datos
    foreach ($ventas as $venta) {
        $datetime = new DateTime($venta['fecha']);
        fputcsv($output, [
            $venta['id'],
            $datetime->format('Y-m-d'),
            $datetime->format('H:i:s'),
            $venta['usuario_nombre'] ?: 'Desconocido',
            // $venta['cliente_nombre'] ?: 'General',
            $venta['productos'] ?: 'Sin detalles',
            $venta['metodo_pago'] ?: 'efectivo',
            formatearMonedaAR($venta['total']),
            $venta['turno'] ?: '-'
        ]);
    }
    
    fclose($output);
    exit();
    
} else if ($tipo == 'pdf') {
    // ============================================
    // EXPORTAR A PDF
    // ============================================
    
    require_once('tcpdf/tcpdf.php');
    
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    
    // Información del documento
    $pdf->SetCreator('Punto de Venta');
    $pdf->SetAuthor('Sistema POS');
    $pdf->SetTitle('Reporte de Ventas');
    
    // Configuración
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetAutoPageBreak(TRUE, 15);
    
    $pdf->AddPage();
    
    // Título
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'REPORTE DE VENTAS', 0, 1, 'C');
    
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 5, 'Período: ' . $_GET['fecha_inicio'] . ' al ' . $_GET['fecha_fin'], 0, 1, 'C');
    $pdf->Ln(5);
    
    // Resumen en tabla
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 7, 'RESUMEN', 0, 1, 'L');
    
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetFillColor(240, 240, 240);
    
    $pdf->Cell(90, 6, 'Total de Ventas:', 1, 0, 'L', true);
    $pdf->Cell(90, 6, $total_ventas, 1, 1, 'R');
    
    $pdf->Cell(90, 6, 'Total Facturado:', 1, 0, 'L', true);
    $pdf->Cell(90, 6, formatearMonedaAR($total_facturado), 1, 1, 'R');
    
    $pdf->Cell(90, 6, 'Productos Vendidos:', 1, 0, 'L', true);
    $pdf->Cell(90, 6, $cantidad_productos, 1, 1, 'R');
    
    $pdf->Cell(90, 6, 'Promedio por Venta:', 1, 0, 'L', true);
    $pdf->Cell(90, 6, formatearMonedaAR($promedio_venta), 1, 1, 'R');
    
    $pdf->Ln(5);
    
    // Tabla de ventas
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 7, 'DETALLE DE VENTAS', 0, 1, 'L');
    
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->SetFillColor(200, 200, 200);
    
    $pdf->Cell(15, 6, 'ID', 1, 0, 'C', true);
    $pdf->Cell(30, 6, 'Fecha', 1, 0, 'C', true);
    $pdf->Cell(40, 6, 'Usuario', 1, 0, 'C', true);
    // $pdf->Cell(30, 6, 'Cliente', 1, 0, 'C', true);
    $pdf->Cell(35, 6, 'Método Pago', 1, 0, 'C', true);
    $pdf->Cell(30, 6, 'Total', 1, 0, 'C', true);
    $pdf->Cell(30, 6, 'Turno', 1, 1, 'C', true);
    
    $pdf->SetFont('helvetica', '', 7);
    
    foreach ($ventas as $venta) {
        $datetime = new DateTime($venta['fecha']);
        
        $pdf->Cell(15, 5, $venta['id'], 1, 0, 'C');
        $pdf->Cell(30, 5, $datetime->format('Y-m-d'), 1, 0, 'C');
        $pdf->Cell(40, 5, substr($venta['usuario_nombre'] ?: 'Desconocido', 0, 25), 1, 0, 'L');
        // $pdf->Cell(30, 5, substr($venta['cliente_nombre'] ?: 'General', 0, 20), 1, 0, 'L');
        $pdf->Cell(35, 5, $venta['metodo_pago'] ?: 'efectivo', 1, 0, 'C');
        $pdf->Cell(30, 5, formatearMonedaAR($venta['total']), 1, 0, 'R');
        $pdf->Cell(25, 5, $venta['turno'] ?: '-', 1, 1, 'C');
    }
    
    // Salida del PDF
    $pdf->Output('reporte_ventas_' . date('Y-m-d_His') . '.pdf', 'D');
    exit();
}
?>
