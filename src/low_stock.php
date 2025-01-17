<?php
include "../conexion.php";

try {
    // Consulta para obtener productos con cantidad menor o igual a 3
    $query = $conexion->query("SELECT * FROM producto WHERE cantidad <= 3");
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
