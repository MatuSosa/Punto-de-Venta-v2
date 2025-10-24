<?php
include "../conexion.php";

try {
    // Consulta para obtener productos con cantidad menor o igual al stock mínimo configurado
    // Si no tiene stock_minimo configurado, usa 5 por defecto
    $query = $conexion->query("SELECT * FROM producto WHERE cantidad <= COALESCE(stock_minimo, 5) ORDER BY (cantidad - COALESCE(stock_minimo, 5)) ASC");
    $data = $query->fetchAll(PDO::FETCH_ASSOC);

    // Devolver el resultado como JSON
    header('Content-Type: application/json');
    echo json_encode($data);

} catch (PDOException $e) {
    // Manejo de errores
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}
?>
