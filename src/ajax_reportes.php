<?php
session_start();
require_once "../conexion.php";

// ============================================
// GENERACIÓN DE REPORTES
// ============================================
if (isset($_POST['generarReporte'])) {
    $fecha_inicio = $_POST['fecha_inicio'] . ' 00:00:00';
    $fecha_fin = $_POST['fecha_fin'] . ' 23:59:59';
    $usuario = $_POST['usuario'];
    $producto = $_POST['producto'];
    $turno = $_POST['turno'];
    $metodo_pago = $_POST['metodo_pago'];
    
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
    
    // Calcular resumen
    $total_ventas = count($ventas);
    $total_facturado = 0;
    $cantidad_productos = 0;
    
    foreach ($ventas as &$venta) {
        $total_facturado += $venta['total'];
        
        // Separar fecha y hora
        $datetime = new DateTime($venta['fecha']);
        $venta['fecha'] = $datetime->format('Y-m-d');
        $venta['hora'] = $datetime->format('H:i:s');
        $venta['usuario'] = $venta['usuario_nombre'] ?: 'Desconocido';
        $venta['cliente'] = $venta['cliente_nombre'] ?: 'General';
        $venta['metodo_pago'] = $venta['metodo_pago'] ?: 'efectivo';
        $venta['turno'] = $venta['turno'] ?: '-';
        $venta['productos'] = $venta['productos'] ?: 'Sin detalles';
        $venta['id_cliente'] = $venta['id_cliente'];
        
        // Contar cantidad de productos vendidos
        $countQuery = $conexion->prepare("SELECT SUM(cantidad) as total FROM detalle_venta WHERE id_venta = ?");
        $countQuery->execute([$venta['id']]);
        $countResult = $countQuery->fetch(PDO::FETCH_ASSOC);
        $cantidad_productos += $countResult['total'] ?: 0;
    }
    
    $promedio_venta = $total_ventas > 0 ? $total_facturado / $total_ventas : 0;
    
    $resumen = [
        'total_ventas' => $total_ventas,
        'total_facturado' => $total_facturado,
        'cantidad_productos' => $cantidad_productos,
        'promedio_venta' => $promedio_venta
    ];
    
    echo json_encode([
        'resumen' => $resumen,
        'ventas' => $ventas
    ]);
    exit();
}
?>
