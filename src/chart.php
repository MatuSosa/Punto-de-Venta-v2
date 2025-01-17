<?php
require_once "../conexion.php";

if ($_POST['action'] == 'sales') {
    $arreglo = array();

    // Consulta para obtener productos con cantidad menor o igual a 10
    $query = $conexion->query("SELECT descripcion, cantidad FROM producto WHERE cantidad <= 10 ORDER BY cantidad ASC LIMIT 10");

    while ($data = $query->fetch(PDO::FETCH_ASSOC)) {
        $arreglo[] = $data;
    }

    echo json_encode($arreglo);
    die();
}

if ($_POST['action'] == 'polarChart') {
    $arreglo = array();

    // Consulta para obtener los productos más vendidos
    $query = $conexion->query("SELECT p.codproducto, p.descripcion, d.id_producto, d.cantidad, SUM(d.cantidad) as total FROM producto p INNER JOIN detalle_venta d ON p.codproducto = d.id_producto GROUP BY d.id_producto ORDER BY total DESC LIMIT 5");

    while ($data = $query->fetch(PDO::FETCH_ASSOC)) {
        $arreglo[] = $data;
    }

    echo json_encode($arreglo);
    die();
}
?>
